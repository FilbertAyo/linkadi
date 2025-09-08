<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
      use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock_quantity',
        'description',
        'category',
        'sku',
        'is_active',
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }
}
