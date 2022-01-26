<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Exceptions\TestException;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

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


    private function newVideo(
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

    public function testCreateWithBasicFields()
    {
        $video = Video::create($this->data);
        $video->refresh();

        $this->assertTrue(Uuid::isValid($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = Video::create($this->data + ['opened' => true]);
        $this->assertTrue(Uuid::isValid($video->id));
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testCreateRelations()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $video = Video::create($this->data + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id]
            ]
        );

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
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

    public function testRollbackCreate()
    {
        $videoMocker = Mockery::mock(Video::class);
        $videoMocker
            ->shouldReceive('handleRelations')
            ->andThrow(new TestException());
        $this->app->instance(Video::class, $videoMocker);
        try {
            $data = $this->newVideo(get_array: true);
            Video::create($data);
        } catch (TestException $e) {
            $this->assertCount(0, Video::all());
        }
    }

    public function testRollbackUpdate()
    {
        $video = $this->newVideo();
        $videoMocker = Mockery::mock(Video::class);
        $videoMocker
            ->shouldReceive('handleRelations')
            ->andThrow(new TestException());
        $this->app->instance(Video::class, $videoMocker);
        try {
            $video = Video::find($video->id);
            $video->update(['title' => 'updated']);
        } catch (TestException $e) {
            $video = Video::find($video->id);
            $this->assertNotEquals('updated', $video->title);
        }
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

    public function testDelete()
    {
        $video = $this->newVideo();
        $idVideo = $video->id;
        $video->delete();
        $videoSearch = Video::find($idVideo);
        $this->assertNull($videoSearch);
    }
}
