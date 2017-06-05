<?php

use Silex\Provider\SwiftmailerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require __DIR__.'/../src/services.php';

$app->register(new SwiftmailerServiceProvider());

$forms = require __DIR__.'/../src/forms.php';

$contactForm = $forms['contactForm'];

$app->before(
    function (Request $request) use ($app) {

        if ($request->getHost() === $app['enDomain']) {
            $app['translator']->setLocale('en');
        }
        else {
            $app['translator']->setLocale('pl');
        }
    }
);

$app->match(
    '/{locale}',
    function () use ($app, $contactForm) {

        $body = $app['twig']->render(
            'home.html.twig',
            array(
                'form'        => $contactForm->createView(),
                'pageModTime' => $app['pageModTime'],
                'tracks'      => $app['lastFMTracks'],
            )
        );

        $response = new Response(
            $body,
            200,
            //six hours reverse proxy cache expiration
            array('Cache-Control' => 's-maxage=21600, public')
        );

        return $response;
    }
)->value('locale', '/');;
