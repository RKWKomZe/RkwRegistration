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
 * Class Twitter
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Twitter
{
    /**
     * apiUrl
     *
     * @var string
     */
    protected $apiUrl;

    /**
     * appKey
     *
     * @var string
     */
    protected $appKey;

    /**
     * appSecret
     *
     * @var string
     */
    protected $appSecret;


    /**
     * injectApiVars
     *
     * @return void
     * @throws \RKW\RkwRegistration\SocialMedia\TwitterException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function insertApiVars()
    {

        $settings = Common::getTyposcriptConfiguration('Rkwregistration');
        $apiData = $settings['apiData']['twitter'];

        // check important data
        if (
            (!$apiData['baseUrl'])
            || (!$apiData['consumerId'])
            || (!$apiData['consumerSecret'])
        ) {
            throw new \RKW\RkwRegistration\SocialMedia\TwitterException('API not configured. Please set baseUrl, consumerSecret and consumerId', 1435056319);
            //===
        }


        $this->apiUrl = $apiData['baseUrl'];
        $this->appKey = preg_replace('/[^a-zA-Z0-9]/', '', $apiData['consumerId']);
        $this->appSecret = preg_replace('/[^a-zA-Z0-9]/', '', $apiData['consumerSecret']);

    }


    /**
     * Redirect user to twitter and do the login
     * Redirects the user to a site from twitter. There, the user can
     * authenticate. Then the user is redirected to a specific app in the
     * callback URL of RKW, together with oauth verifier and oauth token
     *
     * @return void
     * @throws \RKW\RkwRegistration\SocialMedia\TwitterException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function login()
    {

        $this->insertApiVars();

        $oAuth = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\OAuth\\OAuthCommon', $this->appKey, $this->appSecret, $this->apiUrl);
        $request = $oAuth->setAction('oauth/request_token')
            ->getData();
        if (
            (is_array($request))
            && ($request["oauth_token"])
        ) {

            $redirectUrl = 'https://api.twitter.com/oauth/authorize?oauth_token=' . $request["oauth_token"];
            header("Location: " . $redirectUrl);
            exit;
            //===
        }

        throw new \RKW\RkwRegistration\SocialMedia\TwitterException('Could not login to Twitter.', 1407324543);
        //===
    }


    /**
     * Get user data from twitter
     * Get user data from twitter with oauth token and oauth verifier
     * are user data via this function queried
     *
     * @param string $oauthVerifier
     * @param string $oauthToken
     * @return array $userData
     * @throws \RKW\RkwRegistration\SocialMedia\TwitterException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getUserData($oauthVerifier, $oauthToken)
    {

        $this->insertApiVars();

        $oAuth = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\OAuth\\OAuthCommon', $this->appKey, $this->appSecret, $this->apiUrl);
        $userData = $oAuth->setAction('oauth/access_token')
            ->getData($oauthVerifier, $oauthToken);

        if (!$userData) {
            throw new \RKW\RkwRegistration\SocialMedia\TwitterException('Could not fetch data from Twitter.', 1407324543);
            //===
        }

        return $userData;
        //===
    }

}
