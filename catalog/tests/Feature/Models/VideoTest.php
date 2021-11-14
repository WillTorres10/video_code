<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    private function newVideo(
        $title = 'test', $description = 'a test description', $year_launched = 2010, $opened = true,
        $rating = Video::RATING_LIST[0], $duration = 120
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
        return Video::create($toSave);
    }

    public function testList()
    {
        $this->newVideo();

        $count = Video::all();
        $this->assertCount(1, $count);

        $genderKeys = array_keys(Video::first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'title', 'description', 'year_launched', 'opened', 'rating', 'duration',
            'thumb_file', 'banner_file', 'trailer_file','video_file', 'created_at',
            'updated_at', 'deleted_at'
        ], $genderKeys);
    }

    public function testCreate()
    {
        $video = $this->newVideo();
        $video->refresh();
        $this->assertEquals('test', $video->title);
        $this->assertEquals('a test description', $video->description);
        $this->assertEquals(2010, $video->year_launched);
        $this->assertEquals(Video::RATING_LIST[0], $video->rating);
        $this->assertEquals(120, $video->duration);
        $this->assertTrue($video->opened);

        $video = $this->newVideo(opened: null);
        $video->refresh();
        $this->assertFalse($video->opened);

        $video = $this->newVideo();
        $video->refresh();
        $validator = Validator::make( ['id' => $video->id], ['id'=>'uuid']);
        $this->assertFalse($validator->fails());
    }

    public function testUpdate()
    {
        $video = $this->newVideo();
        $data = [
            'title' => "A updated title",
            'description' => "that's a updated description",
            'opened' => true,
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[1],
            'duration' => 50,
        ];
        $video->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $video->{$key});
        }
    }

    public function testDelete()
    {
        $video = $this->newVideo();
        $idVideo = $video->id;
        $video->delete();
        $videoSearch = Video::find($idVideo);
        $this->assertNull($videoSearch);
    }
}
