<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variant = $this->route('product_variant');

        // Get ID whether route binding gives us a model or an ID
        $variantId = $variant instanceof \App\Models\ProductVariant
            ? $variant->id
            : $variant;

        return [
            'product_id' => 'sometimes|exists:products,id',

            'sku' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('product_variants', 'sku')
                    ->ignore($variantId),
            ],

            'color' => 'nullable|string|max:100',

            'size' => 'nullable|string|max:50',

            'price' => 'nullable|numeric|min:0',

            'stock' => 'sometimes|integer|min:0',

            'status' => 'sometimes|boolean',
        ];
    }
}