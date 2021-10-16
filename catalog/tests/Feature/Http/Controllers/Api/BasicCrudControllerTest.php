<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class BasicCrudControllerTest extends TestCase
{
    private CategoryControllerStub $controller;
    private CategoryStub $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CategoryControllerStub();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        $this->assertEquals([$this->category->toArray()], $this->controller->index()->toArray());
    }

    public function testInvalidationDataInStore()
    {
        $this->expectException(ValidationException::class);
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);
        $this->controller->store($request);
    }

    public function testStore()
    {
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name', 'description' => 'test_description']);
        $obj = $this->controller->store($request);
        $this->assertEquals(
            CategoryStub::find(2)->toArray(),
            $obj->toArray()
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($this->controller, [$this->category->id]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invoke($this->controller, [0]);
    }

    public function testShowWhenHasTheModel()
    {
        $result = $this->controller->show($this->category->id);
        $this->assertEquals($this->category->toArray(), $result->toArray());
    }

    public function testShowWhenHasntTheModel()
    {
        $this->expectException(ModelNotFoundException::class);
        $result = $this->controller->show(0);
    }

    public function testInvalidationDataInUpdate()
    {
        $this->expectException(ValidationException::class);
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);
        $this->controller->update($request, $this->category->id);
    }

    public function testUpdate()
    {
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'name_updated']);
        $result = $this->controller->update($request, $this->category->id);
        $this->category->refresh();

        $this->assertEquals($result->toArray(), $this->category->toArray());
    }

    public function testDestroy()
    {
        $result = $this->controller->destroy($this->category->id);
        $this->assertEquals(204, $result->status());
        $tableName = (new CategoryStub())->getTable();
        $this->assertDatabaseMissing($tableName, ['id'=>$this->category->id]);
    }
}
