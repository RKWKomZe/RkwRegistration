<?php
namespace RKW\RkwRegistration\Utility;

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

use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ClientUtility
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ClientUtility extends \Madj2k\CoreExtended\Utility\ClientUtility
{


    /**
     * Checks if the given referrer is part of the current domain
     *
     * @param string|null $referrer
     * @return bool
     */
    public static function isReferrerValid(?string $referrer): bool
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);

        $url = $uriBuilder
            ->reset()
            ->setTargetPageUid(1)
            ->setCreateAbsoluteUri(true)
            ->build();

        $parsedUrl = parse_url($url);
        $parsedReferer = parse_url($referrer);

        return ($parsedReferer['host'] == $parsedUrl['host']);
    }
}
