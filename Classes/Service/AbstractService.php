<?php

namespace RKW\RkwRegistration\Service;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

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
     * @var RegistrationRepository
     */
    protected $registrationRepository;

    /**
     * FrontendUserRepository
     *
     * @var FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * FrontendUserGroupRepository
     *
     * @var FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * Persistence Manager
     *
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * ObjectManager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Signal-Slot Dispatcher
     *
     * @var Dispatcher
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
        // system
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
     * @return RegistrationRepository
     */
    protected function getRegistrationRepository()
    {
        if (!$this->registrationRepository) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->registrationRepository = $objectManager->get(RegistrationRepository::class);
        }
        return $this->registrationRepository;
    }

    /**
     * Returns FrontendUserRepository
     *
     * @return FrontendUserRepository
     */
    protected function getFrontendUserRepository()
    {
        if (!$this->frontendUserRepository) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
        }
        return $this->frontendUserRepository;
    }

    /**
     * Returns FrontendUserGroupRepository
     *
     * @return FrontendUserGroupRepository
     */
    protected function getFrontendUserGroupRepository()
    {
        if (!$this->frontendUserGroupRepository) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->frontendUserGroupRepository = $objectManager->get(FrontendUserGroupRepository::class);
        }
        return $this->frontendUserGroupRepository;
    }

    /**
     * Returns PersistenceManager
     *
     * @return PersistenceManager
     */
    protected function getPersistenceManager()
    {
        if (!$this->persistenceManager) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->persistenceManager = $objectManager->get(PersistenceManager::class);
        }
        return $this->persistenceManager;
    }

    /**
     * Returns SignalSlotDispatcher
     *
     * @return Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {
        if (!$this->signalSlotDispatcher) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->signalSlotDispatcher = $objectManager->get(Dispatcher::class);
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

}