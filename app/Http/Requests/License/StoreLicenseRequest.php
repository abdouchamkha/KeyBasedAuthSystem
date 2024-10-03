<?php

namespace App\Http\Requests\License;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreLicenseRequest extends FormRequest
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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app' => 'required|integer',
            'product' => 'required|integer|exists:products,id|',
            'license_value' => 'nullable|string|max:50',
            'days' => 'sometimes|integer|min:0',
            'hours' => 'sometimes|integer|min:0',
            'minutes' => 'sometimes|integer|min:0',
            'life_time' => 'required|boolean',
            'hwid_lock' => 'required|boolean',
        ];
    }
}
