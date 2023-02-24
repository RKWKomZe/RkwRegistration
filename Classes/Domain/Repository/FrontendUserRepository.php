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
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * FrontendUserRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepository extends AbstractRepository
{

    /**
     * Finds users which have the given uid even if they are disabled
     * This is relevant for checking during registration or profile editing
     *
     * @param int $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     * implicitly tested
     */
    public function findByIdentifierIncludingDisabled(int $uid): ?FrontendUser
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
            $query->equals('uid', $uid)
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Finds users which have the given uid even if they are deleted
     * This is relevant for checking during registration or profile editing
     *
     * @param int $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     * implicitly tested
     */
    public function findByIdentifierIncludingDeleted (int $uid): ?FrontendUser
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
            $query->equals('uid', $uid)
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Finds users which have the given username even if they are disabled
     * This is relevant for checking during registration or profile editing
     *
     * @param string $username
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     * implicitly tested
     */
    public function findOneByUsernameIncludingDisabled(string $username): ?FrontendUser
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
            $query->equals('username', strtolower($username))
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
    }


    /**
     * Find all frontend users that have been expired x days ago
     *
     * @param int $daysExpired
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @api Used for cleanup via CLI*
     */
    public function findExpired (int $daysExpired = 7): QueryResultInterface {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        return $query->matching(
            $query->logicalOr(
                $query->logicalAnd(
                    $query->greaterThan('endtime', 0),
                    $query->lessThanOrEqual('endtime', (time() - ($daysExpired * 24 * 60 * 60)))
                ),
                $query->logicalAnd(
                    $query->equals('disable', 1),
                    $query->lessThanOrEqual('tstamp', (time() - ($daysExpired * 24 * 60 * 60)))
                )
            )
        )->execute();
    }


    /**
     * Find all deleted frontend users that have been deleted x days ago and have not yet been anonymized/encrypted
     *
     * @param int $daysDeleted
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @api Used for cleanup via CLI
     */
    public function findDeleted (int $daysDeleted = 7): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->logicalAnd(
                $query->equals('deleted', 1),
                $query->lessThan('txRkwregistrationDataProtectionStatus', 1),
                $query->logicalAnd(
                    $query->greaterThan('tstamp', 0),
                    $query->lessThanOrEqual('tstamp', (time() - ($daysDeleted * 24 * 60 * 60)))
                )
            )
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
     * tested implicitly
     */
    public function removeHard(FrontendUser $frontendUser): bool
    {
        // Important: We never want to delete a user with related privacy entries
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $rows = $queryBuilder
            ->select('uid')
            ->from('tx_rkwregistration_domain_model_consent')
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

}

