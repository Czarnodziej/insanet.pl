<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Loader\YamlFileLoader;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;



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

$app->register(
    new HttpCacheServiceProvider(),
    array(
        'http_cache.cache_dir' => __DIR__.'/../cache/',
    )
);

$app->register(
    new AssetServiceProvider(),
    array(
        'assets.version'        => 'v1',
        'assets.version_format' => '%s?version=%s',
    )
);

$app->register(new ValidatorServiceProvider());
$app->register(
    new TranslationServiceProvider(),
    array(
        'translator.domains' => array(),
        'locale_fallbacks' => array('pl'),
    )
);

$app->register(new SwiftmailerServiceProvider());

$app->extend(
    'translator',
    function ($translator) {
        $translator->addLoader('yaml', new YamlFileLoader());

        $translator->addResource(
            'yaml',
            __DIR__.'/../resource/translation/pl.yml',
            'pl'
        );
        $translator->addResource(
            'yaml',
            __DIR__.'/../resource/translation/en.yml',
            'en'
        );

        return $translator;
    }
);

//set proxy
Request::setTrustedProxies(array('127.0.0.1'));

$app['locale'] = 'en';


//create contact form
$form = $app['form.factory']->createBuilder(FormType::class)
    ->add(
        'name',
        null,
        array(
            'label'       => false,
            'attr'        => array(
                'placeholder' => 'contact.placeholder.name',
                'pattern'     => '.{2,}', //minlength
                'class'       => 'col-sm-12',
            ),
            'constraints' => array(
                new Assert\NotBlank(
                    array('message' => 'contact.name.not_blank')
                ),
                new Assert\Length(
                    array(
                        'min'        => 2,
                        'minMessage' => 'contact.name.min_message',
                    )
                ),
            ),
        )
    )
    ->add(
        'email',
        EmailType::class,
        array(
            'label'       => false,
            'attr'        => array(
                'placeholder' => 'contact.placeholder.email',
                'class'       => 'col-sm-12',
            ),
            'constraints' => array(
                new Assert\NotBlank(
                    array(
                        'message' => 'contact.email.not_blank',
                    )
                ),
                new Assert\Email(
                    array(
                        'message' => 'contact.email.valid',
                    )
                ),
            ),
        )
    )
    ->add(
        'subject',
        null,
        array(
            'label'       => false,
            'attr'        => array(
                'placeholder' => 'contact.placeholder.subject',
                'pattern'     => '.{3,}', //minlength
                'class'       => 'col-sm-12',
            ),
            'constraints' => array(
                new Assert\NotBlank(
                    array(
                        'message' => 'contact.subject.not_blank',
                    )
                ),
                new Assert\Length(
                    array(
                        'min'        => 3,
                        'minMessage' => 'contact.subject.min_message',
                    )
                ),
            ),
        )
    )
    ->add(
        'message',
        TextareaType::class,
        array(
            'label'       => false,
            'attr'        => array(
                'class'       => 'col-sm-12',
                'rows'        => 10,
                'placeholder' => 'contact.placeholder.message',
            ),
            'constraints' => array(
                new Assert\NotBlank(
                    array(
                        'message' => 'contact.message.not_blank',
                    )
                ),
                new Assert\Length(
                    array(
                        'min'        => 5,
                        'minMessage' => 'contact.message.min_message',
                    )
                ),
            ),
        )
    )
    ->getForm();

//ROUTES
$app->match(
    '/',
    function (Request $request) use ($app, $form) {

        $errors = array();
        $body = $app['twig']->render(
            'home.html.twig',
            array('form' => $form->createView(),
                  'errors' => $errors)
        );


        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $message = \Swift_Message::newInstance()
                    ->setSubject($form->get('subject')->getData())
                    ->setFrom('kontakt@insanet.pl')
                    ->setTo('pagodemc@gmail.com')
                    ->setBody(
                        $this->renderView(
                            ':Mail:contact.html.twig',
                            array(
                                'ip'      => $request->getClientIp(),
                                'name'    => $form->get('name')->getData(),
                                'email'   => $form->get('email')->getData(),
                                'message' => $form->get('message')->getData(),
                            )
                        )
                    );


                $this->get('mailer')->send($message);

                $this->addFlash('success', 'contact.flash.sent');

                return $this->redirect(
                    $this->generateUrl('homepage').'#contact'
                );
            }
        } else {
            $errors = $app['validator']->validate($form);
            var_dump($errors);
            exit;
        }

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
