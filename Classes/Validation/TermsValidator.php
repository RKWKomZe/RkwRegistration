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

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Class TermsValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo write a fucking test
 */
class TermsValidator extends AbstractValidator
{
    /**
     * Validation of terms checkbox
     *
     * - The given entity is not important / not used
     * - The function "forProperty" will not work proper
     * - This validator will only return the message (no field highlighting)
     *
     * @param array $entity
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($entity)
    {
        $request = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_rkwregistration_register');
        $isValid = true;

        if (isset($request['terms'])
            && !$request['terms']
        ) {
            $this->result->forProperty('terms')->addError(
                new \TYPO3\CMS\Extbase\Error\Error(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'registrationController.error.accept_terms',
                        'rkw_registration'
                    ), 1628687661
                )
            );
            $isValid = false;
        }

        return $isValid;
    }
}
