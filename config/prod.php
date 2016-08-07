<?php
require __DIR__.'/parameters.php';

$config=array();
$config['twig.path'] = array(__DIR__.'/../resource/view');
$config['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');
$config['cache.dir'] = __DIR__.'/../var/cache/';
