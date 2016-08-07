<?php
use Silex\Provider\AssetServiceProvider;

use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$app = new Silex\Application();

$app['lastFMApiKey'] = $parameters['lastFMapiKey'];

//register services
$app->register(
    new Silex\Provider\TwigServiceProvider(),
    array(
        'twig.path' => $config['twig.path'],
        'twig.options' => $config['twig.options']
    )
);
$app->register(new LocaleServiceProvider());

$app->register(new SessionServiceProvider());

$app->register(
    new AssetServiceProvider(),
    array(
        'assets.version'        => '1',
        'assets.version_format' => '%s?version=%s',
    )
);

$app->register(
    new TranslationServiceProvider(),
    array(
        'translator.domains' => array(),
        'locale_fallbacks'   => array('pl'),
    )
);

$app->extend(
    'translator',
    function ($translator) {
        $translator->addLoader('yaml', new YamlFileLoader());

        //main translations
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

        //validators
        $translator->addResource(
            'yaml',
            __DIR__.'/../resource/translation/validators/validators.pl.yml',
            'pl',
            'validators'
        );

        $translator->addResource(
            'yaml',
            __DIR__.'/../resource/translation/validators/validators.en.yml',
            'en',
            'validators'
        );

        return $translator;
    }
);

$app['locale'] = $parameters['mainLocale'];

return $app;

