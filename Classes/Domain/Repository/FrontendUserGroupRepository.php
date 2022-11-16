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

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * FrontendUserGroupRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserGroupRepository extends AbstractRepository
{

    /**
     * Finds all frontendUserGroups one can acquire a membership for
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\RKW\RkwRegistration\Domain\Model\FrontendUserGroup|null>
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findMembershipable(): QueryResultInterface
    {
        // return all services which do not pass the closingDate or openingDate
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $query->greaterThanOrEqual('txRkwregistrationMembershipClosingDate', time()),
                    $query->equals('txRkwregistrationMembershipClosingDate', 0)
                ),
                $query->logicalOr(
                    $query->lessThanOrEqual('txRkwregistrationMembershipOpeningDate', time()),
                    $query->equals('txRkwregistrationMembershipOpeningDate', 0)
                ),
                $query->equals('txRkwregistrationIsMembership', 1)
            )
        );

        return $query->execute();
    }


}
