<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use App\Models\Category;

class CategoryUnitTest extends TestCase
{
    protected Category $category;

    protected function setUp():void
    {
        parent::setUp();
        $this->category = new Category();
    }

    public function testFillable(): void
    {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testIfUseTraits(): void
    {
        $traits = [HasFactory::class, SoftDeletes::class, UUID::class];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }

    public function testIfKeyTypeIsString(): void
    {
        $this->assertEquals('string', $this->category->getKeyType());
    }

    public function testIfIncrementingIsFalse(): void
    {
        $this->assertFalse($this->category->incrementing);
    }

    public function testDatesCast(): void
    {
        $expectedDates = ['created_at', 'updated_at', 'deleted_at'];
        $dates = $this->category->getDates();
        foreach ($expectedDates as $expected) {
            $this->assertContains($expected, $dates);
        }
        $this->assertCount(count($expectedDates), $dates);
    }

    public function testCastAttributes(): void
    {
        $casts = ['id' => 'string', 'is_active'=>'boolean', 'deleted_at'=>'datetime'];
        $this->assertEquals($casts, $this->category->getCasts());
    }
}
