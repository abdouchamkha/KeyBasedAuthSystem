<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthLoader extends Model
{
    use HasFactory;
    protected $fillable = ['lang','loader_type','version','hash','app_id','path','unsupported_at','tags','update_note','stage'];
    //set app_id in case it is ui loader
    // 'stage',['production','staging','development']
}
