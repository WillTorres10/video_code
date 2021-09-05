<?php

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    protected Category $category;

    protected function setUp():void
    {
        parent::setUp();
        $this->category = new Category();
    }

    public function testFillable()
    {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [HasFactory::class, SoftDeletes::class];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }

    public function testIfKeyTypeIsString()
    {
        $this->assertEquals('string', $this->category->getKeyType());
    }

    public function testIfIncrementingIsFalse()
    {
        $this->assertFalse($this->category->incrementing);
    }

    public function testDatesCast()
    {
        $expectedDates = ['created_at', 'updated_at', 'deleted_at'];
        $dates = $this->category->getDates();
        foreach ($expectedDates as $expected) {
            $this->assertContains($expected, $dates);
        }
        $this->assertCount(count($expectedDates), $dates);
    }

    public function testCastAttributes()
    {
        $casts = ['id' => 'string', 'is_active'=>'boolean', 'deleted_at'=>'datetime'];
        $this->assertEquals($casts, $this->category->getCasts());
    }

    public function testIncrementingAttributes()
    {
        $this->assertFalse($this->category->incrementing);
    }
}
