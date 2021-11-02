<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Gender;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\{TestSaves, TestValidations};

class GenderControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidations;

    private Gender $gender;
    private string $stringWith256;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gender = Gender::factory(1)->create()->first();
        $this->stringWith256 = str_repeat('a', 256);
    }

    protected function model()
    {
        return Gender::class;
    }

    protected function routeStore()
    {
        return route('genders.store');
    }

    protected function routeUpdate()
    {
        return route('genders.update', ['gender'=>$this->gender->id]);
    }

    public function testIndex()
    {
        $response = $this->json('GET', route('genders.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->gender->toArray()]);
    }

    public function testShow()
    {
        $response = $this->json('GET', route('genders.show', ['gender'=>$this->gender->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->gender->toArray());
    }

    public function testInvalidationDataCreation()
    {
        $this->assertInvalidationInStoreAction(['name' => '', 'is_active' => null], 'required');
        $this->assertInvalidationInStoreAction(['name' => 1], 'string');
        $this->assertInvalidationInStoreAction(['name' => $this->stringWith256], 'string', ['max' => 255]);
        $this->assertInvalidationInStoreAction(['is_active' => 'a'], 'boolean');
    }

    public function testInvalidationDataUpdating()
    {
        $this->assertInvalidationInUpdateAction(['name' => '', 'is_active' => null], 'required');
        $this->assertInvalidationInUpdateAction(['name' => 1], 'string');
        $this->assertInvalidationInUpdateAction(['name' => $this->stringWith256], 'string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction(['is_active' => 'a'], 'boolean');
    }

    public function testStore()
    {
        $data = ['name' => 'test', 'is_active' => false];
        $this->assertStore($data, $data + ['deleted_at'=>null]);
    }

    public function testUpdate()
    {
        $dataToUpdate = [
            'name' => 'A updated name',
            'is_active' => !$this->gender->is_active
        ];
        $this->assertUpdate($dataToUpdate, $dataToUpdate + ['deleted_at' => null]);
    }
}
