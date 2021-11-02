<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use JetBrains\PhpStorm\ArrayShape;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BasicCrudController
{

    private array $rules = [
        'name' => 'required|max:255',
        'description' => 'nullable'
    ];

    protected function model()
    {
        return CategoryStub::class;
    }

    #[ArrayShape(['name' => "string", 'description' => "string"])]
    protected function rulesStore()
    {
        return $this->rules;
    }

    #[ArrayShape(['name' => "string", 'description' => "string"])]
    protected function rulesUpdate()
    {
        return $this->rules;
    }
}
