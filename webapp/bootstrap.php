<?php

define('WEBAPP_DIR', realpath(__DIR__ . '/'));
define('ROOT_DIR', realpath(__DIR__ . '/../'));
define('PUBLIC_DIR', realpath(__DIR__ . '/../public/'));

require_once __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();

require_once __DIR__ . '/config.php';

if (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false ||
    strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
    strpos($_SERVER['SERVER_NAME'], '0.0.0.0') !== false
) {
    define('LOCAL', true);
} else {
    define('LOCAL', false);
}

if ($app['config.host.test'] != '' && strpos($_SERVER['SERVER_NAME'], $app['config.host.test']) !== false) {
    define('TEST', true);
} else {
    define('TEST', false);
}

if ($app['config.host.live'] != '' && strpos($_SERVER['SERVER_NAME'], $app['config.host.live']) !== false) {
    define('LIVE', true);
} else {
    define('LIVE', false);
}

if (LIVE) {
    $app['debug'] = false;
} else {
    $app['debug'] = true;
}

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => WEBAPP_DIR . '/views',
    'twig.options' => array(
        'strict_variables' => false,
        'cache' => $app['config.twig.cache']
    )
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new App\Helpers\Twig());
$app->register(new App\Providers\Storyblok(), array(
    'storyblok.options' => array(
        'privateToken' => $app['storyblok.previewToken'],
        'cacheProvider' => 'filesystem',
        'cacheOptions' => array('path' => __DIR__ . '/../cache/')
    )
));

$app->boot();
