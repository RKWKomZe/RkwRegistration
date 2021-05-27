<?php

namespace RKW\RkwRegistration\Service;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\GuestUser;
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
 * FrontendUserGroupService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserGroupService extends AbstractService
{
    /**
     * Setting
     *
     * @var array
     */
    protected $settings;

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
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;



    /**
     * setUsersGroupsOnRegister
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
