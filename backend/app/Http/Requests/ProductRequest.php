<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id ?? null;
        return [
            'sku' => ['required','string','max:64','unique:products,sku,'.($id ?? 'NULL').',id'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'type' => ['required','in:raw,finished'],
            'reorder_point' => ['nullable','integer','min:0'],
            'safety_stock' => ['nullable','integer','min:0'],
            'lead_time_days' => ['nullable','integer','min:0'],
            'unit_price' => ['nullable','numeric','min:0'],
            'stock_on_hand' => ['nullable','integer','min:0'],
            'stock_allocated' => ['nullable','integer','min:0'],
        ];
    }
}
