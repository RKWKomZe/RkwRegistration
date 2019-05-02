<?php

namespace RKW\RkwRegistration\Tools;

use \RKW\RkwBasics\Helper\Common;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Class Registration
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Registration implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_USER_REGISTER_GRANT = 'afterUserRegisterGrant';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_EXISTING_USER_REGISTER_GRANT = 'afterExistingUserRegisterGrant';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_USER_REGISTER_DENIAL = 'afterUserRegisterDenial';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_CREATING_FINAL_USER = 'afterCreatingFinalUser';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_CREATING_FINAL_USER_SOCIALMEDIA = 'afterCreatingFinalUserSocialMedia';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_CREATING_OPTIN_USER = 'afterCreatingOptinUser';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER = 'afterCreatingOptinExistingUser';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_DELETING_USER = 'afterDeletingUser';


    /**
     *  Length of token for anonymous users
     *
     * @const integer
     * @see \RKW\RkwRegistration\Tools\Authentication::ANONYMOUS_TOKEN_LENGTH
     */
    const ANONYMOUS_TOKEN_LENGTH = 20;

    /**
     * RegistrationRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    protected $registrationRepository;


    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;


    /**
     * FrontendUserGroupRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;


    /**
     * Signal-Slot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;


    /**
     * Setting
     *
     * @var array
     */
    protected $settings;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Checks given tokens from E-mail
     *
     * @param string $tokenYes
     * @param string $tokenNo
     * @param string $userSha1
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param array $data Data as reference
     * @return integer
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function checkTokens($tokenYes, $tokenNo, $userSha1, \TYPO3\CMS\Extbase\Mvc\Request $request = null, &$data = array())
    {
        // load register by SHA-token
        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->getRegistrationRepository()->findOneByUserSha1($userSha1);
        if (!$register) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('No opt-in found for given SHA1-key.'));

            return 0;
            //====
        }

        // is token already invalid?
        if (
            (!$register->getValidUntil())
            || ($register->getValidUntil() < time())
        ) {

            $this->getRegistrationRepository()->remove($register);
            $this->getPersistenceManager()->persistAll();
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Opt-in with id "%s" is not valid any more.', strtolower($register->getUid())));

            return 0;
            //====
        }


        // load fe-user
        if ($frontendUser = $this->getFrontendUserRepository()->findByUidInactiveNonAnonymous($register->getUser())) {

            // check yes-token
            $category = $register->getCategory();
            $settings = $this->getSettings();
            if ($register->getTokenYes() == $tokenYes) {

                if ($frontendUser->getDisable()) {

                    // generate new password and update user
                    $plaintextPassword = Password::generatePassword($frontendUser);
                    $frontendUser->setDisable(0);

                    // set normal lifetime
                    $frontendUser->setEndtime(0);
                    if (intval($settings['users']['lifetime'])) {
                        $frontendUser->setEndtime(time() + intval($settings['users']['lifetime']));
                    }

                    $this->getFrontendUserRepository()->update($frontendUser);


                    // Signal for E-Mails
                    $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_REGISTER_GRANT, array($frontendUser, $plaintextPassword, $register));
                    $data = array(
                        'frontendUser'      => $frontendUser,
                        'registration'      => $register,
                        'plaintextPassword' => $plaintextPassword,
                    );
                }

                // Signal for E-Mails
                if ($category) {

                    $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_REGISTER_GRANT . ucfirst($category), array($frontendUser, $register));
                    $data = array(
                        'frontendUser' => $frontendUser,
                        'registration' => $register,
                    );
                }

                // add privacy for user
                if ($request) {
                    \RKW\RkwRegistration\Tools\Privacy::addPrivacyDataForOptInFinal($request, $frontendUser, $register, ($category ? 'accepted opt-in for ' . $category : 'accepted opt-in'));
                }

                // delete registration
                $this->getRegistrationRepository()->remove($register);
                $this->getPersistenceManager()->persistAll();
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Opt-in with id "%s" (FE-User-Id=%s, category=%s) was successful.', strtolower($register->getUid()), $frontendUser->getUid(), $category));

                return 1;
                //====

                // check no-token
            } elseif ($register->getTokenNo() == $tokenNo) {

                // delete user and registration
                // remove only disabled user! (Fix redmine ticket #2661)
                if ($frontendUser->getDisable()) {
                    $this->getFrontendUserRepository()->removeHard($frontendUser);
                }
                $this->getRegistrationRepository()->remove($register);
                $this->getPersistenceManager()->persistAll();

                // Signal for E-Mails
                $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_REGISTER_DENIAL, array($frontendUser));
                $data = array(
                    'frontendUser' => $frontendUser,
                );

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Opt-in with id "%s" (FE-User-Id=%s, category=%s) was successfully canceled.', strtolower($register->getUid()), $frontendUser->getUid(), $category));

                return 2;
                //===
            }
        }

        // token mismatch or something strange happened - kill that beast!!!
        // $this->getRegistrationRepository()->remove($register);
        // $this->getPersistenceManager()->persistAll();

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Something went wrong when trying to register via opt-in with id "%s".', strtolower($register->getUid())));

        return 0;
        //====

    }


    /**
     * Registers new FE-User - or sends another opt-in to existing user
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|array $userData
     * @param boolean $enable
     * @param mixed $additionalData
     * @param string $category
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function register($userData, $enable = false, $additionalData = null, $category = null, \TYPO3\CMS\Extbase\Mvc\Request $request = null)
    {
        // if we get an array we just migrate the data to our object!
        $frontendUser = $userData;
        if (is_array($userData)) {
            /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
            $frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\FrontendUser');
            foreach ($userData as $key => $value) {
                $setter = 'set' . ucfirst(Common::camelize($key));
                if (method_exists($frontendUser, $setter)) {
                    $frontendUser->$setter($value);
                }
            }
        }

        if (!$frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('No valid object for registration given.'));
            throw new \RKW\RkwRegistration\Exception('No valid object given.', 1434997734);
            //===
        }

        // get settings
        $settings = $this->getSettings();

        // set email as fallback
        if (!$frontendUser->getUsername()) {
            $frontendUser->setUsername($frontendUser->getEmail());
        }

        // check username (aka email)
        if (
            (!$frontendUser->getUsername())
            || (!$this->validUsername($frontendUser->getUsername()))
        ) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('"%s" is not a valid username.', strtolower($frontendUser->getUsername())));
            throw new \RKW\RkwRegistration\Exception('No valid username given.', 1407312133);
            //===
        }

        // lowercase username and email!
        $frontendUser->setEmail(strtolower($frontendUser->getEmail()));
        $frontendUser->setUsername(strtolower($frontendUser->getUsername()));

        $frontendUser->setTxRkwregistrationTitle($this->setTitle($frontendUser));

        // check if user already exists!
        // then we generate an opt-in for additional data given
        // this may also be the case for logged in users without valid email (e.g. when registered via Facebook or Twitter) !!!
        if ($frontendUserDatabase = $this->getFrontendUserRepository()->findOneByEmailOrUsernameInactive($frontendUser->getUsername())) {

            // add opt in - but only if additional data is set!
            if ($additionalData) {

                $settings = $this->getSettings();
                $registration = $this->getRegistrationRepository()->newOptIn($frontendUserDatabase, $additionalData, $category, $settings['users']['daysForOptIn']);

                // add privacy for existing user
                if ($request) {
                    \RKW\RkwRegistration\Tools\Privacy::addPrivacyDataForOptIn($request, $frontendUserDatabase, $registration, ($category ? 'new opt-in for existing user for ' . $category : 'new opt-in for existing user'));
                }
                $this->getPersistenceManager()->persistAll();

                if (
                    ($frontendUser->getEmail() != strtolower($frontendUserDatabase->getEmail()))
                    && (!$this->validEmailUnique($frontendUser->getEmail(), $frontendUserDatabase))
                ) {

                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('E-mail "%s" is already used by another user.', strtolower($frontendUser->getEmail())));
                    throw new \RKW\RkwRegistration\Exception('Given e-mail already used by another user.', 1480500618);
                    //===
                }

                // E-mail may differ - this may be the reason for using an opt-in!
                // but we do not save it into the database here!!!
                $frontendUserDatabase->setEmail($frontendUser->getEmail());

                // Signal for e.g. E-Mails
                $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER . ucfirst($category), array($frontendUserDatabase, $registration));
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Opt-In for existing user "%s" (id=%s, category=%s) successfully generated.', strtolower($frontendUserDatabase->getUsername()), $frontendUserDatabase->getUid(), $category));
            }


            // if user does not exist yet, we need some more data to be set!
        } else {

            // set pid and crdate
            $frontendUser->setPid(intval($settings['users']['storagePid']));
            $frontendUser->setCrdate(time());

            // enable or disable
            if ($enable) {
                $frontendUser->setDisable(0);

                // set normal lifetime
                if (intval($settings['users']['lifetime'])) {
                    $frontendUser->setEndtime(time() + intval($settings['users']['lifetime']));
                }

            } else {
                $frontendUser->setDisable(1);

                // set opt-in lifetime
                if (intval($settings['users']['daysForOptIn'])) {
                    $frontendUser->setEndtime(time() + (intval($settings['users']['daysForOptIn']) * 24 * 60 * 60));
                }
            }

            // set languageKey
            if ($settings['users']['languageKeyOnRegister']) {
                $frontendUser->setTxRkwregistrationLanguageKey($settings['users']['languageKeyOnRegister']);
            }

            // set users server ip-address
            $remoteAddr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
            if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
                $ips = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                if ($ips[0]) {
                    $remoteAddr = filter_var($ips[0], FILTER_VALIDATE_IP);
                }
            }
            $frontendUser->setTxRkwregistrationRegisterRemoteIp($remoteAddr);

            // generate and set password
            $plaintextPassword = Password::generatePassword($frontendUser);

            // set registered-by
            if ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FacebookUser) {
                $frontendUser->setTxRkwregistrationRegisteredBy(1);
            } elseif ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\TwitterUser) {
                $frontendUser->setTxRkwregistrationRegisteredBy(2);
            }

            // set user groups
            $this->setUserGroupsOnRegister($frontendUser);

            // add user and persist!
            $this->getFrontendUserRepository()->add($frontendUser);
            $this->getPersistenceManager()->persistAll();

            if ($enable) {

                // Signal for e.g. E-Mails - but not for Socialmedia-Models
                if ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\SocialMediaInterface) {
                    $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_CREATING_FINAL_USER_SOCIALMEDIA . ucfirst($category), array($frontendUser, $plaintextPassword));
                } else {
                    $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_CREATING_FINAL_USER . ucfirst($category), array($frontendUser, $plaintextPassword));
                }
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully registered and enabled user "%s".', strtolower($frontendUser->getUsername())));

                // add privacy opt-in for non-existing user
                if ($request) {
                    \RKW\RkwRegistration\Tools\Privacy::addPrivacyData($request, $frontendUser, $additionalData, ($category ? 'new user without opt-in for ' . $category : 'new user without opt-in'));
                }
                $this->getPersistenceManager()->persistAll();

            } else {

                // add registration
                $settings = $this->getSettings();
                $registration = $this->getRegistrationRepository()->newOptIn($frontendUser, $additionalData, $category, $settings['users']['daysForOptIn']);

                // add privacy opt-in for non-existing user
                if ($request) {
                    \RKW\RkwRegistration\Tools\Privacy::addPrivacyDataForOptIn($request, $frontendUser, $registration, ($category ? 'new opt-in for non-existing user for ' . $category : 'new opt-in for non-existing user'));
                }
                $this->getPersistenceManager()->persistAll();

                // Signal for e.g. E-Mails
                $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_CREATING_OPTIN_USER . ucfirst($category), array($frontendUser, $registration));
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully registered user "%s". Awaiting opt-in.', strtolower($frontendUser->getUsername())));
            }
        }

        return $frontendUser;
        //===
    }


    /**
     * Creates a new anonymous FE-user
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function registerAnonymous()
    {

        /** @var \RKW\RkwWepstra\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');

        // get settings
        $settings = $this->getSettings();

        // create a token for anonymous login and check if this token already exists
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        do {
            $token = substr(str_shuffle($characters), 0, self::ANONYMOUS_TOKEN_LENGTH);
        } while (count($frontendUserRepository->findByUsername($token)));

        // now that we know that the token is non-existent we create a new user from it!
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $anonymousUser */
        $anonymousUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\FrontendUser');

        // for session identification set username (token == username)
        $anonymousUser->setUsername($token);
        $anonymousUser->setDisable(0);
        $anonymousUser->setTxRkwregistrationIsAnonymous(true);

        // set pid and crdate
        $anonymousUser->setPid(intval($settings['users']['storagePid']));
        $anonymousUser->setCrdate(time());

        // set lifetime
        if (intval($settings['users']['lifetimeAnonymous'])) {
            $anonymousUser->setEndtime(time() + intval($settings['users']['lifetimeAnonymous']));
        }

        // set languageKey
        if ($settings['users']['languageKeyOnRegister']) {
            $anonymousUser->setTxRkwregistrationLanguageKey($settings['users']['languageKeyOnRegister']);
        }

        // set users server ip-address
        $remoteAddr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ips = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ips[0]) {
                $remoteAddr = filter_var($ips[0], FILTER_VALIDATE_IP);
            }
        }
        $anonymousUser->setTxRkwregistrationRegisterRemoteIp($remoteAddr);

        // set password
        /** @var \RKW\RkwRegistration\Tools\Password $passwordTool */
        $passwordTool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Password');
        $passwordTool->generatePassword($anonymousUser);

        // set groups - this is needed - otherwise the user won't be able to login at all!
        $this->setUserGroupsOnRegister($anonymousUser);

        // add to repository
        $frontendUserRepository->add($anonymousUser);

        /** @var \TYPO3\CMS\Extbase\\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        $persistenceManager->persistAll();

        return $anonymousUser;
        //===
    }


    /**
     * Removes existing account of FE-user
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param string $category
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function delete(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser, $category = null)
    {
        // check if user is logged in - only the user himself can delete his account!
        if (Authentication::isUserLoggedIn($frontendUser)) {

            Authentication::logoutUser();
            $this->getFrontendUserRepository()->remove($frontendUser);
            $this->getPersistenceManager()->persistAll();

            // Signal for e.g. E-Mails or other extensions
            $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_DELETING_USER . ucfirst($category), array($frontendUser));

            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully logged out and deleted user "%s".', strtolower($frontendUser->getUsername())));

            return true;
            //===
        }

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Could not delete user "%s". User is not logged in.', strtolower($frontendUser->getUsername())));

        return false;
        //===
    }


    /**
     * setUsersGroupsOnRegister
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param string $userGroups
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setUserGroupsOnRegister(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser, $userGroups = '')
    {

        if (!$userGroups) {
            $settings = $this->getSettings();
            $userGroups = $settings['users']['groupsOnRegister'];

            if ($frontendUser->getTxRkwregistrationIsAnonymous()) {
                $userGroups = $settings['users']['groupsOnRegisterAnonymous'];
            }
        }

        $userGroupIds = GeneralUtility::trimExplode(',', $userGroups);
        foreach ($userGroupIds as $groupId) {

            $frontendUserGroup = $this->getFrontendUserGroupRepository()->findByUid($groupId);
            if ($frontendUserGroup) {
                $frontendUser->addUsergroup($frontendUserGroup);
            }
        }

    }


    /**
     * Checks if FE-User has valid email
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @return boolean
     */
    public static function validEmail($email = null)
    {

        if ($email) {

            if ($email instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
                $email = $email->getEmail();
            }

            if (
                (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($email)))
                && (strpos(strtolower($email), '@facebook.com') === false)
                && (strpos(strtolower($email), '@twitter.com') === false)
            ) {
                return true;
            }
            //===
        }

        return false;
        //===
    }


    /**
     * Checks if FE-User has valid email
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     */
    public static function validEmailUnique($email, $frontendUser)
    {

        if ($email instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
            $email = $email->getEmail();
        }

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');
        $dbFrontendUser = $frontendUserRepository->findOneByEmailOrUsernameInactive(strtolower($email));

        if (
            (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($email)))
            && (strpos(strtolower($email), '@facebook.com') === false)
            && (strpos(strtolower($email), '@twitter.com') === false)
            && (
                (!$dbFrontendUser)
                || (
                    ($dbFrontendUser)
                    && ($dbFrontendUser->getUsername() == $frontendUser->getUsername()))
            )
        ) {
            return true;
        }

        //===

        return false;
        //===
    }


    /**
     * Checks if FE-User has a a valid username
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @return boolean
     */
    public static function validUsername($email)
    {

        if ($email instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
            $email = $email->getEmail();
        }

        if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($email))) {
            return true;
        }

        //===

        return false;
        //===
    }


    /**
     * Returns RegistrationRepository
     *
     * @return \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    protected function getRegistrationRepository()
    {

        if (!$this->registrationRepository) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->registrationRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\RegistrationRepository');
        }

        return $this->registrationRepository;
        //===
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
        //===
    }


    /**
     * Returns FrontendUserGroupRepository
     *
     * @return \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    protected function getFrontendUserGroupRepository()
    {

        if (!$this->frontendUserGroupRepository) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->frontendUserGroupRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserGroupRepository');
        }

        return $this->frontendUserGroupRepository;
        //===
    }


    /**
     * Returns PersistanceManager
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
        //===
    }


    /**
     * Returns SignalSlotDispatcher
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {

        if (!$this->signalSlotDispatcher) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->signalSlotDispatcher = $objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        }

        return $this->signalSlotDispatcher;
        //===
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
            $this->settings = Common::getTyposcriptConfiguration('Rkwregistration');
        }

        if (!$this->settings) {
            return array();
        }

        //===

        return $this->settings;
        //===
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }

    /**
     * Returns \RKW\RkwRegistration\Domain\Model\Title instance
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return \RKW\RkwRegistration\Domain\Model\Title
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function setTitle(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Domain\Repository\TitleRepository $titleRepository */
        $titleRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\TitleRepository');
        $title = $titleRepository->findByName($frontendUser->getTitle())->getFirst();

        if (!$title) {

            $title = new \RKW\RkwRegistration\Domain\Model\Title;
            $title->setName($frontendUser->getTitle());

            $persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');

            $titleRepository->add($title);
            $persistenceManager->persistAll();

        }

        return $title;
    }
}