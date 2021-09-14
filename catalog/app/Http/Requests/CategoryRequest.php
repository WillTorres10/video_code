<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use function PHPUnit\Framework\matches;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    private function postRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function putRules()
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function rules()
    {
        switch ($this->method()) {
            case 'POST': return $this->postRules();
            case 'PUT' : return $this->putRules();
            default:  return [];
        }
    }
}
