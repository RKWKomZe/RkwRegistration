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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;


/**
 * Class UniqueEmailValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UniqueEmailValidator extends AbstractValidator
{
    /**
     * validation
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $givenFrontendUser
     * @return boolean
     */
    public function isValid($givenFrontendUser): bool
    {

        // check if given E-Mail is valid at all
        if (
            ($email = $givenFrontendUser->getEmail())
            && (GeneralUtility::validEmail($email))
        ) {

            // user may not be able to accept the email address of another person
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            
            /** @var FrontendUserRepository $frontendUserRepository */
            $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);

            // check email is still available
            if ($frontendUser = $frontendUserRepository->findOneByEmailOrUsernameInactive($email)) {

                // for registered User
                if ($frontendUser->getUid() != $givenFrontendUser->getUid()
                    || !$givenFrontendUser->getUid()
                ) {

                    $this->result->forProperty('email')->addError(
                        new Error(
                            LocalizationUtility::translate(
                                'validator.email_alreadyassigned',
                                'rkw_registration'
                            ), 1406119134
                        )
                    );

                    return false;
                }
            }

            return true;
        }

        $this->result->forProperty('email')->addError(
            new Error(
                LocalizationUtility::translate(
                    'validator.email_invalid',
                    'rkw_registration'
                ), 1434966688
            )
        );

        return false;
    }
}
