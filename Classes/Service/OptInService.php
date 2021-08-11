<?php

namespace RKW\RkwRegistration\Service;

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Model\Registration;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\GuestUserRepository;
use RKW\RkwRegistration\Exception;
use \RKW\RkwBasics\Utility\GeneralUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use \RKW\RkwRegistration\Utility\RemoteUtility;
use RKW\RkwRegistration\Utility\TitleUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
 * Class OptInService
 *
 * @toDo: Services SHOULD NOT be singletons
 * @toDo: Services MUST be used as objects, they are never static
 * (https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/CodingGuidelines/CglPhp/PhpArchitecture/ModelingCrossCuttingConcerns/Services.html)
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OptInService extends AbstractService
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
    const SIGNAL_AFTER_CREATING_OPTIN_USER = 'afterCreatingOptinUser';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER = 'afterCreatingOptinExistingUser';


    /**
     * Signal name for use in ext_localconf.php
     * @deprecated Will be removed soon. Use signal in RegisterFrontendUserService instead
     * @const string
     */
    const SIGNAL_AFTER_DELETING_USER = 'afterDeletingUser';

    /**
     * Signal name for use in ext_localconf.php
     *
     * @deprecated Will be removed soon. Use signal in RegisterGuestUserService instead
     * @const string
     */
    const SIGNAL_AFTER_REGISTER_GUEST = 'afterRegisterGuest';

    /**
     * Length of token for guest users
     *
     * @deprecated Will be removed soon. Use constant in RegisterGuestUserService instead
     * @const integer
     * @see \RKW\RkwRegistration\Service\AuthService::GUEST_TOKEN_LENGTH
     */
    const GUEST_TOKEN_LENGTH = 20;

    /**
     * Setting
     *
     * @var array
     */
    protected $settings;


    /**
     * @var Logger
     */
    protected $logger;


    /**
     * Checks given tokens from E-mail
     *
     * @param string       $tokenYes
     * @param string       $tokenNo
     * @param string       $userSha1
     * @param Request|null $request
     * @param array        $data Data as reference
     * @return integer 0 = unexpected error; 1 = registration created; 2 = registration dismissed by user; 400 = expired; 500 = registration not found
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function process($tokenYes, $tokenNo, $userSha1, Request $request = null, &$data = array())
    {
        // load register by SHA-token
        /** @var Registration $register */
        $register = $this->registrationRepository->findOneByUserSha1($userSha1);

        // not found
        if (!$register) {
            $this->getLogger()->log(LogLevel::ERROR, sprintf('No opt-in found for given SHA1-key.'));
            return 500;
        }

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());

        $status = $this->check($register, $tokenYes, $tokenNo);

        // token "no" or "expired"
        if (
            $status === 2
            || $status == 400
        ) {
            // delete user and registration
            // remove only disabled user!
            if ($frontendUser->getDisable()) {
                $this->frontendUserRepository->removeHard($frontendUser);
            }

            $this->registrationRepository->remove($register);
            $this->persistenceManager->persistAll();

            // Signal for E-Mails
            $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_REGISTER_DENIAL, array($frontendUser));
            $data = array(
                'frontendUser' => $frontendUser,
            );

            if ($status === 2) {
                $this->getLogger()->log(LogLevel::INFO, sprintf('Opt-in with id "%s" (FE-User-Id=%s, category=%s) was successfully canceled.', strtolower($register->getUid()), $frontendUser->getUid(), $category));
                return 2;
            } elseif ($status == 400) {
                $this->getLogger()->log(LogLevel::WARNING, sprintf('Opt-in with id "%s" is not valid any more.', strtolower($register->getUid())));
                return 400;
            }
        }

        // token "yes"
        if ($status === 1) {

            // load fe-user
            if ($frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser())) {

                if ($frontendUser->getDisable()) {

                    // generate new password and update user
                    $plaintextPassword = PasswordUtility::generatePassword();
                    $frontendUser->setPassword(PasswordUtility::saltPassword($plaintextPassword));

                    $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
                    /** @var RegisterFrontendUserService $registerFrontendUserService */
                    $registerFrontendUserService = $objectManager->get(RegisterFrontendUserService::class, $frontendUser);
                    $registerFrontendUserService->setClearanceAndLifetime(true);

                    // Signal for E-Mails
                    $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_REGISTER_GRANT, array($frontendUser, $plaintextPassword, $register));
                    $data = array(
                        'frontendUser'      => $frontendUser,
                        'registration'      => $register,
                        'plaintextPassword' => $plaintextPassword,
                    );
                }
            }

            if ($register->getCategory()) {

                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_REGISTER_GRANT . ucfirst($register->getCategory()), array($frontendUser, $register));
                $data = array(
                    'frontendUser' => $frontendUser,
                    'registration' => $register,
                );
            }

            // add privacy for frontendUser
            if ($request) {
                PrivacyService::addPrivacyDataForOptInFinal($request, $frontendUser, $register, ($register->getCategory() ? 'accepted opt-in for ' . $register->getCategory() : 'accepted opt-in'));
            }

            // delete registration
            $this->registrationRepository->remove($register);
            $this->persistenceManager->persistAll();
            $this->getLogger()->log(LogLevel::INFO, sprintf('Opt-in with id "%s" (FE-User-Id=%s, category=%s) was successful.', strtolower($register->getUid()), $frontendUser->getUid(), $register->getCategory()));

            return 1;
        }

        // token mismatch or something strange happened - kill that beast!!!
        // $this->getRegistrationRepository()->remove($register);
        // $this->getPersistenceManager()->persistAll();
        $this->getLogger()->log(LogLevel::ERROR, sprintf('Something went wrong when trying to register via opt-in with id "%s".', strtolower($register->getUid())));

        return 0;
    }



    /**
     * Checks given tokens from E-mail
     *
     * @deprecated This function will be removed soon. Use OptInService->process instead
     *
     * @param string $tokenYes
     * @param string $tokenNo
     * @param string $userSha1
     * @param Request $request
     * @param array $data Data as reference
     * @return integer
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function checkTokens($tokenYes, $tokenNo, $userSha1, Request $request = null, &$data = array())
    {
        // load register by SHA-token
        /** @var Registration $register */
        $register = $this->getRegistrationRepository()->findOneByUserSha1($userSha1);
        if (!$register) {
            $this->getLogger()->log(LogLevel::ERROR, sprintf('No opt-in found for given SHA1-key.'));

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
            $this->getLogger()->log(LogLevel::WARNING, sprintf('Opt-in with id "%s" is not valid any more.', strtolower($register->getUid())));

            return 0;
            //====
        }


        // load fe-user
        if ($frontendUser = $this->getFrontendUserRepository()->findByUidInactiveNonGuest($register->getUser())) {

            // check yes-token
            $category = $register->getCategory();
            if ($register->getTokenYes() == $tokenYes) {

                if ($frontendUser = $this->getFrontendUserRepository()->findByUidInactiveNonGuest($register->getUser())) {

                    if ($frontendUser->getDisable()) {

                        /** @var RegisterFrontendUserService $registerFrontendUserService */
                        $registerFrontendUserService = GeneralUtility::makeInstance(RegisterFrontendUserService::class, $frontendUser);
                        $registerFrontendUserService->setClearanceAndLifetime(true);
                        $plaintextPassword = $registerFrontendUserService->setNewPassword();

                        // @toDo: add to Repo?
                        // @toDo: persist?

                        // Signal for E-Mails
                        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_REGISTER_GRANT, array($frontendUser, $plaintextPassword, $register));
                        $data = array(
                            'frontendUser'      => $frontendUser,
                            'registration'      => $register,
                            'plaintextPassword' => $plaintextPassword,
                        );
                    }

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
                    PrivacyService::addPrivacyDataForOptInFinal($request, $frontendUser, $register, ($category ? 'accepted opt-in for ' . $category : 'accepted opt-in'));
                }

                // delete registration
                $this->getRegistrationRepository()->remove($register);
                $this->getPersistenceManager()->persistAll();
                $this->getLogger()->log(LogLevel::INFO, sprintf('Opt-in with id "%s" (FE-User-Id=%s, category=%s) was successful.', strtolower($register->getUid()), $frontendUser->getUid(), $category));

                return 1;

                // check no-token
            } elseif ($register->getTokenNo() == $tokenNo) {

                // delete user and registration
                // remove only disabled user!
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

                $this->getLogger()->log(LogLevel::INFO, sprintf('Opt-in with id "%s" (FE-User-Id=%s, category=%s) was successfully canceled.', strtolower($register->getUid()), $frontendUser->getUid(), $category));

                return 2;
            }
        }

        // token mismatch or something strange happened - kill that beast!!!
        // $this->getRegistrationRepository()->remove($register);
        // $this->getPersistenceManager()->persistAll();

        $this->getLogger()->log(LogLevel::ERROR, sprintf('Something went wrong when trying to register via opt-in with id "%s".', strtolower($register->getUid())));

        return 0;
    }


    /**
     * Registers new FE-User - or sends another opt-in to existing user
     *
     * @param FrontendUser|array $userData A FrontendUser object or an array with equivalent key names to frontendUser properties. E.g. "mail" oder "username"
     * @param boolean $enable If a new user should be enabled or not
     * @param mixed $additionalData Could be an array or a whole object. Everything you need in relation of a registration of something
     * @param string $category To identify a specific context. Normally used to identify an external extension which is using this function
     * @param Request $request For privacy purpose to identify the given context
     * @return FrontendUser
     * @throws Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function register($userData, $enable = false, $additionalData = null, $category = null, Request $request = null)
    {
        // if we get an array we just migrate the data to our object!
        $frontendUser = FrontendUserUtility::convertArrayToObject($userData);

        if (!$frontendUser instanceof FrontendUser) {
            $this->getLogger()->log(LogLevel::ERROR, sprintf('No valid object for registration given.'));
            throw new Exception('No valid object given.', 1434997734);
        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var RegisterFrontendUserService $registerFrontendUserService */
        $registerFrontendUserService = $objectManager->get(RegisterFrontendUserService::class, $frontendUser);

        // set email as fallback
        if (!$frontendUser->getUsername()) {
            $frontendUser->setUsername($frontendUser->getEmail());
        }

        // check username (aka email)

        DebuggerUtility::var_dump($registerFrontendUserService->validateEmail()); exit;

        if (!$registerFrontendUserService->validateEmail()) {
            $this->getLogger()->log(LogLevel::ERROR, sprintf('"%s" is not a valid username.', strtolower($frontendUser->getUsername())));
            throw new Exception('No valid username given.', 1407312133);
        }

        // lowercase username and email!
        $frontendUser->setEmail(strtolower($frontendUser->getEmail()));
        $frontendUser->setUsername(strtolower($frontendUser->getUsername()));

        if ($frontendUser->getTitle()) {
            $frontendUser->setTxRkwregistrationTitle(TitleUtility::extractTxRegistrationTitle($frontendUser->getTitle(), $this->settings));
            //  set old title field to ''
            $frontendUser->setTitle('');
        }

        // check if user already exists!
        // then we generate an opt-in for additional data given
        // this may also be the case for logged in users without valid email (e.g. when registered via Facebook or Twitter) !!!
        if ($frontendUserDatabase = $this->frontendUserRepository->findOneByEmailOrUsernameInactive($frontendUser->getUsername())) {

            // add opt in - but only if additional data is set!
            if ($additionalData) {

                $registration = $this->registrationRepository->newOptIn($frontendUserDatabase, $additionalData, $category, $this->settings['users']['daysForOptIn']);

                // add privacy for existing user
                if ($request) {
                    PrivacyService::addPrivacyDataForOptIn($request, $frontendUserDatabase, $registration, ($category ? 'new opt-in for existing user for ' . $category : 'new opt-in for existing user'));
                }
                $this->persistenceManager->persistAll();

                if (
                    ($frontendUser->getEmail() != strtolower($frontendUserDatabase->getEmail()))
                    && (!$registerFrontendUserService->uniqueEmail($frontendUserDatabase))
                ) {

                    $this->getLogger()->log(LogLevel::ERROR, sprintf('E-mail "%s" is already used by another user.', strtolower($frontendUser->getEmail())));
                    throw new Exception('Given e-mail already used by another user.', 1480500618);
                }

                // E-mail may differ - this may be the reason for using an opt-in!
                // but we do not save it into the database here!!!
                $frontendUserDatabase->setEmail($frontendUser->getEmail());

                // Signal for e.g. E-Mails
                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER . ucfirst($category), array($frontendUserDatabase, $registration));
                $this->getLogger()->log(LogLevel::INFO, sprintf('Opt-In for existing user "%s" (id=%s, category=%s) successfully generated.', strtolower($frontendUserDatabase->getUsername()), $frontendUserDatabase->getUid(), $category));
            }
            // hint: This is more important than it looks. This returns a real existing FrontendUser. If you would remove
            // this line a new created FrontendUser-Instance from above would returned at the end of this function
            // (which is disabled by default! @see convertArrayToObject)
            $frontendUser = $frontendUserDatabase;

            // if user does not exist yet, we need some more data to be set!
        } else {

            $registerFrontendUserService->setBasicData();
            $registerFrontendUserService->setClearanceAndLifetime($enable);
            $plaintextPassword = $registerFrontendUserService->setNewPassword();

            // add user and persist!
            $this->getFrontendUserRepository()->add($frontendUser);
            $this->getPersistenceManager()->persistAll();

            if ($enable) {
                // Signal for e.g. E-Mails
                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_CREATING_FINAL_USER . ucfirst($category), array($frontendUser, $plaintextPassword, null));
                $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully registered and enabled user "%s".', strtolower($frontendUser->getUsername())));
                // add privacy opt-in for non-existing user
                if ($request) {
                    PrivacyService::addPrivacyData($request, $frontendUser, $additionalData, ($category ? 'new user without opt-in for ' . $category : 'new user without opt-in'));
                }
                $this->persistenceManager->persistAll();

            } else {
                // add registration
                $registration = $this->registrationRepository->newOptIn($frontendUser, $additionalData, $category, $this->settings['users']['daysForOptIn']);
                // add privacy opt-in for non-existing user
                if ($request) {
                    PrivacyService::addPrivacyDataForOptIn($request, $frontendUser, $registration, ($category ? 'new opt-in for non-existing user for ' . $category : 'new opt-in for non-existing user'));
                }
                $this->persistenceManager->persistAll();
                // Signal for e.g. E-Mails
                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_CREATING_OPTIN_USER . ucfirst($category), array($frontendUser, $registration));
                $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully registered user "%s". Awaiting opt-in.', strtolower($frontendUser->getUsername())));
            }
        }

        return $frontendUser;
    }


    /**
     * Creates a new anonymous FE-user
     *
     * @deprecated This function is deprecated and will be removed soon. Use registerGuest() instead.
     *
     * @return FrontendUser
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function registerAnonymous()
    {
        return $this->registerGuest();
    }


    /**
     * Creates a new guest FE-user
     *
     * @deprecated Use RegisterGuestUserService->register instead
     *
     * @param int $lifetime Individual lifetime of the guest user. For default value see settings.users.lifetimeGuest
     * @param string $category
     * @return FrontendUser
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function registerGuest($lifetime = 0, $category = '')
    {
        /** @var \RKW\RkwWepstra\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        $guestUserRepository = $objectManager->get(GuestUserRepository::class);

        // get settings
        $settings = $this->getSettings();

        // now that we know that the token is non-existent we create a new user from it!
        /** @var GuestUser $guestUser */
        $guestUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(GuestUser::class);

        // for session identification set username (token == username)
        $guestUser->setUsername($this->createGuestToken());
        $guestUser->setDisable(0);
        $guestUser->setPid(intval($settings['users']['storagePid']));
        $guestUser->setCrdate(time());
        $guestUser->setTxRkwregistrationRegisterRemoteIp(RemoteUtility::getIp());
        $guestUser->setEndtime($this->getGuestLifetime());
        $guestUser->setTxRkwregistrationLanguageKey($settings['users']['languageKeyOnRegister'] ? $settings['users']['languageKeyOnRegister'] : '');

        // set password
        $guestUser->setPassword(PasswordUtility::saltPassword(PasswordUtility::generatePassword()));

        // set groups - this is needed - otherwise the user won't be able to login at all!
        $this->setUserGroupsOnRegister($guestUser);

        // add to repository
        $guestUserRepository->add($guestUser);

        /** @var \TYPO3\CMS\Extbase\\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();

        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_REGISTER_GUEST . ucfirst($category), array($guestUser));

        return $guestUser;
    }


    /**
     * Removes existing account of FE-user
     *
     * @deprecated Will be removed soon. Use Service/RegisterFrontendUserService->delete instead
     *
     * @param FrontendUser $frontendUser
     * @param string $category
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function delete(FrontendUser $frontendUser, $category = null)
    {
        // check if user is logged in - only the user himself can delete his account!
        if (FrontendUserSessionUtility::isUserLoggedIn($frontendUser)) {

            FrontendUserSessionUtility::logout();
            $this->getFrontendUserRepository()->remove($frontendUser);
            $this->getPersistenceManager()->persistAll();

            // Signal for e.g. E-Mails or other extensions
            $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_DELETING_USER . ucfirst($category), array($frontendUser));

            $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully logged out and deleted user "%s".', strtolower($frontendUser->getUsername())));

            return true;
            //===
        }

        $this->getLogger()->log(LogLevel::WARNING, sprintf('Could not delete user "%s". User is not logged in.', strtolower($frontendUser->getUsername())));

        return false;
        //===
    }


    /**
     * setUsersGroupsOnRegister
     *
     * @deprecated Use Service/RegisterFrontendUserService->setUserGroupsOnRegister instead
     *
     * @param FrontendUser $frontendUser
     * @param string $userGroups
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setUserGroupsOnRegister(FrontendUser $frontendUser, $userGroups = '')
    {
        if (!$userGroups) {
            $settings = $this->getSettings();

            if ($frontendUser instanceof GuestUser) {
                $userGroups = $settings['users']['groupsOnRegisterGuest'];

                if (!$settings['users']['groupsOnRegisterGuest']) {
                    $this->getLogger()->log(LogLevel::ERROR, sprintf('Login for guest user "%s" failed. Reason: No groupsOnRegisterGuest is defined in TypoScript.', strtolower($frontendUser->getUsername())));
                }
            } else {
                $userGroups = $settings['users']['groupsOnRegister'];
            }
        }

        $userGroupIds = GeneralUtility::trimExplode(',', $userGroups);
        foreach ($userGroupIds as $groupId) {

            /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
            $frontendUserGroup = $this->getFrontendUserGroupRepository()->findByUid($groupId);
            if ($frontendUserGroup) {
                $frontendUser->addUsergroup($frontendUserGroup);
            }
        }
    }


    /**
     * Checks if FE-User has valid email
     *
     * @deprecated Will be removed soon. Use Service/RegisterFrontendUserService->validEmail instead
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
        }

        return false;
    }


    /**
     * Checks if FE-User has valid email
     *
     * @deprecated Will be removed soon. Use Service/RegisterFrontendUserService->validEmailUnique instead
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

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
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

        return false;
    }


    /**
     * Checks if FE-User has a a valid username
     *
     * @deprecated Will be removed soon. Use Service/RegisterFrontendUserService->validUsername instead
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

        return false;
    }




    /**
     * Checks given tokens from E-mail
     *
     * @param Registration $register
     * @param string $tokenYes
     * @param string $tokenNo
     * @return integer The status. 0 = unexpected error, 1 = token yes, 2 = token no, 400 = expired
     */
    protected function check(Registration $register, $tokenYes, $tokenNo): int
    {
        // is token already invalid?
        if (
            !$register->getValidUntil()
            || ($register->getValidUntil() < time())
        ) {
            return 400;
        }

        if ($register->getTokenYes() == $tokenYes) {
            return 1;
        }

        if ($register->getTokenNo() == $tokenNo) {
            return 2;
        }

        $this->getLogger()->log(LogLevel::WARNING, sprintf('Something unexpected went wrong while checking an registration.'));
        return 0;
    }



    /**
     * creates a valid token for a guest user
     *
     * @deprecated Use RegisterGuestUserService->createToken instead
     *
     * @return string
     */
    protected function createGuestToken()
    {
        /** @var \RKW\RkwWepstra\Domain\Repository\FrontendUserRepository $guestUserRepository */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        $guestUserRepository = $objectManager->get(GuestUserRepository::class);

        // create a token for anonymous login and check if this token already exists
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        do {
            $token = substr(str_shuffle($characters), 0, self::GUEST_TOKEN_LENGTH);
        } while (count($guestUserRepository->findByUsername($token)));

        return $token;
    }



    /**
     * converts an feUser array to an object
     *
     * @deprecated Will be removed soon. Use Service/RegisterFrontendUserService->convertFrontendUserArrayToObject instead
     * validUsername
     * array $userData
     * @return FrontendUser
     */
    protected function convertFrontendUserArrayToObject($userData)
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
     * returns the lifetime of the guest user
     *
     * @deprecated Will be removed soon. Use Service/RegisterGuestUserService->setClearanceAndLifetime instead
     *
     * @return string
     */
    protected function getGuestLifetime($lifetime = 0)
    {
        // get settings
        $settings = $this->getSettings();

        // set lifetime
        if (
            intval($settings['users']['lifetimeGuest'])
            || intval($lifetime)
        ) {
            return time() + ($lifetime ? $lifetime : intval($settings['users']['lifetimeGuest']));
        }

        // default would return 0 - means: there is no endtime
        return $lifetime;
    }



    /**
     * Returns logger instance
     *
     * @return Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}