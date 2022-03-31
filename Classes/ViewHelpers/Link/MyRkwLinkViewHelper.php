<?php

namespace RKW\RkwRegistration\ViewHelpers\Link;

use RKW\RkwRegistration\Domain\Model\SysDomain;
use RKW\RkwRegistration\Domain\Repository\SysDomainRepository;
use RKW\RkwRegistration\Service\DomainLinkService;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

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
 * Class MyRkwLinkViewHelper
 *
 * @deprecated
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @date 2021
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MyRkwLinkViewHelper extends AbstractViewHelper
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
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);
        $redirectUrl = $uriBuilder->reset()
            ->setTargetPageUid($pageUid)
            ->setCreateAbsoluteUri(true)
            ->setLinkAccessRestrictedPages(true)
            ->setUseCacheHash(false)
            ->build();

        return $redirectUrl;
    }
}
