<?php
namespace RKW\RkwRegistration\Utility;

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

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup as FrontendUserGroupCore;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class FrontendUserGroupUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserGroupUtility
{

    /**
     * Returns all mandatory properties of user
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $frontendUserGroup
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function getMandatoryFields(FrontendUserGroupCore $frontendUserGroup): array
    {
        $mandatoryFields = [];
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);

        // upgrade given object
        if (!$frontendUserGroup instanceof FrontendUserGroup) {
            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

            /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository $frontendUserGroupRepository */
            $frontendUserGroupRepository = $objectManager->get(FrontendUserGroupRepository::class);
            $frontendUserGroup = $frontendUserGroupRepository->findByIdentifier($frontendUserGroup->getUid());
        }

        // get mandatory fields
        if (
            ($frontendUserGroup instanceof FrontendUserGroup)
            && ($mandatoryFieldsTemp = $frontendUserGroup->getTxRkwregistrationMembershipMandatoryFields())
        ){
            $mandatoryFieldsTemp = GeneralUtility::trimExplode(',', $mandatoryFieldsTemp);
            foreach($mandatoryFieldsTemp as $field) {
                $field = GeneralUtility::camelize($field);
                if (property_exists($frontendUser, $field)) {
                    $mandatoryFields[] = $field;
                }
            }
        }

        return $mandatoryFields;
    }



}
