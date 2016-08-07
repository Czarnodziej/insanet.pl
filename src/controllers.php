<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\SwiftmailerServiceProvider;

require __DIR__.'/../src/services.php';

$app->register(new SwiftmailerServiceProvider());

$forms = require __DIR__.'/../src/forms.php';

Request::setTrustedProxies(array('127.0.0.1'));

$app->match(
    '/',
    function (Request $request) use ($app, $forms) {

        if ($request->isMethod('POST')) {
            $forms->handleRequest($request);
            if ($forms->isValid()) {

                if ($forms['contactForm']->get('dummy')->getData()) {
                    echo 'denied';
                    exit;
                }
                $message = \Swift_Message::newInstance()
                                         ->setSubject($forms['contactForm']->get('subject')->getData())
                                         ->setFrom('kontakt@insanet.pl')
                                         ->setTo('pagodemc@gmail.com')
                                         ->setBody(
                                             $app['twig']->render(
                                                 'mail/contact.html.twig',
                                                 array(
                                                     'ip'      => $request->getClientIp(),
                                                     'name'    => $forms['contactForm']->get('name')->getData(),
                                                     'email'   => $forms['contactForm']->get('email')->getData(),
                                                     'message' => $forms['contactForm']->get('message')->getData(),
                                                 )
                                             )
                                         );


                $app['mailer']->send($message);
                $app['session']->getFlashBag()->add(
                    'success',
                    'contact.flash.sent'
                );

                $app->redirect('/#contact', 301);
            }
        }

        $body = $app['twig']->render(
            'home.html.twig',
            array(
                'form'        => $forms['contactForm']->createView(),
                'pageModTime' => $app['pageModTime'],
                'tracks'      => $app['lastFMTracks'],
            )
        );

        $response = new Response(
            $body,
            200,
            //one year reverse proxy cache expiration
            array('Cache-Control' => 's-maxage=31536000, public')
        );

        return $response;
    }
);
