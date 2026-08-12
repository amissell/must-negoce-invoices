<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    public const CACHE_KEY = 'company_settings.current';
    protected $fillable = [
        'name', 'logo_path', 'address', 'phone', 'email', 'website',
        'ice', 'if_number', 'rc', 'patente', 'cnss',
        'tva_rate', 'currency', 'invoice_prefix',
        'footer_text', 'legal_mentions', 'default_pdf_template',
    ];
    protected $casts = [
        'tva_rate' => 'decimal:2',
    ];
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->first() ?? static::create(['name' => 'Ma Société']);
        });
    }
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'public/')) {
            return asset(str_replace('public/', 'storage/', $this->logo_path));
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    public function getLogoPathForPdfAttribute(): string
    {
        if ($this->logo_path) {
            $absolute = Storage::disk('public')->path($this->logo_path);
            if (is_file($absolute)) {
                return $absolute;
            }
        }

        $fallback = public_path('images/must-negoce-logo.png');
        return is_file($fallback) ? $fallback : '';
    }
}
