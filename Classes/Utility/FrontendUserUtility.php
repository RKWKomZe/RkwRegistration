<?php

namespace RKW\RkwRegistration\Utility;

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
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
 * Class FrontendUserUtility
 * Handles everything to a FeUser session (e.g. login and logout).
 * Hint: For authentication take a look to \RKW\RkwRegistration\Service\FrontendUserAuthService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserUtility
{
    /**
     * converts an feUser array to an object
     * Hint: By default a new created FrontendUser is DISABLED = 1 !
     *
     * validUsername
     * array $userData
     * @return FrontendUser
     */
    public static function convertArrayToObject($userData)
    {
        $frontendUser = $userData;
        if (is_array($userData)) {
            /** @var FrontendUser $frontendUser */
            $frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\FrontendUser');
            foreach ($userData as $key => $value) {
                $setter = 'set' . ucfirst(GeneralUtility::camelize($key));
                if (method_exists($frontendUser, $setter)) {
                    $frontendUser->$setter($value);
                }
            }
        }
        return $frontendUser;
    }

}
