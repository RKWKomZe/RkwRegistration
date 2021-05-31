<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use \RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwRegistration\Service\RegistrationService;
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
 * RegistrationServiceTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RegistrationServiceTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [
    ];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    private $registrationRepository = null;

    /**
     * @var \RKW\RkwRegistration\Service\RegistrationService
     */
    private $registrationService = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();
        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Tests/Integration/Utility/RegistrationServiceTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        // Repository
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);
        // Service
        $this->registrationService = $this->objectManager->get(RegistrationService::class);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'mail@default.rkw';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW Default';

    }



    /**
     * @test
     */
    public function processOptInWithTokenYesReturnsSuccess ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * Given is an ENABLED frontendUser
         * When this register is checked with "YES"-token
         * Then the function gives back a success value ("1")
         * Then the registration entry is removed from database
         * Then the frontendUser is still part of the database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 0);

        $result = $this->registrationService->processOptIn($register->getTokenYes(), '', $register->getUserSha1());

        static::assertTrue($result === 1);
        static::assertTrue($frontendUser->getDisable() === 0);
        // And check that the feUser entry is still in database
        $frontendUserFromDb = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());
        static::assertNotNull($frontendUserFromDb);
        // the new disable value is also persistent now
        static::assertTrue($frontendUserFromDb->getDisable() === 0);
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithTokenYesReturnsSuccessAndEnablesTheDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * Given is an DISABLED FrontendUser
         * When this register is checked with "YES"-token
         * Then the function gives back a success value ("1")
         * Then the registration entry is removed from database
         * Then the disabled frontendUser is enabled
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->registrationService->processOptIn($register->getTokenYes(), '', $register->getUserSha1());

        static::assertTrue($result === 1);
        static::assertTrue($frontendUser->getDisable() === 0);
        // And check that the feUser entry is still in database
        $frontendUserFromDb = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());
        static::assertNotNull($frontendUserFromDb);
        // the new disable value is also persistent now
        static::assertTrue($frontendUserFromDb->getDisable() === 0);
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithTokenNoReturnsDismissed ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * Given is an ENABLED frontendUser
         * When this register is checked with "NO"-token
         * Then the function gives back a dismissed by user value ("2")
         * Then the registration entry is removed from database
         * Then the frontendUser is still part of the database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        $result = $this->registrationService->processOptIn('', $register->getTokenNo(), $register->getUserSha1());

        static::assertTrue($result === 2);
        // but the database entry is completely deleted (because disabled users are removed when using token "No")
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithTokenNoReturnsDismissedAndRemovesTheDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * When this register is checked with "NO"-token
         * Then the function gives back a dismissed by user value ("2")
         * Then the registration entry is removed from database
         * Then the disabled frontendUser is removed from database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->registrationService->processOptIn('', $register->getTokenNo(), $register->getUserSha1());

        static::assertTrue($result === 2);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but the database entry is completely deleted (because disabled users are removed when using token "No")
        static::assertNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithWrongYesTokenReturnsFailed ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * When this register is checked with WRONG "YES"-token
         * Then the function gives back a unexpected value ("0")
         * Then the registration is NOT removed from database
         * Then the disabled frontendUser is NOT removed from database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->registrationService->processOptIn('thisTokenIsBullshit', '', $register->getUserSha1());

        static::assertTrue($result === 0);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but nothing is happen. User and registration still exist in database
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNotNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithWrongNoTokenReturnsFailed ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * When this register is checked with WRONG "NO"-token
         * Then the function gives back a unexpected value ("0")
         * Then the registration is NOT removed from database
         * Then the disabled frontendUser is NOT removed from database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        /** @var RegistrationService $service */
        $result = $this->registrationService->processOptIn('', 'thisTokenIsBullshit', $register->getUserSha1());

        static::assertTrue($result === 0);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but nothing is happen. User and registration still exist in database
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNotNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInExpiredReturnsFailedAndRemovesRegistration ()
    {
        /**
         * Scenario:
         *
         * Given is an EXPIRED event registration
         * When this register is checked with "YES"-token
         * Then the function gives back an expired value ("400")
         * Then the registration is removed from database
         * Then the disabled frontendUser is removed from database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() - 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        $result = $this->registrationService->processOptIn('', 'thisTokenIsBullshit', $register->getUserSha1());

        static::assertTrue($result === 400);
        static::assertNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWhichNotExistsReturnsNotFound ()
    {
        /**
         * Scenario:
         *
         * Given is an EXPIRED event registration
         * When this register is checked with "YES"-token
         * Then the function gives back a not found value ("500")
         */

        $result = $this->registrationService->processOptIn('something', '', 'whatEver :)');

        static::assertTrue($result === 500);
    }



    /**
     * @test
     */
    public function registerNewFrontendUserReturnsPersistentAndDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an e-mail address
         * When a new user is registered
         * Then a new (disabled) frontendUser is created
         */

        $userData = [
            'email' => 'test@email.de'
        ];

        // email does not exists
        static::assertNull($this->frontendUserRepository->findByEmail($userData['email'])->getFirst());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->registrationService->register($userData);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        static::assertEquals($userData['email'], $result->getUsername());
        // with UID -> successfully persisted
        static::assertNotNull($result->getUid());
        // disabled
        static::assertEquals(1, $result->getDisable());
        // additional: Query from DB
        static::assertNotNull($this->frontendUserRepository->findOneByEmailOrUsernameInactive($userData['email']));
    }


    /**
     * @test
     */
    public function registerNewFrontendUserReturnsPersistentAndEnabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an e-mail address
         * When a new user is registered with flag "enabled" true
         * Then a new (enabled) frontendUser is created
         */

        $userData = [
            'email' => 'test@email.de'
        ];

        // email does not exists
        static::assertNull($this->frontendUserRepository->findByEmail($userData['email'])->getFirst());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->registrationService->register($userData, true);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        static::assertEquals($userData['email'], $result->getUsername());
        // with UID -> successfully persisted
        static::assertNotNull($result->getUid());
        // enabled
        static::assertEquals(0, $result->getDisable());
        // additional: query from db
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $this->frontendUserRepository->findByEmail($userData['email'])->getFirst());
    }



    /**
     * @test
     */
    public function registerWithAlreadyExistingEmailOfEnabledFrontendUserReturnsTheExistingFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing e-mail address of an ENABLED frontendUser
         * When a user with existing email is registered
         * Then the already existing user is returned without any changes
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // email does not exists
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        static::assertEquals(0, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->registrationService->register(['email' => $existingFrontendUser->getEmail()]);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        // still enabled. Is a not unimportant check, because new user would be disabled by default
        static::assertEquals(0, $result->getDisable());

        // still the same UID
        static::assertEquals($existingFrontendUser->getUid(), $result->getUid());
    }



    /**
     * @test
     */
    public function registerWithAlreadyExistingEmailOfDisabledFrontendUserReturnsUnchancedFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing e-mail address of an DISABLED frontendUser
         * When a user with this email is registered
         * Then the already existing user is returned without any changes (still disabled)
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous(1);

        // email does not exists
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        static::assertEquals(1, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->registrationService->register(['email' => $existingFrontendUser->getEmail()]);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        // still disabled. Is a not unimportant check, because new user would be disabled by default
        static::assertEquals(1, $result->getDisable());

        // still the same UID
        static::assertEquals($existingFrontendUser->getUid(), $result->getUid());
    }



    /**
     * @test
     */
    public function registerWithEnabledFlagAlreadyExistingEmailOfDisabledFrontendUserReturnsEnabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing e-mail address of an DISABLED frontendUser
         * When a user with this email is registered with ENABLED flag
         * Then the already existing user is returned as enabled user
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous(1);

        // email does not exists
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        static::assertEquals(1, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->registrationService->register(['email' => $existingFrontendUser->getEmail()], true);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        // now enabled
        static::assertEquals(0, $result->getDisable());

        // still the same UID
        static::assertEquals($existingFrontendUser->getUid(), $result->getUid());
    }


    /**
     * @test
     */
    public function registerAdditionalDataWithRegisteredFrontendUserReturnsFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing e-mail address of an DISABLED frontendUser
         * When a user with this email is registered with ENABLED flag
         * Then the already existing user is returned as enabled user
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByUid(1);

        $additionalData = [
            'some' => 'thing',
            'any' => 'thing',
            'what' => 'ever'
        ];

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->registrationService->register(['email' => $existingFrontendUser->getEmail()], true, $additionalData);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        // now enabled
        static::assertEquals(0, $result->getDisable());

        // still the same UID
        static::assertEquals($existingFrontendUser->getUid(), $result->getUid());
    }






    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}