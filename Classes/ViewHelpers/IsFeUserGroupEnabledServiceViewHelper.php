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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
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
class IsFeUserGroupEnabledServiceViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('feUserGroup', FrontendUserGroup::class, 'The frontendUserGroup to check.', true);
        $this->registerArgument('services', QueryResultInterface::class, 'The list of services in which we search for the given frontendUserGroup.', true);
    }


    /**
     * Checks if admin has granted service inquiry for given group id
     *
     * @return boolean
     */
    public function render()
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $groupId */
        $feUserGroup= $this->arguments['feUserGroup'];

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $services */
        $services = $this->arguments['services'];

        // go through all inquiries
        /** @var \RKW\RkwRegistration\Domain\Model\Service $service */
        foreach ($services as $service) {

            // get group list
            $userGroups = $service->getUsergroup();

            // go through all given groups and check them against given group id and
            // check if the admin has granted access
            foreach ($userGroups as $userGroup) {

                if ($userGroup instanceof \RKW\RkwRegistration\Domain\Model\FrontendUserGroup) {
                    if ($userGroup->getUid() == $feUserGroup->getUid()) {
                        if ($service->getEnabledByAdmin()) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
