<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\AssetServiceProvider;

$app = new Silex\Application();

$app['debug'] = true;

//register services
$app->register(
    new Silex\Provider\TwigServiceProvider(),
    array(
        'twig.path' => __DIR__.'/../resource/view',
    )
);
$app->register(new LocaleServiceProvider());

$app->register(new SessionServiceProvider());


$app->register(new FormServiceProvider());

$app->register(new TranslationServiceProvider(), array(
    'locale_fallbacks' => array('pl'),
));

$app->register(
    new HttpCacheServiceProvider(),
    array(
        'http_cache.cache_dir' => __DIR__.'/../cache/',
    )
);

$app->register(new AssetServiceProvider(), array(
    'assets.version' => 'v1',
    'assets.version_format' => '%s?version=%s'
));

//set proxy
Request::setTrustedProxies(array('127.0.0.1'));

$app['locale'] = 'pl';

$app->get(
    '/',
    function () use ($app) {
        $body = $app['twig']->render(
            'home.html.twig'
        );

        $response = new Response(
            $body,
            200,
            array('Cache-Control' => 's-maxage=3600, public')
        );
        return $response;
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
