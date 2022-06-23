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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * FrontendUserRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * Finds deleted users
     *
     * @param int $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     */
    public function findOneDeletedByUid(int $uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);

        $user = $query->matching(
                $query->logicalAnd(
                    $query->equals('uid', $uid),
                    $query->equals('deleted', 1)
                )
            )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Finds deleted users
     *
     * @param int $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     */
    public function findOneDisabledByUid(int $uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
            $query->logicalAnd(
                $query->equals('uid', $uid),
                $query->equals('disable', 1)
            )
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Finds users which have the given username OR email-address
     * This is relevant for checking during registration or profile editing
     *
     * @param string $input
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     */
    public function findOneByEmailOrUsernameAlsoInactive($input)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
                $query->logicalOr(
                    $query->equals('email', $input),
                    $query->equals('username', $input)
                )
            )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }



    /**
     * Finds inactive users - this is relevant for registration
     * This way we can activate the user then
     *
     * @param int $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     */
    public function findByUidAlsoInactiveNonGuest(int $uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        // the field "txRkwregistrationIsAnonymous" is deprecated. Solely not removed for backward compatibility reasons
        $user = $query->matching(
                $query->logicalAnd(
                    $query->equals('uid', $uid),
                    $query->logicalNot(
                        $query->equals('txExtbaseType', '\RKW\RkwRegistration\Domain\Model\GuestUser')
                    ),
                    $query->logicalOr(
                        $query->equals('txRkwregistrationIsAnonymous', null),
                        $query->equals('txRkwregistrationIsAnonymous', 0)
                    )
                )
            )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Find all expired frontend users that have been expired x days ago
     *
     * @param int $daysSinceExpiredOrDisabled
     * @param int $timebase set the timebase for the calculation. if not set, time() is used
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findExpiredAndDisabledSinceDays (
        int $daysSinceExpiredOrDisabled = 0, 
        int $timebase = 0
    ): QueryResultInterface {
        
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timebase = ($timebase ?: time());
        $timestamp = $timebase - ($daysSinceExpiredOrDisabled * 24 * 60 * 60);

        $query->matching(
            $query->logicalOr(
                $query->logicalAnd(
                    $query->greaterThan('endtime', 0),
                    $query->lessThanOrEqual('endtime', $timestamp)
                ),
                $query->logicalAnd(
                    $query->equals('disable', 1),
                    $query->lessThanOrEqual('tstamp', $timestamp)
                )
            )
        );

        return $query->execute();
    }
    

    /**
     * Find all deleted frontend users that have been deleted x days ago and have not yet been anonymized/encrypted
     *
     * @param int $daysSinceDelete
     * @param int $timebase sets the timebase for the calculation. if not set, time() is used
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findDeletedSinceDays (int $daysSinceDelete = 0, int $timebase = 0): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timebase = ($timebase ?: time());
        $timestamp = $timebase - ($daysSinceDelete * 24 * 60 * 60);

        $query->matching(
            $query->logicalAnd(
                $query->equals('deleted', 1),
                $query->lessThan('txRkwregistrationDataProtectionStatus', 1),
                $query->logicalAnd(
                    $query->greaterThan('tstamp', 0),
                    $query->lessThanOrEqual('tstamp', $timestamp)
                )
            )
        );

        return $query->execute();
    }
    

    /**
     * Finds an object matching the given identifier.
     *
     * @param int $uid The identifier of the object to find
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     * @api used by RKW Soap
     */
    public function findByUidSoap(int $uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->equals('uid', $uid)
        );

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }
    
    
    /**
     * Find all deleted users with optional timestamp tolerance
     *
     * @param int $tolerance Tolerance timestamp
     * @param bool $guestOnly Only return guest users
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findDeletedOrDisabled(int $tolerance = 0, bool $guestOnly = false): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timestamp = time();
        if ($tolerance) {
            $timestamp = $tolerance;
        }

        $constraints = [
            $query->logicalOr(
                $query->equals('deleted', 1),
                $query->equals('disable', 1)
            ),
            $query->logicalAnd(
                $query->greaterThan('tstamp', 0),
                $query->lessThan('tstamp', $timestamp)
            ),
        ];

        if ($guestOnly) {
            $constraints[] = $query->equals('tx_extbase_type', '\RKW\RkwRegistration\Domain\Model\GuestUser');
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        return $query->execute();
    }



    /**
     * Delete user from DB (really!)
     *
     * FrontendUser only deleted if:
     * - no privacy entry exists
     * - (AND) marked als "deleted"
     * - (AND) older than 10 years (OR) with no login yet (rejected registrations)
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return bool
     */
    public function removeHard(FrontendUser $frontendUser): bool
    {
        // Important: We never want to delete a user with related privacy entries
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $rows = $queryBuilder
            ->select('uid')
            ->from('tx_rkwregistration_domain_model_privacy')
            ->where(
                $queryBuilder->expr()->eq(
                    'frontend_user', $queryBuilder->createNamedParameter($frontendUser->getUid(), \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();

        // delete only if: (1) there are no privacy entries AND (2) if the user is older than 10 years AND (3) marked as "deleted"
        // -> never delete a frontendUser with privacy entries. They have to removed first!
        // unless a user who has (4) no privacy entries and never was logged in (rejected registration)
        if (
            empty($rows)
            && $frontendUser->getDeleted()
            && (
                $frontendUser->getTstamp() < strtotime("-10 years", time())
                || !$frontendUser->getLastlogin()
            )
        ) {

            GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('fe_users')
                ->delete(
                    'fe_users',
                    [
                        'uid' => intval($frontendUser->getUid()),
                        'deleted' => 1
                    ]
                );

            return true;
        }

        return false;
    }


    /**
     * Finds non-anonymous users
     *
     * @param int $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     * @deprecated This method is deprecated and will be removed soon.
     */
    public function findByUidNoAnonymous(int $uid)
    {
        GeneralUtility::logDeprecatedFunction();

        $query = $this->createQuery();
        $user = $query->matching(
            $query->logicalAnd(
                $query->equals('uid', $uid),
                $query->equals('txRkwregistrationIsAnonymous', 0)
            )
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Finds anonymous users
     *
     * @param string $username
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     * @deprecated Will be removed soon. Simply use magic function $guestUserRepository->findByUsername($token) instead
     */
    public function findOneByToken(string $token)
    {
        GeneralUtility::logDeprecatedFunction();

        $query = $this->createQuery();

        $user = $query->matching(
            $query->logicalAnd(
                $query->equals('username', $token),
                $query->equals('txRkwregistrationIsAnonymous', 1)
            )
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Finds inactive users - this is relevant for registration
     * This way we can activate the user then
     *
     * @param int $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     * @deprecated Will be removed soon. Use findByUidInactiveNonGuest instead
     */
    public function findByUidInactiveNonAnonymous(int $uid)
    {
        GeneralUtility::logDeprecatedFunction();
        return $this->findByUidAlsoInactiveNonGuest($uid);
    }


    /**
     * Find all expired users with optional timestamp tolerance
     *
     * @param int $tolerance Tolerance timestamp
     * @param bool $anonymousOnly Only return anonymous users
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @deprecated since 26.06.2018
     */
    public function findExpired(int $tolerance = 0, bool $anonymousOnly = false): QueryResultInterface
    {
        GeneralUtility::logDeprecatedFunction();

        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timestamp = time();
        if ($tolerance) {
            $timestamp = intval($tolerance);
        }

        $constraints = [
            $query->greaterThan('endtime', 0),
            $query->lessThan('endtime', $timestamp),
        ];

        if ($anonymousOnly) {
            $constraints[] = $query->equals('txRkwregistrationIsAnonymous', 1);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        return $query->execute();
    }


    /**
     * Find all deleted or expired users with optional timestamp tolerance
     *
     * @param int $tolerance Tolerance timestamp
     * @param bool $anonymousOnly if true only anonymous users will be checked
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @deprecated since 19.12.2019
     */
    public function findExpiredOrDeletedByTstamp(int $tolerance = 0, bool $anonymousOnly = false): QueryResultInterface
    {
        GeneralUtility::logDeprecatedFunction();

        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timestamp = time();
        if ($tolerance) {
            $timestamp = $tolerance;
        }

        $constraints = [
            $query->logicalOr(
                $query->logicalAnd(
                    $query->greaterThan('endtime', 0),
                    $query->lessThan('endtime', $timestamp)
                ),
                $query->logicalAnd(
                    $query->equals('deleted', 1),
                    $query->logicalAnd(
                        $query->greaterThan('tstamp', 0),
                        $query->lessThan('tstamp', $timestamp)
                    )
                )
            ),
        ];

        if ($anonymousOnly) {
            $constraints[] = $query->equals('txRkwregistrationIsAnonymous', 1);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        return $query->execute();
    }


    /**
     * Finds inactive users - this is relevant for registration
     * This way no one can register twice (only when deleted)
     *
     * @param string $username
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|object|null
     * @deprecated NOT USED INSIDE COMPLETE /typo3conf/ext/
     */
    public function findOneByUsernameAlsoInactive($username)
    {
        GeneralUtility::logDeprecatedFunction();

        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
            $query->equals('username', $username)
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Checks if user is already registered
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     * @deprecated NOT USED INSIDE COMPLETE /typo3conf/ext/
     */
    public function isUser(FrontendUser $frontendUser)
    {
        GeneralUtility::logDeprecatedFunction();

        if ($this->findUser($frontendUser)) {
            return true;
        }

        return false;
    }


    /**
     * Loads registered user from database
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     * @deprecated NOT USED INSIDE COMPLETE /typo3conf/ext/
     */
    public function findUser(FrontendUser $frontendUser)
    {
        if ($frontendUser = $this->findOneByUsername(strtolower($frontendUser->getUsername()))) {
            return $frontendUser;
        }

        return null;
    }


}

