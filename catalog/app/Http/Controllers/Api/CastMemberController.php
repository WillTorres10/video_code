<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;
use Illuminate\Validation\Rule;

class CastMemberController extends BasicCrudController
{

    private $rules;

    public function __construct()
    {
        $this->rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'integer', Rule::in(CastMember::$types)]
        ];
    }

    protected function model()
    {
        return CastMember::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return CastMemberResource::class;
    }

    protected function relationshipsToLoad(): array
    {
        return [];
    }
}
