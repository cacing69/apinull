<?php return array (
  'index' => 
  array (
    'static' => 
    array (
      '/ping' => 
      array (
        'path' => '/ping',
        'handler' => 'Modules\\User\\Http\\UserHandler::ping',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      '/profile' => 
      array (
        'path' => '/profile',
        'handler' => 'Modules\\User\\Http\\UserHandler::profile',
        'methods' => 
        array (
          0 => 'GET',
        ),
        'middleware' => 
        array (
          0 => 'auth',
        ),
      ),
      '/check-db' => 
      array (
        'path' => '/check-db',
        'handler' => 'Modules\\User\\Http\\UserHandler::checkDb',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      '/validate' => 
      array (
        'path' => '/validate',
        'handler' => 'Modules\\User\\Http\\UserHandler::formValidate',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      '/scrape/ig/post' => 
      array (
        'path' => '/scrape/ig/post',
        'handler' => 'Modules\\Scrape\\Http\\ScrapeHandler::igPost',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      '/scrape/ig/profile' => 
      array (
        'path' => '/scrape/ig/profile',
        'handler' => 'Modules\\Scrape\\Http\\ScrapeHandler::igProfile',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      '/scrape/ig/feed' => 
      array (
        'path' => '/scrape/ig/feed',
        'handler' => 'Modules\\Scrape\\Http\\ScrapeHandler::igFeed',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      '/auth/register' => 
      array (
        'path' => '/auth/register',
        'handler' => 'Modules\\Auth\\Http\\AuthHandler::register',
        'methods' => 
        array (
          0 => 'POST',
        ),
      ),
      '/auth/token' => 
      array (
        'path' => '/auth/token',
        'handler' => 'Modules\\Auth\\Http\\AuthHandler::token',
        'methods' => 
        array (
          0 => 'POST',
        ),
      ),
      '/auth/profile' => 
      array (
        'path' => '/auth/profile',
        'handler' => 'Modules\\Auth\\Http\\AuthHandler::profile',
        'methods' => 
        array (
          0 => 'GET',
        ),
        'middleware' => 
        array (
          0 => 'auth',
        ),
      ),
      '/' => 
      array (
        'path' => '/',
        'handler' => 'Modules\\Home\\Http\\HomeHandler::index',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      '/dump' => 
      array (
        'path' => '/dump',
        'handler' => 'Modules\\Home\\Http\\HomeHandler::dump',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      '/check' => 
      array (
        'path' => '/check',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'POST',
        ),
        'middleware' => 
        array (
          0 => 'auth',
        ),
        'handler' => 'Modules\\Home\\Http\\HomeHandler::check',
      ),
    ),
    'dynamic' => 
    array (
      0 => 
      array (
        'path' => '/check/{id}',
        'handler' => 'Modules\\User\\Http\\UserHandler::check',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
      1 => 
      array (
        'path' => '/path/{id}',
        'handler' => 'Modules\\Home\\Http\\HomeHandler::path',
        'methods' => 
        array (
          0 => 'GET',
        ),
      ),
    ),
  ),
  'routes' => 
  array (
    0 => 
    array (
      'path' => '/ping',
      'handler' => 'Modules\\User\\Http\\UserHandler::ping',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    1 => 
    array (
      'path' => '/profile',
      'handler' => 'Modules\\User\\Http\\UserHandler::profile',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'auth',
        1 => 'CorsMiddleware',
        2 => 'FixHeadersMiddleware',
        3 => 'InputSanitizationMiddleware',
      ),
    ),
    2 => 
    array (
      'path' => '/check/{id}',
      'handler' => 'Modules\\User\\Http\\UserHandler::check',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    3 => 
    array (
      'path' => '/check-db',
      'handler' => 'Modules\\User\\Http\\UserHandler::checkDb',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    4 => 
    array (
      'path' => '/validate',
      'handler' => 'Modules\\User\\Http\\UserHandler::formValidate',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    5 => 
    array (
      'path' => '/scrape/ig/post',
      'handler' => 'Modules\\Scrape\\Http\\ScrapeHandler::igPost',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    6 => 
    array (
      'path' => '/scrape/ig/profile',
      'handler' => 'Modules\\Scrape\\Http\\ScrapeHandler::igProfile',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    7 => 
    array (
      'path' => '/scrape/ig/feed',
      'handler' => 'Modules\\Scrape\\Http\\ScrapeHandler::igFeed',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    8 => 
    array (
      'path' => '/auth/register',
      'handler' => 'Modules\\Auth\\Http\\AuthHandler::register',
      'methods' => 
      array (
        0 => 'POST',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    9 => 
    array (
      'path' => '/auth/token',
      'handler' => 'Modules\\Auth\\Http\\AuthHandler::token',
      'methods' => 
      array (
        0 => 'POST',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    10 => 
    array (
      'path' => '/auth/profile',
      'handler' => 'Modules\\Auth\\Http\\AuthHandler::profile',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'auth',
        1 => 'CorsMiddleware',
        2 => 'FixHeadersMiddleware',
        3 => 'InputSanitizationMiddleware',
      ),
    ),
    11 => 
    array (
      'path' => '/',
      'handler' => 'Modules\\Home\\Http\\HomeHandler::index',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    12 => 
    array (
      'path' => '/path/{id}',
      'handler' => 'Modules\\Home\\Http\\HomeHandler::path',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    13 => 
    array (
      'path' => '/dump',
      'handler' => 'Modules\\Home\\Http\\HomeHandler::dump',
      'methods' => 
      array (
        0 => 'GET',
      ),
      'middleware' => 
      array (
        0 => 'CorsMiddleware',
        1 => 'FixHeadersMiddleware',
        2 => 'InputSanitizationMiddleware',
      ),
    ),
    14 => 
    array (
      'path' => '/check',
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
      ),
      'middleware' => 
      array (
        0 => 'auth',
        1 => 'CorsMiddleware',
        2 => 'FixHeadersMiddleware',
        3 => 'InputSanitizationMiddleware',
      ),
      'handler' => 'Modules\\Home\\Http\\HomeHandler::check',
    ),
  ),
);