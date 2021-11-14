<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    private function newCastMember($name = 'test', $type = CastMember::TYPE_DIRECTOR)
    {
        return CastMember::create([
            'name' => $name,
            'type' => $type
        ]);
    }

    public function testList()
    {
        $this->newCastMember();

        $count = CastMember::all();
        $this->assertCount(1, $count);

        $genderKeys = array_keys(CastMember::first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'type', 'created_at', 'updated_at', 'deleted_at'],
            $genderKeys
        );
    }

    public function testCreate()
    {
        $castMember = $this->newCastMember();

        $this->assertEquals('test', $castMember->name);
        $this->assertEquals(CastMember::TYPE_DIRECTOR, $castMember->type);

        $castMember = $this->newCastMember(type: CastMember::TYPE_ACTOR);
        $this->assertEquals(CastMember::TYPE_ACTOR, $castMember->type);

        $castMember = $this->newCastMember();
        $validator = Validator::make( ['id' => $castMember->id], ['id'=>'uuid']);
        $this->assertFalse($validator->fails());
    }

    public function testUpdate()
    {
        $castMember = $this->newCastMember();
        $data = [
            'name' => 'test_name_updated',
            'type' => CastMember::TYPE_ACTOR
        ];
        $castMember->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $castMember->{$key});
        }
    }

    public function testDelete()
    {
        $castMember = $this->newCastMember();
        $idGender = $castMember->id;
        $castMember->delete();
        $genderSearch = CastMember::find($idGender);
        $this->assertNull($genderSearch);
    }
}
