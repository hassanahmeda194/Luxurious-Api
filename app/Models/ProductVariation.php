<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'size',
        'color',
        'variation_price',
        'stock_quantity',
        'image'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
