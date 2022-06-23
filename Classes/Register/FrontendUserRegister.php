<?php

namespace RKW\RkwRegistration\Register;
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

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\ServiceRepository;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\PasswordUtility;
use RKW\RkwRegistration\Utility\ClientUtility;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
        if ($this->frontendUser->_isNew()) {
            $this->setBasicData();
        }

        $this->initializeObject();
    }

    
    /**
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     */
    public function getFrontendUser(): FrontendUser
    {
        return $this->frontendUser;
    }


    /**
     * sets some basic data to a frontendUser (if not already set)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setBasicData(): void
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

        $this->frontendUser->setTxRkwregistrationRegisterRemoteIp(ClientUtility::getIp());

        // set user groups
        if (!$this->frontendUser->getUsergroup()->count()) {
            $this->setUserGroupsOnRegister($this->frontendUser);
        }
    }

    
    /**
     * enables or disables a created frontendUser (if not enabled yet)
     * Hint: This function is made for newly created frontendUsers and should not used for already existing users with lifetime
     *
     * @param bool $enable if the user should be enabled or not
     * @return bool
     */
    public function setClearanceAndLifetime(bool $enable = false): bool
    {
        if (
            $enable
            && !$this->frontendUser->getDisable()
        ) {
            $this->getLogger()->log(
                LogLevel::WARNING, 
                sprintf('Cannot enable active user %s.', $this->frontendUser->getUid())
            );
            
            return false;
        }

        // enable or disable
        if ($enable) {
            
            $this->frontendUser->setDisable(0);

            // set normal lifetime
            $this->frontendUser->setEndtime(0);

            // override if there is set a specific frontendUser lifetime
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
        
        return true;
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
    public function delete(string $category = ''): bool
    {
        if (FrontendUserSessionUtility::isUserLoggedIn($this->frontendUser)) {
            FrontendUserSessionUtility::logout();
        }

        // @toDo: Check it: Added "setDelete" while writing the functions tests. The remove itself does not work. Should not be necessary
        $this->frontendUser->setDeleted(1);
        $this->frontendUserRepository->remove($this->frontendUser);
        $this->persistenceManager->persistAll();

        // Signal for e.g. E-Mails or other extensions
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            self::SIGNAL_AFTER_DELETING_USER . ucfirst($category),
            [$this->frontendUser]
        );
        $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully logged out and deleted user "%s".', strtolower($this->frontendUser->getUsername())));

        return true;
    }



    /**
     * Checks if FE-User has valid email
     * Since we're using the email also as username, this function can also be used as "validateUsername"
     *
     * @return boolean
     */
    public function validateEmail(): bool
    {
        $email = $this->frontendUser->getEmail();
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
     * @return bool return true is email still available (not used by another FrontendUser)
     */
    public function uniqueEmail(): bool
    {
        $email = $this->frontendUser->getEmail();
        $dbFrontendUser = $this->frontendUserRepository->findOneByEmailOrUsernameAlsoInactive(strtolower($email));

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
    public function setUserGroupsOnRegister(string $userGroups = '')
    {
        if (!$userGroups) {
            $settings = $this->getSettings();

            if ($this->frontendUser instanceof GuestUser) {
                $userGroups = $settings['users']['guest']['groupsOnRegister'];
                if (!$userGroups) {
                    $this->getLogger()->log(LogLevel::ERROR, sprintf('GuestUser "%s" will not be usable. Reason: Setting users.guest.groupsOnRegister is not defined in TypoScript.', strtolower($this->frontendUser->getUsername())));
                }
                
            } else {
                $userGroups = $settings['users']['groupsOnRegister'];
                if (!$userGroups) {
                    $this->getLogger()->log(LogLevel::ERROR, sprintf('FrontendUser "%s" will not be usable. Reason: Setting users.groupsOnRegister is not defined in TypoScript.', strtolower($this->frontendUser->getUsername())));
                }
            }
        }

        $userGroupIds = GeneralUtility::trimExplode(',', $userGroups);
        foreach ($userGroupIds as $groupId) {
            /** @var FrontendUserGroup $frontendUserGroup */
            $frontendUserGroup = $this->frontendUserGroupRepository->findByUid($groupId);
            if ($frontendUserGroup instanceof FrontendUserGroup) {
                $this->frontendUser->addUsergroup($frontendUserGroup);
            }
        }
    }


    /**
     * Creates and set a new password
     * (a Service function which is calling the PasswordUtility)
     *
     * @return string The newly created plaintext password
     */
    public function setNewPassword(): string
    {
        $plaintextPassword = PasswordUtility::generatePassword();
        $this->frontendUser->setPassword(PasswordUtility::saltPassword($plaintextPassword));

        return $plaintextPassword;
    }


    /**
     * Returns the required fields that needs to be filled out by a user in the light of its service affiliation
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getMandatoryFields(): array
    {
        // get mandatory fields from TypoScript
        $settings = $this->getSettings();
        $requiredFields = [];

        // get default mandatory fields
        if ($settings['users']['requiredFormFields']) {
            $requiredFields = explode(
                ',', 
                str_replace(' ', '', $settings['users']['requiredFormFields']))
            ;
        }

        // add specific mandatory fields which are based on service groups
        /** @var ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        
        /** @var \RKW\RkwRegistration\Register\GroupRegister $groupRegister */
        $groupRegister = $objectManager->get(GroupRegister::class);
       
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $objectManager->get(ServiceRepository::class);

        // get mandatory fields by fe_groups the user is registered for
        $requiredFields = array_merge(
            $requiredFields, 
            $groupRegister->getMandatoryFieldsOfGroupList(
                $this->frontendUser->getUsergroup()->toArray()
            )
        );

        // get mandatory fields by fe_groups the user is still waiting to be
        // registered but admin has already granted him access
        $serviceInquiryList = $serviceRepository->findConfirmedByUser($this->frontendUser);
        foreach ($serviceInquiryList as $serviceInquiry) {
            if ($groups = $serviceInquiry->getUsergroup()) {
                $requiredFields = array_merge(
                    $requiredFields, 
                    $groupRegister->getMandatoryFieldsOfGroupList(
                        $groups->toArray()
                    )
                );
            }
        }        

        return $requiredFields;
    }
}
