<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;

class CategoryController extends BasicCrudController
{
    private array $rules = [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'is_active' => ['nullable', 'boolean'],
    ];

    protected function model()
    {
        return Category::class;
    }

    protected function rulesStore():array
    {
        return $this->rules;
    }

    protected function rulesUpdate():array
    {
        return $this->rules;
    }
}
