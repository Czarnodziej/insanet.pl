<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->get('/', function () {
    return 'helo łorld';
});
$app->get('/test', function () {
    return 'helo łorld';
});

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Sziema '.$app->escape($name);
});



$app->run();