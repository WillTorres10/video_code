<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\{Category, Genre};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\{TestSaves, TestValidations};
use Mockery;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidations;

    private Genre $genre;
    private string $stringWith256;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = Genre::factory(1)->create()->first();
        $this->stringWith256 = str_repeat('a', 256);
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre'=>$this->genre->id]);
    }

    public function testIndex()
    {
        $response = $this->json('GET', route('genres.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->json('GET', route('genres.show', ['genre'=>$this->genre->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function testInvalidationDataCreation()
    {
        $this->assertInvalidationInStoreAction(['name' => '', 'is_active' => null, 'categories_id' => null], 'required');
        $this->assertInvalidationInStoreAction(['name' => 1], 'string');
        $this->assertInvalidationInStoreAction(['name' => $this->stringWith256], 'string', ['max' => 255]);
        $this->assertInvalidationInStoreAction(['is_active' => 'a'], 'boolean');
        $this->assertInvalidationInStoreAction(['categories_id' => 'a'], 'array');
        $this->assertInvalidationInStoreAction(['categories_id' => 'a'], 'exists');
    }

    public function testInvalidationDataUpdating()
    {
        $this->assertInvalidationInUpdateAction(['name' => '', 'is_active' => null, 'categories_id' => null], 'required');
        $this->assertInvalidationInUpdateAction(['name' => 1], 'string');
        $this->assertInvalidationInUpdateAction(['name' => $this->stringWith256], 'string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction(['is_active' => 'a'], 'boolean');
        $this->assertInvalidationInStoreAction(['categories_id' => 'a'], 'array');
        $this->assertInvalidationInStoreAction(['categories_id' => 'a'], 'exists');
    }

    public function testStore()
    {
        $categories = Category::factory(3)->create()->pluck('id');
        $data = ['name' => 'test', 'is_active' => false];
        $this->assertStore($data + ['categories_id' => $categories], $data + ['deleted_at'=>null]);
    }

    public function testUpdate()
    {
        $categories = Category::factory(3)->create()->pluck('id');
        $dataToUpdate = [
            'name' => 'A updated name',
            'is_active' => !$this->genre->is_active
        ];
        $this->assertUpdate($dataToUpdate + ['categories_id' => $categories], $dataToUpdate + ['deleted_at' => null]);
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->genre->id]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
    }

    public function testRollbackStore()
    {
        $categories = Category::factory(3)->create()->pluck('id');
        $data = ['name' => 'test', 'is_active' => false, 'categories_id' => $categories];
        $controller = Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());
        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($data);
        $controller
            ->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);
        $request = Mockery::mock(Request::class);
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
        }
    }

    public function testRollbackUpdate()
    {
        $categories = Category::factory(3)->create()->pluck('id');
        $data = ['name' => 'test', 'is_active' => false, 'categories_id' => $categories];
        $controller = Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());
        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($data);
        $controller
            ->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);
        $request = Mockery::mock(Request::class);
        try {
            $controller->update($request, $this->genre->id);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
        }
    }
}
