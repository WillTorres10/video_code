<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\ValidateCategoriesOfGenres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VideoController extends BasicCrudController
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'year_launched' => ['required', 'date_format:Y'],
            'opened' => ['boolean'],
            'rating' => ['required', Rule::in(Video::RATING_LIST)],
            'duration' => ['required', 'integer'],
            'categories_id' => ['required', 'array', 'exists:categories,id', new ValidateCategoriesOfGenres],
            'genres_id' => ['required', 'array', 'exists:genres,id'],
        ];
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $obj = DB::transaction(function () use ($request, $validatedData, $self){
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $model = DB::transaction(function () use($request, $model, $validatedData, $self) {
            $model->update($validatedData);
            $self->handleRelations($model, $request);
            return $model;
        });
        $model->refresh();
        return $model;
    }

    protected function handleRelations(Video $video, Request $request)
    {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

}
