<?php

namespace RKW\RkwRegistration\ViewHelpers\Link;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * Class CustomLinkViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @date October 2020
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UriBuilderLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Creates a link with custom baseUrl (not possible with FLUID link VHs)
     *
     * @param integer $pageUid
     * @param array $additionalParams A one dimensional associative array with key & value pair to add as GET param
     *
     * @return string
     */
    public function render($pageUid, $additionalParams = [])
    {
        $finalParams = array_merge(['id' => $pageUid], $additionalParams);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        // else: Fallback with standard domain behavior
        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
        $redirectUrl = $uriBuilder->reset()
            ->setTargetPageUid($pageUid)
            ->setCreateAbsoluteUri(true)
            ->setLinkAccessRestrictedPages(true)
            ->setUseCacheHash(false)
            ->setArguments($additionalParams)
            ->build();

        return $redirectUrl;
        //===
    }
}
