<?php
namespace RKW\RkwRegistration\ViewHelpers\FrontendUserGroups;

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

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * IsMembershipRequestedViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKWKompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsMembershipRequestedViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('frontendUserGroup', FrontendUserGroup::class, 'The frontendUserGroup to check for pending memberships.', true);
        $this->registerArgument('membershipsRequested', QueryResultInterface::class, 'List of pending optIns for memberships in frontendUserGroups..', true);
    }

    /**
     * @return int
     */
    public function render(): int
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->arguments['frontendUserGroup'];

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $membershipsRequested */
        $membershipsRequested = $this->arguments['membershipsRequested'];

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        foreach ($membershipsRequested as $optIn) {
            if (
                ($optIn->getForeignTable() == 'fe_groups')
                && ($optIn->getForeignUid() == $frontendUserGroup->getUid()))
            {
                return 1;
            }
        }

        return 0;
    }
}
