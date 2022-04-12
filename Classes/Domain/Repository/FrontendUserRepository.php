<?php

namespace RKW\RkwRegistration\Domain\Repository;

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

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
     * initializeObject
     *
     * @return void
     */
    public function initializeObject()
    {
        /** @var $querySettings Typo3QuerySettings */
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);

        $version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        // for backward compatibility with old system with all fe_users in one storage PID
        if ($version < 9000000) {
            // don't add the pid constraint and enable fields
            $querySettings->setRespectStoragePage(false);
        }

        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * Finds deleted users
     *
     * @param int $uid
     * @return FrontendUser
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
     * @return FrontendUser
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
     * @return FrontendUser
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
     * @param string $uid
     * @return FrontendUser
     */
    public function findByUidAlsoInactiveNonGuest($uid)
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
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findExpiredAndDisabledSinceDays ($daysSinceExpiredOrDisabled = 0, $timebase = 0)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timebase = ($timebase ? $timebase : time());
        $timestamp = $timebase - (intval($daysSinceExpiredOrDisabled) * 24 * 60 * 60);

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
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findDeletedSinceDays ($daysSinceDelete = 0, $timebase = 0)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timebase = ($timebase ? $timebase : time());
        $timestamp = $timebase - (intval($daysSinceDelete) * 24 * 60 * 60);

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
     * @return FrontendUser The matching object if found, otherwise NULL
     * @api used by RKW Soap
     */
    public function findByUidSoap($uid)
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
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findDeletedOrDisabled($tolerance = 0, $guestOnly = false)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timestamp = time();
        if ($tolerance) {
            $timestamp = intval($tolerance);
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
     * @param FrontendUser $frontendUser
     * @return bool
     */
    public function removeHard(FrontendUser $frontendUser) :bool
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
     * @param integer $uid
     * @return FrontendUser
     * @deprecated This method is deprecated and will be removed soon.
     */
    public function findByUidNoAnonymous($uid)
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
     * @deprecated Will be removed soon. Simply use magic function $guestUserRepository->findByUsername($token) instead
     * @param string $username
     * @return FrontendUser
     */
    public function findOneByToken($token)
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
        //====
    }

    /**
     * Finds inactive users - this is relevant for registration
     * This way we can activate the user then
     *
     * @deprecated Will be removed soon. Use findByUidInactiveNonGuest instead
     *
     * @param string $uid
     * @return FrontendUser
     */
    public function findByUidInactiveNonAnonymous($uid)
    {
        GeneralUtility::logDeprecatedFunction();
        return $this->findByUidAlsoInactiveNonGuest($uid);
    }


    /**
     * Find all expired users with optional timestamp tolerance
     *
     * @param int $tolerance Tolerance timestamp
     * @param bool $anonymousOnly Only return anonymous users
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @deprecated since 26.06.2018
     */
    public function findExpired($tolerance = 0, $anonymousOnly = false)
    {
        GeneralUtility::deprecationLog(__CLASS__ . ': Do not use this method any more.');

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
    public function findExpiredOrDeletedByTstamp($tolerance = 0, $anonymousOnly = false)
    {
        GeneralUtility::deprecationLog(__CLASS__ . ': Do not use this method any more.');

        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timestamp = time();
        if ($tolerance) {
            $timestamp = intval($tolerance);
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
        //===
    }


    /**
     * Finds inactive users - this is relevant for registration
     * This way no one can register twice (only when deleted)
     *
     * @deprecated NOT USED INSIDE COMPLETE /typo3conf/ext/
     *
     * @param string $username
     * @return FrontendUser
     */
    public function findOneByUsernameAlsoInactive($username)
    {
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
     * @deprecated NOT USED INSIDE COMPLETE /typo3conf/ext/
     *
     * @param FrontendUser $frontendUser
     * @return boolean
     */
    public function isUser(FrontendUser $frontendUser)
    {
        if ($this->findUser($frontendUser)) {
            return true;
        }

        return false;
    }


    /**
     * Loads registered user from database
     *
     * @deprecated NOT USED INSIDE COMPLETE /typo3conf/ext/
     *
     * @param FrontendUser $frontendUser
     * @return FrontendUser | NULL
     */
    public function findUser(FrontendUser $frontendUser)
    {
        if ($frontendUser = $this->findOneByUsername(strtolower($frontendUser->getUsername()))) {
            return $frontendUser;
        }

        return null;
    }


}

