<?php
use Silex\Provider\HttpCacheServiceProvider;

//ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config/prod.php';
$app = require __DIR__.'/../src/app.php';

$app->register(
    new HttpCacheServiceProvider(),
    array(
        'http_cache.cache_dir' => $config['cache.dir'],
    )
);

require __DIR__.'/../src/controllers.php';
ini_set('display_errors', '0');
$app['http_cache']->run();
