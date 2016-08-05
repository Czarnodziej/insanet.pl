<?php
$app = require __DIR__.'/../src/app.php';

if ($app['debug']) {
    $app->run();
} else {
    $app['http_cache']->run();
}
