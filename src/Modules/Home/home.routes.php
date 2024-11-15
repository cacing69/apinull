<?php
use Modules\Home\Http\HomeHandler;

return [
    '/' => [
        'handler' => [HomeHandler::class, 'index'],
        'methods' => ['GET'],
        'middleware' => ['auth'],
    ]
];
