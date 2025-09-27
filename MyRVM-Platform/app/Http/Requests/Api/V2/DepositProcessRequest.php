<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class DepositProcessRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:completed,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
            'final_reward_amount' => 'nullable|numeric|min:0',
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status is required',
            'status.in' => 'Status must be either completed or rejected',
            'rejection_reason.required_if' => 'Rejection reason is required when status is rejected',
            'rejection_reason.max' => 'Rejection reason cannot exceed 500 characters',
            'final_reward_amount.min' => 'Final reward amount cannot be negative',
            'admin_notes.max' => 'Admin notes cannot exceed 1000 characters',
        ];
    }
}
