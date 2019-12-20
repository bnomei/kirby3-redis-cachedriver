<?php
return [
    'debug' => true,
    'cache' => [
        'type' => 'redis',
        'pages' => [
            'active' => true,
//            'type'   => 'redis',
            'prefix'  => 'pages',
        ]
    ],
];
