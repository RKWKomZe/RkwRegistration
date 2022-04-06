<?php

namespace RKW\RkwRegistration\Utility;

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;

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
 *
 * handles everything to a FrontendUser
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
     * @param array|object $userData
     * @return FrontendUser
     */
    public static function convertArrayToObject($userData)
    {
        $frontendUser = $userData;
        if (is_array($userData)) {
            /** @var FrontendUser $frontendUser */
            $frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FrontendUser::class);
            foreach ($userData as $key => $value) {
                $setter = 'set' . ucfirst(GeneralUtility::camelize($key));
                if (method_exists($frontendUser, $setter)) {
                    $frontendUser->$setter($value);
                }
            }
        }

        return $frontendUser;
    }


    /**
     * remainingLoginAttempts
     *
     * @param FrontendUser $frontendUser
     * @return int
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function remainingLoginAttempts(FrontendUser $frontendUser): int
    {
        $settings = self::getSettings();
        return intval($settings['users']['maxLoginErrors']) - $frontendUser->getTxRkwregistrationLoginErrorCount();
    }


    /**
     * Returns TYPO3 settings
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected static function getSettings()
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration');
    }

}
