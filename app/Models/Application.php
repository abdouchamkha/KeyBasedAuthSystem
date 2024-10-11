<?php

namespace App\Models;

use App\ActiveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // To generate unique token
use Illuminate\Support\Facades\Hash; // To hash the token

class Application extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'name', 'owner_id', 'is_selected', 'token'];

    protected $casts = [
        'status' => ActiveType::class, // inactive if the user has not paid the plan
    ];

    protected static function boot()
    {
        parent::boot();
        // Hook into the "creating" event for new records
        static::creating(function ($application) {
            // Generate a unique token if it's not already set
            if (empty($application->token)) {
                $application->token = (Str::uuid()); // Generate another UUID for the token
            }
        });
    }

    public function owner(){
        return $this->hasOne(User::class, 'owner_id', 'id');
    }

    /**
     * Generates and sets a new unique hashed token for the application.
     */
    public function generateToken()
    {
        // Generate a unique token
        $plainToken = Str::uuid()->toString();

        // Hash the token and save it
        $this->token = Hash::make($plainToken);
        $this->save();

        return $plainToken; // Return plain token so it can be shared once
    }

    /**
     * Resets the application token.
     */
    public function resetToken()
    {
        return $this->generateToken(); // Call generateToken to reset
    }
}
