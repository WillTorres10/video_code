<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    private function postRules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean']
        ];
    }

    private function putRules()
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean']
        ];
    }

    public function rules()
    {
        switch ($this->method()){
            case 'POST': return $this->postRules();
            case 'PUT': return $this->putRules();
            default: return [];
        }
    }
}
