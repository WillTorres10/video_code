<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    protected $category;

    private $serializedFields = ['id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory(1)->create()->first();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson(['meta' => ['per_page' => 15]])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializedFields],
                'links' => [],
                'meta' => []
            ]);

        $resource = CategoryResource::collection(collect([$this->category]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertCategoryResource($response);
    }

    public function testInvalidationDataCreation()
    {
        $this->assertInvalidationInStoreAction(
            ['name' => ''],
            'required'
        );
        $this->assertInvalidationInStoreAction(
            ['name' => str_repeat('a', 256)],
            'max.string',
            ['max' => 255]
        );
        $this->assertInvalidationInStoreAction(
            ['is_active' => 'a'],
            'boolean'
        );
    }

    public function testInvalidationDataUpdating()
    {
        $this->assertInvalidationInUpdateAction(
            ['name' => ''],
            'required'
        );

        $this->assertInvalidationInUpdateAction(
            ['name' => str_repeat('a', 256)],
            'max.string',
            ['max' => 255]
        );

        $this->assertInvalidationInUpdateAction(
            ['is_active' => 'a'],
            'boolean'
        );
    }

    public function testStore()
    {
        $data = ['name' => 'test'];
        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at'=>null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertCategoryResource($response);

        $data = [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false
        ];
        $response = $this->assertStore($data, $data + ['deleted_at'=>null]);

        $this->assertCategoryResource($response);
    }

    public function testUpdate()
    {
        $this->category = Category::factory(1)->create([
            'description' => 'description',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'test',
            'is_active' => true,
            'description' => 'test'
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at'=>null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertCategoryResource($response);

        $data = [
            'name' => 'test',
            'description' => ''
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, $data);
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['name'], 'required', []);
        $response->assertJsonMissingValidationErrors(['is active']);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['name'], 'max.string', ['max' => 255]);
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['is_active'], 'boolean', []);
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category'=>$this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }

    private function assertCategoryResource(TestResponse $response)
    {
        $category = Category::findOrFail($response->json('data.id'));
        $this->assertResource($response, (new CategoryResource($category)));
    }
}
