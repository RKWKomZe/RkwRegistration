<?php

namespace RKW\RkwRegistration\Tests\Integration\Validation;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Validation\FrontendUserValidator;
use RKW\RkwRegistration\Validation\PasswordValidator;
use RKW\RkwRegistration\ViewHelpers\GetAllFlashMessageIdentifierViewHelper;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
 * Class PasswordValidatorTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PasswordValidatorTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const PASSWORD_ALPHANUM = 'newValidPassword1';

    /**
     * @const
     */
    const PASSWORD_ALPHANUM_ALT = 'newValidPassword2';

    /**
     * @const
     */
    const PASSWORD_ALPHA = 'newInvalidPassword';

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/PasswordValidatorTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration'
    ];


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        FrontendSimulatorUtility::simulateFrontendEnvironment();
    }

    /**
     * @test
     * @throws \RKW\RkwRegistration\Exception
     */
    public function isValidWithCorrectDataReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * Given are correct form data for changing the password
         * When the validator is called
         * Then true is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\PasswordValidator $passwordValidator */
        $passwordValidator = $this->objectManager->get(PasswordValidator::class);

        // START: Login existing User
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);
        FrontendUserSessionUtility::login($frontendUser);
        // END: Login existing User

        $formInputArray = [
            'old' => 'secretPassword',
            'first' => self::PASSWORD_ALPHANUM,
            'second' => self::PASSWORD_ALPHANUM
        ];

        // workaround start: for creating $this->result of the validator
        $passwordValidator->validate($formInputArray);
        // workaround end

        $result = $passwordValidator->isValid($formInputArray);

        static::assertTrue($result);
    }

    /**
     * @test
     * @throws \RKW\RkwRegistration\Exception
     */
    public function isValidWithFirstNewPasswordFieldIsNotFilledReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * Given are form data but the first new password field is not filled
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\PasswordValidator $passwordValidator */
        $passwordValidator = $this->objectManager->get(PasswordValidator::class);

        // START: Login existing User
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);
        FrontendUserSessionUtility::login($frontendUser);
        // END: Login existing User

        $formInputArray = [
            'old' => 'secretPassword',
            'first' => '',                      // <- not filled!
            'second' => self::PASSWORD_ALPHANUM
        ];

        // workaround start: for creating $this->result of the validator
        $passwordValidator->validate($formInputArray);
        // workaround end

        $result = $passwordValidator->isValid($formInputArray);

        static::assertFalse($result);
    }

    /**
     * @test
     * @throws \RKW\RkwRegistration\Exception
     */
    public function isValidWithSecondNewPasswordFieldIsNotFilledReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * Given are form data but the second new password field is not filled
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\PasswordValidator $passwordValidator */
        $passwordValidator = $this->objectManager->get(PasswordValidator::class);

        // START: Login existing User
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);
        FrontendUserSessionUtility::login($frontendUser);
        // END: Login existing User

        $formInputArray = [
            'old' => 'secretPassword',
            'first' => self::PASSWORD_ALPHANUM,
            'second' => ''    // <- not filled!
        ];

        // workaround start: for creating $this->result of the validator
        $passwordValidator->validate($formInputArray);
        // workaround end

        $result = $passwordValidator->isValid($formInputArray);

        static::assertFalse($result);
    }

    /**
     * @test
     * @throws \RKW\RkwRegistration\Exception
     */
    public function isValidWithTypoInNewPasswordReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * Given are form data with a typo in the repeat
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\PasswordValidator $passwordValidator */
        $passwordValidator = $this->objectManager->get(PasswordValidator::class);

        // START: Login existing User
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);
        FrontendUserSessionUtility::login($frontendUser);
        // END: Login existing User

        $formInputArray = [
            'old' => 'secretPassword',
            'first' => self::PASSWORD_ALPHANUM,
            'second' => self::PASSWORD_ALPHANUM_ALT // <- the typo! Same length!
        ];

        // workaround start: for creating $this->result of the validator
        $passwordValidator->validate($formInputArray);
        // workaround end

        $result = $passwordValidator->isValid($formInputArray);

        static::assertFalse($result);
    }


    /**
     * @test
     * @throws \RKW\RkwRegistration\Exception
     */
    public function isValidWithTooShortNewPasswordLengthReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * Given are form data with a too short new password
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\PasswordValidator $passwordValidator */
        $passwordValidator = $this->objectManager->get(PasswordValidator::class);

        // START: Login existing User
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);
        FrontendUserSessionUtility::login($frontendUser);
        // END: Login existing User

        $formInputArray = [
            'old' => 'secretPassword',
            'first' => 'pswd1',         // <- too short!
            'second' => 'pswd1'         // <- too short!
        ];

        // workaround start: for creating $this->result of the validator
        $passwordValidator->validate($formInputArray);
        // workaround end

        $result = $passwordValidator->isValid($formInputArray);

        static::assertFalse($result);
    }


    /**
     * @test
     * @throws \RKW\RkwRegistration\Exception
     */
    public function isValidWithVeryLongNewPasswordLengthReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * Given are form data with a very long new password
         * When the validator is called
         * Then true is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\PasswordValidator $passwordValidator */
        $passwordValidator = $this->objectManager->get(PasswordValidator::class);

        // START: Login existing User
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);
        FrontendUserSessionUtility::login($frontendUser);
        // END: Login existing User

        $formInputArray = [
            'old' => 'secretPassword',
            'first' => self::PASSWORD_ALPHANUM . 'Loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
            'second' => self::PASSWORD_ALPHANUM . 'Loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong'
        ];

        // workaround start: for creating $this->result of the validator
        $passwordValidator->validate($formInputArray);
        // workaround end

        $result = $passwordValidator->isValid($formInputArray);

        static::assertTrue($result);
    }


    /**
     * @test
     * @throws \RKW\RkwRegistration\Exception
     */
    public function isValidWithNotAlphaNumericNewPasswordReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * Given are form data with a not alphanumeric new password
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\PasswordValidator $passwordValidator */
        $passwordValidator = $this->objectManager->get(PasswordValidator::class);

        // START: Login existing User
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);
        FrontendUserSessionUtility::login($frontendUser);
        // END: Login existing User

        $formInputArray = [
            'old' => 'secretPassword',
            'first' => self::PASSWORD_ALPHA,
            'second' => self::PASSWORD_ALPHA
        ];

        // workaround start: for creating $this->result of the validator
        $passwordValidator->validate($formInputArray);
        // workaround end

        $result = $passwordValidator->isValid($formInputArray);

        static::assertFalse($result);
    }

    /**
     * @test
     * @throws \RKW\RkwRegistration\Exception
     */
    public function isValidWithNotSetOldPasswordReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * Given are form data but old password is not given
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\PasswordValidator $passwordValidator */
        $passwordValidator = $this->objectManager->get(PasswordValidator::class);

        // START: Login existing User
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);
        FrontendUserSessionUtility::login($frontendUser);
        // END: Login existing User

        $formInputArray = [
            'old' => '',                    // <- old password not set!
            'first' => self::PASSWORD_ALPHANUM,
            'second' => self::PASSWORD_ALPHANUM
        ];

        // workaround start: for creating $this->result of the validator
        $passwordValidator->validate($formInputArray);
        // workaround end

        $result = $passwordValidator->isValid($formInputArray);

        static::assertFalse($result);
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        FrontendSimulatorUtility::resetFrontendEnvironment();
        parent::tearDown();
    }

}