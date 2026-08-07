<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class storeCategoryRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

         'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'parent_id' => 'nullable|exists:categories,id',
         ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a string.',
            'name.max' => 'The category name may not be greater than 255 characters.',
            'name.unique' => 'The category name has already been taken.',
            'slug.string' => 'The slug must be a string.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'slug.unique' => 'The slug has already been taken.',
            'description.string' => 'The description must be a string.',
            'status.required' => 'The status field is required.',
            'status.boolean' => 'The status field must be true or false.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ];
    }
}
