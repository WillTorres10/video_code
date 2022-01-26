<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\{Category, Genre, Video};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Ramsey\Collection\Collection;
use Tests\TestCase;
use Tests\Traits\{TestSaves, TestValidations};
use Tests\Exceptions\TestException;
use Mockery;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidations;

    private Video $video;
    private string $stringWith256;
    private array $sendData;
    private array $nullFields = [
        'thumb_file' => null,
        'banner_file' => null,
        'trailer_file' => null,
        'video_file' => null,
        'deleted_at' => null
    ];

    public function testIndex()
    {
        $response = $this->json('GET', route('video.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->json('GET', route('video.show', ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationMax()
    {
        $data = ['title' => $this->stringWith256];
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = ['duration' => 'A'];
        $this->assertInvalidationInUpdateAction($data, 'integer');
        $this->assertInvalidationInStoreAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = ['year_launched' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = ['opened' => 's'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField()
    {
        $data = ['rating' => 0];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testInvalidationCategoriesIdField()
    {
        $data = ['genres_id'=> [], 'categories_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');
        $data = ['genres_id'=> [], 'categories_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationGenersIdField()
    {
        $data = ['genres_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');
        $data = ['genres_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationCategoriesRelatedWithGenres()
    {
        $generated = $this->generateGenresWithCategories();
        $genreSelected = $this->selectGenresAndCategories($generated);
        $genreSelected['categories_id'] += $generated['catToNotUse']->toArray();
        $data = [
            [
                'categories_id' => $generated['catToNotUse'],
                'genres_id' => [$generated['genres']->random()->id]
            ], [
                'categories_id' => $generated['catToNotUse'],
                'genres_id' => $generated['genres']->random(2)->pluck('id')
            ], $genreSelected
        ];
        foreach ($data as $testData) {
            $this->assertInvalidationStoreActionSpecificFields($testData, ['categories_id'], 'categories.no_related');
            $this->assertInvalidationInUpdateActionSpecificFields($testData, ['categories_id'], 'categories.no_related');
        }
    }

    public function testSave()
    {
        $generated = $this->generateGenresWithCategories();
        $this->video->update(['opened' => false]);
        $data = [
            [
                'send_data' => $this->sendData + $this->selectGenresAndCategories($generated),
                'test_data' => $this->sendData + ['opened' => false]
            ], [
                'send_data' => $this->sendData + $this->selectGenresAndCategories($generated) + ['opened' => true],
                'test_data' => $this->sendData + ['opened' => true]
            ], [
                'send_data' => $this->sendData + $this->selectGenresAndCategories($generated) + ['rating' => Video::RATING_LIST[1]],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
            ],
        ];
        foreach ($data as $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data']);
            $response->assertJsonStructure(['created_at', 'updated_at', 'deleted_at']);
            $this->assertUpdate($value['send_data'], $value['test_data'] + $this->nullFields);
        }
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

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('video.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

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
