<?php

namespace Railroad\RemoteStorage\Tests;

use Illuminate\Support\Facades\Storage;
use Railroad\RemoteStorage\Services\RemoteStorageService;

class RemoteStorageServiceTest extends RemoteStorageTestCase
{
    /**
     * @var RemoteStorageService
     */
    private $classBeingTested;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classBeingTested = $this->app->make(RemoteStorageService::class);
    }

    public function generate_local_image($fileName = 'test-image.jpg')
    {
        $imgLocalPath = sys_get_temp_dir() . "/" . $fileName;
        $img = imagecreatetruecolor(20, 20);
        $bg = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, 120, 20, $bg);
        imagejpeg($img, $imgLocalPath, 10);

        return $imgLocalPath;
    }

    public function test_put()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        if (!$upload) {
            $this->fail('upload appears to have failed.');
        }

        $this->assertTrue($this->classBeingTested->exists($fileName));
    }

    public function test_read()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        $this->assertTrue($upload);
        $this->assertEquals(
            file_get_contents($this->getFilenameAbsoluteFromRelative($fileName)),
            $this->classBeingTested->filesystem->read($fileName)
        );
    }

    public function test_exists()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        $this->assertTrue($this->classBeingTested->exists($fileName));
    }

    public function test_delete()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        $this->assertTrue($this->classBeingTested->delete($fileName));
        $this->assertFalse($this->classBeingTested->exists($fileName));
    }

    public function test_create_dir()
    {
        $directoryName = 'test-path';
        $results = $this->classBeingTested->createDir($directoryName);

        $this->assertTrue($results);
        $this->assertTrue(Storage::exists($directoryName));
    }

    public function test_rename()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        $results = $this->classBeingTested->rename($fileName, 'roxana.jpg');

        $this->assertTrue($results);
        $this->assertFalse($this->classBeingTested->exists($fileName));
        $this->assertTrue($this->classBeingTested->exists('roxana.jpg'));
    }

    public function test_copy()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        $newFile = 'roxana.jpg';
        $this->classBeingTested->copy($fileName, $newFile);

        $this->assertEquals(
            file_get_contents($imgLocalPath),
            $this->classBeingTested->read($newFile)
        );
        $this->assertEquals(
            file_get_contents($imgLocalPath),
            $this->remoteStorageService->read($newFile)
        );
    }

    public function test_get_mimetype()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        $this->assertEquals(
            exif_read_data($imgLocalPath)['MimeType'],
            $this->classBeingTested->getMimetype($this->getFilenameRelativeFromAbsolute($imgLocalPath))
        );
    }

    public function test_get_timestamp()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        $expected = exif_read_data($imgLocalPath)['FileDateTime'];
        $actual = $this->classBeingTested->getTimestamp($this->getFilenameRelativeFromAbsolute($imgLocalPath));

        /*
         * Actual may be a second or two behind expected because of time file transfer time.
         * Time in exif data of local file is when **dummy** file was created, time retrieved is when file was created
         * in s3 (when transfer was complete).
         */
        $difference = $actual - $expected;
        $this->assertTrue($difference < 5);
    }

    public function test_get_size()
    {
        $fileName = 'test-image.jpg';
        $imgLocalPath = $this->generate_local_image($fileName);

        $upload = $this->classBeingTested->put($fileName, $imgLocalPath);

        $this->assertEquals(
            exif_read_data($imgLocalPath)['FileSize'],
            $this->classBeingTested->getSize($this->getFilenameRelativeFromAbsolute($imgLocalPath))
        );
    }

    public function test_delete_dir()
    {
        $pass = false;
        $dirName = 'test-path';
        $this->classBeingTested->createDir($dirName);
        $contents = $this->classBeingTested->listContents();
        foreach ($contents as $item) {
            if ($item['path'] === $dirName && $item['type'] === 'dir') {
                $pass = true;
            }
        }
        $this->assertTrue($pass);
        $this->classBeingTested->deleteDir($dirName);
        $this->assertFalse(Storage::exists($dirName));
    }
}
