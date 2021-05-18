<?php

namespace RKW\RkwRegistration\Utility;

use \RKW\RkwBasics\Utility\GeneralUtility;

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
     * @param integer   $pageUid
     * @param bool      $createAbsoluteUri
     * @param bool      $linkAccessRestrictedPages
     * @param false     $useCacheHash
     * @return string
     */
    public static function urlToPageUid($pageUid, $createAbsoluteUri = true, $linkAccessRestrictedPages = true, $useCacheHash = false)
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');

        $redirectUrl = $uriBuilder->reset()
            ->setTargetPageUid(intval($pageUid))
            ->setCreateAbsoluteUri($createAbsoluteUri)
            ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
            ->setUseCacheHash($useCacheHash)
            ->build();

        return self::checkRedirectUrl($redirectUrl);
    }



    /**
     * Checks if given url is valid for redirect
     *
     * @param string|integer $url
     * @return string
     * @see \TYPO3\CMS\Felogin\Controller\FrontendLoginController
     */
    public static function checkRedirectUrl($url)
    {

        $settings = self::getSettings();
        $checkedUrl = '';

        // if it is numeric, we have a pid and have to generate the real url
        if (is_numeric($url)) {

            // build url from pid!
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
            if ($uriBuilder instanceof \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder) {
                $url = $uriBuilder->reset()
                    ->setTargetPageUid(intval($url))
                    ->setCreateAbsoluteUri(true)
                    ->setLinkAccessRestrictedPages(true)
                    ->build();
            }
        }


        // check if settings are available and redirect not disabled
        self::getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::DEBUG, sprintf('Configured redirect domains: "%s"', $settings['redirectDomains']));
        if ($redirectDomains = $settings['redirectDomains']) {

            // Is referring url allowed to redirect?
            if ($redirectDomain = self::getDomainFromUrl($url)) {

                $validDomains = GeneralUtility::trimExplode(',', $redirectDomains, true);
                if (in_array($redirectDomain, $validDomains)) {
                    $checkedUrl = $url;
                }
            }
        }

        // take domain if we are not in production environement
        if (getenv('TYPO3_CONTEXT') != 'Production') {
            $checkedUrl = $url;
            self::getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('URL passed as valid due to TYPO3_CONTEXT settings.', $url));
        }


        if ($checkedUrl) {
            self::getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('URL "%s" is valid for redirecting.', $checkedUrl));
        } else {
            self::getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('URL "%s" is not valid for redirecting.', $url));
        }

        // Avoid forced logout for fe-login-extension, when trying to login immediately after a logout
        $checkedUrl = preg_replace('/[&?]logintype=[a-z]+/', '', $checkedUrl);

        return $checkedUrl;
        //===
    }



    /**
     * Extracts the domain from the given url
     *
     * @param string $url
     * @return string | NULL
     */
    public static function getDomainFromUrl($url)
    {
        $match = array();
        if (preg_match('#^http(s)?://([[:alnum:]._-]+)/#', $url, $match)) {
            return $match[2];
            //===
        }

        return null;
    }



    /**
     * Gives back current Domain
     *
     * @return string
     */
    public static function getCurrentDomainName()
    {
        // @toDo: Does "->getDomainNameForPid" also work in our dynamic MyRkw-Domain setting?
        return $GLOBALS['TSFE']->getDomainNameForPid(intval($GLOBALS['TSFE']->page['uid']));
    }


    /**
     * Gives back guest redirect url
     *
     * @return string|bool
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function getGuestRedirectUrl()
    {
        $settings = self::getSettings();

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        $sysDomainRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\SysDomainRepository');
        $sysDomain = $sysDomainRepository->findByDomainName(RedirectUtility::getCurrentDomainName())->getFirst();
        $targetPageUid = 0;
        if (
            $sysDomain instanceof \RKW\RkwRegistration\Domain\Model\SysDomain
            && $sysDomain->getTxRkwregistrationPageLoginGuest() instanceof \RKW\RkwRegistration\Domain\Model\Pages
        ) {
            $targetPageUid = $sysDomain->getTxRkwregistrationPageLoginGuest()->getUid();
        }

        if (
            $targetPageUid
            || $settings['users']['guestRedirectPid']
        ) {
            /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
            $redirectUrl = $uriBuilder->reset()
                ->setTargetPageUid(intval($settings['users']['guestRedirectPid'] ? $settings['users']['guestRedirectPid'] : $targetPageUid))
                ->setCreateAbsoluteUri(true)
                ->setLinkAccessRestrictedPages(true)
                ->setUseCacheHash(false)
                ->build();

            return $redirectUrl;
        }

        // if there is no redirect set
        return false;
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
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
    }


}