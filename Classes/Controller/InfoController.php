<?php

namespace RKW\RkwRegistration\Controller;

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
 * Class InfoController
 *

 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class InfoController extends ControllerAbstract
{

    /**
     * Returns personal info of user, used via AJAX
     *
     * @return void
     */
    public function loginInfoAction()
    {
         $this->view->assignMultiple(
            [
                'frontendUser' => $this->getFrontendUser(),
                'logoutPid'    => intval($this->settings['users']['logoutPid']),
            ]
        );
    }


}