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

use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Service\GroupService;
use RKW\RkwRegistration\Service\RegisterFrontendUserService;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * validation
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserForm
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($frontendUserForm)
    {
        $isValid = true;

        // get required fields of user
        /** @var GroupService $register */
        $register = GeneralUtility::makeInstance(GroupService::class);
        $requiredFields = $register->getMandatoryFieldsOfUser($frontendUserForm);

        // user may not be able to use the email address of another person
        // only relevant for existing users
        if ($frontendUserForm->getUid()) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var FrontendUserRepository $frontendUserRepository */
            $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
            $frontendUser = $frontendUserRepository->findOneByEmailOrUsernameInactive($frontendUserForm->getEmail());

            if ($frontendUser) {
                if ($frontendUser->getUid() != $frontendUserForm->getUid()) {
                    $this->result->forProperty('email')->addError(
                        new \TYPO3\CMS\Extbase\Error\Error(
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'validator.email_alreadyassigned',
                                'rkw_registration'
                            ), 1406119134
                        )
                    );
                    $isValid = false;
                }
            }
        }

        // check valid email
        if (in_array('email', $requiredFields)) {

            $objectManager = \RKW\RkwBasics\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            /** @var RegisterFrontendUserService $registerFrontendUserService */
            $registerFrontendUserService = $objectManager->get(RegisterFrontendUserService::class, FrontendUserUtility::convertArrayToObject($frontendUserForm));

            if (!$registerFrontendUserService->validateEmail()) {

                $this->result->forProperty('email')->addError(
                    new \TYPO3\CMS\Extbase\Error\Error(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'validator.email_invalid',
                            'rkw_registration'
                        ), 1414589184
                    )
                );
                $isValid = false;
            }

            // do only check this for new registration (do NOT check this, if a logged user want to update his user data)
            if (
                !$registerFrontendUserService->uniqueEmail($frontendUserForm->getEmail())
                && !FrontendUserSessionUtility::isUserLoggedIn()
            ) {
                $this->result->forProperty('email')->addError(
                    new \TYPO3\CMS\Extbase\Error\Error(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'registrationController.error.username_exists',
                            'rkw_registration'
                        ), 1628688993
                    )
                );
                $isValid = false;
            }
        }


        // check valid zip
        if (in_array('zip', $requiredFields)) {

            if (!($frontendUserForm->getZip())
                || (strlen(trim($frontendUserForm->getZip())) != 5)
            ) {
                $this->result->forProperty('zip')->addError(
                    $this->translateErrorMessage(
                        'validator.zip.incorrect',
                        'rkwRegistration'
                    ), 1462806656
                );
                $isValid = false;
            }
        }

        // check all properties on required
        $frontendUserForm = (array)$frontendUserForm;
        foreach ($frontendUserForm as $property => $value) {

            $property = trim(substr($property, 2));

            // has already been checked above!
            if ($property == 'email') {
                continue;
            }

            if (in_array($property, $requiredFields)) {

                if (empty($value)) {

                    $this->result->forProperty($property)->addError(
                        new \TYPO3\CMS\Extbase\Error\Error(
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'validator_field_notfilled',
                                'rkw_registration'
                            ), 1414595322
                        )
                    );
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }


}

