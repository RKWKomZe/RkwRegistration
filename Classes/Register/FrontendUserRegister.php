<?php

namespace RKW\RkwRegistration\Register;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Register\AbstractRegister;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;
use RKW\RkwRegistration\Utility\RemoteUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
 * FrontendUserRegister
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRegister extends AbstractRegister
{
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_DELETING_USER = 'afterDeletingUser';

    /**
     * FrontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    protected $frontendUser;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function __construct(FrontendUser $frontendUser)
    {
        $this->frontendUser = $frontendUser;

        if ($this->frontendUser->_isNew()) {
            $this->setBasicData();
        }

        $this->initializeObject();
    }



    /**
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }



    /**
     * sets some basic data to a frontendUser (if not already set)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setBasicData()
    {
        $settings = $this->getSettings();

        if (!$this->frontendUser->getPid()) {
            $this->frontendUser->setPid(intval($settings['users']['storagePid']));
        }

        if (!$this->frontendUser->getCrdate()) {
            $this->frontendUser->setCrdate(time());
        }

        // set languageKey
        if (!$this->frontendUser->getTxRkwregistrationLanguageKey()
            && $settings['users']['languageKeyOnRegister']
        ) {
            $this->frontendUser->setTxRkwregistrationLanguageKey($settings['users']['languageKeyOnRegister']);
        }

        $this->frontendUser->setTxRkwregistrationRegisterRemoteIp(RemoteUtility::getIp());

        // set user groups
        if (!$this->frontendUser->getUsergroup()->count()) {
            $this->setUserGroupsOnRegister($this->frontendUser);
        }

    }



    /**
     * enables or disables a created frontendUser (if not enabled yet)
     * Hint: This function is made for new created frontendUser and should not used for already existing users with lifetime
     *
     * @param bool $enable if the user should be enabled or not
     * @return void
     */
    public function setClearanceAndLifetime($enable = false)
    {
        if (
            $enable
            && !$this->frontendUser->getDisable()
        ) {
            $this->getLogger()->log(LogLevel::WARNING, sprintf('Cannot enable active user %s.', $this->frontendUser->getUid()));
        }

        // enable or disable
        if ($enable) {
            $this->frontendUser->setDisable(0);

            // set frontendUser lifetime
            if (intval($this->settings['users']['lifetime'])) {
                $this->frontendUser->setEndtime(time() + intval($this->settings['users']['lifetime']));
            }

            // override if it's a GuestUser
            if ($this->frontendUser instanceof GuestUser) {
                // set guestUser lifetime
                if (intval($this->settings['users']['guest']['lifetime'])) {
                    $this->frontendUser->setEndtime(time() + intval($this->settings['users']['guest']['lifetime']));
                }
            }
        } else {
            // this function should never disable an already existing user
            $this->frontendUser->setDisable(1);

            // set opt-in lifetime
            if (intval($this->settings['users']['daysForOptIn'])) {
                $this->frontendUser->setEndtime(time() + (intval($this->settings['users']['daysForOptIn']) * 24 * 60 * 60));
            }
        }
    }



    /**
     * Removes existing account of FE-user
     *
     * @param string $category
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function delete($category = null): bool
    {
        if (FrontendUserSessionUtility::isUserLoggedIn($this->frontendUser)) {
            FrontendUserSessionUtility::logout();
        }

        // @toDo: Check it: Added "setDelete" while writing the functions tests. The remove itself does not work. Should not be necessary
        $this->frontendUser->setDeleted(1);
        $this->getFrontendUserRepository()->remove($this->frontendUser);
        $this->getPersistenceManager()->persistAll();

        // Signal for e.g. E-Mails or other extensions
        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_DELETING_USER . ucfirst($category), array($this->frontendUser));
        $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully logged out and deleted user "%s".', strtolower($this->frontendUser->getUsername())));

        return true;
    }



    /**
     * Checks if FE-User has valid email
     * Because we're using the email also as username, this function can also be used as "validUsername"
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @return boolean
     */
    public function validateEmail($email = null): bool
    {
        $email = $email ? $email : $this->frontendUser->getEmail();

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
     * Checks if an email address is unique
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @return boolean Return true is email still available (not used by another FrontendUser)
     */
    public function uniqueEmail($email): bool
    {
        if ($email instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
            $email = $email->getEmail();
        }

        $dbFrontendUser = $this->frontendUserRepository->findOneByEmailOrUsernameInactive(strtolower($email));

        if (!$dbFrontendUser) {
            return true;
        }
        return false;
    }



    /**
     * setUsersGroupsOnRegister
     *
     * Hint: Handles FrontendUser AND GuestUser. Split GuestUser part into GuestUserRegister?
     *
     * @param string $userGroups
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setUserGroupsOnRegister($userGroups = '')
    {
        if (!$userGroups) {
            $settings = $this->getSettings();

            if ($this->frontendUser instanceof GuestUser) {
                $userGroups = $settings['users']['guest']['groupsOnRegister'];

                if (!$settings['users']['guest']['groupsOnRegister']) {
                    $this->getLogger()->log(LogLevel::ERROR, sprintf('GuestUser "%s" will not be useable. Reason: Setting guest.groupsOnRegister is not defined in TypoScript.', strtolower($this->frontendUser->getUsername())));
                }
            } else {
                $userGroups = $settings['users']['groupsOnRegister'];

                if (!$settings['users']['groupsOnRegister']) {
                    $this->getLogger()->log(LogLevel::ERROR, sprintf('FrontendUser "%s" will not be useable. Reason: Setting groupsOnRegister is not defined in TypoScript.', strtolower($this->frontendUser->getUsername())));
                }
            }
        }

        $userGroupIds = GeneralUtility::trimExplode(',', $userGroups);
        foreach ($userGroupIds as $groupId) {
            /** @var FrontendUserGroup $frontendUserGroup */
            $frontendUserGroup = $this->getFrontendUserGroupRepository()->findByUid($groupId);
            if ($frontendUserGroup instanceof FrontendUserGroup) {
                $this->frontendUser->addUsergroup($frontendUserGroup);
            }
        }
    }


    /**
     * creates and set a new password
     * (a Service function which is calling the PasswordUtility)
     *
     * @return string The new created plaintext password
     */
    public function setNewPassword()
    {
        $plaintextPassword = PasswordUtility::generatePassword();
        $this->frontendUser->setPassword(PasswordUtility::saltPassword($plaintextPassword));

        return $plaintextPassword;
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
