<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\{Request, Response};

abstract class BasicCrudController extends Controller
{
    abstract protected function model();

    abstract protected function rulesStore();

    abstract protected function rulesUpdate();

    public function index()
    {
        return $this->model()::all();
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        return $obj;
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $model->update($validatedData);
        return $model->refresh();
    }

    public function destroy($id): Response
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return response()->noContent();
    }
}
