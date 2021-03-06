<?php

namespace RKW\RkwRegistration\Domain\Repository;

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
 * FrontendUserGroupRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserGroupRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
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
        $querySettings->setIgnoreEnableFields(true);

        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * function findUserServices
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUserGroup
     */
    public function findServices()
    {

        //give all services which do not pass the closingDate or openingDate
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $query->greaterThanOrEqual('txRkwregistrationServiceClosingDate', time()),
                    $query->equals('txRkwregistrationServiceClosingDate', 0)
                ),

                $query->equals('txRkwregistrationIsService', 1)
            )
        );

        return $query->execute();
        //====
    }


    /**
     * Find all users that have been updated recently
     *
     * @api Used by RKW Soap
     * @param integer $timestamp
     * @param integer $serviceOnly
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByTimestamp($timestamp, $serviceOnly)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);

        $query->matching(
            $query->logicalAnd(
                $query->greaterThanOrEqual('tstamp', intval($timestamp)),
                $query->equals('txRkwregistrationIsService', intval($serviceOnly))
            )
        );

        $query->setOrderings(array('tstamp' => QueryInterface::ORDER_ASCENDING));

        return $query->execute();
        //===
    }


}