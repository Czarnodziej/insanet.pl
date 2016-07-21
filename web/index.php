<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

#register services
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'loclahost',
        'dbname'    => 'my_database',
        'user'      => 'my_username',
        'password'  => 'my_password',
        'charset'   => 'utf8mb4',
    ),
));

$app['locale'] = 'pl';

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