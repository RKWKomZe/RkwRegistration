<?php

namespace RKW\RkwRegistration\Domain\Repository;

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

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\Service;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * ServiceRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ServiceRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * initializeObject
     *
     * @return void
     */
    public function initializeObject()
    {
        /** @var $querySettings Typo3QuerySettings */
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);

        // don't add the pid constraint and enable fields
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * function findConfirmedByUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findConfirmedByUser(FrontendUser $frontendUser): QueryResultInterface
    {
        $query = $this->createQuery();
        return $query
            ->matching(
                $query->logicalAnd(
                    $query->equals('user', $frontendUser),
                    $query->equals('enabledByAdmin', 1)
                )
            )
            ->execute();

    }


    /**
     * find expired services
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findExpired(): QueryResultInterface
    {
        $query = $this->createQuery();
        return $query
            ->matching(
                $query->lessThan('validUntil', time())
            )
            ->execute();
    }



    /**
     * function newOptIn
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup
     * @param integer $daysForOptIn
     * @return \RKW\RkwRegistration\Domain\Model\Service
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function newOptIn(
        FrontendUser $frontendUser, 
        FrontendUserGroup $frontendUserGroup, 
        int $daysForOptIn = 0
    ) {
        
        /** @var \RKW\RkwRegistration\Domain\Model\Service $service */
        $service = GeneralUtility::makeInstance(Service::class);
        $keyForSha1 = $frontendUser->getUid() . $frontendUserGroup->getUid() . time();

        $service->setUser($frontendUser);
        $service->addUsergroup($frontendUserGroup);
        $service->setServiceSha1($this->cryptServiceIdSha1($keyForSha1));

        $service->setTokenYes($this->generateRandomSha1());
        $service->setTokenNo($this->generateRandomSha1());

        // token valid for fourteen days
        if (!$daysForOptIn) {
            $daysForOptIn = 14;
        }
        
        $service->setValidUntil(strtotime("+" . $daysForOptIn . " day", time()));
        $this->add($service);

        return $service;
    }


    /**
     * function generateRandomSha1
     *
     * @return string
     */
    public function generateRandomSha1(): string
    {
        return sha1(rand());
    }


    /**
     * function cryptServiceIdSha1
     *
     * @param int $serviceId
     * @return string
     */
    public function cryptServiceIdSha1(int $serviceId): string
    {
        return sha1($serviceId);
    }

}
