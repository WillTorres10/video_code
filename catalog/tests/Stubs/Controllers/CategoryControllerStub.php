<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use App\Http\Resources\CategoryResource;
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

    protected function resource()
    {
        return CategoryResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function relationshipsToLoad(): array
    {
        return [];
    }
}
