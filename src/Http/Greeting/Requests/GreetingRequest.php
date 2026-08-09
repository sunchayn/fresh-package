<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Http\Greeting\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use VendorName\Skeleton\Modules\Greeting\Enums\GreetingTone;

class GreetingRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'tone' => ['sometimes', Rule::enum(GreetingTone::class)],
        ];
    }
}
