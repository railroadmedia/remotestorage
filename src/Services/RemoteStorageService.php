<?php

namespace Railroad\RemoteStorage\Services;

use Illuminate\Filesystem\FilesystemManager;
use League\Flysystem\Filesystem;

class RemoteStorageService
{
    static $visibilityPublic = ['visibility' => 'public'];

    protected $availableVisibilities = [
        'public' => 'public',
    ];

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var FilesystemManager
     */
    protected $fileSystemManager;

    public function __construct(FilesystemManager $filesystemManager)
    {
        $this->fileSystemManager = $filesystemManager;
        $this->filesystem = $this->fileSystemManager->disk(ConfigService::$defaultFileSystemDisk);
    }

    /**
     * @param string $filenameRelative
     * @param string $filenameAbsolute
     * @return bool
     *
     * Note that 'put' is create or update accordingly. In League\Flysystem there also exist separate create
     * (called 'write') and update methods. They could be used if there was a need to return error if expectation
     * of file to create or update did exist or not already (respectively). For now just using "put" works though.
     *  - Jonathan, Oct 2017
     */
    public function put($filenameRelative, $filenameAbsolute)
    {
        return $this->filesystem->put(
            $filenameRelative,
            file_get_contents($filenameAbsolute),
            self::$visibilityPublic
        );
    }

    /**
     * Read a file
     *
     * @param $target
     * @return bool|false|string
     */
    public function read($target)
    {
        return $this->filesystem->read($target);
    }

    /**
     * Check if file exist
     *
     * @param string $target
     * @return bool
     */
    public function exists($target)
    {
        return $this->filesystem->has($target);
    }

    /**
     * Delete a file
     *
     * @param string $target
     * @return bool
     */
    public function delete($target)
    {
        return $this->filesystem->delete($target);
    }

    /**
     * Rename a file
     *
     * @param string $target
     * @param string $newName
     * @return bool
     */
    public function rename($target, $newName)
    {
        return $this->filesystem->move($target, $newName);
    }

    /**
     * Duplicate a file
     *
     * @param string $original
     * @param string $duplicate
     * @return bool
     */
    public function copy($original, $duplicate)
    {
        return $this->filesystem->copy($original, $duplicate);
    }

    /**
     * Get file mimetype
     *
     * @param string $target
     * @return bool|false|string
     */
    public function getMimetype($target)
    {
        return $this->filesystem->mimeType($target);
    }

    /**
     * Get file timestamp
     *
     * @param string $target
     * @return bool|false|string
     */
    public function getTimestamp($target)
    {
        return $this->filesystem->lastModified($target);
    }

    /**
     * Get file size
     *
     * @param string $target
     * @return bool|false|int
     */
    public function getSize($target)
    {
        return $this->filesystem->fileSize($target);
    }

    /**
     * Create a directory
     *
     * @param string $target
     * @return bool
     */
    public function createDir($target)
    {
        return $this->filesystem->makeDirectory($target);
    }

    /**
     * Delete a directory
     *
     * @param string $target
     * @return bool
     */
    public function deleteDir($target)
    {
        return $this->filesystem->deleteDirectory($target);
    }

    /**
     * Return directory content
     *
     * @param null|string $targetDir
     * @return array
     */
    public function listContents($targetDir = null)
    {
        if (!empty($targetDir)) {
            return $this->filesystem->listContents($targetDir, true);
        } else {
            return $this->filesystem->listContents('/');
        }
    }

    /**
     * Return the target url
     *
     * @param $target
     * @return mixed
     */
    public function url($target)
    {
        return $this->filesystem->getDriver()
            ->getAdapter()
            ->applyPathPrefix($target);
    }

    /**
     * @param $driver
     * @return $this
     */
    public function setDriver($driver)
    {
        $this->filesystem = $this->fileSystemManager->disk($driver);

        return $this;
    }
}