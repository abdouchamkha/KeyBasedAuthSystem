<?php

namespace App\Http\Requests\ProductDownload;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreProductDownloadRequest extends FormRequest
{
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ], 400));
    }

    public function authorize(): bool
    {
        return auth()->user()->selectedApp()->first()->owner_id == auth()->id();
    }

    public function rules(): array
    {
        return [
            'files' => 'required|array|min:1',
            // 'files.*' => 'required|file|max:8192|mimes:exe,sys,dll',  // Handle multiple files
            'files.*' => 'required|file|max:8192',  // Handle multiple files

            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string',  // Each file tag
            'products' => 'required_without:all|array',
            'products.*' => 'integer|exists:products,id',
            'all' => 'boolean',
        ];
    }
}
