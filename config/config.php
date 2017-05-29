<?php

return [
    'paths' => [
        'resources' => __DIR__ . '/../resources',
        'source' => __DIR__ . '/../source',
        'destination' => __DIR__ . '/../result',
    ],
    'layout' => [
        'styles' => __DIR__ . '/../resources/style/css/BBBDefault.css',
        'background' => __DIR__ . '/../resources/background.jpg',
//        'width' => 1280,  // optional
//        'height' => 720,  // optional
        'windows' => [
            'NotesWindow' => [
                'x' => 0,
                'y' => 0,
                'width' => 1,
                'height' => 1,
                'hidden' => true,
            ],
            'BroadcastWindow' => [
                'x' => 1,
                'y' => 1,
                'width' => 1,
                'height' => 1,
                'hidden' => true,
            ],
            'PresentationWindow' => [
                'x' => 144,
                'y' => 55,
                'width' => 411,
                'height' => 337,
            ],
            'VideoDock' => [
                'x' => 2,
                'y' => 285,
                'width' => 139,
                'height' => 106,
            ],
            'ChatWindow' => [
                'x' => 560,
                'y' => 42,
                'width' => 231,
                'height' => 318,
                'fontSize' => 6,
            ],
            'UsersWindow' => [
                'x' => 2,
                'y' => 38,
                'width' => 138,
                'height' => 211,
                'fontSize' => 6,
            ],
            'ViewersWindow' => [
                'x' => 0,
                'y' => 0,
                'width' => 1,
                'height' => 1,
                'hidden' => true,
            ],
            'ListenersWindow' => [
                'x' => 0,
                'y' => 0,
                'width' => 1,
                'height' => 1,
                'hidden' => true,
            ],
        ],
    ],
    'log' => true
];
