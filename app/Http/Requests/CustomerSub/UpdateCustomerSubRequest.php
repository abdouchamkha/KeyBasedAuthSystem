<?php

namespace App\Http\Requests\CustomerSub;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class UpdateCustomerSubRequest extends FormRequest
{
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ],400));
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
                return auth()->user()->selectedApp()->first()->owner_id == auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'can_add_setup_time' => 'required|boolean',
            'unlimited_key_freeze_times' => 'required|boolean',
            'unlimited_key_reset_times' => 'required|boolean',
            'can_create_key_with_no_hwid' => 'required|boolean',
            'subscription_type' => 'required|string|in:days system,unlimited panel',
            'product_id' => 'required|integer|exists:products,id',
            'customer_id' => 'required|integer|exists:customers,id',
        ];
    }
}
