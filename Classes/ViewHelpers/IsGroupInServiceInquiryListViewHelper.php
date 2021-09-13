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

use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\Service;

/**
 * IsGroupInServiceInquiryListViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsGroupInServiceInquiryListViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Checks if given group id is in the list of the groups that belong to the given inquiry-set
     *
     * @param int $groupId
     * @param object $serviceInquiries
     * @return boolean
     */
    public function render($groupId, $serviceInquiries)
    {

        if ($serviceInquiries instanceof \Traversable) {

            // go through all inquiries
            foreach ($serviceInquiries as $inquiry) {

                // check instance type
                if ($inquiry instanceof Service) {

                    // get group list
                    $userGroups = $inquiry->getUsergroup();

                    // go through all given groups and check them against given group id
                    foreach ($userGroups as $userGroup) {

                        if ($userGroup instanceof FrontendUserGroup) {
                            if ($userGroup->getUid() == $groupId) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
