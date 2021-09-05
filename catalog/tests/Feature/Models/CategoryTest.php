<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    private function newCategory(): Category
    {
        return Category::factory(1)
            ->create(['description' => 'test_description', 'is_active'=>false])
            ->first();
    }

    public function testList()
    {
        Category::factory(1)->create();

        $count = Category::all();
        $this->assertCount(1, $count);

        $categoryKeys = array_keys(Category::first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            $categoryKeys
        );
    }

    public function testCreate()
    {
        $category = Category::create(['name'=>'test']);
        $category->refresh();

        $this->assertEquals('test', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create(['name'=>'test', 'description'=>null]);
        $this->assertNull($category->description);

        $category = Category::create(['name'=>'test', 'description'=>'test_description']);
        $this->assertEquals('test_description', $category->description);

        $category = Category::create(['name'=>'test', 'is_active'=>false]);
        $this->assertFalse($category->is_active);

        $category = Category::create(['name'=>'test', 'is_active'=>true]);
        $this->assertTrue($category->is_active);

        $category = Category::create(['name'=>'test', 'is_active'=>true]);
        $validator = Validator::make( ['id' => $category->id], ['id'=>'uuid']);
        $this->assertFalse($validator->fails());
    }

    public function testUpdate()
    {
        $category = $this->newCategory();
        $data = [
            'name' => 'test_name_updated',
            'description' => 'test_description_updated',
            'is_active' => true
        ];
        $category->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDelete()
    {
        $category = $this->newCategory();
        $idCategory = $category->id;
        $category->delete();
        $categorySearch = Category::find($idCategory);
        $this->assertNull($categorySearch);
    }

}
