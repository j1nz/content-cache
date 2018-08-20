# LoveCoding\ContentCache

ContentCache is a simple package - for web service or any web need to that helps you cache your content on server. One time handle, more time using.

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install ContentCache.

```bash
$ composer require lovecoding/content-cache
```

This will install ContentCache and all required dependencies. ContentCache requires PHP 5.3.0 or newer.

## Usage

Create an index.php file with the following contents:

Demo using Slim 3.x framework
```php
<?php

require 'vendor/autoload.php';

$app = new Slim\App();

$container = $app->getContainer();

$container['cacheService'] = function() {
    // path to folder contains cached
    $cacheProvider = new \LoveCoding\ContentCache\CacheProvider('storage/cache');
    
    // add salt to path contains cached file
    // this is optional
    $cacheProvider->setSalt('abc');
    
    return $cacheProvider;
};

$app->get('/cache/array', function ($request, $response, $args) use($container) {
    $cacheService = $container->get('cacheService');

    // $cacheService->cache return a json
    $contentArrayCache = $cacheService->cacheArray($request, function() {
        // This function will run when $content is null on server
        // TODO something

        // Must return an array
        return ...;
    });
    
    return $response->withJson($contentArrayCache);
});

$app->get('/cache/plaintext', function ($request, $response, $args) use($container) {
    $cacheService = $container->get('cacheService');

    $contentCache = $cacheService->cache($request, function() {
        // This function will run when $content is null on server
        // TODO something

        // return and save something you want on server
        return ...;
    });

    return $response->getBody()->write($contentCache);
});

$app->run();
```

You may quickly test this using the built-in PHP server:
```bash
$ php -S localhost:8000
```

Going to http://localhost:8000/cache/array or http://localhost:8000/cache/plaintext will now display your content cached.

