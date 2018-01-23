<?php

namespace App\Helpers;

use Silex\Application;
use Silex\ServiceProviderInterface;
use \Parsedown;

/**
* Global Twig helpers
*/
class Twig implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        function getUrl($app, $link) {
            if(!isset($app['storyblok.links'])) {
                $app['storyblok.links'] = $app['storyblok']->getLinks()->getBody()['links'];
            }
            if(isset($link['id']) && isset($app['storyblok.links'][$link['id']])) {
                $url_slug = $app['storyblok.links'][$link['id']]['slug'];

                if ($url_slug === $app['config.homeSlug']) {
                    return '/';
                }

                return '/'. $url_slug;
            } else {
                return $link['url'];
            }
        }

        function addToUrl($url, $key, $value = null) {
            $query = parse_url($url, PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $queryParams);
                $queryParams[$key] = $value;
                $url = str_replace("?$query", '?' . http_build_query($queryParams), $url);
            } else {
                $url .= '?' . urlencode($key) . '=' . urlencode($value);
            }
            return $url;
        }

        /*
        * Allows to use Markdown in your templates
        *
        * Usage: `{{ $my_url | markdown }}`
        */
        $app['twig']->addFilter(new \Twig_SimpleFilter('markdown', function ($text) {
            if (is_string($text)) {
                return Parsedown::instance()
                    ->setMarkupEscaped(false)
                    ->setBreaksEnabled(true)
                    ->text($text);
            }
            return '';
        }, array('is_safe' => array('html'))));

        /*
        * Appends the version string to an url
        *
        * Usage: `{{ $my_url | version }}`
        */
        $app['twig']->addFilter(new \Twig_SimpleFilter('version', function ($url) use ($app) {
            return addToUrl($url, 'version', $app['config.version']);
        }));

        /*
        * Transforms an `url` object from the storyblok api to an URL.
        *
        * Usage: `{{ $my_url_object | url }}`
        */
        $app['twig']->addFilter(new \Twig_SimpleFilter('url', function ($link) use ($app) {
            return getUrl($app, $link);
        }));

        /*
        * Checks if a url with the storyblok-fieldtype 'link' is set
        *
        * Usage: `{% if isUrl(globalcontent.someLink) %}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('isUrl', function ($link) use ($app) {
            if ((isset($link['id']) && $link['id'] != '') || (isset($link['url']) && $link['url'] != '')) {
                return true;
            }

            return false;
        }));

        /*
        * Pretty dump a variable with pre tags.
        *
        * Usage: `{{ $my_variable | dump }}`
        */
        $app['twig']->addFilter(new \Twig_SimpleFilter('dump', function ($variable) use ($app) {
             echo '<pre>';
             var_dump($variable);
             echo '</pre>';
        }));

        /**
         * Returns true or false if the current url is the active navigation item
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('isActiveNavigationItem', function ($navigationitem) use ($app) {
            $link_url = getUrl($app, $navigationitem);
            $request_url = $_SERVER['REQUEST_URI'];

            if (strlen($request_url) > 1 && substr($request_url, -1) === '/') {
                $request_url = substr($request_url, 0, -1);
            }

            return $link_url === $request_url;
        }));

        /*
        * asset function (beta)
        *
        * Usage: `{{ asset('path') }}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('asset', function ($path) use ($app) {
            return '/' . $path;
        }));

        /*
        * allows you to access the storyblok tags of a folder
        *
        * Usage: `{% set tags = getTags(folder_name) %}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('getTags', function ($starts_with) use ($app) {
            try {
                return $app['storyblok']->getTags(array(
                    'starts_with' => $starts_with
                ))->getTagsAsStringArray();
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
            return array();
        }));

        /*
        * allows you to access a storyblok datasource
        *
        * Usage: `{% set datasources = getDatasourceEntries(folder_name) %}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('getDatasourceEntries', function ($slug) use ($app) {
            try {
                return $app['storyblok']->getDatasourceEntries($slug)->getAsNameValueArray();
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
            return null;
        }));

        /*
        * allows you to access a list of stories from storyblok
        *
        * Usage: `{% set stories = getStories('starts_with') %}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('getStories', function ($starts_with, $page = 1, $per_page = 25, $options = array()) use ($app) {
            try {

                $app['storyblok']->getStories(
                    array_filter(
                        array_merge( $options,
                            array(
                            'starts_with' => $starts_with,
                            'per_page' => $per_page,
                            'page' => $page,
                            )
                        ), function($var) { return !is_null($var); } // removes only NULL
                    )
                );

                $data = $app['storyblok']->getBody();

                return $data['stories'];
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
            return null;
        }));

        /*
        * allows you to use a json to configure the getStories options
        *
        * Usage: `{% set stories = getStories('starts_with', 1, 25, options({"sort_by":"name:asc","is_startpage":false})) %}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('options', function ($string) use ($app) {
            return (array) json_decode($string);
        }));

        /*
        * allows you to access a single story by slug
        *
        * Hint: If you want to get the story from an Storylink just use the `| url` to generate the full slug.
        *
        * Usage: `{% set story = getStoryBySlug('/your/full/slug') %}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('getStoryBySlug', function ($full_slug) use ($app) {
            try {
                $app['storyblok']->getStoryBySlug($full_slug);

                $data = $app['storyblok']->getBody();

                return $data['story'];
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
            return null;
        }));

        /**
         * fetches the global story
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('getGlobalStory', function () use ($app) {
            try {
                $app['storyblok']->getStoryBySlug('global');

                $data = $app['storyblok']->getBody();

                return $data['story'];
            } catch (\Exception $e) {
                return '';
            }
            return null;
        }));

        /**
         * Returns true if you are in local environment
         *
         * Usage: `{% if isLocal() %} ... {% endif %}`
         *
         * @return boolean true if you are in local environment
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('isLocal', function () use ($app) {
            return LOCAL;
        }));

        /**
         * Returns true if you are in test environment
         *
         * Usage: `{% if isTest() %} ... {% endif %}`
         *
         * @return boolean true if you are in test environment
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('isTest', function () use ($app) {
            return TEST;
        }));

        /**
         * Returns true if you are in live environment
         *
         * Usage: `{% if isLive() %} ... {% endif %}`
         *
         * @return boolean true if you are in live environment
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('isLive', function () use ($app) {
            return LIVE;
        }));

        /*
        * Usage: `{{ getPageTitle(story) }}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('getPageTitle', function ($story) use ($app) {
            $title = $story['name'];

            if (isset($story['content']['meta_title'])) {
                $title = $story['content']['meta_title'];
            }

            return $title;
        }));

        /**
         * Returns the canonical of the current page
         *
         * Usage: `{{ getCanonical() }}`
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('getCanonical', function () use ($app) {
            $request_url_elements = explode('?', $_SERVER['REQUEST_URI']);
            $canonical = 'https://' . $app['config.host.live'] . $request_url_elements[0];
            return $canonical;
        }));

        /**
         * Returns the localstorage key for accessing the saved font
         *
         * Usage: `{{ getFontCacheKey() }}`
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('getFontCacheKey', function () use ($app) {
            return $app['config.fontCacheName'];
        }));

        /**
         * Usage: `{{ embedRecaptcha(story.full_slug) }}`
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('embedRecaptcha', function ($full_slug) use ($app) {
            return in_array($full_slug, $app['config.recaptcha.slugs']);
        }));

        /*
        * Image helper
        *
        * Usage: `{{ image(imagepath, width) }}`
        */
        $app['twig']->addFunction(new \Twig_SimpleFunction('image', function ($image, $width) use ($app) {
            $imageService = '//img2.storyblok.com';
            $path = str_replace('//a.storyblok.com', '', $image);

            $output = $imageService;

            if ($width != '') {
                $output .= '/' . $width . 'x0';
            }

            $output .= $path;

            return $output;
        }));

        /**
         * Get the contents of a file
         *
         * @param $path path of the file relative to the `public` directory with leading slash (e.g. /styles/inline.css)
         * @return string Stringified content of the file
         */
        $app['twig']->addFunction(new \Twig_SimpleFunction('include_file', function ($path) use ($app) {
            return @file_get_contents(PUBLIC_DIR . $path);
        }));
    }

    public function boot(Application $app)
    {

    }
}
