<?php

namespace App\Http\Requests;

use App\Models\Provider;
use Illuminate\Foundation\Http\FormRequest;

class ProviderRequest extends FormRequest
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
        return [];
    }

    /**
     * Get the provider from the request context.
     * 
     * @throws \RuntimeException if provider is not in context
     */
    public function getProvider(): Provider
    {
        if (!isset($this->context['provider']) && ! $this->context['provider'] instanceof Provider) {
            throw new \RuntimeException('Provider not found in request context');
        }

        return $this->context['provider'];
    }
} 