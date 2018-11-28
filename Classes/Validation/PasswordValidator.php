<?php

namespace RKW\RkwRegistration\Validation;

use \RKW\RkwBasics\Helper\Common;

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
 * Class PasswordValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PasswordValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * Validation of password
     *
     * @var array $passwordArray
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($passwordArray)
    {

        $settings = Common::getTyposcriptConfiguration('Rkwregistration');
        $configuration = $settings['users']['passwordSettings'];

        $minLength = ($configuration['minLength'] ? $configuration['minLength'] : 8);
        $alphaNum = ($configuration['alphaNum'] ? true : false);

        // are the passwords set?
        if (
            (!$passwordArray['first'])
            || (!$passwordArray['second'])
        ) {

            $this->result->addError(
                new \TYPO3\CMS\Extbase\Error\Error(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'validator.passwords_not_all_set',
                        'rkw_registration'
                    ), 1435068293
                )
            );

            return false;
            //===
        }


        // check if identical
        if ($passwordArray['first'] != $passwordArray['second']) {

            $this->result->addError(
                new \TYPO3\CMS\Extbase\Error\Error(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'validator.passwords_not_identical',
                        'rkw_registration'
                    ), 1435068407
                )
            );

            return false;
            //===
        }


        // check length
        if (strlen($passwordArray['first']) < intval($minLength)) {
            $this->result->addError(
                new \TYPO3\CMS\Extbase\Error\Error(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'validator.password_too_short',
                        'rkw_registration',
                        array($minLength)
                    ), 1435066509
                )
            );

            return false;
            //===
        }

        // check if all important signs are included
        if ($alphaNum) {
            if (
                (!preg_match('/[A-Za-z]/', $passwordArray['first']))
                || (!preg_match('/[0-9]/', $passwordArray['first']))
            ) {
                $this->result->addError(
                    new \TYPO3\CMS\Extbase\Error\Error(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'validator.password_missing_signs',
                            'rkw_registration'
                        ), 1435066509
                    )
                );

                return false;
                //===
            }
        }

        return true;
        //===

    }


}