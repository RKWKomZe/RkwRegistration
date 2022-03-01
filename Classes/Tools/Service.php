<?php

namespace RKW\RkwRegistration\Tools;


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
 * Class Service
 *
 * @deprecated This class is deprecated and will be removed soon. Use  RKW\RkwRegistration\Register\GroupRegister instead.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Service extends \RKW\RkwRegistration\Register\GroupRegister
{
    /**
     * Service constructor.
     */
    public function __construct()
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
    }


}