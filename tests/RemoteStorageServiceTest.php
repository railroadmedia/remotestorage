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
}
