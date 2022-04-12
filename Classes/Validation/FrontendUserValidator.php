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

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Register\GroupRegister;
use RKW\RkwRegistration\Register\FrontendUserRegister;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class FrontendUserValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * booleanValidator
     *
     * @var \TYPO3\CMS\Extbase\Validation\Validator\BooleanValidator
     * @inject
     */
    protected $booleanValidator;

    /**
     * emailAddressValidator
     *
     * @var \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator
     * @inject
     */
    protected $emailAddressValidator;

    /**
     * frontendUserFormData
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    protected $frontendUserFormData;

    /**
     * isValid
     *
     * @var bool
     */
    protected $isValid = true;

    /**
     * requiredFields
     *
     * @var array
     */
    protected $requiredFields = [];


    /**
     * validation
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserForm
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($frontendUserForm)
    {
        $this->frontendUserFormData = $frontendUserForm;

        // get required fields of user
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var FrontendUserRegister $register */
        $register = $objectManager->get(FrontendUserRegister::class, $this->frontendUserFormData);
        $this->requiredFields = $register->getMandatoryFieldsOfUser();

        // do checks
        $this->checkEmailOfExistingUser();
        if (!empty($this->frontendUserFormData->getEmail())) {
            // empty values are covered in "checkMandatoryFields" function
            $this->checkEmailOfNewUserIsValid();
            $this->checkEmailOfNewUserIsAvailable();
        }
        $this->checkZip();
        $this->checkMandatoryFields();

        return $this->isValid;
    }



    /**
     * checkIfEmailAddressAlreadyAssigned
     *
     * @return void
     */
    protected function checkEmailOfExistingUser()
    {
        // user may not be able to use the email address of another person
        // only relevant for existing users
        if ($this->frontendUserFormData->getUid()) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var FrontendUserRepository $frontendUserRepository */
            $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
            $frontendUser = $frontendUserRepository->findOneByEmailOrUsernameAlsoInactive($this->frontendUserFormData->getEmail());

            if ($frontendUser) {
                if ($frontendUser->getUid() != $this->frontendUserFormData->getUid()) {
                    $this->result->forProperty('email')->addError(
                        new Error(
                            LocalizationUtility::translate(
                                'validator.email_alreadyassigned',
                                'rkw_registration'
                            ), 1406119134
                        )
                    );
                    $this->isValid = false;
                }
            }
        }
    }



    /**
     * checkEmailAddress
     *
     * @return void
     */
    protected function checkEmailOfNewUserIsValid()
    {
        // check valid email
        if (in_array('email', $this->requiredFields)) {

            $objectManager = \RKW\RkwBasics\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            /** @var FrontendUserRegister $frontendUserRegister */
            $frontendUserRegister = $objectManager->get(FrontendUserRegister::class, $this->frontendUserFormData);

            if (!$frontendUserRegister->validateEmail()) {

                $this->result->forProperty('email')->addError(
                    new Error(
                        LocalizationUtility::translate(
                            'validator.email_invalid',
                            'rkw_registration'
                        ), 1414589184
                    )
                );
                $this->isValid = false;
            }
        }
    }


    /**
     * checkEmailAddress
     *
     * @return void
     */
    protected function checkEmailOfNewUserIsAvailable()
    {
        // check valid email
        if (in_array('email', $this->requiredFields)) {

            $objectManager = \RKW\RkwBasics\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            /** @var FrontendUserRegister $frontendUserRegister */
            $frontendUserRegister = $objectManager->get(FrontendUserRegister::class, $this->frontendUserFormData);

            // do only check this for new registration (do NOT check this, if a logged user want to update his user data)
            if (
                !$frontendUserRegister->uniqueEmail($this->frontendUserFormData->getEmail())
                && !FrontendUserSessionUtility::isUserLoggedIn()
            ) {
                $this->result->forProperty('email')->addError(
                    new Error(
                        LocalizationUtility::translate(
                            'registrationController.error.username_exists',
                            'rkw_registration'
                        ), 1628688993
                    )
                );
                $this->isValid = false;
            }
        }
    }



    /**
     * checkZip
     *
     * @return void
     */
    protected function checkZip()
    {
        // check valid zip
        if (in_array('zip', $this->requiredFields)) {

            if (!($this->frontendUserFormData->getZip())
                || (strlen(trim($this->frontendUserFormData->getZip())) != 5)
                || !is_numeric($this->frontendUserFormData->getZip())
            ) {
                $this->result->forProperty('zip')->addError(
                    new Error(
                        $this->translateErrorMessage(
                            'validator.zip.incorrect',
                            'rkwRegistration'
                        ), 1462806656
                    )
                );
                $this->isValid = false;
            }
        }
    }


    /**
     * checkMandatoryFields
     *
     * @return void
     */
    protected function checkMandatoryFields()
    {
        $frontendUserFormData = (array)$this->frontendUserFormData;
        foreach ($frontendUserFormData as $property => $value) {

            $property = trim(substr($property, 2));

            if (in_array($property, $this->requiredFields)) {

                if (empty($value)) {
                    $this->result->forProperty($property)->addError(
                        new Error(
                            LocalizationUtility::translate(
                                'validator_field_notfilled',
                                'rkw_registration'
                            ), 1414595322
                        )
                    );
                    $this->isValid = false;
                }
            }
        }
    }
}

