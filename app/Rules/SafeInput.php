<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeInput implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Custom regex to prevent potentially dangerous input
        $pattern = '/<\?php|\?>|SELECT|INSERT|DELETE|UPDATE|DROP|--|<script>/i';

        // Check if the input matches the dangerous patterns
        if (preg_match($pattern, $value)) {
            $fail("The {$attribute} contains potentially executable code.");
        }

        // Sanitize the input to ensure it's safe
        $sanitizedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
