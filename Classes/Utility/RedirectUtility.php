<?php

namespace RKW\RkwRegistration\Utility;

use RKW\RkwBasics\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
 * Class RedirectUtility
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RedirectUtility implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Gives back guest redirect url
     *
     * @return string|bool
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function getGuestRedirectUrl()
    {
        $settings = self::getSettings();

        /** @var ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

        if ($settings['users']['guestRedirectPid']) {

            /** @var UriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get(UriBuilder::class);
            $redirectUrl = $uriBuilder->reset()
                ->setTargetPageUid(intval($settings['users']['guestRedirectPid']))
                ->setCreateAbsoluteUri(true)
                ->setLinkAccessRestrictedPages(true)
                ->setUseCacheHash(false)
                ->buildFrontendUri();

            return self::checkRedirectUrl($redirectUrl);
        }

        // if there is no redirect set
        return false;
    }


    /**
     * Checks if given url is valid for redirect
     *
     * @param string|integer $url
     * @return string
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @see \TYPO3\CMS\Felogin\Controller\FrontendLoginController
     */
    public static function checkRedirectUrl($url)
    {

        $settings = self::getSettings();
        $checkedUrl = '';

        // if it is numeric, we have a pid and have to generate the real url
        if (is_numeric($url)) {

            // build url from pid!
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            $uriBuilder = $objectManager->get(UriBuilder::class);
            if ($uriBuilder instanceof UriBuilder) {
                $url = $uriBuilder->reset()
                    ->setTargetPageUid(intval($url))
                    ->setCreateAbsoluteUri(true)
                    ->setLinkAccessRestrictedPages(true)
                    ->build();
            }
        }


        // check if settings are available and redirect not disabled
        self::getLogger()->log(LogLevel::DEBUG, sprintf('Configured redirect domains: "%s"', $settings['redirectDomains']));
        if ($redirectDomains = $settings['redirectDomains']) {

            // Is referring url allowed to redirect?
            if ($redirectDomain = self::getDomainFromUrl($url)) {

                $validDomains = GeneralUtility::trimExplode(',', $redirectDomains, true);
                if (in_array($redirectDomain, $validDomains)) {
                    $checkedUrl = $url;
                }
            }
        }


        // take domain if we are not in production environment
        if (getenv('TYPO3_CONTEXT') != 'Production') {
            $checkedUrl = $url;
            self::getLogger()->log(LogLevel::WARNING, sprintf('URL passed as valid due to TYPO3_CONTEXT settings.', $url));
        }


        if ($checkedUrl) {
            self::getLogger()->log(LogLevel::INFO, sprintf('URL "%s" is valid for redirect.', $checkedUrl));
        } else {
            self::getLogger()->log(LogLevel::WARNING, sprintf('URL "%s" is not valid for redirect.', $url));
        }

        // Avoid forced logout for fe-login-extension, when trying to login immediately after a logout
        $checkedUrl = preg_replace('/[&?]logintype=[a-z]+/', '', $checkedUrl);

        return $checkedUrl;
    }



    /**
     * Extracts the domain from the given url
     *
     * @param string $url
     * @return string | NULL
     */
    public static function getDomainFromUrl($url)
    {
        $match = [];
        if (preg_match('#^http(s)?://([[:alnum:]._-]+)/#', $url, $match)) {
            return $match[2];
        }

        return null;
    }



    /**
     * Returns TYPO3 settings
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected static function getSettings()
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration');
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected static function getLogger()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }



    /**
     * Gives back current Domain
     *
     * @deprecated Used for fake (sub-)domains. Not longer needed
     *
     * @return string
     */
    public static function getCurrentDomainName()
    {
        // @toDo: Does "->getDomainNameForPid" also work in our dynamic MyRkw-Domain setting?
        return $GLOBALS['TSFE']->getDomainNameForPid(intval($GLOBALS['TSFE']->page['uid']));
    }



    /**
     *
     * @deprecated Seems to be no longer used
     *
     * @param integer   $pageUid
     * @param bool      $createAbsoluteUri
     * @param bool      $linkAccessRestrictedPages
     * @param false     $useCacheHash
     * @return string
     */
    public static function urlToPageUid($pageUid, $createAbsoluteUri = true, $linkAccessRestrictedPages = true, $useCacheHash = false)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);

        $redirectUrl = $uriBuilder->reset()
            ->setTargetPageUid(intval($pageUid))
            ->setCreateAbsoluteUri($createAbsoluteUri)
            ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
            ->setUseCacheHash($useCacheHash)
            ->buildFrontendUri();

        return self::checkRedirectUrl($redirectUrl);
    }


}
