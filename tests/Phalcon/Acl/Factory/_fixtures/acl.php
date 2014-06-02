<?php
return array(
    'defaultAction' => \Phalcon\Acl::DENY,
    'resource' => array(
        'index' => array(
            'description' => 'index resource',
            'actions' => array(
                'index',
                'create',
                'update',
                'delete',
            ),
        ),
        'test' => array(
            'description' => 'test resource',
            'actions' => array(
                'index',
                'test',
            ),
        ),
    ),
    'role' => array(
        'guest' => array(
            'description' => 'guest user',
            'allow' => array(
                'index' => array(
                    'actions' => 'index',
                ),
            ),
        ),
        'user' => array(
            'description' => 'logged in user',
            'inherit' => 'guest',
            'allow' => array(
                'index' => array(
                    'actions' => array(
                        'create',
                        'update',
                        'delete',
                    ),
                ),
                'test' => array(
                    'actions' => array(
                        'index',
                        'test',
                    ),
                ),
            ),
        ),
    ),
);