<?php

namespace RKW\RkwRegistration\Service;

use phpDocumentor\Reflection\Types\This;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Exception;
use \RKW\RkwRegistration\Service\AuthService as Authentication;
use \RKW\RkwBasics\Utility\GeneralUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use \RKW\RkwRegistration\Utility\RemoteUtility;
use RKW\RkwRegistration\Utility\TitleUtility;
use TYPO3\CMS\Core\Log\LogLevel;
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
 * Class AbstractService
 *
 * @toDo: Services SHOULD NOT be singletons
 * @toDo: Services MUST be used as objects, they are never static
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractService
{

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
     * FrontendUserRegisterService
     *
     * @var \RKW\RkwRegistration\Service\FrontendUserRegisterService
     */
    protected $frontendUserRegisterService;

    /**
     * FrontendUserGroupService
     *
     * @var \RKW\RkwRegistration\Service\FrontendUserGroupService
     */
    protected $frontendUserGroupService;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    /**
     * ObjectManager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

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
     * initializeObject
     *
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function initializeObject()
    {
        // system (it's important to get the object manager first!)
        //$this->getObjectManager();
        $this->getPersistenceManager();
        $this->getSignalSlotDispatcher();

        // repositories
        $this->getFrontendUserRepository();
        $this->getFrontendUserGroupRepository();
        $this->getRegistrationRepository();

        // other
        $this->getSettings();
    }

    /**
     * Returns RegistrationRepository
     *
     * @return \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    protected function getRegistrationRepository()
    {
        if (!$this->registrationRepository) {
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->registrationRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\RegistrationRepository');
        }
        return $this->registrationRepository;
    }

    /**
     * Returns FrontendUserRepository
     *
     * @return \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    protected function getFrontendUserRepository()
    {
        if (!$this->frontendUserRepository) {
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');
        }
        return $this->frontendUserRepository;
    }

    /**
     * Returns FrontendUserGroupRepository
     *
     * @return \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    protected function getFrontendUserGroupRepository()
    {
        if (!$this->frontendUserGroupRepository) {
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->frontendUserGroupRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserGroupRepository');
        }
        return $this->frontendUserGroupRepository;
    }

    /**
     * Returns ObjectManager
     *
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        }
        return $this->objectManager;
    }

    /**
     * Returns PersistenceManager
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected function getPersistenceManager()
    {
        if (!$this->persistenceManager) {
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        }
        return $this->persistenceManager;
    }

    /**
     * Returns SignalSlotDispatcher
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {
        if (!$this->signalSlotDispatcher) {
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->signalSlotDispatcher = $objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        }
        return $this->signalSlotDispatcher;
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
     * Returns FrontendUserRegisterService
     * Hint: Do not initialize services inside the initializeObject() function to prevent a continuous loop
     *
     * @return \RKW\RkwRegistration\Service\FrontendUserRegisterService
     */
    protected function getFrontendUserRegisterService()
    {
        if (!$this->frontendUserRegisterService) {
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->frontendUserRegisterService = $objectManager->get('RKW\\RkwRegistration\\Service\\FrontendUserRegisterService');
        }
        return $this->frontendUserRegisterService;
    }

    /**
     * Returns FrontendUserGroupService
     * Hint: Do not initialize services inside the initializeObject() function to prevent a continuous loop
     *
     * @return \RKW\RkwRegistration\Service\FrontendUserGroupService
     */
    protected function getFrontendUserGroupService()
    {
        if (!$this->frontendUserGroupService) {
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->frontendUserGroupService = $objectManager->get('RKW\\RkwRegistration\\Service\\FrontendUserGroupService');
        }
        return $this->frontendUserGroupService;
    }

}