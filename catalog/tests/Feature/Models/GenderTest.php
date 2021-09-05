<?php

namespace Tests\Feature\Models;

use App\Models\Gender;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GenderTest extends TestCase
{
    use DatabaseMigrations;

    private function newGender($name = 'test', $is_active = true)
    {
        return Gender::create([
            'name' => $name,
            'is_active' => $is_active
        ]);
    }

    public function testList()
    {
        $this->newGender();

        $count = Gender::all();
        $this->assertCount(1, $count);

        $genderKeys = array_keys(Gender::first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            $genderKeys
        );
    }

    public function testCreate()
    {
        $gender = $this->newGender();

        $this->assertEquals('test', $gender->name);
        $this->assertTrue($gender->is_active);

        $gender = $this->newGender(is_active: false);
        $this->assertFalse($gender->is_active);

        $gender = $this->newGender();
        $validator = Validator::make( ['id' => $gender->id], ['id'=>'uuid']);
        $this->assertFalse($validator->fails());
    }

    public function testUpdate()
    {
        $gender = $this->newGender();
        $data = [
            'name' => 'test_name_updated',
            'is_active' => false
        ];
        $gender->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $gender->{$key});
        }
    }

    public function testDelete()
    {
        $gender = $this->newGender();
        $idGender = $gender->id;
        $gender->delete();
        $genderSearch = Gender::find($idGender);
        $this->assertNull($genderSearch);
    }
}
