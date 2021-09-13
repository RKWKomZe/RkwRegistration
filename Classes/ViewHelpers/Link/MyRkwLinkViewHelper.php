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
        if (
            $additionalParams
            && is_array($additionalParams)
        ) {
            $finalParams = array_merge(['id' => $pageUid], $additionalParams);
        } else {
            $finalParams = ['id' => $pageUid];
        }

        $currentBaseUrl = preg_replace('/^http(s)?:\/\/(www\.)?([^\/]+)\/?$/i', '$3', $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL']);

        /** @var ObjectManager objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        /** @var SysDomainRepository $sysDomainRepository */
        $sysDomainRepository = $objectManager->get(SysDomainRepository::class);

        // get object of current sysDomain
        $currentSysDomain = $sysDomainRepository->findByDomainName($currentBaseUrl)->getFirst();
        if ($currentSysDomain instanceof SysDomain) {
            $sysDomain = $sysDomainRepository->findByTxRkwregistrationRelatedSysDomain($currentSysDomain)->getFirst();

            // use $sysDomain entry if given domain is available
            if ($sysDomain instanceof SysDomain) {
                return $sysDomain->getDomainName() . '/index.php?' . http_build_query($finalParams);
            }
        }

        // else: Fallback with standard domain behavior
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
