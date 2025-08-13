<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required','exists:products,id'],
            'movement_type' => ['required','in:inbound,outbound,adjustment'],
            'quantity_change' => ['required','integer'],
            'note' => ['nullable','string','max:500'],
        ];
    }
}
