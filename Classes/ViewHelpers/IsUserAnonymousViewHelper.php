<?php

namespace RKW\RkwRegistration\ViewHelpers;
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
 * sUserAnonymousViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsUserAnonymousViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     */
    public function render($frontendUser)
    {

        if (!$frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser) {
            return false;
            //===
        }

        return $frontendUser->getTxRkwregistrationIsAnonymous();
        //===

    }

}