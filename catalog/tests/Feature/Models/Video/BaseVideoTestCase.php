<?php

namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

abstract class BaseVideoTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90
        ];
    }

    protected function newVideo(
        $title = 'test', $description = 'a test description', $year_launched = 2010, $opened = true,
        $rating = Video::RATING_LIST[0], $duration = 120, $get_array = false
    )
    {
        $toSave = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $year_launched,
            'rating' => $rating,
            'duration' => $duration
        ];
        if(isset($opened)) {
            $toSave['opened'] = $opened;
        }
        if($get_array)
            return $toSave;
        return Video::create($toSave);
    }

    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);
    }
}
