<?php

namespace App\Actions\Fortify;

use App\ActiveType;
use App\Models\User;
use App\Models\Application;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
            // Automatically create an application for the new user
        Application::create([
            'owner_id' => $user->id,
            'name' => $user->name.' app',
            'status' => ActiveType::ACTIVE,
            'is_selected' => true,
        ]);
        return $user;
    }
}
