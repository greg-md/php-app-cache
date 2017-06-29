<?php

return [
    'default_store' => 'base',

    'stores' => [
        'base' => [
            'type' => 'redis',
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port' => getenv('REDIS_PORT') ?: '6379',
        ],
    ],
];
