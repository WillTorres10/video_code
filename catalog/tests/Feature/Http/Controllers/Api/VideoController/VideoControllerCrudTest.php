<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use Tests\Traits\TestResources;


class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestResources;

    protected function getModel()
    {
        return Video::class;
    }

    public function testIndex()
    {
        $response = $this->json('GET', route('video.index'));
        $response
            ->assertStatus(200)
            ->assertJson(['meta' => ['per_page' => 15]])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializedFields],
                'links' => [],
                'meta' => []
            ]);
        $this->video->load(['categories', 'genres']);
        $this->videoCompleto->load(['categories', 'genres']);
        $resource = VideoResource::collection(collect([$this->videoCompleto, $this->video]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        foreach ([$this->video, $this->videoCompleto] as $video) {
            $response = $this->json('GET', route('video.show', ['video' => $video->id]));
            $response
                ->assertStatus(200)
                ->assertJsonStructure(['data' => $this->serializedFields]);
            $this->assertVideoResource($response);
        }
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
            $this->assertVideoResource($response);
            $response = $this->assertUpdate($value['send_data'], $value['test_data']);
            $this->assertVideoResource($response);
        }
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('video.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }
}
