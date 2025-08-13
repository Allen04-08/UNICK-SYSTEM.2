<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_number' => ['sometimes','string','max:64'],
            'product_id' => ['required','exists:products,id'],
            'quantity_planned' => ['required','integer','min:1'],
            'current_stage_id' => ['nullable','exists:stages,id'],
            'status' => ['nullable','in:scheduled,in_progress,completed,paused,cancelled'],
            'start_date' => ['nullable','date'],
            'due_date' => ['nullable','date','after_or_equal:start_date'],
        ];
    }
}
