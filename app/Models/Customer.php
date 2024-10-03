<?php

namespace App\Models;

use App\CustomerType;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = ['app_id','owner_id','customer_id','customer_type','invite_uuid','invite_status'];
    // the invite status can be either pending or joined defualt is pending
    protected $casts = [
        'customer_type' => CustomerType::class,
    ];
    protected static function boot()
    {
        parent::boot();

        // Automatically generate a unique UUID for the `invite_uuid` field before creating the customer
        static::creating(function ($customer) {
            $customer->invite_uuid = (string) Str::uuid();
        });
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function app()
    {
        return $this->belongsTo(Application::class, 'app_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
