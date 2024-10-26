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
        'deleted_by',
        'deleted_at',
        'updated_by',
        'created_by',
        'app_id',
    ];

    protected $casts = [
        'labels' => 'array',
        'tags' => 'array',
    ];
     // Relationship for the user who uploaded the file, fetching only the 'name'
     public function uploadedBy()
     {
         return $this->hasOne(User::class, 'id', 'created_by')->select(['id', 'name']);
     }

     // Relationship for the user who last updated the file, fetching only the 'name'
     public function updatedBy()
     {
         return $this->hasOne(User::class, 'id', 'updated_by')->select(['id', 'name']);
     }

     // Relationship for the user who deleted the file, fetching only the 'name'
     public function deletedBy()
     {
         return $this->hasOne(User::class, 'id', 'deleted_by')->select(['id', 'name']);
     }
     // Relationship for the user who deleted the file, fetching only the 'name'
     public function createdBy()
     {
         return $this->hasOne(User::class, 'id', 'created_by')->select(['id', 'name']);
     }
     // Many-to-Many Relationship with Products
     public function products()
     {
         return $this->belongsToMany(Product::class, 'file_product', 'product_download_id', 'product_id');
     }
}
