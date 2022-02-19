<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\UUID;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Tests\Traits\TestFileFieldsAndValidations;

class VideoUnitTest extends TestCase
{
    use TestFileFieldsAndValidations;

    protected Video $video;

    protected function setUp():void
    {
        parent::setUp();
        $this->video = new Video();
    }

    protected function getModel()
    {
        return Video::class;
    }

    public function testFillable()
    {
        $fillable = [
            'title', 'description', 'year_launched', 'opened', 'rating', 'duration',
            'thumb_file', 'banner_file', 'trailer_file', 'video_file'
        ];
        $this->assertEquals($fillable, $this->video->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [HasFactory::class, SoftDeletes::class, UUID::class, UploadFiles::class];
        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
    }

    public function testIfKeyTypeIsString()
    {
        $this->assertEquals('string', $this->video->getKeyType());
    }

    public function testIfIncrementingIsFalse()
    {
        $this->assertFalse($this->video->incrementing);
    }

    public function testDatesCast()
    {
        $expectedDates = ['created_at', 'updated_at', 'deleted_at'];
        $dates = $this->video->getDates();
        foreach ($expectedDates as $expected) {
            $this->assertContains($expected, $dates);
        }
        $this->assertCount(count($expectedDates), $dates);
    }

    public function testCastAttributes()
    {
        $casts = ['id' => 'string', 'opened'=>'boolean', 'year_launched' => 'integer', 'duration' => 'integer', 'deleted_at'=>'datetime'];
        $this->assertEquals($casts, $this->video->getCasts());
    }
}
