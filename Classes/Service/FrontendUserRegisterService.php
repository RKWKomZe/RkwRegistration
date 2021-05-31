<?php

namespace RKW\RkwRegistration\Service;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;
use RKW\RkwRegistration\Utility\RemoteUtility;
use TYPO3\CMS\Core\Log\LogLevel;

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
 * FrontendUserRegisterService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRegisterService extends AbstractService
{
    /**
     * FrontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    protected $frontendUser;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function __construct(FrontendUser $frontendUser)
    {
        $this->frontendUser = $frontendUser;

        if ($frontendUser->_isNew()) {
            $this->setBasicData();
        }
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
        if (!$this->frontendUser->getPid()) {
            $this->frontendUser->setPid(intval($this->settings['users']['storagePid']));
        }

        if (!$this->frontendUser->getCrdate()) {
            $this->frontendUser->setCrdate(time());
        }

        // set languageKey
        if (!$this->frontendUser->getTxRkwregistrationLanguageKey()
            && $this->settings['users']['languageKeyOnRegister']
        ) {
            $this->frontendUser->setTxRkwregistrationLanguageKey($this->settings['users']['languageKeyOnRegister']);
        }

        $this->frontendUser->setTxRkwregistrationRegisterRemoteIp(RemoteUtility::getIp());

        // set user groups
        if (!$this->frontendUser->getUsergroup()->count()) {
            $this->setUserGroupsOnRegister($this->frontendUser);
        }

    }



    /**
     * enables a newly created frontendUser (if not enabled yet)
     *
     * @param bool $enable if the user should be enabled or not
     * @return void
     */
    public function setClearanceAndLifetime($enable = false)
    {
        if (!$this->frontendUser->getDisable()) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Cannot enable active user.', $this->frontendUser->getUid()));
        }

        // enable or disable
        if ($enable) {
            $this->frontendUser->setDisable(0);

            // set normal lifetime
            if (intval($this->settings['users']['lifetime'])) {
                $this->frontendUser->setEndtime(time() + intval($this->settings['users']['lifetime']));
            }
        } else {
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
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function delete($category = null)
    {
        // check if user is logged in - only the user himself can delete his account!
        if (FrontendUserSessionUtility::isUserLoggedIn($this->frontendUser)) {

            FrontendUserSessionUtility::logout();
            $this->getFrontendUserRepository()->remove($this->frontendUser);
            $this->getPersistenceManager()->persistAll();

            return true;
        }

        $this->getLogger()->log(LogLevel::WARNING, sprintf('Could not delete user "%s". User is not logged in.', strtolower($this->frontendUser->getUsername())));

        return false;
        //===
    }



    /**
     * Checks if FE-User has valid email
     *
     * @toDo: This function could be also a static part of a FrontendUserUtility
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @return boolean
     */
    public function validEmail($email = null)
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
     * @return boolean
     */
    public function validEmailUnique($email)
    {
        if ($email instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
            $email = $email->getEmail();
        }

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $this->frontendUserRepository */
        $this->frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');
        $dbFrontendUser = $this->frontendUserRepository->findOneByEmailOrUsernameInactive(strtolower($email));

        if (
            (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($email)))
            && (strpos(strtolower($email), '@facebook.com') === false)
            && (strpos(strtolower($email), '@twitter.com') === false)
            && (
                !$dbFrontendUser
                || $dbFrontendUser->getUsername() == $this->frontendUser->getUsername()
            )
        ) {
            return true;
        }
        return false;
    }



    /**
     * Checks if FE-User has a a valid username
     *
     * @toDo: This function could be also a static part of a FrontendUserUtility
     *
     * @return boolean
     */
    public function validUsername()
    {
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($this->frontendUser->getEmail()))) {
            return true;
        }
        return false;
    }



    /**
     * setUsersGroupsOnRegister
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
                $userGroups = $settings['users']['groupsOnRegisterGuest'];

                if (!$settings['users']['groupsOnRegisterGuest']) {
                    $this->getLogger()->log(LogLevel::ERROR, sprintf('Login for guest user "%s" failed. Reason: No groupsOnRegisterGuest is defined in TypoScript.', strtolower($this->frontendUser->getUsername())));
                }
            } else {
                $userGroups = $settings['users']['groupsOnRegister'];
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
    }
}
