<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Models\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class CastMemberUnitTest extends TestCase
{
    protected CastMember $castMember;

    protected function setUp():void
    {
        parent::setUp();
        $this->castMember = new CastMember();
    }

    public function testFillable()
    {
        $fillable = ['name', 'type'];
        $this->assertEquals($fillable, $this->castMember->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [HasFactory::class, SoftDeletes::class, UUID::class];
        $castMemberTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $castMemberTraits);
    }

    public function testIfKeyTypeIsString()
    {
        $this->assertEquals('string', $this->castMember->getKeyType());
    }

    public function testIfIncrementingIsFalse()
    {
        $this->assertFalse($this->castMember->incrementing);
    }

    public function testDatesCast()
    {
        $expectedDates = ['created_at', 'updated_at', 'deleted_at'];
        $dates = $this->castMember->getDates();
        foreach ($expectedDates as $expected) {
            $this->assertContains($expected, $dates);
        }
        $this->assertCount(count($expectedDates), $dates);
    }

    public function testCastAttributes()
    {
        $casts = ['id' => 'string', 'type' => 'integer', 'deleted_at' => 'datetime'];
        $this->assertEquals($casts, $this->castMember->getCasts());
    }
}
