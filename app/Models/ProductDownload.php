<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDownload extends Model
{
    use HasFactory;
    protected $fillable = [
        'path',
        'name',
        'product_id',
        'file_extension',
        'labels',
        'tags',
    ];

    protected $casts = [
        'labels' => 'array',
        'tags' => 'array',
    ];
       public function product_id(){
        return $this->hasOne(Product::class);
    }
    // New Many-to-Many Relationship
    public function products()
    {
        return $this->belongsToMany(Product::class, 'file_product', 'product_download_id', 'product_id');
    }
}
