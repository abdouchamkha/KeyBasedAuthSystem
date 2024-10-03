<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthLoader extends Model
{
    use HasFactory;
    protected $fillable = ['version','hash', 'path','unsupported_at','tags',];
}
