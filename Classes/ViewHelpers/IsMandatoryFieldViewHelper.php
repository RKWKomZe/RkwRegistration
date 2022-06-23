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

use RKW\RkwRegistration\Register\GroupRegister;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * IsMandatoryFieldViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsMandatoryFieldViewHelper extends AbstractViewHelper
{
    /**
     * @param string $fieldName
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function render($fieldName, $frontendUser = null)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Register\GroupRegister $register */
        $register = $objectManager->get(GroupRegister::class);

        $requiredFields = $register->getMandatoryFieldsOfUser($frontendUser);
        
        return in_array($fieldName, $requiredFields);
    }
}
