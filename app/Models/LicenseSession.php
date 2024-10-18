<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // To generate UUID
use Illuminate\Support\Facades\Log;

class LicenseSession extends Model
{
    use HasFactory;

    // Define which fields can be mass-assigned
    protected $fillable = ['license_id', 'app_id', 'uuid_value', 'ip', 'duration', 'type'];

    // Boot method to hook into model events
    protected static function boot()
    {
        parent::boot();
        // Hook into the "creating" event for new records
        static::creating(function ($licenseSession) {
            // Generate a unique token if it's not already set
            if (empty($licenseSession->token)) {
                $licenseSession->token = Str::uuid(); // Generate another UUID for the token
            }
        });
        // Hook into the "updating" event for existing records (if needed)
        static::updating(function ($licenseSession) {
            $licenseSession->token = Str::uuid();
        });
    }
}
