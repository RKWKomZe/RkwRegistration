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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * IsGroupInServiceInquiryListViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsGroupInServiceInquiryListAndEnabledByAdminViewHelper extends AbstractViewHelper
{
    /**
     * Checks if admin has granted service inquiry for given group id
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
                if ($inquiry instanceof \RKW\RkwRegistration\Domain\Model\Service) {

                    // get group list
                    $userGroups = $inquiry->getUsergroup();

                    // go through all given groups and check them against given group id and
                    // check if the admin has granted access
                    foreach ($userGroups as $userGroup) {

                        if ($userGroup instanceof \RKW\RkwRegistration\Domain\Model\FrontendUserGroup) {
                            if ($userGroup->getUid() == $groupId) {
                                if ($inquiry->getEnabledByAdmin()) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
