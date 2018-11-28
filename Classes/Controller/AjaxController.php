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
 * Class AjaxController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AjaxController extends ControllerAbstract
{
    /**
     * Returns the init for personal info- action
     *
     * @return void
     */
    public function loginInfoInitAction()
    {

        $this->view->assignMultiple(
            array(
                'pageUid'     => intval($GLOBALS['TSFE']->id),
                'languageUid' => intval($GLOBALS['TSFE']->sys_language_uid),
            )
        );
    }

    /**
     * Returns personal info of user, used via AJAX
     *
     * @return string
     */
    public function loginInfoAction()
    {

        $this->view->assignMultiple(
            array(
                'frontendUser' => $this->getFrontendUser(),
                'logoutPid'    => intval($this->settings['users']['logoutPid']),
            )
        );

        $content = $this->view->render();

        return json_encode(
            array(
                'data' => str_replace(array("\n", "\r", "\t"), '', $content),
            )
        );
        //===
    }


}