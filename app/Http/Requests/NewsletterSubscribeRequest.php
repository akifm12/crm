<?php
// app/Http/Requests/NewsletterSubscribeRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsletterSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:150'],
            'name'  => ['nullable', 'string', 'max:150'],
        ];
    }
}
