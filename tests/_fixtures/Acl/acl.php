<?php

use Phalcon\Acl;

return [
    'acl' => [
        'defaultAction' => Acl::DENY,
        'resource' => [
            'index' => [
                'description' => 'index resource',
                'actions' => [
                    'index',
                    'create',
                    'update',
                    'delete',
                ],
            ],
            'test' => [
                'description' => 'test resource',
                'actions' => [
                    'index',
                    'test',
                ],
            ],
        ],
        'role' => [
            'guest' => [
                'description' => 'guest user',
                'allow' => [
                    'index' => [
                        'actions' => 'index',
                    ],
                ],
            ],
            'user' => [
                'description' => 'logged in user',
                'inherit' => 'guest',
                'allow' => [
                    'index' => [
                        'actions' => [
                            'create',
                            'update',
                            'delete',
                        ],
                    ],
                    'test' => [
                        'actions' => [
                            'index',
                            'test',
                        ],
                    ],
                ],
            ],
        ],
    ]
];
