<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean',
    ];
      public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function carts()
{
    return $this->hasMany(Cart::class);
}
   public function wishlists()
{
    return $this->hasMany(Wishlist::class);
}
public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

}
