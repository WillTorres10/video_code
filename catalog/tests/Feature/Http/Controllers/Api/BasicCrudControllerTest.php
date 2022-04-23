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
        $resource = $this->controller->index();
        $serialized = $resource->response()->getData(true);
        $this->assertEquals(
            [$this->category->toArray()],
            $serialized['data']
        );
        $this->assertArrayHasKey('meta', $serialized);
        $this->assertArrayHasKey('links', $serialized);
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
        $resource = $this->controller->store($request);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals( CategoryStub::find(2)->toArray(), $serialized['data']);
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
        $resource = $this->controller->show($this->category->id);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals($this->category->toArray(), $serialized['data']);
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
        $resource = $this->controller->update($request, $this->category->id);
        $serialized = $resource->response()->getData(true);
        $this->category->refresh();
        $this->assertEquals($serialized['data'], $this->category->toArray());
    }

    public function testDestroy()
    {
        $result = $this->controller->destroy($this->category->id);
        $this->assertEquals(204, $result->status());
        $tableName = (new CategoryStub())->getTable();
        $this->assertDatabaseMissing($tableName, ['id'=>$this->category->id]);
    }
}
