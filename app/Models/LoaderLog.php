<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaderLog extends Model
{
    use HasFactory;
    protected $fillable = ['token', 'data', 'ip_address','app_id','loader_id'];
    protected $casts = [
        'data' => 'encrypted', // Automatically encrypt and decrypt data
    ];
    // If using encrypted manually, uncomment below mutator & accessor

    // public function setDataAttribute($value)
    // {
    //     $this->attributes['data'] = Crypt::encryptString($value);
    // }

    // public function getDataAttribute($value)
    // {
    //     return Crypt::decryptString($value);
    // }
}
