<?php

namespace Railroad\RemoteStorage\Tests;

use Faker\Generator;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Railroad\RemoteStorage\Providers\RemoteStorageServiceProvider;
use Railroad\RemoteStorage\Services\RemoteStorageService;


class RemoteStorageTestCase extends BaseTestCase
{

    /**
     * @var Generator
     */
    protected $faker;

    protected $remoteStorageService;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('cache:clear', []);
        $this->faker = $this->app->make(Generator::class);
        $this->remoteStorageService = $this->app->make(RemoteStorageService::class);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // setup package config for testing
        $defaultConfig = require(__DIR__ . '/../config/remotestorage.php');
        $app['config']->set('filesystems.disks', $defaultConfig['filesystems.disks']);
        $app['config']->set('filesystems.default', $defaultConfig['filesystems.default']);


        // register provider
        $app->register(RemoteStorageServiceProvider::class);
    }

    /**
     * @param string $filenameAbsolute
     * @return string
     */
    protected function getFilenameRelativeFromAbsolute($filenameAbsolute)
    {
        $tempDirPath = sys_get_temp_dir() . '/';

        return str_replace($tempDirPath, '', $filenameAbsolute);
    }

    /**
     * @param string $filenameRelative
     * @return string
     */
    protected function getFilenameAbsoluteFromRelative($filenameRelative)
    {
        return sys_get_temp_dir() . '/' . $filenameRelative;
    }

    protected function tearDown()
    {
        parent::tearDown();

        $contentsList = $this->remoteStorageService->listContents();
        foreach ($contentsList as $content) {
            if ($content['type'] == "file") {
                $this->remoteStorageService->delete($content['path']);
            } else if ($content['type'] == "dir" && ($content['path'] != "framework")) {
                $this->remoteStorageService->deleteDir($content['path']);
            }
        }
    }
}