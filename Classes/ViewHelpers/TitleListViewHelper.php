<?php

namespace RKW\RkwRegistration\ViewHelpers;

use RKW\RkwRegistration\Domain\Repository\TitleRepository;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
 * Class TitleListViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Fäßler Web UG
 * @date October 2018
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TitleListViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns a list of user title options
     *
     * This example is equal to a findAll: <rkwRegistration:titleList showTitleAfter='true' />
     * Shorthand for showing only title after: <rkwRegistration:titleList showTitleBefore='false' />
     *
     * @param boolean $showTitleBefore
     * @param boolean $showTitleAfter
     * @param boolean $returnArray
     * @param boolean $returnJson
     * @param string $mapProperty
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function render($showTitleBefore = true, $showTitleAfter = false, $returnArray = false, $returnJson = false, $mapProperty = '')
    {
        // a) This avoids possible empty results by calling <rkwRegistration:titleList showTitleBefore='false' showTitleAfter='false' />
        // b) Makes a shorter invoke possible for showing up only "isTitleAfter"-Elements (see PHPdocs example above)
        if (!$showTitleBefore) {
            $showTitleAfter = true;
        }

        /** @var ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        /** @var TitleRepository $titleRepository */
        $titleRepository = $objectManager->get(TitleRepository::class);

        $titles = $titleRepository->findAllOfType($showTitleBefore, $showTitleAfter, $returnArray);

        if ($mapProperty) {
            $mappedTitles = array_map(function($item) use ($mapProperty) {
                return $item[$mapProperty];
            }, $titles);

            $titles = $mappedTitles;
        }

        return ($returnJson) ? json_encode($titles) : $titles;
    }
}
