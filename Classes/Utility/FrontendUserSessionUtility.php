<?php

namespace RKW\RkwRegistration\Utility;

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Exception;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * Class FrontendUserSessionUtility
 * Handles everything to a FeUser session (e.g. login and logout).
 * Hint: For authentication take a look to \RKW\RkwRegistration\Service\AuthFrontendUserService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserSessionUtility
{

    /**
     * login
     *
     * Sets a temporary session cookie with the user-id
     * IMPORTANT: After a redirect the user is logged in then
     * DANGER: This method authenticates the given user without checking for password!!!
     * @see \RKW\RkwRegistration\Service\AuthFrontendUserService for authentication
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @return void
     * @throws Exception
     */
    public static function login(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser)
    {

        if (!$frontendUser->getUid()) {
            throw new Exception('No valid uid for user given.', 1435002338);
        }

        $userArray = array(
            'uid' => $frontendUser->getUid()
        );

        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $GLOBALS['TSFE']*/
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $GLOBALS['TSFE']->fe_user */
        $GLOBALS['TSFE']->fe_user->is_permanent = 0; //set 1 for a permanent cookie, 0 for session cookie
        $GLOBALS['TSFE']->fe_user->checkPid = 0;
        $GLOBALS['TSFE']->fe_user->dontSetCookie = false;

        $version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        if ($version >= 8000000) {

            $GLOBALS['TSFE']->fe_user->start(); // set cookie and initiate login
            $GLOBALS['TSFE']->fe_user->createUserSession($userArray);  // create user session in database
            $GLOBALS['TSFE']->fe_user->user = $GLOBALS['TSFE']->fe_user->fetchUserSession(); // get user session from database
            $GLOBALS['TSFE']->fe_user->loginSessionStarted = true; // set session as started equal to a successful login
            $GLOBALS['TSFE']->initUserGroups(); // Initializes the front-end user groups based on all fe_groups records that the current fe_user is member of
            $GLOBALS['TSFE']->loginUser = true; //  Global flag indicating that a frontend user is logged in. Should already by set by \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::initUserGroups();
            $GLOBALS['TSFE']->storeSessionData(); // store session in database

        } else {
            $GLOBALS['TSFE']->fe_user->createUserSession($userArray);
            $GLOBALS['TSFE']->fe_user->user = $GLOBALS['TSFE']->fe_user->fetchUserSession();
            $GLOBALS['TSFE']->fe_user->fetchGroupData();
            $GLOBALS['TSFE']->loginUser = true;

            // set a dummy cookie
            $GLOBALS['TSFE']->fe_user->setAndSaveSessionData('dummy', true);
        }

        self::getLogger()->log(LogLevel::INFO, sprintf('Logging in User "%s" with uid %s.', strtolower($frontendUser->getUsername()), $frontendUser->getUid()));
    }


    /**
     * Logout
     *
     * @return void
     */
    public static function logout()
    {
        self::getLogger()->log(LogLevel::INFO, sprintf('Logging out user with uid %s.', intval($GLOBALS['TSFE']->fe_user->user['uid'])));
        $GLOBALS['TSFE']->fe_user->removeSessionData();
        $GLOBALS['TSFE']->fe_user->logoff();
    }


    /**
     * Checks if user is logged in
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser To check a specific user
     * @return boolean
     */
    public static function isUserLoggedIn(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser = null)
    {
        // check which id is logged in and compare it with given user
        if (
            ($GLOBALS['TSFE'])
            && ($GLOBALS['TSFE']->loginUser)
            && ($GLOBALS['TSFE']->fe_user->user['uid'])
        ) {
            if (
                $frontendUser
                && $frontendUser->getUid() == intval($GLOBALS['TSFE']->fe_user->user['uid'])
            ) {
                // the given frontendUser is logged in
                return true;
            } else {
                // somebody is logged in
                return true;
            }
        }

        // nobody is logged in
        return false;
    }



    /**
     * Id of logged User
     *
     * @return integer|NULL
     */
    public static function getFrontendUserId()
    {
        // is $GLOBALS set?
        if (
            ($GLOBALS['TSFE'])
            && ($GLOBALS['TSFE']->loginUser)
            && ($GLOBALS['TSFE']->fe_user->user['uid'])
        ) {
            return intval($GLOBALS['TSFE']->fe_user->user['uid']);
        }

        return null;
    }


    /**
     * converts an feUser array to an object
     *
     * @toDo: This function could be also a static part of a FrontendUserUtility
     *
     * array $userData
     * @return FrontendUser
     */
    public function convertFrontendUserArrayToObject($userData)
    {
        $frontendUser = $userData;
        if (is_array($userData)) {
            /** @var FrontendUser $frontendUser */
            $frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FrontendUser::class);
            foreach ($userData as $key => $value) {
                $setter = 'set' . ucfirst(GeneralUtility::camelize($key));
                if (method_exists($frontendUser, $setter)) {
                    $frontendUser->$setter($value);
                }
            }
        }
        return $frontendUser;
    }



    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    public static function getLogger()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
    }

}
