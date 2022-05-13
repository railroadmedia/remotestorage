<?php
return
    [
        'filesystems.disks' => [
            's3' => [
                'driver' => 's3',
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'bucket' => env('AWS_BUCKET', 'drumeo'),
            ],

            'local' => [
                'driver' => 'local',
                'root' => storage_path('app'),
            ],
        ],
        'filesystems.default' => 's3'
    ];
