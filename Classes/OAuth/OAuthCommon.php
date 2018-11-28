<?php

namespace RKW\RkwRegistration\OAuth;

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * Class OAuthCommon
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OAuthCommon implements \TYPO3\CMS\Core\SingletonInterface
{
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
     * apiBaseUrl
     *
     * @var string
     */
    protected $apiBaseUrl;

    /**
     * apiUrlPath
     *
     * @var string
     */
    protected $apiUrlPath;


    /**
     * Constructor
     *
     * @param string $appKey
     * @param string $appSecret
     * @param string $apiBaseUrl
     */
    public function __construct($appKey, $appSecret, $apiBaseUrl)
    {

        // The ordinary identifier and password of a third provider app
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;

        // the provider-url (for example "https://api.twitter.com/" or "https://api.xing.com/")
        $this->apiBaseUrl = $apiBaseUrl;

    }


    /**
     * Set API-Action
     *
     * @param string $apiUrlPath
     * @return $this
     */
    public function setAction($apiUrlPath)
    {

        // resource to be queried (for example "oauth/request_token" (twitter) or "v1/request_token" (xing)
        $this->apiUrlPath = $apiUrlPath;

        return $this;
        //===

    }

    /**
     * Gets token or user data from api
     *
     * @param string $oauthVerifier
     * @param string $oauthToken
     * @param string $oauthCallback
     * @return string
     * @throws \RKW\RkwRegistration\OAuth\OAuthException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getData($oauthVerifier = null, $oauthToken = null, $oauthCallback = null)
    {

        $requestTokenUrl = $this->apiBaseUrl . $this->apiUrlPath;
        $oauthTimestamp = time();
        $nonce = md5(mt_rand());
        $settings = $this->getSettings();

        $oauthSignatureMethod = "HMAC-SHA1";
        $oauthVersion = "1.0";

        $sigBase = "GET&" . rawurlencode($requestTokenUrl) . "&"
            . rawurlencode("oauth_consumer_key=" . $this->appKey)
            . rawurlencode("&oauth_nonce=" . $nonce)
            . rawurlencode("&oauth_signature_method=" . $oauthSignatureMethod)
            . rawurlencode("&oauth_timestamp=" . $oauthTimestamp)
            . rawurlencode("&oauth_version=" . $oauthVersion);

        if ($oauthVerifier) {
            $sigBase .= rawurlencode("&oauth_verifier=" . $oauthVerifier);
        }

        if ($oauthToken) {
            $sigBase .= rawurlencode("&oauth_token=" . $oauthToken);
        }

        if ($oauthCallback) {
            $sigBase .= rawurlencode("&oauth_callback=" . $oauthCallback);
        }

        //	var_dump($sigBase); exit();

        $sigKey = $this->appSecret . "&";
        $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

        $requestUrl = $requestTokenUrl . "?"
            . "oauth_consumer_key=" . rawurlencode($this->appKey)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_version=" . rawurlencode($oauthVersion)
            . "&oauth_signature=" . rawurlencode($oauthSig);

        if ($oauthVerifier && $oauthToken) {
            $requestUrl .= "&oauth_verifier=" . rawurlencode($oauthVerifier);
            $requestUrl .= "&oauth_token=" . rawurlencode($oauthToken);
        }

        if ($oauthCallback) {
            $requestUrl .= "&oauth_callback=" . rawurlencode($oauthCallback);
        }

        try {

            // set up context if proxy is used
            $aContext = array();
            if ($settings['proxy']) {

                $aContext = array(
                    'http' => array(
                        'proxy'           => $settings['proxy'],
                        'request_fulluri' => true,
                    ),
                );

                if ($settings['proxyUsername']) {
                    $auth = base64_encode($settings['proxyUsername'] . ':' . $settings['proxyPassword']);
                    $aContext['http']['header'] = 'Proxy-Authorization: Basic ' . $auth;
                }
            }

            $cxContext = stream_context_create($aContext);
            $response = file_get_contents($requestUrl, false, $cxContext);
            parse_str($response, $values);
            if (
                ($values)
                && (array($values))
            ) {
                return $values;
            }
            //===


        } catch (\Exception $e) {

            throw new \RKW\RkwRegistration\OAuth\OAuthException(sprintf('An error occurred while trying to connect an API. Call: %s, Error: %s', urldecode($requestTokenUrl), $e->getMessage()), 1406138089);
            //===
        }

        throw new \RKW\RkwRegistration\OAuth\OAuthException(sprintf('Invalid or empty data returned from API. Call: %s', urldecode($requestTokenUrl)), 1407318691);
        //===

    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {

        return Common::getTyposcriptConfiguration('RkwRegistration', $which);
        //===
    }


}