<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\{Request, Resources\Json\ResourceCollection, Response};

abstract class BasicCrudController extends Controller
{
    protected $paginationSize = 15;

    abstract protected function model();

    abstract protected function rulesStore();

    abstract protected function rulesUpdate();

    abstract protected function resource();

    abstract protected function resourceCollection();

    abstract protected function relationshipsToLoad():array;

    public function index()
    {
        $data = !$this->paginationSize
            ? $this->model()::with($this->relationshipsToLoad())->all()
            : $this->model()::with($this->relationshipsToLoad())->paginate($this->paginationSize);

        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($resourceCollectionClass);

        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        $obj = $this->findOrFail($id);
        $resource = $this->resource();
        return new $resource($obj);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $model->update($validatedData);
        $model->refresh();
        $resource = $this->resource();
        return new $resource($model);
    }

    public function destroy($id): Response
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return response()->noContent();
    }
}
