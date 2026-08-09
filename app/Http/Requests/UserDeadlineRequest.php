<?php
// app/Http/Requests/UserDeadlineRequest.php

namespace App\Http\Requests;

use App\Models\UserDeadline;
use Illuminate\Foundation\Http\FormRequest;

class UserDeadlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'compliance_deadline_id' => ['nullable', 'integer', 'exists:public_compliance_deadlines,id'],
            'type'     => ['required_without:compliance_deadline_id', 'nullable', 'in:'.implode(',', array_keys(UserDeadline::typeLabels()))],
            'label'    => ['nullable', 'string', 'max:150'],
            'due_date' => ['required', 'date'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (empty($data['type'] ?? null) && ! empty($data['compliance_deadline_id'] ?? null)) {
            $data['type'] = 'other';
        }

        return $data;
    }
}
