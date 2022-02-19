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

}
