<?php
namespace RKW\RkwRegistration\Validation;

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

use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;


/**
 * Class UniqueEmailValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UniqueEmailValidator extends AbstractValidator
{
    /**
     * validation
     *
     * @return boolean
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $value
     */
    public function isValid($value): bool
    {

        // check if given eMail is valid at all
        if (
            (! $email = $value->getEmail())
            || (! FrontendUserUtility::isEmailValid($email))
        ){
            $this->result->forProperty('email')->addError(
                new Error(
                    LocalizationUtility::translate(
                        'validator.emailInvalid',
                        'rkw_registration'
                    ), 1434966688
                )
            );

            return false;
        }

        // check if given eMail is unique
        if (! FrontendUserUtility::isUsernameUnique($email, FrontendUserSessionUtility::getLoggedInUser())) {
            $this->result->forProperty('email')->addError(
                new Error(
                    LocalizationUtility::translate(
                        'validator.emailAlreadyAssigned',
                        'rkw_registration'
                    ), 1406119134
                )
            );

            return false;
        }

        return true;
    }
}
