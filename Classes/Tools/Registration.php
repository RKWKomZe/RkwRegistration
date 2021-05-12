<?php

namespace RKW\RkwRegistration\Tools;

use \RKW\RkwRegistration\Service\AuthService as Authentication;
use \RKW\RkwBasics\Utility\GeneralUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;

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
 * Class Registration
 *
 * @deprecated This class is deprecated and will be removed soon. Use  RKW\RkwRegistration\Service\RegistrationService instead.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Registration extends \RKW\RkwRegistration\Service\RegistrationService
{

    /**
     * FrontendLocalization constructor.
     */
    public function __construct()
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
    }

}