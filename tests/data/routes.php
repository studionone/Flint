<?php

return [
    '/' => [ 'get', function() { return "hello"; }],
    '/post' => [ 'post', function() { return 'from post'; }],
    '/put' => [ 'put', function() { return 'from put'; }],
    '/delete' => [ 'delete', function() { return 'from delete'; }],
    '/fake' => [
        '/' => [ 'get', 'fake.controller:indexAction' ],
        '/list' => [ 'get', 'fake.controller:listAction' ],
        '/post' => [ 'post', function() { return 'from post'; }],
        '/put' => [ 'put', function() { return 'from put'; }],
        '/delete' => [ 'delete', function() { return 'from delete'; }]
    ],
    '/plus-2/{number}' => [
        'get',
        'some.controller:addAction',
        'add_number',
        null,
        [
            'name',
            '(\d+)'
        ]
    ]
/*,
    '/test' => [
        'method' => 'get',
        'name' => 'test',
        'converter' => null, // callable or string
        'controller' => null, //callable or string
    ]*/
];
