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
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use RKW\RkwRegistration\Domain\Repository\ServiceRepository;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Class AbstractRegistration
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractRegister
{

    /**
     * RegistrationRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     * @inject
     */
    protected $registrationRepository;

    
    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    
    /**
     * FrontendUserGroupRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     * @inject
     */
    protected $frontendUserGroupRepository;
    

    /**
     * ServiceRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\ServiceRepository
     * @inject
     */
    protected $serviceRepository;


    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    
    /**
     * ObjectManager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    
    /**
     * Signal-Slot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
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
        $this->getSettings();
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
            return [];
        }
        return $this->settings;
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}