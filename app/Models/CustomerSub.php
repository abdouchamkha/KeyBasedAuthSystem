<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSub extends Model
{
    use HasFactory;
    protected $fillable = ['customer_id','app_id','product_id','subscription_type','permissions',];
    public function app()
    {
        return $this->belongsTo(Application::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function product_id()
    {
        return $this->hasOne(Product::class,'id','product_id');
    }
    public function subDuration()
    {
        return $this->hasOne(CustomerSubDuration::class,'subscripton_id','id');
    }
}
