<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => ['required', 'string', 'max:255'],
        'is_active' => ['required', 'boolean'],
        'categories_id' => ['required', 'array', 'exists:categories,id']
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $obj = DB::transaction(function () use ($request, $validatedData, $self) {
            unset($validatedData['categories_id']);
            $obj = $self->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            $obj->refresh();
            return $obj;
        });
        return new GenreResource($obj);
    }

    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $model = DB::transaction(function () use ($model, $request, $validatedData, $self) {
            $model->update($validatedData);
            $self->handleRelations($model, $request);
            $model->categories()->sync($request->get('categories_id'));
            return $model;
        });
        return new GenreResource($model->refresh());
    }

    protected function handleRelations(Genre $genre, Request $request)
    {
        $genre->categories()->sync($request->get('categories_id'));
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return GenreResource::class;
    }

    protected function relationshipsToLoad(): array
    {
        return ['categories'];
    }
}
