<?php

namespace RKW\RkwRegistration\Tests\Integration\Validation;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Validation\FrontendUserValidator;
use RKW\RkwRegistration\Validation\PasswordValidator;
use RKW\RkwRegistration\Validation\UniqueEmailValidator;
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
 * Class UniqueEmailValidatorTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UniqueEmailValidatorTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/UniqueEmailValidatorTest/Fixtures';

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
     */
    public function isValidWithAlreadyPersistentEmailOfExistingUserReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is a persistent FrontendUser
         * Given is that user who is checked for it's email address
         * When the validator is called with that FrontendUser
         * When this users email address is validated again
         * When the validator compares the UID of the given and of the fetched user
         * Then true is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\UniqueEmailValidator $uniqueEmailValidator */
        $uniqueEmailValidator = $this->objectManager->get(UniqueEmailValidator::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $frontendUserRepository->findByIdentifier(1);

        // workaround start: for creating $this->result of the validator
        $uniqueEmailValidator->validate($frontendUser);
        // workaround end

        $result = $uniqueEmailValidator->isValid($frontendUser);

        static::assertTrue($result);
    }


    /**
     * @test
     */
    public function isValidWithAlreadyPersistentEmailOfNotExistingUserReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a persistent FrontendUser
         * Given is a new FrontendUser with an equal email address which already exists
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\UniqueEmailValidator $uniqueEmailValidator */
        $uniqueEmailValidator = $this->objectManager->get(UniqueEmailValidator::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);
        $frontendUser->setEmail('lauterbach@spd.de');

        // workaround start: for creating $this->result of the validator
        $uniqueEmailValidator->validate($frontendUser);
        // workaround end

        $result = $uniqueEmailValidator->isValid($frontendUser);

        static::assertFalse($result);
    }


    /**
     * @test
     */
    public function isValidWithNotExistingEmailReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is a new FrontendUser with a not existing email address
         * When the validator is called
         * Then true is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\UniqueEmailValidator $uniqueEmailValidator */
        $uniqueEmailValidator = $this->objectManager->get(UniqueEmailValidator::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);
        $frontendUser->setEmail('best@of.de');

        // workaround start: for creating $this->result of the validator
        $uniqueEmailValidator->validate($frontendUser);
        // workaround end

        $result = $uniqueEmailValidator->isValid($frontendUser);

        static::assertTrue($result);
    }


    /**
     * @test
     */
    public function isValidWithFaultyEmailReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a new FrontendUser with a faulty email address
         * When the validator is called
         * Then false is returned
         */

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \RKW\RkwRegistration\Validation\UniqueEmailValidator $uniqueEmailValidator */
        $uniqueEmailValidator = $this->objectManager->get(UniqueEmailValidator::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);
        $frontendUser->setEmail('bestofde');

        // workaround start: for creating $this->result of the validator
        $uniqueEmailValidator->validate($frontendUser);
        // workaround end

        $result = $uniqueEmailValidator->isValid($frontendUser);

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
