<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reference',
        'description',
        'prix_ttc',
        'unite',
    ];

    protected $casts = [
        'prix_ttc' => 'decimal:2',
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
}