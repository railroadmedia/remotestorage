<?php

namespace Railroad\RemoteStorage\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Railroad\RemoteStorage\Services\ConfigService;

class RemoteStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->setupConfig();

        $this->publishes(
            [
                __DIR__ . '/../../config/remotestorage.php' => config_path('remotestorage.php'),
            ]
        );
    }

    private function setupConfig()
    {
        ConfigService::$fileSystemDisks = config('remotestorage.filesystems.disks');
        ConfigService::$defaultFileSystemDisk = config('remotestorage.filesystems.default');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}