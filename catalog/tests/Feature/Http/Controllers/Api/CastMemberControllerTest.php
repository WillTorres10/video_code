<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Validation\Rule;
use Tests\TestCase;
use Tests\Traits\{TestSaves, TestValidations};

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidations;

    private CastMember $castMember;

    private array $validDirectorData = [ 'name' => 'aaaaa', 'type' => CastMember::TYPE_DIRECTOR ];
    private array $validActorData = [ 'name' => 'bbbbb', 'type' => CastMember::TYPE_ACTOR ];

    private string $stringWith256;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = CastMember::factory(1)->create()->first();
        $this->stringWith256 = str_repeat('a', 256);
    }

    protected function model()
    {
        return CastMember::class;
    }

    protected function routeStore()
    {
        return route('caster_member.store');
    }

    protected function routeUpdate()
    {
        return route('caster_member.update', ['caster_member'=>$this->castMember->id]);
    }

    public function testIndex()
    {
        $response = $this
            ->json('GET', route('caster_member.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this
            ->json('GET', route('caster_member.show', ['caster_member'=>$this->castMember->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
    }

    public function testInvalidationDataCreation()
    {
        $this->assertInvalidationInStoreAction(['name'=>'', 'type'=>''], 'required');
        $this->assertInvalidationInStoreAction(['name' => $this->stringWith256],'max.string', ['max' => 255]);
        $this->assertInvalidationInStoreAction(['type' => 'a'],'intenger');
        $this->assertInvalidationInStoreAction(['type' => 3], 'in', ['in' => Rule::in(CastMember::$types)]);
    }

    public function testInvalidationDataUpdating()
    {
        $this->assertInvalidationInUpdateAction(['name'=>'', 'type'=>''], 'required');
        $this->assertInvalidationInUpdateAction(['name' => $this->stringWith256],'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction(['type' => 'a'],'integer');
        $this->assertInvalidationInUpdateAction(['type' => 3],'in', ['in' => Rule::in(CastMember::$types)]);
    }

    public function testStore()
    {
        $this->assertStore($this->validActorData, $this->validActorData + ['deleted_at' => null]);
    }

    public function testUpdate()
    {
        $this->castMember = CastMember::factory(1)->create($this->validDirectorData)->first();
        $response = $this->assertUpdate($this->validActorData, $this->validActorData + ['deleted_at'=>null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }
}
