<?php


use Illuminate\Support\Facades\Storage;
use Railroad\RemoteStorage\Services\RemoteStorageService;
use Railroad\RemoteStorage\Tests\RemoteStorageTestCase;

class RemoteStorageServiceTest extends RemoteStorageTestCase
{
    /**
     * @var RemoteStorageService
     */
    private $classBeingTested;

    protected function setUp()
    {
        parent::setUp();
        $this->classBeingTested = $this->app->make(RemoteStorageService::class);
    }

    public function test_put()
    {
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());
        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);

        $upload = $this->classBeingTested->put($filenameRelative, $filenameAbsolute);

        if (!$upload) {
            $this->fail('upload appears to have failed.');
        }

        $this->assertTrue($this->classBeingTested->exists($filenameRelative));
    }

    public function test_read()
    {
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());
        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);

        $upload = $this->classBeingTested->put($filenameRelative, $filenameAbsolute);
        $this->assertTrue($upload);
        $this->assertEquals(
            file_get_contents($this->getFilenameAbsoluteFromRelative($filenameRelative)),
            $this->classBeingTested->filesystem->read($filenameRelative)
        );
    }

    public function test_exists()
    {
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());
        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);

        $this->classBeingTested->put($filenameRelative, $filenameAbsolute);

        $this->assertTrue($this->classBeingTested->exists($filenameRelative));
    }

    public function test_delete()
    {
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());
        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);
        $this->classBeingTested->put($filenameRelative, $filenameAbsolute);

        $this->assertTrue($this->classBeingTested->delete($filenameRelative));
        $this->assertFalse($this->classBeingTested->exists($filenameRelative));
    }

    public function test_create_dir()
    {
        $directoryName = $this->faker->word;
        $results = $this->classBeingTested->createDir($directoryName);

        $this->assertTrue($results);
        $this->assertTrue(Storage::exists($directoryName));
    }

    public function test_rename()
    {
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());

        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);
        $this->classBeingTested->put($filenameRelative, $filenameAbsolute);
        $results = $this->classBeingTested->rename($filenameRelative, 'roxana.jpg');

        $this->assertTrue($results);
        $this->assertFalse($this->classBeingTested->exists($filenameRelative));
    }

    public function test_copy()
    {
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());

        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);
        $this->classBeingTested->put($filenameRelative, $filenameAbsolute);


        $newFile = $this->faker->word . '.' . $this->faker->word;
        $this->classBeingTested->copy($filenameRelative, $newFile);

        $this->assertEquals(
            file_get_contents($filenameAbsolute),
            $this->classBeingTested->read($newFile)
        );
        $this->assertEquals(
            file_get_contents($filenameAbsolute),
            $this->remoteStorageService->read($newFile)
        );
    }

    public function test_get_mimetype()
    {
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());

        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);
        $this->classBeingTested->put($filenameRelative, $filenameAbsolute);

        $this->assertEquals(
            exif_read_data($filenameAbsolute)['MimeType'],
            $this->classBeingTested->getMimetype($this->getFilenameRelativeFromAbsolute($filenameAbsolute))
        );
    }

    public function test_get_timestamp()
    {
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());

        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);
        $this->classBeingTested->put($filenameRelative, $filenameAbsolute);

        $expected = exif_read_data($filenameAbsolute)['FileDateTime'];
        $actual = $this->classBeingTested->getTimestamp($this->getFilenameRelativeFromAbsolute($filenameAbsolute));

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
        $filenameAbsolute = $this->faker->image(sys_get_temp_dir());

        $filenameRelative = $this->getFilenameRelativeFromAbsolute($filenameAbsolute);
        $this->classBeingTested->put($filenameRelative, $filenameAbsolute);

        $this->assertEquals(
            exif_read_data($filenameAbsolute)['FileSize'],
            $this->classBeingTested->getSize($this->getFilenameRelativeFromAbsolute($filenameAbsolute))
        );
    }

    public function test_delete_dir()
    {
        $pass = false;
        $word = $this->faker->word;
        $this->classBeingTested->createDir($word);
        $contents = $this->classBeingTested->listContents();
        foreach($contents as $item){
            if($item['basename'] === $word && $item['type'] === 'dir'){
                $pass = true;
            }
        }
        $this->assertTrue($pass);
        $this->classBeingTested->deleteDir($word);
        $this->assertFalse(Storage::exists($word));
    }
}
