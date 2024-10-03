<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;
    protected $fillable = [
        'app_id', 'product_id', 'user_id', 'license_value', 'uuid_value',
        'frozen_at', 'banned_at', 'started_at', 'end_at', 'extra_time',
        'subscription_duration','hwid_lock','freeze_type','unfreeze_at'
    ];
    protected $casts = [
        'started_at' => 'datetime',
        'end_at' => 'datetime',
        'frozen_at' => 'datetime',
        'unfreeze_at' => 'datetime',
        'banned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function product(){
        return $this->hasOne('App\Models\Product','id','product_id');
    }
    public function app(){
        return $this->hasOne('App\Models\Application');
    }
    public function user(){
        return $this->hasOne('App\Models\User');
    }
    public function sessions(){
        return $this->hasMany('App\Models\LicenseSession','license_id','id');
    }
    public function hwid(){
        return $this->hasOne('App\Models\LicenseSession','license_id','id');
    }
    // Convert to hours
    public function setExtraTime($days=0,$hours=0,$minutes=0)
    {
        $days=$days*24;
        $minutes= ceil($minutes/60);
        $this->extra_time = $hours+$days+$minutes;
    }

    public function setSubscriptionDuration($days=0,$hours=0,$minutes=0)
    {
        $days=$days*24;
        $minutes= ceil($minutes/60);
        $this->subscription_duration = $hours+$days+$minutes;
    }

    // Convert hours back to days, hours, or minutes
    public function getExtraTime($unit = 'hours')
    {
        if ($unit === 'days') {
            return $this->extra_time / 24; // Convert hours to days
        } elseif ($unit === 'hours') {
            return $this->extra_time; // Already in hours
        } elseif ($unit === 'minutes') {
            return $this->extra_time * 60; // Convert hours to minutes
        }

        return $this->extra_time; // Return hours by default
    }

    public function getSubscriptionDuration($unit = 'hours')
    {
        if ($unit === 'days') {
            return $this->subscription_duration / 24; // Convert hours to days
        } elseif ($unit === 'hours') {
            return $this->subscription_duration; // Already in hours
        } elseif ($unit === 'minutes') {
            return $this->subscription_duration * 60; // Convert hours to minutes
        }

        return $this->subscription_duration; // Return hours by default
    }
}
