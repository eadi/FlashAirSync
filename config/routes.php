<?php
return [
    [
        'name' => '<host> <dir>',
        'description' => 'Sync given FlashAir sd card to given dir',
        'short_description' => 'Sync card',
        'handler' => FlashAirSync\Command\Command::class,
    ]
];
