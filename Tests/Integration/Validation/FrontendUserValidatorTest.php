<?php
namespace RKW\RkwRegistration\Tests\Integration\Validation;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Validation\FrontendUserValidator;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class FrontendUserValidatorTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
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
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    private $frontendUserGroupRepository;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
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


        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var  \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository frontendUserRepository */
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

        /** @var  \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository frontendUserGroupRepository */
        $this->frontendUserGroupRepository = $this->objectManager->get(FrontendUserGroupRepository::class);

    }

    #==============================================================================


    /**
     * @test
     */
    public function isValidWithAllFilledMandatoryFieldsReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given a valid configuration of mandatory fields
         * Given an email address that has not been used by another user
         * Given all mandatory fields are filled
         * When the validator is called
         * Then true is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = GeneralUtility::makeInstance(FrontendUser::class);
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
         * Given a valid configuration of mandatory fields with no mandatory fields set
         * Given no fields are filled out
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

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = GeneralUtility::makeInstance(FrontendUser::class);

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
         * Given a valid configuration of mandatory fields
         * Given an email address that has not been used by another user
         * Given not all mandatory fields are filled out
         * When the validator is called
         * Then false is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUserFormData->setEmail('test@gmx.de');
        $frontendUserFormData->setFirstName('');
        $frontendUserFormData->setLastName('');

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
    public function isValidWithAlreadyUsedEmailAddressReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a valid configuration of mandatory fields
         * Given an email address that has been used by another user
         * Given all mandatory fields are filled
         * When the validator function is called
         * Then false is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUserFormData->setEmail('lauterbach@spd.de');
        $frontendUserFormData->setFirstName('Först naime');
        $frontendUserFormData->setLastName('Säcond naime');

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
    public function isValidWithAlreadyUsedEmailAddressButLoggedInReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given a valid configuration of mandatory fields
         * Given an email address that has been used
         * Given the user that used this email is logged in
         * Given all mandatory fields are filled
         * When the validator function is called
         * Then true is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(10);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
        FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUserFormData->setEmail('lauterbach@spd.de');
        $frontendUserFormData->setFirstName('Först naime');
        $frontendUserFormData->setLastName('Säcond naime');

        /** @var \RKW\RkwRegistration\Validation\FrontendUserValidator $frontendUserValidator */
        $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);

        // workaround start: for creating $this->result of the validator
        $frontendUserValidator->validate($frontendUserFormData);
        // workaround end

        $result = $frontendUserValidator->isValid($frontendUserFormData);
        static::assertTrue($result);

        FrontendSimulatorUtility::resetFrontendEnvironment();
    }

    /**
     * @test
     */
    public function isValidWithInvalidEmailAddressReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a valid configuration of mandatory fields
         * Given an invalid email address
         * Given all mandatory fields are filled
         * When the validator function is called
         * Then false is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUserFormData->setEmail('lauterbach');
        $frontendUserFormData->setFirstName('Först naime');
        $frontendUserFormData->setLastName('Säcond naime');

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
         * Given a valid configuration of mandatory fields
         * Given a correct zip
         * Given all mandatory fields are filled
         * When the validator function is called
         * Then true is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUserFormData->setEmail('lauterbach');
        $frontendUserFormData->setFirstName('Först naime');
        $frontendUserFormData->setLastName('Säcond naime');
        $frontendUserFormData->setZip(35745);

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
    public function isValidInvalidZipReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a valid configuration of mandatory fields
         * Given an incorrect zip
         * Given all mandatory fields are filled
         * When the validator function is called
         * Then false is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserFormData */
        $frontendUserFormData = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUserFormData->setEmail('lauterbach');
        $frontendUserFormData->setFirstName('Först naime');
        $frontendUserFormData->setLastName('Säcond naime');
        $frontendUserFormData->setZip(35);

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
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
