<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Gender;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Lang;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GenderControllerTest extends TestCase
{
    use DatabaseMigrations;

    private function createAndGetAEmptyGender():Gender
    {
        return Gender::factory(1)->create()->first();
    }

    public function testIndex()
    {
        $gender = $this->createAndGetAEmptyGender();
        $response = $this->json('GET', route('genders.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$gender->toArray()]);
    }

    public function testShow()
    {
        $gender = $this->createAndGetAEmptyGender();
        $response = $this->json('GET', route('genders.show', ['gender'=>$gender->id]));
        $response
            ->assertStatus(200)
            ->assertJson($gender->toArray());
    }

    public function testInvalidationDataCreation()
    {
        $response = $this->json('POST', route('genders.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('genders.store'), [
            'name' => 10,
            'is_active' => 'aaaa'
        ]);
        $this->assertInvalidationString($response);
        $this->assertInvalidationBoolean($response);

        $response = $this->json('POST', route('genders.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => false
        ]);
        $this->assertInvalidationMax($response);
    }

    public function testInvalidationDataUpdating()
    {
        $gender = Gender::factory(1)->create([
            'name' => 'aaaaa',
            'is_active' => false
        ])->first();
        $response = $this->json(
            'PUT',
            route('genders.update', ['gender'=>$gender->id]),
            [
                'name' => 10,
                'is_active' => 'asss'
            ]);
        $this->assertInvalidationString($response);
        $this->assertInvalidationBoolean($response);

        $response = $this->json(
            'PUT',
            route('genders.update', ['gender'=>$gender->id]),
            [
                'name' => str_repeat('a', 256),
            ]);
        $this->assertInvalidationMax($response);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('genders.store'),[
            'name' => 'test',
            'is_active' => false
        ]);
        $gender = Gender::find($response->json('id'));
        $response
            ->assertStatus(201)
            ->assertJson($gender->toArray());
    }

    public function testUpdate()
    {
        $gender = Gender::create([
            'name' => 'aaaa',
            'is_active' => false
        ])->first();
        $response = $this->json('PUT', route('genders.update', ['gender'=>$gender->id]), [
            'name' => 'test',
            'is_active' => true
        ]);

        $gender->refresh();
        $response
            ->assertStatus(200)
            ->assertJson($gender->toArray())
            ->assertJsonFragment([
                'name' => 'test',
                'is_active' => true
            ]);
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'name'])
            ])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'is active'])
            ]);
    }

    protected function assertInvalidationString(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.string', ['attribute' => 'name']),
            ]);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }
}
