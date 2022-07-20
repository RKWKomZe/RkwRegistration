<?php

namespace RKW\RkwRegistration\Hooks;

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
 * Class DatahandlerHook
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DatahandlerHook
{

    /**
     * Hook: processCmdmap_deleteAction - fired when datasets are deleted
     *
     * @param string $table The table currently processing data for
     * @param string $id The record uid currently processing data for, [integer] or [string] (like 'NEW...')
     * @param array $recordToDelete The record that has been deleted including all data
     * @param boolean $recordWasDeleted Shows if record was already deleted (e.g. by another hook-call)
     * @param object $object \TYPO3\CMS\Core\DataHandling\DataHandler
     * @return void
     * @see \TYPO3\CMS\Core\DataHandling\DataHandler::deleteAction
     */
    public function processCmdmap_deleteAction($table, $id, $recordToDelete, $recordWasDeleted, $object)
    {

        if ($table == 'fe_users') {

            // @todo if this table is fe_users, then delete all tx_rkwregistration_domain_model_privacy entries of this user (do not remove!)
        }


    }
}

?>
