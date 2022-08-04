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
use RKW\RkwRegistration\Domain\Model\Registration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * RegistrationRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RegistrationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
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
        $querySettings->setIgnoreEnableFields(true);

        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * find expired registrations
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
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
     * @toDo: It's possible so set 0 or negative daysForOptIn $daysForOptIn. Do we need a fallback value?
     * -> newOptInWithNoTimeForOptInReturnsRegistration
     * -> newOptInWithMinusTimeForOptInReturnsRegistration
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param mixed $additionalData
     * @param string $category
     * @param integer $daysForOptIn
     * @return \RKW\RkwRegistration\Domain\Model\Registration
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function newOptIn(
        FrontendUser $frontendUser,
        int $daysForOptIn,
        $additionalData = null, 
        string $category = ''
        )
    {
        /** @var \RKW\RkwRegistration\Domain\Model\Registration $registration */
        $registration = GeneralUtility::makeInstance(Registration::class);

        $registration->setCategory($category);
        $registration->setData($additionalData);
        $registration->setUser($frontendUser);
        $registration->setUserSha1($this->generateRandomSha1());
        $registration->setTokenYes($this->generateRandomSha1());
        $registration->setTokenNo($this->generateRandomSha1());

        $registration->setValidUntil(strtotime("+" . $daysForOptIn . " day", time()));
        $this->add($registration);

        return $registration;
    }
    
    
    /**
     * generateRandomSha1
     *
     * @return string
     */
    protected function generateRandomSha1(): string
    {
        return sha1(rand());
    }

}
