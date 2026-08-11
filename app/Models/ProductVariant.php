<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'sku',
        'color',
        'size',
        'price',
        'stock',
        'status'
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'boolean',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
      public function carts()
    {
        return $this->hasMany(Cart::class, 'product_variant_id');
    }
}
