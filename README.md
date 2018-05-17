# LoveCoding\ContentCache

ContentCache is a simple package - for web service (API) that helps you cache your content on server and response to clients - One cached, one time redering for everyone.

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install ContentCache.

```bash
$ composer require lovecoding/content-cache
```

This will install ContentCache and all required dependencies. ContentCache requires PHP 5.3.0 or newer.

## Usage

Create an index.php file with the following contents:

```php
<?php

require 'vendor/autoload.php';

$app = new Slim\App();

$container = $app->getContainer();

$container['cacheService'] = function() {
    // path to folder contains cached
    return new \LoveCoding\ContentCache\CacheProvider('../storage/cache');
};

$app->get('/hello/{name}', function ($request, $response, $args) use($container) {
    $cacheService = $container->get('cacheService');

    // $cacheService->cache return a json
    $contentCache = $cacheService->cache($request, $response, function() {
        // This function will run when $content is null on server
        // TODO something

        // return an array
        return ...;
    });
    
    return $response->withJson($contentCache);
});

$app->run();
```

You may quickly test this using the built-in PHP server:
```bash
$ php -S localhost:8000
```

Going to http://localhost:8000/hello/world will now display your content cached.

## Note

It's only save arrays to json file on dish.
