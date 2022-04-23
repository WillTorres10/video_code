<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Resources\GenreResource;
use App\Models\{Category, Genre};
use Database\Seeders\GenresTableSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\{TestResources, TestSaves, TestValidations};
use Mockery;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidations, TestResources;

    private Genre $genre;
    private string $stringWith256;

    private $serializedFields = [
        'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at', 'categories' => ['*' => ['id', 'name']]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->factoryGenresWithRelationShips();
        $this->stringWith256 = str_repeat('a', 256);
    }

    private function factoryGenresWithRelationShips()
    {
        $categories = Category::factory(3)->make();
        $this->genre = Genre::factory(1)->create()->first();
        $this->genre->categories()->saveMany($categories);
        $this->genre->refresh();
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
            ->assertJson(['meta' => ['per_page' => 15]])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializedFields],
                'links' => [],
                'meta' => []
            ]);
        $resource = GenreResource::collection(collect([$this->genre]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->json('GET', route('genres.show', ['genre'=>$this->genre->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertGenreResource($response);
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
        $response = $this->assertStore($data + ['categories_id' => $categories], $data + ['deleted_at'=>null]);
        $this->assertGenreResource($response);
    }

    public function testUpdate()
    {
        $categories = Category::factory(3)->create()->pluck('id');
        $dataToUpdate = [
            'name' => 'A updated name',
            'is_active' => !$this->genre->is_active
        ];
        $response = $this->assertUpdate($dataToUpdate + ['categories_id' => $categories], $dataToUpdate + ['deleted_at' => null]);
        $this->assertGenreResource($response);
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

    private function assertGenreResource(TestResponse $response)
    {
        $genere = Genre::with('categories')->findOrFail($response->json('data.id'));
        $this->assertResource($response, (new GenreResource($genere)));
    }
}
