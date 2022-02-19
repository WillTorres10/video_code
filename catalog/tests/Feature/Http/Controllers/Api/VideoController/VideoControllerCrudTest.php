<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;


class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
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

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('video.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }
}
