<?php
return [
    [
        'name' => '<remoteHost> <remoteDir>',
        'description' => 'Sync given FlashAir sd card to given dir',
        'short_description' => 'Sync card',
        'handler' => FlashAirSync\Command\Command::class,
    ]
];
