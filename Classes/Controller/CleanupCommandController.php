<?php

namespace RKW\RkwRegistration\Controller;

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

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CleanupCommandController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CleanupCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ServiceRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $serviceRepository;


    /**
     * RegistrationRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $registrationRepository;


    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserRepository;


    /**
     * dataProtectionRepository
     *
     * @var \RKW\RkwRegistration\DataProtection\DataProtectionHandler
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $dataProtectionHandler;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    

    /**
     * Removes old service and registration requests
     */
    public function cleanupOptInAndServiceCommand(): void
    {

        try {

            $expiredRegistrations = $this->registrationRepository->findExpired();
            $cnt = 0;
            foreach ($expiredRegistrations as $registration) {
                $this->registrationRepository->remove($registration);
                $cnt++;
            }

            $expiredServices = $this->serviceRepository->findExpired();
            foreach ($expiredServices as $service) {
                $this->serviceRepository->remove($service);
                $cnt++;
            }

            $this->getLogger()->log(
                LogLevel::INFO, 
                sprintf(
                    'Successfully removed %s expired registration- and service-requests completely from the database.', 
                    $cnt
                )
            );

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR, 
                sprintf(
                    'An error occurred. Message: %s', 
                    str_replace(["\n", "\r"], '', $e->getMessage())
                )
            );
        }
    }


    /**
     * @todo Export and cleanup for deleted privacy entries
     * !! DANGER !! Cleanup executes a real MySQL-Delete- Query!!!
     * @param integer $daysFromNow Users that have been marked as deleted x days from now are deleted
     * @return void
     */
    public function cleanupDeletedAndExportPrivacyData(int $daysFromNow = 365): void
    {
        // WRITE THIS ;-)
    }


    /**
     * Cleanup for expired and disabled users
     *
     * Deletes expired and disabled users after x days (only sets deleted = 1)
     * default: 30 days
     *
     * @param integer $deleteExpiredAndDisabledAfterDays Delete users that are expired or disabled since x days
     * @return void
     */
    public function deleteExpiredAndDisabledUsersCommand(int $deleteExpiredAndDisabledAfterDays = 30): void
    {

        try {
            $this->dataProtectionHandler->deleteAllExpiredAndDisabled($deleteExpiredAndDisabledAfterDays);
            $this->getLogger()->log(
                LogLevel::INFO, 
                sprintf('Successfully deleted expired or disabled fe-users.')
            );

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf(
                    'An error occurred. Message: %s',
                    str_replace(["\n", "\r"], '', $e->getMessage())
                )
            );
        }
    }



    /**
     * Data-protection cleanup for deleted users
     *
     * Anonymizes and encrypts all data of users that are deleted since x days
     * Also includes user-related data if configured
     * default: 30 days
     *
     * @param string $encryptionKey
     * @param integer $anonymizeDeletedAfterDays Anonymize and encrypt data of users that have been deleted x days before
     * @return void
     */
    public function anonymizeAndEncryptDeletedUsersCommand(string $encryptionKey, int $anonymizeDeletedAfterDays = 30): void
    {

        try {

            $this->dataProtectionHandler->setEncryptionKey($encryptionKey);
            $this->dataProtectionHandler->anonymizeAndEncryptAll($anonymizeDeletedAfterDays);
            $this->getLogger()->log(
                LogLevel::INFO, 
                sprintf('Successfully anonymized data of fe-users.')
            );

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf(
                    'An error occurred. Message: %s',
                    str_replace(["\n", "\r"], '', $e->getMessage())
                )
            );
        }
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {

        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
