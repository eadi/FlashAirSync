<?php
return [
    [
        'name' => 'FlashAirSync',
        'route' => '<remoteHost> <remoteDir> [--targetDir=] [--interval=0]',
        'prepend_command_to_route' => false,
        'description' => 'Sync given FlashAir sd card path to the current working directory',
        'options_descriptions' => [
            '<remoteHost>' => 'Host name or IP address of the sd card. For example "flashair.local"',
            '<remoteDir>' => 'Directory of the sd card to sync. For example "DCIM/Canon500"',
            'targetDir' => 'Absolute path to a local directory to store successfully synced files. If not specified, the current working directory will be used. For example "/home/max/images/incoming"',
            'interval' => 'Time in seconds between two sync attempts. Omit to sync only once. For example "30"',
        ],
        'defaults' => [
            'targetDir' => getcwd(),
            'interval' => 0,
        ],
        'handler' => FlashAirSync\Command\Command::class,
    ]
];
