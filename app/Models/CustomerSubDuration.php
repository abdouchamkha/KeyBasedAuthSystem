<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSubDuration extends Model
{
    use HasFactory;
    protected $fillable = ['subscripton_id','started_at','end_at', 'days_left',];

    public function subscripton_id(){
        return $this->hasOne(CustomerSub::class,'subscripton_id');
    }
    public function customer()
    {
        return $this->hasOneThrough(Customer::class, CustomerSub::class, 'id', 'id', 'subscripton_id', 'customer_id');
    }
}
