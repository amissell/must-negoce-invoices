<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string|null $cost_price
 */
class Product extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'name',
        'reference',
        'description',
        'prix_ttc',
        'cost_price',
        'unite',
    ];
    protected $casts = [
        'prix_ttc' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {

            $lastProduct = Product::latest('id')->first();

            $nextNumber = $lastProduct
                ? $lastProduct->id + 1
                : 1;

            $product->reference =
                'PR-' . str_pad(
                    $nextNumber,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
        });
    }

    public function invoiceItems()
    {
        return $this->hasMany(
            InvoiceItem::class
        );
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'price', 'tax_rate', 'stock', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('product')
            ->setDescriptionForEvent(fn(string $eventName) => "Product {$this->name} was {$eventName}");
    }
}
