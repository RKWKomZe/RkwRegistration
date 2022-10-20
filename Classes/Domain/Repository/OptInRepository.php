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
use RKW\RkwRegistration\Domain\Model\OptIn;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * OptInRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OptInRepository extends AbstractRepository
{

    /**
     * Finds optIns which have the given uid even if they are deleted
     *
     * @param int $uid
     * @return \RKW\RkwRegistration\Domain\Model\OptIn|null
     * implicitly tested
     */
    public function findByIdentifierIncludingDeleted(int $uid): ?OptIn
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $optIn = $query->matching(
            $query->equals('uid', $uid)
        )->setLimit(1)
            ->execute();

        return $optIn->getFirst();
    }


    /**
     * Finds optIns by tokenUser even if they are deleted
     *
     * @param string $tokenUser
     * @return \RKW\RkwRegistration\Domain\Model\OptIn|null
     * implicitly tested
     */
    public function findOneByTokenUserIncludingDeleted(string $tokenUser): ?OptIn
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $optIn = $query->matching(
            $query->equals('tokenUser', $tokenUser)
        )->setLimit(1)
            ->execute();

        return $optIn->getFirst();
    }


    /**
     * find expired opt-ins
     *
     * @param int $daysExpired
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @api Used for cleanup via CLI
     */
    public function findExpired(int $daysExpired = 0): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query->matching(
            $query->logicalAnd(
                $query->greaterThan('endtime', 0),
                $query->lessThanOrEqual('endtime', (time() - ($daysExpired * 24 * 60 * 60)))
            )

        )->execute();
    }


    /**
     * Find all pending group-memberships by frontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\RKW\RkwRegistration\Domain\Model\OptIn|null>
     */
    public function findPendingGroupMembershipsByFrontendUser(
        FrontendUser $frontendUser
    ): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query->matching(
            $query->logicalAnd(
                $query->equals('frontendUserUid', $frontendUser->getUid()),
                $query->equals('foreignTable', 'fe_groups')
            )
        )->execute();
    }

}
