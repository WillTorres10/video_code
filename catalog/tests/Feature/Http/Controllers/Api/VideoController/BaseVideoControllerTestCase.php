<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\{Category, Genre, Video};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\{TestSaves, TestValidations};

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidations;

    protected Video $video;
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
    }

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

}
