<?php
return [
    'debug' => true,
    'cache' => [
        'type' => 'redis',
        'pages' => [
            'active' => false, //
//            'type'   => 'redis',
            'prefix'  => 'pages',
        ]
    ],
];
