<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Tests\Traits\TestUploadHooks;

class VideoControllerUploadTest extends BaseVideoControllerTestCase
{
    use TestUploadHooks;

    protected function getModel()
    {
        return Video::class;
    }

    public function testInvalidationIfIsFile()
    {
        Storage::fake();
        $fieldsToTest = [];
        foreach (Video::$fileFields as $field){
            $fieldsToTest[$field] = 'test';
        }
        $this->assertInvalidationInStoreAction($fieldsToTest, 'file');
    }

    public function testInvalidationMaxFileSize()
    {
        Storage::fake();
        $files = $this->generateArrayFilesUploadForModel(10);
        $this->assertInvalidationInStoreAction($files, 'max.file');
    }

    public function testInvalidationMimetypeFiles()
    {
        Storage::fake();
        $files = $this->generateArrayFilesUploadForModel(reverse: true);
        $this->assertInvalidationInStoreAction($files, 'mimetypes');
    }

    public function testUploadFileStore()
    {
        Storage::fake();
        $generated = $this->generateGenresWithCategories();
        $data = $this->sendData + $this->selectGenresAndCategories($generated);
        $data += $this->generateArrayFilesUploadForModel();
        $response = $this->json('POST', $this->routeStore(), $data);
        $id = $response->json('id');
        foreach (Video::getFilesFieldsRules() as $fieldRules) {
            $file_name = $response->json($fieldRules->field);
            Storage::assertExists("$id/$file_name");
        }
    }

    public function testUploadFileOnUpdateWithoutExistingFile()
    {
        Storage::fake();
        $generated = $this->generateGenresWithCategories();
        $data = $this->sendData + $this->selectGenresAndCategories($generated);
        $data += $this->generateArrayFilesUploadForModel();
        $toTest = $this->sendData;
        foreach (Video::$fileFields as $field) {
            $toTest[$field] = $data[$field]->hashName();
        }
        $this->assertUpdate($data, $toTest);
        foreach (Video::$fileFields as $field) {
            Storage::assertExists("{$this->video->id}/{$toTest[$field]}");
        }
    }

    public function testUploadFileOnUpdateWithExistingFile()
    {
        // Setting file for the first time
        Storage::fake();
        $generated = $this->generateGenresWithCategories();
        $data = $this->sendData + $this->selectGenresAndCategories($generated);
        $data += $this->generateArrayFilesUploadForModel();
        $toTest = $this->sendData;
        foreach (Video::$fileFields as $field) {
            $toTest[$field] = $data[$field]->hashName();
        }
        $this->assertUpdate($data, $toTest);
        foreach (Video::$fileFields as $field) {
            Storage::assertExists("{$this->video->id}/{$toTest[$field]}");
        }
        // Updating existing files
        $toUpdate = [
            'title' => 'title updated',
            'description' => 'description updated',
            'year_launched' => 2011,
            'rating' => Video::RATING_LIST[1],
            'duration' => 95,
        ];
        $data2 = $toUpdate + $this->selectGenresAndCategories($generated);
        $data2 += $this->generateArrayFilesUploadForModel();
        $toTest2 = $toUpdate;
        foreach (Video::$fileFields as $field) {
            $toTest2[$field] = $data2[$field]->hashName();
        }
        $this->assertUpdate($data2, $toTest2);
        foreach (Video::$fileFields as $field) {
            Storage::assertMissing("{$this->video->id}/{$toTest[$field]}");
            Storage::assertExists("{$this->video->id}/{$toTest2[$field]}");
        }
    }
}
