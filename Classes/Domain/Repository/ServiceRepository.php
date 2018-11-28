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

        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

        // don't add the pid constraint and enable fields
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * function findEnabledByAdminByUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findEnabledByAdminByUser(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {

        $query = $this->createQuery();
        $services = $query
            ->matching(
                $query->logicalAnd(
                    $query->equals('user', $frontendUser),
                    $query->equals('enabledByAdmin', 1)
                )
            )
            ->execute();

        return $services;
        //===
    }


    /**
     * find expired services
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findExpired()
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $userServices = $query
            ->matching(
                $query->lessThan('validUntil', time())
            )
            ->execute();

        return $userServices;
        //===
    }


    /**
     * function generateRandomSha1
     *
     * @return string
     */
    public function generateRandomSha1()
    {

        return sha1(rand());
        //====

    }


    /**
     * function cryptServiceIdSha1
     *
     * @param int $serviceId
     * @return string
     */
    public function cryptServiceIdSha1($serviceId)
    {

        return sha1($serviceId);
        //====

    }


    /**
     * function newOptIn
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup
     * @param integer $daysForOptIn
     * @return \RKW\RkwRegistration\Domain\Model\Service
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function newOptIn(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser, \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup, $daysForOptIn = 0)
    {

        /** @var \RKW\RkwRegistration\Domain\Model\Service $service */
        $service = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\Service');
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
        $service->setValidUntil(strtotime("+" . intval($daysForOptIn) . " day", time()));

        $this->add($service);

        return $service;
        //====
    }


}