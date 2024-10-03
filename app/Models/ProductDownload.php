<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDownload extends Model
{
    use HasFactory;
    protected $fillable = ['product_id','name', 'tags', 'path','type','file_extension'];
    public function product_id(){
        return $this->hasOne(Product::class);
    }
}
