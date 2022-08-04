<?php

namespace RKW\RkwRegistration\Tests\Integration\Validation;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Validation\FrontendUserValidator;
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
 * Class FrontendUserValidatorTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserValidatorTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserValidatorTest/Fixtures';

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
    }

    /**
     * @test
     */
    public function isValidWithAllFilledMandatoryFieldsReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given are mandatory form field data
         * When the validator is called
         * Then true is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setEmail('test@gmx.de');
        $frontendUserFormData->setFirstName('Först naime');
        $frontendUserFormData->setLastName('Säcond naime');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertTrue($result);
    }

    /**
     * @test
     */
    public function isValidWithNoFilledFieldsAndWithoutMandatoryFieldsReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is a rootpage with alternative settings (no mandatory fields)
         * Given are frontendUser form data without content (no field is filled out)
         * When the validator function is called
         * Then true is returned
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check10.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertTrue($result);
    }

    /**
     * @test
     */
    public function isValidWithIncompleteDataReturnFalse ()
    {
        /**
         * Scenario:
         *
         * Given are incomplete form field data
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setEmail('test@gmx.de');
        //$frontendUserFormData->setFirstName('');
        //$frontendUserFormData->setLastName('');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }

    /**
     * @test
     */
    public function isValidWithPartialGivenDataReturnFalse ()
    {
        /**
         * Scenario:
         *
         * Given are partial mandatory form field data
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }


    /**
     * @test
     */
    public function isValidWithNotExistingEmailAddressReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is a rootpage with alternative settings (no mandatory fields)
         * Given a not existing email address
         * When the validator function is called
         * Then true is returned
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check10.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setEmail('test@gmx.de');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertTrue($result);
    }


    /**
     * @test
     */
    public function isValidWithAlreadyExistingEmailAddressReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given an already existing email address
         * When the validator function is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check20.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setEmail('lauterbach@spd.de');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }

    /**
     * @test
     */
    public function isValidWithFaultyEmailAddressReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given an faulty email address
         * When the validator function is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check20.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setEmail('lauterbach');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }


    /**
     * @test
     */
    public function isValidWithCorrectZipReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given a correct zip
         * When the validator function is called
         * Then true is returned
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setZip('33333');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertTrue($result);
    }

    /**
     * @test
     */
    public function isValidWithTooShortZipReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a faulty zip
         * When the validator function is called
         * Then false is returned
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setZip('333');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }

    /**
     * @test
     */
    public function isValidWithTooLongZipReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a faulty zip
         * When the validator function is called
         * Then false is returned
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setZip('33333333333333');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }

    /**
     * @test
     */
    public function isValidWithAlphabeticZipReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a alphabetic zip with the expected length of 5
         * When the validator function is called
         * Then false is returned
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setZip('ABCDE');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }

    /**
     * @test
     */
    public function isValidWithAlphanumericZipReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a alphanumeric zip with the expected length of 5
         * When the validator function is called
         * Then false is returned
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setZip('3A3B3C');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }

    /**
     * @test
     */
    public function isValidWithSpecialCharactersZipReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a alphanumeric zip with the expected length of 5
         * When the validator function is called
         * Then false is returned
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = $this->objectManager->get(FrontendUser::class);
        $frontendUserFormData->setZip('$%&()');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);

        static::assertFalse($result);
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}
