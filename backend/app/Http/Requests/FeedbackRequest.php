<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['nullable','exists:orders,id'],
            'rating' => ['required','integer','min:1','max:5'],
            'comment' => ['nullable','string','max:1000'],
        ];
    }
}
