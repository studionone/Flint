<?php

return [
    'Fake' => [
        'class' => 'FakeService',
        'arguments' => [ '@Fake2' ]
    ],
    'Fake2' => [
        'class' => 'FakeService2',
        'arguments' => [ 'Josh' ],
    ]
];
