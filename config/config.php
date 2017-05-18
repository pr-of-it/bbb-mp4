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
                'width' => 896,
                'height' => 720,
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
                'x' => 231,
                'y' => 0,
                'width' => 658,
                'height' => 716,
            ],
            'VideoDock' => [
                'x' => 0,
                'y' => 495,
                'width' => 227,
                'height' => 222,
            ],
            'ChatWindow' => [
                'x' => 892,
                'y' => 0,
                'width' => 388,
                'height' => 717,
            ],
            'UsersWindow' => [
                'x' => 0,
                'y' => 0,
                'width' => 227,
                'height' => 489,
            ],
            'ViewersWindow' => [
                'x' => 0,
                'y' => 0,
                'width' => 227,
                'height' => 242,
                'hidden' => true,
            ],
            'ListenersWindow' => [
                'x' => 247,
                'y' => 1,
                'width' => 227,
                'height' => 242,
                'hidden' => true,
            ],
        ],
    ],
    'log' => true
];
