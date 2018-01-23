<p align="center">
  <h1 align="center">netural-boilerplate for Storyblok</h1>
  <p align="center">A <a href="https://www.storyblok.com" target="_blank">Storyblok</a> boilerplate in php with silex to simply start your website with us.</p>
</p>
<br>

## Install

The most efficient way to start a storyblok project as a developer would be our [Command Line Interface](https://www.storyblok.com/docs/Guides/command-line-interface).

```
npm i storyblok-cli -g
storyblok
```

and choose your boilerplate. You can of course simply `download` or `clone` this repository as well.

```
git clone https://github.com/netural/netural-boilerplate
```

Make sure [Composer](https://getcomposer.org/) and [npm](https://www.npmjs.com/) are installed:

```shell
cd netural-boilerplate
composer install
npm install
gulp
```

If you are creating a fresh project with this boilerplate please run the following command once to update composer dependencies and commit the new `composer.lock`.
```shell
composer update
```

## Configuration
In the `webapp/config.php` all you need to change is the `STORYBLOK_CONFIGURATION` - by adding your space information. [What is a Space?](https://www.storyblok.com/docs/terminology/space):

```PHP
$app['config.homeSlug']             = 'home'; //change this to your home story slug
$app['storyblok.previewToken']  = 'Iw3XKcJb6MwkdZEwoQ9BCQtt'; // change this to your private key.
```

## Folder structure

- `/app/`
  scripts, styles, images
  make sure to add a gulp task which copies that to the `public` folder.
- `/webapp/`
  The php application using [silex](http://silex.sensiolabs.org/) and our [client library](https://github.com/storyblok/php-client).
- `/webapp/views/`
  layouts and components in [Twig](http://twig.sensiolabs.org/) (`.twig`)
  If you create a `Footer Navigation` component in Storyblok, the corresponding `footer_headline.twig` in this folder will be rendered
- `/public/`
  Once you run `gulp` the `app` source files will be prepared and copied to the `/public/` folder for delivery.
- `/cache/`
  Our [client library](https://github.com/storyblok/php-client) directly adds a file cache (you can change this setting as well) for every storyblok request you do - this folder is the place where we save the cached results.


## You want to know more about storyblok?

- [Prologue - Introduction](https://www.storyblok.com/docs/Prologue/Introduction)
- [Terminology - Introduction](https://www.storyblok.com/docs/terminology/introduction)
- [Content Delivery API - Introduction](https://www.storyblok.com/docs/Delivery-Api/introduction)

## How to install composer?
```shell
php -r "readfile('https://getcomposer.org/installer');" | php
sudo mkdir /usr/local/bin/
sudo mv composer.phar /usr/local/bin/composer
```

## How can I add Google Analytics?
- Add your Google Analytics key to `index.twig`

## How can I add Favicons?
- Use [RealFaviconGenerator](http://realfavicongenerator.net/) to generate all the icons and files
- Replace the contents of `app/images/favicons`
- Uncomment necessary lines in `webapp/views/head.twig`

## How can I add meta tags for SEO and social media?
- Add a `meta` field to your story and use the custom field type `meta`
- Uncomment necessary lines in `webapp/views/head.twig`
- Replace UPPERCASE_PLACEHOLDERS in `webapp/views/head.twig`

## How can I add sharing buttons?
- Uncomment the `SocialShareController` in `app/scripts/controllers/Controllers.ts`
- Learn how to use it in `app/scripts/controllers/SocialShareController.ts`

## How can I add critical CSS for above the fold ?
- `app/styles/inline.scss` will be inlined on top of each page
- If you optimize for critical CSS, **move** styles from `main.scss` to `inline.scss`

## How can I add font precaching with service workers?
- Please check out other projects, because techniques change all the time

## How can I use SVG icons?
- Place the `my-icon.svg` file in the `app/images` folder
- In Twig: `{{ include_file('/images/my-icon.svg')|raw }}`
- If you need SVG icons in CSS, please follow [Making the switch](https://sarasoueidan.com/blog/icon-fonts-to-svg/)

## How can I add a new language?
- Add it to the array `$app['config.availableLocales']` in `config.php`

## How can I add a cookiebar?
- Include the `cookiebar.twig` component in a page template
- Uncomment the `CookiebarController` in `app/scripts/controllers/Controllers.ts`

## How can I use the Google Recaptcha?
- Register your website at [Google Recaptcha](https://www.google.com/recaptcha)
- Add your captcha secret in `config.php`
- Embed the google recaptcha script at the bottom of the `<head>` tag

```
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
```

- Add your data attributes to the submit button of the form

```
<button
    class="g-recaptcha"
    data-sitekey="<<< RECAPTCHA SITEKEY >>>"
    data-callback="submitForm">
    Submit
</button>
```

- In your javascript form controller, you can add your `formData` and add the `g-recaptcha-response` value (set by google). With that, you will make a request to `/api/googlerecaptcha`.

```
const requestData = {
    formData,
};

requestData['g-recaptcha-response'] = form.find('[name="g-recaptcha-response"]').val();

$.ajax({
    type: 'POST',
    url: '/api/googlerecaptcha',
    data: requestData,
    success() {
        // form submitted
    },
    error() {
        // form not submitted
    },
});
```

- If the captcha is valid, you can implement the main form request in `webapp/src/App/Controllers/GoogleCaptcha.php`.

## How can I enable the maintenance page?
- Go to the `app` folder
- Rename `index.php` to `index-original.php`
- Rename `index-maintenance.php` to `index.php`

Gulp copies the `index.php` file from the `app` folder to the `public` folder. The maintenance page is a standalone page, it won't load any dependencies. In case you like to embed images on the maintenance page, you can add them to the `app/images` folder and the gulp task will copy them to the `public/images` folder.

> [Netural](https://www.netural.com/) & [Storyblok](https://www.storyblok.com/)
