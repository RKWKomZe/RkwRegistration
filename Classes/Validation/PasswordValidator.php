<?php

namespace RKW\RkwRegistration\Validation;

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Register\FrontendUserRegister;
use RKW\RkwRegistration\Service\AuthFrontendUserService;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     * passwordArray
     *
     * @var array
     */
    protected $passwordArray = [];

    /**
     * passwordSettings
     *
     * @var array
     */
    protected $passwordSettings = [];

    /**
     * isValid
     *
     * @var bool
     */
    protected $isValid = true;

    /**
     * Validation of password
     *
     * @param array $passwordArray
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($passwordArray): bool
    {
        $this->passwordArray = $passwordArray;
        $settings = GeneralUtility::getTyposcriptConfiguration('Rkwregistration');
        $this->passwordSettings = $settings['users']['passwordSettings'];

        $this->checkIfNewPasswordIsGiven();
        $this->checkEquality();
        $this->checkLength();
        $this->checkMandatorySigns();
        $this->checkIfOldPasswordIsSet();
        $this->checkIfOldPasswordIsValid();

        return $this->isValid;
    }


    /**
     * checkIfPasswordIsGiven
     *
     * @return void
     */
    protected function checkIfNewPasswordIsGiven()
    {
        // are the passwords set?
        if (
            (!$this->passwordArray['first'])
            || (!$this->passwordArray['second'])
        ) {

            $this->result->addError(
                new Error(
                    LocalizationUtility::translate(
                        'validator.passwords_not_all_set',
                        'rkw_registration'
                    ), 1435068293
                )
            );

            $this->isValid = false;
        }
    }

    /**
     * checkEquality
     *
     * @return void
     */
    protected function checkEquality()
    {
        if ($this->passwordArray['first'] != $this->passwordArray['second']) {

            $this->result->addError(
                new Error(
                    LocalizationUtility::translate(
                        'validator.passwords_not_identical',
                        'rkw_registration'
                    ), 1435068407
                )
            );

            $this->isValid = false;
        }
    }


    /**
     * checkLength
     *
     * @return void
     */
    protected function checkLength()
    {
        // min length
        $minLength = ($this->passwordSettings['minLength'] ?: 8);
        if (strlen($this->passwordArray['first']) < intval($minLength)) {
            $this->result->addError(
                new Error(
                    LocalizationUtility::translate(
                        'validator.password_too_short',
                        'rkw_registration',
                        [$minLength]
                    ), 1435066509
                )
            );
            $this->isValid = false;
        }

        // max length
        $maxLength = ($this->passwordSettings['maxLength'] ?: 100);

        if (strlen($this->passwordArray['first']) > intval($maxLength)) {
            $this->result->addError(
                new Error(
                    LocalizationUtility::translate(
                        'validator.password_too_long',
                        'rkw_registration',
                        [$maxLength]
                    ), 1649316598
                )
            );
            $this->isValid = false;
        }
    }


    /**
     * checkMandatorySigns
     *
     * @return void
     */
    protected function checkMandatorySigns()
    {
        $alphaNum = (bool) $this->passwordSettings['alphaNum'];

        if ($alphaNum) {
            if (
                (!preg_match('/[A-Za-z]/', $this->passwordArray['first']))
                || (!preg_match('/[0-9]/', $this->passwordArray['first']))
            ) {
                $this->result->addError(
                    new Error(
                        LocalizationUtility::translate(
                            'validator.password_missing_signs',
                            'rkw_registration'
                        ), 1435066509
                    )
                );

                $this->isValid = false;
            }
        }
    }


    /**
     * checkIfOldPasswordIsSet
     *
     * @return void
     */
    protected function checkIfOldPasswordIsSet()
    {
        if (!$this->passwordArray['old']) {

            $this->result->addError(
                new Error(
                    LocalizationUtility::translate(
                        'validator.old_password_not_set',
                        'rkw_registration'
                    ), 1649148502
                )
            );
            $this->isValid = false;
        }
    }


    /**
     * checkIfOldPasswordIsSet
     *
     * @return void
     */
    protected function checkIfOldPasswordIsValid()
    {
        // do only check if all other checks does not detect an error
        // -> this is important that the user is not smashed by multiple error messages at once
        if ($this->isValid) {

            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            /** @var AuthFrontendUserService $authentication */
            $authentication = $objectManager->get(AuthFrontendUserService::class);
            /** @var FrontendUserRepository $frontendUserRepository */
            $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
            /** @var FrontendUser $frontendUser */
            $frontendUser = $frontendUserRepository->findByUid(FrontendUserSessionUtility::getFrontendUserId());

            $authResult = $authentication->validateUser($frontendUser->getUsername(), $this->passwordArray['old']);

            if (!$authResult instanceof FrontendUser) {

                $this->result->addError(
                    new Error(
                        LocalizationUtility::translate(
                            'validator.password_old_wrong',
                            'rkw_registration'
                        ), 1649151982
                    )
                );

                // for usability: Show remaining attempts before that dude gets disabled.
                if (FrontendUserUtility::remainingLoginAttempts($frontendUser) <= 3) {
                    $this->result->addError(
                        new Error(
                            LocalizationUtility::translate(
                                'validator.remaining_attempts',
                                'rkw_registration',
                                [FrontendUserUtility::remainingLoginAttempts($frontendUser)]
                            ), 1649151982
                        )
                    );
                }

                $this->isValid = false;
            }
        }
    }
}

