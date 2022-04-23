<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Tests\TestCase;
use Tests\Traits\{TestResources, TestSaves, TestValidations};

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidations, TestResources;

    private CastMember $castMember;

    private array $validDirectorData = [ 'name' => 'aaaaa', 'type' => CastMember::TYPE_DIRECTOR ];
    private array $validActorData = [ 'name' => 'bbbbb', 'type' => CastMember::TYPE_ACTOR ];

    private string $stringWith256;

    private $serializedFields = ['id', 'name', 'type', 'updated_at', 'created_at', 'deleted_at'];

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
            ->assertJson(['meta' => ['per_page' => 15]])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializedFields],
                'links' => [],
                'meta' => []
            ]);
        $resource = CastMemberResource::collection(collect([$this->castMember]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this
            ->json('GET', route('caster_member.show', ['caster_member'=>$this->castMember->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertCastMemberResource($response);
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
        $response = $this->assertStore($this->validActorData, $this->validActorData + ['deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertCastMemberResource($response);
    }

    public function testUpdate()
    {
        $this->castMember = CastMember::factory(1)->create($this->validDirectorData)->first();
        $response = $this->assertUpdate($this->validActorData, $this->validActorData + ['deleted_at'=>null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertCastMemberResource($response);
    }

    private function assertCastMemberResource(TestResponse $response)
    {
        $cast_member = CastMember::findOrFail($response->json('data.id'));
        $this->assertResource($response, (new CastMemberResource($cast_member)));
    }
}
