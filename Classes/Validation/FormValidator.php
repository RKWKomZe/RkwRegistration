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

/**
 * Class FormValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FormValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * booleanValidator
     *
     * @var \TYPO3\CMS\Extbase\Validation\Validator\BooleanValidator
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $booleanValidator;

    /**
     * emailAddressValidator
     *
     * @var \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator
     * @TYPO3\CMS\Extbase\Annotation\Inject
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
        /** @var \RKW\RkwRegistration\Tools\Service $register */
        $register = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Service');
        $requiredFields = $register->getMandatoryFieldsOfUser($frontendUserForm);

        // user may not be able to use the email address of another person
        // only relevant for existing users
        if ($frontendUserForm->getUid()) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
            $frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');
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

            if (!\RKW\RkwRegistration\Tools\Registration::validEmail($frontendUserForm->getEmail())) {

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
            //===

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
        //====

    }


}

