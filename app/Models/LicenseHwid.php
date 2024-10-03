<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseHwid extends Model
{
    use HasFactory;
    protected $fillable = ['license_id','uuid_value', 'ip','hwid','banned_at','ban_type','last_active'];

}
