<?php

$app['debug']                   = true;
$app['config.twig.cache']       = false;
$app['config.redirectHome']    = false;

$app['config.locale']           = 'de';
$app['config.availableLocales'] = array('de');

$app['config.version']           = @file_get_contents(PUBLIC_DIR . '/version.cache'); // initializes with git hash

$app['config.homeSlug']             = 'home';
$app['storyblok.previewToken']  = 'Iw3XKcJb6MwkdZEwoQ9BCQtt'; // a free-for-all example space

$app['config.host.test']        = ''; // test.website.com
$app['config.host.live']        = ''; // www.website.com

$app['config.fontCacheName']           = 'netural-fontcache'; // used for localstorage lowercase (e.g. my-customer-fontcache)

// META
$app['config.meta.brandName']               = ''; // For the meta title

// RECAPTCHA
$app['recaptcha.url']            = 'https://www.google.com/recaptcha/api/siteverify';
$app['config.recaptcha.slugs']              = array(); // Slugs of pages where the recaptcha should be embedded
$app['config.recaptcha.secret']             = ''; // You get the secret at https://www.google.com/recaptcha
$app['config.recaptcha.url']                = 'https://www.google.com/recaptcha/api/siteverify';

$app['config.contactform.googleScriptUrl']  = '';
$app['config.contactform.email.test']       = '';
$app['config.contactform.email.live']       = '';
