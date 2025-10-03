<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class DepositCreateRequest extends FormRequest
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
            'rvm_id' => 'required|integer|exists:reverse_vending_machines,id',
            'session_token' => 'nullable|string|max:255',
            'waste_type' => 'required|string|in:plastic,glass,metal,paper,mixed',
            'weight' => 'required|numeric|min:0.001|max:100',
            'quantity' => 'required|integer|min:1|max:1000',
            'image_data' => 'nullable|string', // Base64 encoded image for AI analysis
            'metadata' => 'nullable|array', // Additional metadata for AI analysis
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
            'rvm_id.required' => 'RVM ID is required',
            'rvm_id.exists' => 'The selected RVM does not exist',
            'waste_type.required' => 'Waste type is required',
            'waste_type.in' => 'Waste type must be one of: plastic, glass, metal, paper, mixed',
            'weight.required' => 'Weight is required',
            'weight.min' => 'Weight must be at least 0.001 kg',
            'weight.max' => 'Weight cannot exceed 100 kg',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'quantity.max' => 'Quantity cannot exceed 1000',
        ];
    }
}
