<?php

namespace App\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/*
* Validates the google captcha
* http://localhost:4200/api/googlecaptcha/
*/
class GoogleCaptcha implements ControllerProviderInterface {

    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];

        $controllers->post('/', function (Request $request) use ($app) {

            $requestData = $app['request']->request->all();

            if (!array_key_exists('g-recaptcha-response', $requestData)) {
                return $app->json( array( 'success' => false, 'message' => 'g-recaptcha-response not found' ), 400);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $app['config.recaptcha.url']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'response=' . $requestData['g-recaptcha-response'] . '&secret=' . $app['config.recaptcha.secret']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            $google_response = json_decode($result, true);

            if($google_response['success']) {
                // Do whatever you want to do after the google captcha succeeded
                // Here for example a request to a google script to send an email with the content
                // https://github.com/dwyl/html-form-send-email-via-google-script-without-server

                $formData = $requestData['formData'];

                if (LIVE) {
                    $formData['TO_ADDRESS'] = $app['config.contactform.email.live'];
                } else {
                    $formData['TO_ADDRESS'] = $app['config.contactform.email.test'];
                }

                $google_script_url = $app['config.contactform.googleScriptUrl'];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $google_script_url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);

                return $app->json( array( 'success' => true ), 200);
            }

            return $app->json( array( 'success' => false, 'message' => 'google response failed' ), 400);
        });

        return $controllers;
    }

}
