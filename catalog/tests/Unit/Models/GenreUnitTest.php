<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use App\Models\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class GenreUnitTest extends TestCase
{
    protected Genre $gender;

    protected function setUp():void
    {
        parent::setUp();
        $this->gender = new Genre();
    }

    public function testFillable()
    {
        $fillable = ['name', 'is_active'];
        $this->assertEquals($fillable, $this->gender->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [HasFactory::class, SoftDeletes::class, UUID::class];
        $genderTraits = array_keys(class_uses(Genre::class));
        $this->assertEquals($traits, $genderTraits);
    }

    public function testIfKeyTypeIsString()
    {
        $this->assertEquals('string', $this->gender->getKeyType());
    }

    public function testIfIncrementingIsFalse()
    {
        $this->assertFalse($this->gender->incrementing);
    }

    public function testDatesCast()
    {
        $expectedDates = ['created_at', 'updated_at', 'deleted_at'];
        $dates = $this->gender->getDates();
        foreach ($expectedDates as $expected) {
            $this->assertContains($expected, $dates);
        }
        $this->assertCount(count($expectedDates), $dates);
    }

    public function testCastAttributes()
    {
        $casts = ['id' => 'string', 'is_active'=>'boolean', 'deleted_at'=>'datetime'];
        $this->assertEquals($casts, $this->gender->getCasts());
    }
}
