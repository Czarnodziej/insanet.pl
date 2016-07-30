<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

//$app['debug'] = true;

//register services
$app->register(
    new Silex\Provider\TwigServiceProvider(),
    array(
        'twig.path' => __DIR__.'/../resource/view',
    )
);
$app->register(new Silex\Provider\LocaleServiceProvider());

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(
    new Silex\Provider\HttpCacheServiceProvider(),
    array(
        'http_cache.cache_dir' => __DIR__.'/../cache/',
    )
);

//set proxy
Request::setTrustedProxies(array('127.0.0.1'));

$app['locale'] = 'pl';

$app->get(
    '/',
    function () {
        return 'helo łorld';
    }
);
$app->get(
    '/test',
    function () {
        return 'helo łorld';
    }
);

$app->get(
    '/hello/{name}',
    function ($name) use ($app) {
        $body = $app['twig']->render(
            'hello.html.twig',
            array(
                'name' => $name,
            )
        );

        $response = new Response(
            $body,
            200,
            array('Cache-Control' => 's-maxage=3600, public')
        );
        return $response;
    }
);

if ($app['debug']) {
    $app->run();
} else {
    $app['http_cache']->run();
}
