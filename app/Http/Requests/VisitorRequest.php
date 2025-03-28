<?php

namespace App\Http\Requests;

use App\Models\Visitor;
use Illuminate\Foundation\Http\FormRequest;

class VisitorRequest extends FormRequest
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
     * Get the visitor from the request context.
     * 
     * @throws \RuntimeException if visitor is not in context
     */
    public function getVisitor(): Visitor
    {
        if (!isset($this->context['visitor']) && ! $this->context['visitor'] instanceof Visitor) {
            throw new \RuntimeException('Visitor not found in request context');
        }

        return $this->context['visitor'];
    }
} 