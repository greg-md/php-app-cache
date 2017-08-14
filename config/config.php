<?php

return [
    'default_store' => 'base',

    'stores' => [
        'base' => [
            'type' => \Greg\AppCache\CacheServiceProvider::TYPE_REDIS,
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port' => getenv('REDIS_PORT') ?: '6379',
        ],
    ],
];
