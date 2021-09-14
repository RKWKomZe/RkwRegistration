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

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * TitleRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Fäßler Web UG
 * @date October 2018
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TitleRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
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

        // don't add the pid constraint
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * function findAllOfType
     *
     * @param boolean $showTitleBefore
     * @param boolean $showTitleAfter
     * @param boolean $returnArray
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findAllOfType($showTitleBefore = true, $showTitleAfter = false, $returnArray = false)
    {
        $query = $this->createQuery();
        $result = $query
            ->matching(
                $query->logicalAnd(
                    $query->logicalOr(
                        $query->equals('isTitleAfter', $showTitleBefore ? false : true),
                        $query->equals('isTitleAfter', $showTitleAfter)
                    ),
                    $query->equals('isChecked', true)
                )
            )->execute($returnArray);

        return $result;
    }
}