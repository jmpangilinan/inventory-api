<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DeviceType;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeviceWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:100'],
            'device_type' => ['required', Rule::enum(DeviceType::class)],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'type' => ['required', Rule::enum(TransactionType::class)],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
