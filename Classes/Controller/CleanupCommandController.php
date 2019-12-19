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
     * @inject
     */
    protected $serviceRepository;


    /**
     * RegistrationRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     * @inject
     */
    protected $registrationRepository;


    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;


    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwRegistration\Utilities\DataProtectionUtility
     * @inject
     */
    protected $dataProtectionUtility;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Removes old service and registration requests
     */
    public function cleanupOptInAndServiceCommand()
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

            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully removed %s expired registration- and service-requests completely from the database.', $cnt));

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('An error occurred while trying to remove expired registration- and service-requests completely from the database. Message: %s', str_replace(array("\n", "\r"), '', $e->getMessage())));
        }
    }


    /**
     * @toDo: Export and cleanup for deleted privacy entries
     * !! DANGER !! Cleanup executes a real MySQL-Delete- Query!!!
     * @param integer $daysFromNow Users that have been marked as deleted x days from now are deleted
     * @return void
     */
    public function cleanupDeletedAndExportPrivacyData($daysFromNow = 365)
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
    public function deleteExpiredAndDisabledUsersCommand($deleteExpiredAndDisabledAfterDays = 30)
    {

        try {
            $this->dataProtectionUtility->deleteAllExpiredAndDisabled($deleteExpiredAndDisabledAfterDays);
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully deleted expired or disabled fe-users.'));

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('An error occurred: %s', $e->getMessage()));
        }
    }



    /**
     * Data-protection cleanup for deleted users
     *
     * Anonymizes and encrypts all data of users that are deleted since x days
     * Also includes user-related data if configured
     * default: 30 days
     *
     * @param integer $anonymizeDeletedAfterDays Anonymize and encrypt data of users that have been deleted x days before
     * @return void
     */
    public function anonymizeAndEncryptDeletedUsersCommand($anonymizeDeletedAfterDays = 30)
    {

        try {

            $this->dataProtectionUtility->anonymizeAndEncryptAll($anonymizeDeletedAfterDays);
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully anonymized data of fe-users.'));

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('An error occurred: %s', $e->getMessage()));
        }
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
