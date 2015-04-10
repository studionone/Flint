<?php
/**
 * Route definitions
 *
 * Single route shape:
 *    string $route => [
 *        string $method,
 *        string|callable $controller,
 *        ?string $name,
 *        array => [
 *            string $varToBePassedIn
 *            ?string|?callable $converter
 *        ]
 *    ];
 *
 * Route group shape:
 *     string $routeBase => [
 *         string $route => <route as above>,
 *         string $route => <route as above>,
 *         ...
 *     ];
 */
return [
    '/' => [ 'get', function() { return "hello"; }, 'home_test'],
    '/hello/{name}' => [
        'get',
        function($name) { return $name; },
        null,
        [
            'name',
            function($name) { return strrev($name); }
        ]
    ],
    '/post' => [ 'post', function() { return 'from post'; } ],
    '/put' => [ 'put', function() { return 'from put'; } ],
    '/delete' => [ 'delete', function() { return 'from delete'; } ],
    '/fake' => [
        '/' => [ 'get', 'fake.controller:indexAction', 'fake.index' ],
        '/hello/{name}' => [
            'get',
            function($name) { return $name; },
            'fake.hello',
            [ 'name', function($name) { return $name.'!'; } ]
        ],
        '/list' => [ 'get', 'fake.controller:listAction', 'fake.list' ],
        '/post' => [ 'post', function() { return 'from post'; }],
        '/put' => [ 'put', function() { return 'from put'; }],
        '/delete' => [ 'delete', function() { return 'from delete'; }]
    ]
];
