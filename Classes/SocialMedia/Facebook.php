<?php

namespace RKW\RkwRegistration\SocialMedia;

use \RKW\RkwBasics\Helper\Common;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * Class Facebook
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

// start session
session_start();

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rkw_registration') . 'Classes/Libs/Facebook/Facebook/autoload.php');
define('FACEBOOK_SDK_V4_SRC_DIR', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rkw_registration') . 'Classes/Libs/Facebook/Facebook/');

class Facebook
{
    /**
     * facebookAppId
     *
     * @var string
     */
    protected $facebookAppId;

    /**
     * facebookAppSecret
     *
     * @var string
     */
    protected $facebookAppSecret;

    /**
     * callbackUrl
     *
     * @var string
     */
    protected $callbackUrl;


    /**
     * Injects API-variables into object
     *
     * @return void
     * @throws \RKW\RkwRegistration\SocialMedia\FacebookException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function insertApiVars()
    {

        $settings = Common::getTyposcriptConfiguration('Rkwregistration');
        $apiData = $settings['apiData']['facebook'];

        // check important data
        if (
            (!$apiData['callbackUrl'])
            || (!$apiData['consumerId'])
            || (!$apiData['consumerSecret'])
        ) {
            throw new \RKW\RkwRegistration\SocialMedia\FacebookException('API not configured. Please set callbackUrl, consumerSecret and consumerId', 1435056319);
        }
        //===

        $this->callbackUrl = $apiData['callbackUrl'];
        $this->facebookAppId = preg_replace('/[^a-zA-Z0-9]/', '', $apiData['consumerId']);
        $this->facebookAppSecret = preg_replace('/[^a-zA-Z0-9]/', '', $apiData['consumerSecret']);

    }


    /**
     * Login and login-link
     *
     * @return \Facebook\GraphNodes\GraphUser | string | NULL | bool
     * @throws \RKW\RkwRegistration\SocialMedia\FacebookException
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function login()
    {
        // inject variables
        $this->insertApiVars();

        $fb = new \Facebook\Facebook([
            'app_id'                => $this->facebookAppId,
            'app_secret'            => $this->facebookAppSecret,
            'default_graph_version' => 'v2.3',
        ]);

        if (!session_id()) {
            session_start();
        }

        $helper = $fb->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken();
        $permissions = ['email']; //, 'id', 'first_name', 'last_name', 'name', 'gender', 'link', 'locale', 'timezone',
        // 'updated_time', 'verified'];

        // Ganz wichtig dieser Punkt in der neuen Version: Diese Funktion hier wird 2x aufgerufen. Einmal um den Facebook-login
        // link bereitzustellen und einmal um diesen auszuführen. Wird getLoginUrl jedoch zum zweiten mal aufgerufen,
        // stimmen die generierten LoginUrl's nicht mehr überein (weil neu generiert) und ein Fehler wird geworfen.
        if (!$accessToken instanceof \Facebook\Authentication\AccessToken) {
            $loginUrl = $helper->getLoginUrl($this->callbackUrl, $permissions);

            return $loginUrl;
            //===
        }

        if ($accessToken instanceof \Facebook\Authentication\AccessToken) {

            $fb->setDefaultAccessToken($accessToken);

            try {
                $response = $fb->get('/me?fields=email,id,first_name,last_name,name,gender,link,locale,timezone,updated_time,verified');
                $userNode = $response->getGraphUser();

            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                throw new \RKW\RkwRegistration\SocialMedia\FacebookException(sprintf('Graph returned an error: %s',
                    $e->getMessage()),
                    1496759236);
                //===

            } catch (\Facebook\Exceptions\FacebookSDKException $e) {

                // When validation fails or other local issues
                throw new \RKW\RkwRegistration\SocialMedia\FacebookException(sprintf('Facebook SDK returned an error: %s',
                    $e->getMessage()),
                    1496759237);
                //===
            }

            return $userNode;
            //===
        }

        return false;
        //===
    }
}


