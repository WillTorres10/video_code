<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Resources\VideoResource;
use App\Models\{Category, Genre, Video};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\{TestResources, TestSaves, TestUploadHooks, TestValidations};

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidations, TestUploadHooks, TestResources;

    protected Video $video, $videoCompleto;
    protected string $stringWith256;
    protected array $sendData;

    protected array $nullFields = [
        'thumb_file' => null,
        'banner_file' => null,
        'trailer_file' => null,
        'video_file' => null,
        'deleted_at' => null
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = Video::factory(1)->create()->first();
        $this->stringWith256 = str_repeat('a', 256);
        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
        ];

        $generated = $this->generateGenresWithCategories();
        $this->videoCompleto = Video::create(
            $this->sendData + $this->selectGenresAndCategories($generated) + $this->generateArrayFilesUploadForModel()
        );
    }

    protected $serializedFields = [
        'id', 'title', 'description', 'year_launched', 'opened', 'rating', 'duration',
        'genres' => ['*' => ['id', 'name']],
        'categories' => ['*' => ['id', 'name']],
        'thumb_file', 'banner_file', 'trailer_file', 'video_file',
        'thumb_file_url', 'banner_file_url', 'trailer_file_url', 'video_file_url',
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected function generateGenresWithCategories()
    {
        $noUsed = Category::factory(5)->create()->pluck('id');
        $toUse = collect();
        $genres = Genre::factory(3)->create();
        foreach ($genres as $genre) {
            $categories = Category::factory(random_int(2, 4))->create()->pluck('id');
            $toUse = $toUse->merge($categories);
            $genre->categories()->sync($categories);
            $genre->load('categories');
        }
        return ['catToNotUse' => $noUsed, 'catToUse' => $toUse, 'genres' => $genres];
    }

    protected function selectGenresAndCategories($generated)
    {
        $genre = $generated['genres']->random();
        return [
            'categories_id' => $genre->categories()->pluck('id')->toArray(),
            'genres_id' => [$genre->id]
        ];
    }

    protected function model()
    {
        return Video::class;
    }

    protected function routeStore()
    {
        return route('video.store');
    }

    protected function routeUpdate()
    {
        return route('video.update', ['video' => $this->video->id]);
    }

    protected function assertVideoResource(TestResponse $response)
    {
        $resource = $this->getResourceOfVideo($response->json('data.id'));
        $this->assertResource($response, (new VideoResource($resource)));
    }

    protected function getResourceOfVideo($id)
    {
        return Video::with(['genres', 'categories'])->findOrFail($id);
    }
}
