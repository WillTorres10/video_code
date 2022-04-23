<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\ValidateCategoriesOfGenres;
use Illuminate\Http\Request;
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
        $this->rules += Video::validationRulesFiles();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $obj->load($this->relationshipsToLoad());
        return new VideoResource($obj);
    }

    public function update(Request $request, $id)
    {
        /* @var $model Video */
        $model = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $model->update($validatedData);
        $model->refresh();
        $model->load($this->relationshipsToLoad());
        return new VideoResource($model);
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

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return VideoResource::class;
    }

    protected function relationshipsToLoad(): array
    {
        return ['genres', 'categories'];
    }


}
