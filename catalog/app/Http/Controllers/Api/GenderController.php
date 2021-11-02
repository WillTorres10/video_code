<?php

namespace App\Http\Controllers\Api;

use App\Models\Gender;
use JetBrains\PhpStorm\ArrayShape;

class GenderController extends BasicCrudController
{
    private $rules = [
        'name' => ['required', 'string', 'max:255'],
        'is_active' => ['required', 'boolean']
    ];

    protected function model()
    {
        return Gender::class;
    }

    #[ArrayShape(['name' => "string[]", 'is_active' => "string[]"])]
    protected function rulesStore()
    {
        return $this->rules;
    }

    #[ArrayShape(['name' => "string[]", 'is_active' => "string[]"])]
    protected function rulesUpdate()
    {
        return $this->rules;
    }
}
