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
    function (Request $request) use ($app, $contactForm) {

        if ($request->isMethod('POST')) {
            $contactForm->handleRequest($request);
            if ($contactForm->isValid()) {

                if ($contactForm->get('dummy')->getData()) {
                    echo 'denied';
                    exit;
                }

                $maildata = array();
                $maildata['subject'] = $contactForm->get('subject')->getData();
                $maildata['name'] = $contactForm->get('name')->getData();
                $maildata['email'] = $contactForm->get('email')->getData();
                $maildata['message'] = $contactForm->get('message')->getData();

                $sendContactEmail = $app['mail'];

                if($sendContactEmail($request, $maildata, $app)) {
                    $app['session']->getFlashBag()->add(
                        'success',
                        'contact.flash.sent'
                    );
                } else {
                    $app['session']->getFlashBag()->add(
                        'error',
                        'contact.flash.notsent'
                    );

                };

                $app->redirect('/#contact', 301);
            }
        }

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
