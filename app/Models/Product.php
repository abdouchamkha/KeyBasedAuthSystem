<?php

namespace App\Models;

use App\ActiveType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['app_id','user_id','name', 'status','product_status'];
    protected $casts = [
        'status' => ActiveType::class,
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function app()
    {
        return $this->belongsTo(Application::class, 'app_id', 'id');
    }
}
