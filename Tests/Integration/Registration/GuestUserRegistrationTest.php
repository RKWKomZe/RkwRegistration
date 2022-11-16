<?php
namespace RKW\RkwRegistration\Tests\Integration\Registration;

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
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\GuestUserRepository;
use RKW\RkwRegistration\Domain\Repository\OptInRepository;
use RKW\RkwRegistration\Domain\Repository\ConsentRepository;
use RKW\RkwRegistration\Registration\GuestUserRegistration;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


/**
 * GuestUserRegisterTest
 *
 * @author Steffen Krogel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GuestUserRegistrationTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/GuestUserRegistrationTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_ajax',
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [
        'filemetadata'
    ];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\GuestUserRepository
     */
    private $guestUserRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    private $frontendUserGroupRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\OptInRepository
     */
    private $optInRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ConsentRepository
     */
    private $consentRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * @var \RKW\RkwRegistration\Registration\GuestUserRegistration
     */
    private $fixture;


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
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** \RKW\RkwRegistration\Register\FrontendUser\GuestUserRegistration $fixture */
        $this->fixture = $this->objectManager->get(GuestUserRegistration::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\GuestUserRepository guestUserRepository */
        $this->guestUserRepository = $this->objectManager->get(GuestUserRepository::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository frontendUserGroupRepository */
        $this->frontendUserGroupRepository = $this->objectManager->get(FrontendUserGroupRepository::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\ConsentRepository consentRepository */
        $this->consentRepository = $this->objectManager->get(ConsentRepository::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\OptInRepository optInRepository */
        $this->optInRepository = $this->objectManager->get(OptInRepository::class);
    }

    #==============================================================================
    /**
     * @test
     * @throws \Exception
     */
    public function getContextAwareFrontendUserRepositoryReturnsGuestUserRepository ()
    {
        /**
         * Scenario:
         *
         * Given a guestUser-object of type \RKW\RkwRegistration\Domain\Model\GuestUser
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called before
         * When the method is called
         * Then an instance of \RKW\RkwRegistration\Domain\Repository\GuestUserRepository is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        $guestUser->setEmail('test@test.de');
        $this->fixture->setFrontendUser($guestUser);

        self::assertInstanceOf(GuestUserRepository::class, $this->fixture->getContextAwareFrontendUserRepository());
    }

    #==============================================================================

    /**
     * @test
     */
    public function constructSetsFrontendUser ()
    {
        /**
         * Scenario:
         *
         * When the class is instantiated
         * Then getFrontendUser returns an instance of \RKW\RkwRegistration\Domain\Model\GuestUser
         */

        self::assertInstanceOf(GuestUser::class, $this->fixture->getFrontendUser());
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserUsesRandomStringAsUsername()
    {
        /**
         * Scenario:
         *
         * Given a guestUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * When the method is called
         * Then the username-property of the guestUser-object is set to a random string
         * Then this random string is no email-address
         * Then this random string is unique in 10.000 calls
         * Then the email-property of the guestUser-object is empty
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        $guestUser->setEmail('test@test.de');
        $guestUser->setUsername('tester@test.de');

        $this->fixture->setFrontendUser($guestUser);

        self::assertNotEquals('test@test.de', $guestUser->getUsername());
        self::assertStringNotContainsString('@', $guestUser->getUsername());

        $arrayOfNames = [];
        for ($i = 1; $i <= 10000; $i++) {
            $guestUser = GeneralUtility::makeInstance(GuestUser::class);
            $this->fixture->setFrontendUser($guestUser);
            $username = $guestUser->getUsername();

            self::assertNotContains($username, $arrayOfNames);
            $arrayOfNames[] = $guestUser->getUsername();
        }
        self::assertEmpty($guestUser->getEmail());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserSetsLanguageKey ()
    {
        /**
         * Scenario:
         *
         * Given is guestUser-object
         * Given this object has no value for the languageKey-property set
         * When the method is called
         * Then the languageKey-property of the guestUser-object is set to the current time
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);

        $this->fixture->setFrontendUser($guestUser);
        self::assertEquals('ru', $guestUser->getTxRkwregistrationLanguageKey());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserKeepsLanguageKey ()
    {
        /**
         * Scenario:
         *
         * Given is guestUser-object
         * Given this object has a value for the LanguageKey-property set
         * When the method is called
         * Then the LanguageKey-property of the guestUser-object is not changed
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        $guestUser->setTxRkwregistrationLanguageKey('it');

        $this->fixture->setFrontendUser($guestUser);
        self::assertEquals('it', $guestUser->getTxRkwregistrationLanguageKey());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserSetsCrdateOnNewObject ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted guestUser-object
         * Given this object has a value for the crdate-property set
         * When the method is called
         * Then the crdate-property of the guestUser-object is set to the current time
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        $guestUser->setCrdate(10);

        $this->fixture->setFrontendUser($guestUser);
        self::assertGreaterThan(time() -5, $guestUser->getCrdate());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserKeepsCrdateOnExistingObject ()
    {
        /**
         * Scenario:
         *
         * Given is guestUser-object
         * Given this object has a value for the crdate-property set
         * When the method is called
         * Then the crdate-property of the guestUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = $this->guestUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($guestUser);
        self::assertEquals(10, $guestUser->getCrdate());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserSetsDisableOnNewObject ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted guestUser-object
         * Given this object has a value for the disable-property set
         * When the method is called
         * Then the disable-property is set to value one
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        $guestUser->setDisable(0);

        $this->fixture->setFrontendUser($guestUser);
        self::assertEquals(1, $guestUser->getDisable());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserKeepsDisabledOnExistingObject ()
    {
        /**
         * Scenario:
         *
         * Given a persisted guestUser-object
         * Given this object has a value for the disable-property set
         * When the method is called
         * Then the disable-property of the guestUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = $this->guestUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($guestUser);
        self::assertEquals(0, $guestUser->getDisable());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserSetsRegisterRemoteIpOnNewObject ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted guestUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a value for the registerRemoteIp-property set
         * When the method is called
         * Then the registerRemoteIp-property of the guestUser-object is set to the current IP
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        $guestUser->setEmail('test@test.de');
        $guestUser->setTxRkwregistrationRegisterRemoteIp('10');

        $this->fixture->setFrontendUser($guestUser);
        self::assertEquals('127.0.0.1', $guestUser->getTxRkwregistrationRegisterRemoteIp());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserKeepsRegisterRemoteIpOnExistingObject ()
    {
        /**
         * Scenario:
         *
         * Given a persisted guestUser-object
         * Given this object has a value for the registerRemoteIp-property set
         * When the method is called
         * Then the registerRemoteIp-property of the guestUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = $this->guestUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($guestUser);
        self::assertEquals('1.2.3.4', $guestUser->getTxRkwregistrationRegisterRemoteIp());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserSetsEndtimeOnNewObject ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted guestUser-object
         * Given this object has a value for the endtime-property set
         * When the method is called
         * Then the endtime-property of the guestUser-object is set to the value from the typoscript-configuration
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        $guestUser->setEndtime(10);

        $this->fixture->setFrontendUser($guestUser);
        self::assertGreaterThanOrEqual(time() + (intval(7 * 24 * 60 * 60) -5), $guestUser->getEndtime());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserKeepsEndtimeOnExistingObject ()
    {
        /**
         * Scenario:
         *
         * Given a persisted guestUser-object
         * Given this object has a value for the endtime-property set
         * When the method is called
         * Then the endtime-property of the guestUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = $this->guestUserRepository->findByIdentifier(10);
        $time = time();
        $guestUser->setEndtime($time);

        $this->fixture->setFrontendUser($guestUser);
        self::assertEquals($time, $guestUser->getEndtime());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserResetsFrontendUserPersisted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted guestUser-object
         * Given the method has been called before with this persisted guestUser-object
         * Given a non-persisted guestUser-object
         * When the method is called with that non-persisted guestUser-object as parameter
         * Then getFrontendUserPersisted returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = $this->guestUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($guestUser);
        self::assertInstanceOf(GuestUser::class, $this->fixture->getFrontendUserPersisted());

        $guestUser = GeneralUtility::makeInstance(GuestUser::class);

        $this->fixture->setFrontendUser($guestUser);
        self::assertNull($this->fixture->getFrontendUserPersisted());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserResetsFrontendUserToken ()
    {
        /**
         * Scenario:
         *
         * Given a persisted guestUser-object
         * Given setFrontendUserToken has been called before with a random token
         * When the method is called
         * Then getFrontendUserToken returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = $this->guestUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUserToken('test');
        $this->fixture->setFrontendUser($guestUser);

        self::assertEmpty($this->fixture->getFrontendUserToken());

    }
    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserResetsOptInPersisted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted guestUser-object
         * Given a non-persisted guestUser-object
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the guestUser-object
         * Given this object has a valid token set
         * Given setFrontendUserToken has been called with that token before
         * Given getOptInPersisted was called before
         * Given getOptInPersisted returned the corresponding optIn-object
         * When the method is called with the non-persisted guestUser-object as parameter
         * Then getOptInPersisted returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check20.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $result */
        $result = $this->fixture->getOptInPersisted();

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $OptIn */
        $optIn = $this->optInRepository->findByIdentifier(20);
        self::assertEquals($optIn, $result);

        $guestUser = GeneralUtility::makeInstance(GuestUser::class);

        $this->fixture->setFrontendUser($guestUser);
        self::assertNull($this->fixture->getOptInPersisted());

    }
    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationThrowsNoExceptionIfNoFrontendUserSet ()
    {
        /**
         * Scenario:
         *
         * Given no guestUser-object
         * When the method is called
         * Then boolean is returned
         */;

        self::assertIsBool($this->fixture->startRegistration());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationThrowsExceptionIfUserLoggedIn ()
    {
        /**
         * Scenario:
         *
         * Given a persisted guestUser-object
         * Given this guestUser is logged in
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1661332376
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1661332376);

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check30.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->guestUserRepository->findByIdentifier(30);
        $frontendUserGroup = $this->frontendUserGroupRepository->findByIdentifier(30);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
        FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup);

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->startRegistration();

        FrontendSimulatorUtility::resetFrontendEnvironment();

    }

    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationReturnsFalseIfUserNotNew ()
    {
        /**
         * Scenario:
         *
         * Given a persisted guestUser-object
         * When the method is called
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->guestUserRepository->findByIdentifier(10);
        $this->fixture->setFrontendUser($frontendUser);

        self::assertFalse($this->fixture->startRegistration());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationReturnsTrueIfUserNew ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted guestUser-object
         * When the method is called
         * Then true is returned
         * Then the guestUser is persisted
         * Then the disable-property is set to true
         * Then the password property is set
         * Then the tempPlaintextPassword-property is not set
         * Then no consent-object is created
         */

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);

        $this->fixture->setFrontendUser($guestUser);
        self::assertTrue($this->fixture->startRegistration());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $guestUserDatabase = $this->guestUserRepository->findByIdentifier(1);

        self::assertTrue($guestUserDatabase->getDisable());
        self::assertNotEmpty($guestUserDatabase->getPassword());
        self::assertEmpty($guestUserDatabase->getTempPlaintextPassword());
        self::assertEquals(0, $this->consentRepository->countAll());

    }


    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
