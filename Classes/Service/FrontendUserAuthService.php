<?php

namespace RKW\RkwRegistration\Service;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Database\ConnectionPool;
use \TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Saltedpasswords\Salt\SaltFactory;
use TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility;

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
 * FrontendUserAuthService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserAuthService extends \TYPO3\CMS\Sv\AbstractAuthenticationService
{
    /**
     * 0 - this service was the right one to authenticate the user but it failed
     */
    const STATUS_AUTHENTICATION_FAILURE_BREAK = 0;

    /**
     * 100 - just go on. User is not authenticated but there's still no reason to stop
     */
    const STATUS_AUTHENTICATION_FAILURE_CONTINUE = 100;

    /**
     * 200 - authenticated and no more checking needed
     */
    const STATUS_AUTHENTICATION_SUCCESS_BREAK = 200;

    /**
     * Length of token for guest users
     *
     * @const integer
     * @see \RKW\RkwRegistration\Service\RegistrationService::GUEST_TOKEN_LENGTH
     */
    const GUEST_TOKEN_LENGTH = 20;

    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * AuthStatusResult
     *
     * @var integer
     */
    protected $authStatusResult;

    /**
     * @return int
     */
    public function getAuthStatusResult(): int
    {
        return $this->authStatusResult;
    }

    /**
     * @param int $authStatusResult
     */
    public function setAuthStatusResult(int $authStatusResult): void
    {
        $this->authStatusResult = $authStatusResult;
    }

    /**
     * @return bool
     */
    function init() {
        $available = parent::init();
        return $available;
    }


    /**
     * Use it by adding "getUserFE" to service subtype in ext_localconf
     *
     * Find a user (eg. look up the user record in database when a login is sent)
     *
     * @return mixed User array or FALSE
     */
    public function getUser()
    {

        if ($this->login['status'] !== 'login') {
            return false;
        }

        /*
        if ((string)$this->login['uident_text'] === '') {
            // Failed Login attempt (no password given)
            $this->writelog(255, 3, 3, 2, 'Login-attempt from %s (%s) for username \'%s\' with an empty password!', [
                $this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->login['uname']
            ]);
            GeneralUtility::sysLog(sprintf('Login-attempt from %s (%s), for username \'%s\' with an empty password!', $this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->login['uname']), 'Core', GeneralUtility::SYSLOG_SEVERITY_WARNING);
            return false;
        }
        */

        //$user = $this->fetchUserRecord($this->login['uname']);
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        //$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        //$frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');
        //$user = $frontendUserRepository->findOneByUsername(strtolower(trim($this->login['uname'])));



        $user = '';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
        $statement = $queryBuilder
            ->select('*')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter(trim($this->login['uname'])))
            )
            ->execute();
        while ($row = $statement->fetch()) {
            $user = $row;
        }

        if (!is_array($user)) {
            // Failed login attempt (no username found)
            $this->writelog(255, 3, 3, 2, 'Login-attempt from %s (%s), username \'%s\' not found!!', [$this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->login['uname']]);
            // Logout written to log
            GeneralUtility::sysLog(sprintf('Login-attempt from %s (%s), username \'%s\' not found!', $this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->login['uname']), 'core', GeneralUtility::SYSLOG_SEVERITY_WARNING);
        } else {
            if ($this->writeDevLog) {
                GeneralUtility::devLog('User found: ' . GeneralUtility::arrayToLogString($user, [$this->db_user['userid_column'], $this->db_user['username_column']]), self::class);
            }
        }
        return $user;


    }


    /**
     * authUser
     *
     * @param array $user
     * @return int >= 200: User authenticated successfully.
     *                     No more checking is needed by other auth services.
     *             >= 100: User not authenticated; this service is not responsible.
     *                     Other auth services will be asked.
     *             > 0:    User authenticated successfully.
     *                     Other auth services will still be asked.
     *             <= 0:   Authentication failed, no more checking needed
     *                     by other auth services.
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @see \RKW\RkwRegistration\Tools\Authentication function validateUser
     */
    public function authUser(array $user): int
    {
        if (
            $this->login['status'] == 'login'
            && is_array($user)
        ) {
            if ($this->login['uname'] && $this->login['uident']) {

                $frontendUser = $this->validateUser($this->login['uname'], $this->login['uident']);

                if ($frontendUser instanceof FrontendUser) {
                    return static::STATUS_AUTHENTICATION_SUCCESS_BREAK;
                } else {
                    return static::STATUS_AUTHENTICATION_FAILURE_BREAK;
                }
            }
        }

        // if nothing there to validate: Do nothing
        return static::STATUS_AUTHENTICATION_FAILURE_CONTINUE;
    }



    /**
     * setLoginData
     *
     * @param string $uname The given login name
     * @param string $uident The given login password
     * @param string $status The auth status. Default is 'login'
     * @return void
     */
    public function setLoginData($uname, $uident, $status = 'login')
    {
        $this->login['status'] = $status;
        $this->login['uname'] = $uname;
        $this->login['uident'] = $uident;
    }



    /**
     * Checks the given token of an guest user
     *
     * @param string $token
     * @return FrontendUser| boolean
     */
    public function authGuest($token)
    {

        /** @var \RKW\RkwRegistration\Domain\Repository\GuestUserRepository $frontendUserRepository */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $guestUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\GuestUserRepository');

        // check if given token exists and has the expected length!
        if (
            (strlen($token) == self::GUEST_TOKEN_LENGTH)
            && ($guestUser = $guestUserRepository->findByUsername($token)->getFirst())
        ) {
            $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully authenticated guest user with token "%s".', trim($token)));

            // @toDo: A thought: Maybe give here also back the STATUS_AUTHENTICATION_SUCCESS_BREAK value
            // Impact: A further repository call inside the called action to get the associated user
            return $guestUser;
        } else {

            // Fallback: THIS AREA WILL BE REMOVED SOON!
            // with @deprecated function $frontendUserRepository->findOneByToken($token). Is just a fallback vor already registered anonymous user
            if (
                (strlen($token) == self::GUEST_TOKEN_LENGTH)
                && ($frontendUser = $frontendUserRepository->findOneByToken($token))
            ) {
                return $frontendUser;
            }
        }

        $this->getLogger()->log(LogLevel::WARNING, sprintf('Guest user with token "%s" not found.', trim($token)));

        return false;
    }



    /**
     * Validates the given username/password combination against the saved user data
     *
     * @param string $username
     * @param string $password
     * @return FrontendUser | integer
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function validateUser($username, $password)
    {

        $settings = $this->getSettings();
        $this->authStatusResult = 1;

        if (!$username) {
            $this->getLogger()->log(LogLevel::ERROR, sprintf('No valid username given.'));
            throw new \RKW\RkwRegistration\Exception('No valid username given.', 1435035135);
            //===
        }

        if (!$password) {
            $this->getLogger()->log(LogLevel::ERROR, sprintf('No valid password given.'));
            throw new \RKW\RkwRegistration\Exception('No valid password given.', 1435035166);
            //===
        }


        if (
            ($user = $this->getFrontendUserRepository()->findOneByUsername(strtolower(trim($username))))
            && ($user instanceof FrontendUser)
            && ($user->getPassword())
        ) {

            // check for salted passwords
            if (
                (ExtensionManagementUtility::isLoaded('saltedpasswords'))
                && (SaltedPasswordsUtility::isUsageEnabled('FE'))
            ) {

                $objSalt = SaltFactory::getSaltingInstance($user->getPassword());
                if (is_object($objSalt)) {

                    if ($objSalt->checkPassword($password, $user->getPassword())) {
                        // reset counter
                        $user->setTxRkwregistrationLoginErrorCount(0);
                        $this->getFrontendUserRepository()->update($user);

                        $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully authentificated user "%s" using a salted password.', strtolower(trim($username))));

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

                    $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully authenticated user "%s" using a plaintext password.', strtolower(trim($username))));

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
                $this->authStatusResult = 2;
                $this->getLogger()->log(LogLevel::WARNING, sprintf('Disabled user "%s", because of too many authentication failures.', strtolower(trim($username))));
            }

            $this->getFrontendUserRepository()->update($user);
            $this->getLogger()->log(LogLevel::WARNING, sprintf('Authentication failed for user "%s".', strtolower(trim($username))));

            return $this->authStatusResult;
        }

        $this->getLogger()->log(LogLevel::WARNING, sprintf('User "%s" not found.', strtolower(trim($username))));
        $this->authStatusResult = 0;
        return $this->authStatusResult;
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
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration', $which);
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
}
