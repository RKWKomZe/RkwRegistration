<?php

namespace RKW\RkwRegistration\Service;

use \RKW\RkwBasics\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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
 * Class AuthService
 *
 * @deprecated Will be removed soon. Use \RKW\RkwRegistration\Service\FrontendUserAuthService instead
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AuthService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Length of token for anonymous users
     *
     * @const integer
     * @see \RKW\RkwRegistration\Service\RegistrationService::ANONYMOUS_TOKEN_LENGTH
     */
    const ANONYMOUS_TOKEN_LENGTH = 20;

    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;


    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;


    /**
     * Setting
     *
     * @var array
     */
    protected $settings;


    /**
     * Validates the given username/password combination against the saved user data
     *
     * @deprecated Will be removed soon.
     *
     * @param string $username
     * @param string $password
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser | integer
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function validateUser($username, $password)
    {

        $settings = $this->getSettings();
        $status = 1;

        if (!$username) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('No valid username given.'));
            throw new \RKW\RkwRegistration\Exception('No valid username given.', 1435035135);
            //===
        }

        if (!$password) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('No valid password given.'));
            throw new \RKW\RkwRegistration\Exception('No valid password given.', 1435035166);
            //===
        }

        if (
            ($user = $this->getFrontendUserRepository()->findOneByUsername(strtolower(trim($username))))
            && ($user instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser)
            && ($user->getPassword())
        ) {

            // check for salted passwords
            if (
                (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords'))
                && (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('FE'))
            ) {

                $objSalt = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($user->getPassword());
                if (is_object($objSalt)) {

                    if ($objSalt->checkPassword($password, $user->getPassword())) {

                        // reset counter
                        $user->setTxRkwregistrationLoginErrorCount(0);
                        $this->getFrontendUserRepository()->update($user);

                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully authentificated user "%s" using a salted password.', strtolower(trim($username))));

                        return $user;
                        //===
                    }
                }

                // check for plaintext passwords
            } else {
                if ($password == $user->getPassword()) {

                    // reset counter
                    $user->setTxRkwregistrationLoginErrorCount(0);
                    $this->getFrontendUserRepository()->update($user);

                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully authenticated user "%s" using a plaintext password.', strtolower(trim($username))));

                    return $user;
                    //===
                }
            }

            // increment counter
            $user->incrementTxRkwregistrationLoginErrorCount();

            // check max error counter
            $maxLoginErrors = 10;
            if (intval($settings['users']['maxLoginErrors']) > 0) {
                $maxLoginErrors = intval($settings['users']['maxLoginErrors']);
            }

            // disable user if maxLoginErrors is reached
            if ($user->getTxRkwregistrationLoginErrorCount() >= $maxLoginErrors) {
                $user->setDisable(1);
                $status = 2;
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Disabled user "%s", because of too many authentication failures.', strtolower(trim($username))));
            }

            $this->getFrontendUserRepository()->update($user);
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Authentication failed for user "%s".', strtolower(trim($username))));

            return $status;
        }

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('User "%s" not found.', strtolower(trim($username))));
        return 0;
    }


    /**
     * Checks the given token of an anonymous user
     *
     * @deprecated Will be removed soon.
     *
     * @param string $token
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser| boolean
     */
    public function validateAnonymousUser($token)
    {

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');

        // check if given token exists and has the expected length!
        if (
            (strlen($token) == self::ANONYMOUS_TOKEN_LENGTH)
            && ($anonymousUser = $frontendUserRepository->findOneByToken($token))
        ) {

            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully authenticated anonymous user with token "%s".', trim($token)));

            return $anonymousUser;

        }

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Anonymous user with token "%s" not found.', trim($token)));

        return false;
    }


    /**
     *
     * @deprecated Will be removed soon.
     *
     * Sets a temporary session cookie with the user-id
     * IMPORTANT: After a redirect the user is logged in then
     * DANGER: This method authenticates the given user without checking for password!!!
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     */
    public static function loginUser(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser)
    {

        if (!$frontendUser->getUid()) {
            throw new \RKW\RkwRegistration\Exception('No valid uid for user given.', 1435002338);
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
        if ($version >=  8000000) {

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

        self::getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Logging in User "%s" with uid %s.', strtolower($frontendUser->getUsername()), $frontendUser->getUid()));
    }


    /**
     *
     * @deprecated Will be removed soon.
     *
     * Checks if user is logged in
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     */
    public static function isUserLoggedIn(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser)
    {

        // check which id is logged in and compare it with given user
        if (
            ($GLOBALS['TSFE'])
            && ($GLOBALS['TSFE']->loginUser)
            && ($GLOBALS['TSFE']->fe_user->user['uid'])
        ) {
            if ($frontendUser->getUid() == intval($GLOBALS['TSFE']->fe_user->user['uid'])) {
                return true;
            }
        }

        return false;
    }


    /**
     * Logout
     *
     * @deprecated Will be removed soon.
     *
     * @return void
     */
    public static function logoutUser()
    {
        self::getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Logging out user with uid %s.', intval($GLOBALS['TSFE']->fe_user->user['uid'])));
        $GLOBALS['TSFE']->fe_user->removeSessionData();
        $GLOBALS['TSFE']->fe_user->logoff();
    }


    /**
     * Extracts the domain from the given url
     *
     * @deprecated Will be removed soon.
     *
     * @param string $url
     * @return string | NULL
     */
    public function getDomain($url)
    {

        $match = array();
        if (preg_match('#^http(s)?://([[:alnum:]._-]+)/#', $url, $match)) {
            return $match[2];
        }

        return null;
    }


    /**
     * Returns logger instance
     *
     * @deprecated Will be removed soon.
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    public static function getLogger()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
    }


    /**
     * Returns FrontendUserRepository
     *
     * @return \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    protected function getFrontendUserRepository()
    {

        if (!$this->frontendUserRepository) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');
        }

        return $this->frontendUserRepository;
    }


    /**
     * Returns PersistenceManager
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected function getPersistenceManager()
    {

        if (!$this->persistenceManager) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        }

        return $this->persistenceManager;
    }


    /**
     * Returns TYPO3 settings
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings()
    {

        if (!$this->settings) {
            $this->settings = GeneralUtility::getTyposcriptConfiguration('Rkwregistration');
        }


        if (!$this->settings) {
            return array();
        }

        return $this->settings;
    }


}