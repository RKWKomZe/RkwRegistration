<?php
namespace RKW\RkwRegistration\XClasses\Frontend\Authentication;

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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DefaultRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionContainerInterface;
use TYPO3\CMS\Core\Database\Query\Restriction\RootLevelRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Http\CookieHeaderTrait;
use TYPO3\CMS\Core\Session\Backend\Exception\SessionNotFoundException;
use TYPO3\CMS\Core\Session\Backend\HashableSessionBackendInterface;
use TYPO3\CMS\Core\Session\Backend\SessionBackendInterface;
use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Authentication of users in TYPO3
 *
 * This class is used to authenticate a login user.
 * The class is used by both the frontend and backend.
 * In both cases this class is a parent class to BackendUserAuthentication and FrontendUserAuthentication
 *
 * See Inside TYPO3 for more information about the API of the class and internal variables.
 */
class FrontendUserAuthentication extends \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
{

    /**
     * Sets the session cookie for the current disposal.
     *
     * @throws Exception
     */
    protected function setSessionCookie()
    {
        $isSetSessionCookie = $this->isSetSessionCookie();
        $isRefreshTimeBasedCookie = $this->isRefreshTimeBasedCookie();
        if ($isSetSessionCookie || $isRefreshTimeBasedCookie) {
            $settings = $GLOBALS['TYPO3_CONF_VARS']['SYS'];
            // Get the domain to be used for the cookie (if any):
            $cookieDomain = $this->getCookieDomain();
            // If no cookie domain is set, use the base path:
            $cookiePath = $cookieDomain ? '/' : GeneralUtility::getIndpEnv('TYPO3_SITE_PATH');
            // If the cookie lifetime is set, use it:
            $cookieExpire = $isRefreshTimeBasedCookie ? $GLOBALS['EXEC_TIME'] + $this->lifetime : 0;
            // Use the secure option when the current request is served by a secure connection:
            $cookieSecure = (bool)$settings['cookieSecure'] && GeneralUtility::getIndpEnv('TYPO3_SSL');
            // Valid options are "strict", "lax" or "none", whereas "none" only works in HTTPS requests (default & fallback is "strict")
            $cookieSameSite = $this->sanitizeSameSiteCookieValue(
                strtolower($GLOBALS['TYPO3_CONF_VARS'][$this->loginType]['cookieSameSite'] ?? Cookie::SAMESITE_STRICT)
            );
            // SameSite "none" needs the secure option (only allowed on HTTPS)
            if ($cookieSameSite === Cookie::SAMESITE_NONE) {
                $cookieSecure = true;
            }
            // Do not set cookie if cookieSecure is set to "1" (force HTTPS) and no secure channel is used:
            if ((int)$settings['cookieSecure'] !== 1 || GeneralUtility::getIndpEnv('TYPO3_SSL')) {
                $cookie = new Cookie(
                    $this->name,
                    $this->id,
                    $cookieExpire,
                    $cookiePath,
                    $cookieDomain,
                    $cookieSecure,
                    false, // only change
                    false,
                    $cookieSameSite
                );
                header('Set-Cookie: ' . $cookie->__toString(), false);
                $this->cookieWasSetOnCurrentRequest = true;
            } else {
                throw new Exception('Cookie was not set since HTTPS was forced in $TYPO3_CONF_VARS[SYS][cookieSecure].', 1254325546);
            }
            $this->logger->debug(
                ($isRefreshTimeBasedCookie ? 'Updated Cookie: ' : 'Set Cookie: ')
                . sha1($this->id) . ($cookieDomain ? ', ' . $cookieDomain : '')
            );
        }
    }

    private function sanitizeSameSiteCookieValue(string $cookieSameSite): string
    {
        if (!in_array($cookieSameSite, [Cookie::SAMESITE_STRICT, Cookie::SAMESITE_LAX, Cookie::SAMESITE_NONE], true)) {
            $cookieSameSite = Cookie::SAMESITE_STRICT;
        }
        return $cookieSameSite;
    }

}
