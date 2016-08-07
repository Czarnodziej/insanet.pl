<?php

use Silex\Provider\SwiftmailerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require __DIR__.'/../src/services.php';

$app->register(new SwiftmailerServiceProvider());

$forms = require __DIR__.'/../src/forms.php';

Request::setTrustedProxies(array('127.0.0.1'));

$contactForm = $forms['contactForm'];
$app->match(
    '/',
    function (Request $request) use ($app, $contactForm) {

        if ($request->isMethod('POST')) {
            $contactForm->handleRequest($request);
            if ($contactForm->isValid()) {

                if ($contactForm->get('dummy')->getData()) {
                    echo 'denied';
                    exit;
                }
                $message = \Swift_Message::newInstance()
                                         ->setSubject($contactForm->get('subject')->getData())
                                         ->setFrom('kontakt@insanet.pl')
                                         ->setTo('pagodemc@gmail.com')
                                         ->setBody(
                                             $app['twig']->render(
                                                 'mail/contact.html.twig',
                                                 array(
                                                     'ip'      => $request->getClientIp(),
                                                     'name'    => $contactForm->get('name')->getData(),
                                                     'email'   => $contactForm->get('email')->getData(),
                                                     'message' => $contactForm->get('message')->getData(),
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
                'form'        => $contactForm->createView(),
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
