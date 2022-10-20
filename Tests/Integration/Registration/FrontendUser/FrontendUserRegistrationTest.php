<?php
namespace RKW\RkwRegistration\Tests\Integration\Registration\FrontendUser;

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
 *
 */

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Model\OptIn;
use RKW\RkwRegistration\Domain\Repository\BackendUserRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\OptInRepository;
use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;


/**
 * FrontendUserRegisterTest
 *
 * @author Steffen Krogel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRegistrationTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserRegistrationTest/Fixtures';

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
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    private $frontendUserGroupRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\BackendUserRepository
     */
    private $backendUserRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\OptInRepository
     */
    private $optInRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository
     */
    private $privacyRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;

    /**
     * @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration
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
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_basics/Configuration/TypoScript/constants.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/constants.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** \RKW\RkwRegistration\Register\FrontendUser\FrontendUserRegistration $fixture */
        $this->fixture = $this->objectManager->get(FrontendUserRegistration::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository frontendUserRepository */
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository frontendUserGroupRepository */
        $this->frontendUserGroupRepository = $this->objectManager->get(FrontendUserGroupRepository::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\BackendUserRepository backendUserRepository */
        $this->backendUserRepository = $this->objectManager->get(BackendUserRepository::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\OptInRepository optInRepository */
        $this->optInRepository = $this->objectManager->get(OptInRepository::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository privacyRepository */
        $this->privacyRepository = $this->objectManager->get(PrivacyRepository::class);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'mail@default.rkw';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW Default';

    }


    #==============================================================================
    /**
     * @test
     * @throws \Exception
     */
    public function getContextAwareFrontendUserRepositoryReturnsFrontendUserRepository ()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser-object of type \RKW\RkwRegistration\Domain\Model\FrontendUser
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called before
         * When the method is called
         * Then an instance of \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');
        $this->fixture->setFrontendUser($frontendUser);

        static::assertInstanceOf(FrontendUserRepository::class, $this->fixture->getContextAwareFrontendUserRepository());
    }


    #==============================================================================



    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserThrowsExceptionOnInvalidEmail ()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser-object
         * Given this object has an invalid value for the email-property set
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1407312133)
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1407312133);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('TEST');

        $this->fixture->setFrontendUser($frontendUser);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserThrowsExceptionOnInvalidUsername ()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has an invalid value for the username-property set
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1407312134
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1407312134);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');
        $frontendUser->setUsername('TEST');

        $this->fixture->setFrontendUser($frontendUser);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserThrowsExceptionIfAnotherUserLoggedIn ()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has no value for the username-property set
         * Given another frontendUser is logged in
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1666014579
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1666014579);

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check30.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(30);
        $frontendUserGroup = $this->frontendUserGroupRepository->findByIdentifier(30);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
        FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@rkw.de');

        $this->fixture->setFrontendUser($frontendUser);

        FrontendSimulatorUtility::resetFrontendEnvironment();

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserNormalizesEmailAndUsername ()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser-object
         * Given this object has a value for the email-property with uppercase set
         * Given this object has a value for the username-property with uppercase set
         * When the method is called
         * Then the email-property is changed to lowercase
         * Then the username-property is changed to lowercase*
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('TEST@TEST.DE');
        $frontendUser->setUsername('TESTER@TEST.DE');

        $this->fixture->setFrontendUser($frontendUser);

        static::assertEquals('test@test.de', $frontendUser->getEmail());
        static::assertEquals('tester@test.de', $frontendUser->getUsername());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserUsesEmailAsUsername ()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has no value for the username-property set
         * When the method is called
         * Then the username-property of the frontendUser-object is set to the value of the email-property
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals('test@test.de', $frontendUser->getUsername());

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
         * Given is frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has no value for the languageKey-property set
         * When the method is called
         * Then the languageKey-property of the frontendUser-object is set to the current time
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals('ru', $frontendUser->getTxRkwregistrationLanguageKey());

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
         * Given is frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a value for the LanguageKey-property set
         * When the method is called
         * Then the LanguageKey-property of the frontendUser-object is not changed
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');
        $frontendUser->setTxRkwregistrationLanguageKey('it');

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals('it', $frontendUser->getTxRkwregistrationLanguageKey());
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
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a value for the crdate-property set
         * When the method is called
         * Then the crdate-property of the frontendUser-object is set to the current time
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');
        $frontendUser->setCrdate(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertGreaterThan(time() -5, $frontendUser->getCrdate());

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
         * Given is frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given this object has a value for the crdate-property set
         * When the method is called
         * Then the crdate-property of the frontendUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals(10, $frontendUser->getCrdate());

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
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a value for the disable-property set
         * When the method is called
         * Then the disable-property is set to value one
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');
        $frontendUser->setDisable(0);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals(1, $frontendUser->getDisable());

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
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given this object has a value for the disable-property set
         * When the method is called
         * Then the disable-property of the frontendUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals(0, $frontendUser->getDisable());

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
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a value for the registerRemoteIp-property set
         * When the method is called
         * Then the registerRemoteIp-property of the frontendUser-object is set to the current IP
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');
        $frontendUser->setTxRkwregistrationRegisterRemoteIp('10');

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals('127.0.0.1', $frontendUser->getTxRkwregistrationRegisterRemoteIp());

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
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the username-property set
         * Given this object has a value for the registerRemoteIp-property set
         * When the method is called
         * Then the registerRemoteIp-property of the frontendUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals('1.2.3.4', $frontendUser->getTxRkwregistrationRegisterRemoteIp());

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
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a value for the endtime-property set
         * When the method is called
         * Then the endtime-property of the frontendUser-object is set to the value from the typoscript-configuration
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');
        $frontendUser->setEndtime(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertGreaterThanOrEqual(time() + (intval(7 * 24 * 60 * 60) -5), $frontendUser->getEndtime());

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
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given this object has a value for the endtime-property set
         * When the method is called
         * Then the endtime-property of the frontendUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        $time = time();
        $frontendUser->setEndtime($time);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals($time, $frontendUser->getEndtime());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserSetsPidOnNewObject ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a value for the pid-property set
         * When the method is called
         * Then the pid-property of the frontendUser-object is set to the value from the typoscript-configuration
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@test.de');
        $frontendUser->setPid(2222);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals(99, $frontendUser->getPid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserKeepsPidOnExistingObject ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given this object has a value for the pid-property set
         * When the method is called
         * Then the pid-property of the frontendUser-object is not changed
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertEquals(88, $frontendUser->getPid());
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
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given the method has been called before with this persisted frontendUser-object
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * When the method is called with that non-persisted frontendUser-object as parameter
         * Then getFrontendUserPersisted returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertSame($frontendUser, $this->fixture->getFrontendUserPersisted());

        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('my@test.de');

        $this->fixture->setFrontendUser($frontendUser);
        static::assertNull($this->fixture->getFrontendUserPersisted());

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
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUserToken has been called before with a random token
         * When the method is called
         * Then getFrontendUserToken returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUserToken('test');
        $this->fixture->setFrontendUser($frontendUser);

        static::assertEmpty($this->fixture->getFrontendUserToken());

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
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given setFrontendUserToken has been called with that token before
         * Given getOptInPersisted was called before
         * Given getOptInPersisted returned the corresponding optIn-object
         * When the method is called with the non-persisted frontendUser-object as parameter
         * Then getOptInPersisted returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check40.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $result */
        $result = $this->fixture->getOptInPersisted();

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $OptIn */
        $optIn = $this->optInRepository->findByIdentifier(40);
        static::assertEquals($optIn, $result);

        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('my@test.de');

        $this->fixture->setFrontendUser($frontendUser);
        static::assertNull($this->fixture->getOptInPersisted());

    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserTokenThrowsExceptionIfAnotherUserLoggedIn ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has no value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given another frontendUser is logged in
         * When the method is called with
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1666021555
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1666021555);

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check150.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(151);
        $frontendUserGroup = $this->frontendUserGroupRepository->findByIdentifier(151);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
        FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $result */
        $this->fixture->getOptInPersisted();

        FrontendSimulatorUtility::resetFrontendEnvironment();

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserTokenResetsFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called before with that object
         * When the method is called with a random token
         * Then getFrontendUser returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->setFrontendUserToken('abcdef');

        static::assertNull($this->fixture->getFrontendUser());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserTokenResetsFrontendUserPersisted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called before with this persisted frontendUser-object
         * Given getFrontendUserPersisted has been called before
         * Given getFrontendUserPersisted returned the set frontendUser-object
         * When the method is called with a random token
         * Then getFrontendUserPersisted returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertSame($frontendUser, $this->fixture->getFrontendUserPersisted());

        $this->fixture->setFrontendUserToken('abcdef');
        static::assertNull($this->fixture->getFrontendUserPersisted());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserTokenResetsOptInPersisted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given setFrontendUserToken has been called with that token before
         * Given getOptInPersisted was called before
         * Given getOptInPersisted returned the corresponding optIn-object
         * When the method is called with a random token
         * Then getOptInPersisted returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check40.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $result */
        $result = $this->fixture->getOptInPersisted();

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $OptIn */
        $optIn = $this->optInRepository->findByIdentifier(40);
        static::assertEquals($optIn, $result);

        $this->fixture->setFrontendUserToken('abcdef');
        static::assertNull($this->fixture->getOptInPersisted());

    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserUpdateReturnsOnlyDirtyProperties ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given two properties of this object are set to new values
         * When the method is called
         * Then an array is returned
         * Then this array contains only the new set properties
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        $frontendUser->setCity('test');
        $frontendUser->setZip(123456);

        $this->fixture->setFrontendUserUpdate($frontendUser);
        $result = $this->fixture->getFrontendUserUpdate();

        static::assertIsArray($result);
        static::assertCount(2, $result);
        static::assertEquals('test', $result['city']);
        static::assertEquals(123456, $result['zip']);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserUpdateReturnsOnlyDirtyPropertiesForNewObject ()
    {
        /**
         * Scenario:
         *
         * Given a new frontendUser-object
         * Given two properties of this object are set to new values
         * When the method is called
         * Then an array is returned
         * Then this array contains only the new set properties
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setCity('test');
        $frontendUser->setZip(123456);

        $this->fixture->setFrontendUserUpdate($frontendUser);
        $result = $this->fixture->getFrontendUserUpdate();

        static::assertIsArray($result);
        static::assertCount(2, $result);
        static::assertEquals('test', $result['city']);
        static::assertEquals(123456, $result['zip']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setFrontendUserUpdateIgnoresSecurityRelevantProperties ()
    {
        /**
         * Scenario:
         *
         * Given a new frontendUser-object
         * Given two properties of this object are set to new values
         * Given those to properties are username and password
         * When the method is called
         * Then an array is returned
         * Then this array is empty
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setUsername('test');
        $frontendUser->setPassword('test');

        $this->fixture->setFrontendUserUpdate($frontendUser);
        $result = $this->fixture->getFrontendUserUpdate();
var_dump($result);
        static::assertIsArray($result);
        static::assertCount(0, $result);
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedLoadsObjectFromDatabase ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given this object has a value for the endtime-property set that is not persisted
         * Given setFrontendUser has been called with that object as parameter
         * When the method is called
         * Then getFrontendUserPersisted returns the given frontendUser-object newly loaded from the database
         * Then this object has no endtime-property set
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        $time = time();
        $frontendUser->setEndtime($time);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        static::assertSame($frontendUserDatabase, $result);
        static::assertEquals(0, $result->getEndtime());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedLoadsObjectViaUid ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given the value for the email-property set to a value that is not identical to the one in the database
         * Given the value for the username-property set to a value that is not identical to the one in the database
         * Given setFrontendUser has been called with that object as parameter
         * When the method is called
         * Then getFrontendUserPersisted returns the frontendUser-object from the database
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        $frontendUser->setEmail('bubber@test.de');
        $frontendUser->setUsername('bubber@test.de');

        $this->fixture->setFrontendUser($frontendUser);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifier(10);
        static::assertEquals($frontendUserDatabase, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedLoadsObjectViaUidIfDisabled ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object is disabled
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given the value for the email-property set to a value that is not identical to the one in the database
         * Given the value for the username-property set to a value that is not identical to the one in the database
         * Given setFrontendUser has been called with that object as parameter
         * When the method is called
         * Then getFrontendUserPersisted returns the frontendUser-object from the database
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(20);
        $frontendUser->setEmail('bubber@test.de');
        $frontendUser->setUsername('bubber@test.de');

        $this->fixture->setFrontendUser($frontendUser);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifierIncludingDisabled(20);
        static::assertEquals($frontendUserDatabase, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedLoadsObjectViaUsername ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object X
         * Given this object X has a valid value for the email-property set
         * Given this object X has a valid value for the username-property set
         * Given the value for the email-property set to a value that is not identical to the one in the database
         * Given the value for the username-property set to a value that is not identical to the one in the database
         * Given a non-persisted frontendUser-object Y
         * Given the value for the email-property of object Y set to a value that is identical to the one of object X
         * Given the value for the username-property of object Y set to a value that is identical to the one of object X
         * Given setFrontendUser has been called with that object as parameter
         * When the method is called
         * Then getFrontendUserPersisted returns the frontendUser-object from the database
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('lauterbach@spd.de');
        $frontendUser->setUsername('lauterbach@spd.de');

        $this->fixture->setFrontendUser($frontendUser);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifier(10);
        static::assertEquals($frontendUserDatabase, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedLoadsObjectViaUsernameIfDisabled ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object X
         * Given this object is disabled
         * Given this object X has a valid value for the email-property set
         * Given this object X has a valid value for the username-property set
         * Given the value for the email-property set to a value that is not identical to the one in the database
         * Given the value for the username-property set to a value that is not identical to the one in the database
         * Given a non-persisted frontendUser-object Y
         * Given the value for the email-property of object Y set to a value that is identical to the one of object X
         * Given the value for the username-property of object Y set to a value that is identical to the one of object X
         * Given setFrontendUser has been called with that object as parameter
         * When the method is called
         * Then getFrontendUserPersisted returns the frontendUser-object from the database
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('lauterbach@spd.de');
        $frontendUser->setUsername('lauterbach@spd.de');

        $this->fixture->setFrontendUser($frontendUser);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifierIncludingDisabled(20);
        static::assertEquals($frontendUserDatabase, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedLoadsObjectViaToken ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given setFrontendUserToken has been called with that token
         * When the method is called
         * Then getFrontendUserPersisted returns the frontendUser-object from the database
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check40.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifier(40);
        static::assertEquals($frontendUserDatabase, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedLoadsObjectViaTokenIfDisabled ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object is disabled
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given setFrontendUserToken has been called with that token
         * When the method is called
         * Then getFrontendUserPersisted returns the frontendUser-object from the database
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check50.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifierIncludingDisabled(50);
        static::assertEquals($frontendUserDatabase, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedLoadsObjectViaTokenIfOptInDeleted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given this object is marked as deleted
         * Given setFrontendUserToken has been called with that token
         * When the method is called
         * Then getFrontendUserPersisted returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check90.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifierIncludingDisabled(90);
        static::assertEquals($frontendUserDatabase, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getFrontendUserPersistedDoesNotLoadObjectViaTokenIfOptInExpired ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given this object has an expired endtime-property
         * Given setFrontendUserToken has been called with that token
         * When the method is called
         * Then getFrontendUserPersisted returns null
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check60.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->fixture->getFrontendUserPersisted();

        static::assertNull($result);
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function getOptInPersistedLoadsObjectViaToken ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given setFrontendUserToken has been called with that token
         * When the method is called
         * Then the optIn-object is returned
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check40.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $result */
        $result = $this->fixture->getOptInPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $OptIn */
        $optIn = $this->optInRepository->findByIdentifier(40);
        static::assertEquals($optIn, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getOptInPersistedLoadsObjectViaTokenIfDeleted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given this object is deleted
         * Given setFrontendUserToken has been called with that token
         * When the method is called
         * Then the optIn-object is returned
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check90.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $result */
        $result = $this->fixture->getOptInPersisted();

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $OptIn */
        $optIn = $this->optInRepository->findByIdentifierIncludingDeleted(90);
        static::assertEquals($optIn, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getOptInPersistedDoesNotLoadObjectViaTokenIfOptInExpired ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given a persisted optIn-object
         * Given the frontendUserUid-property of this object refers to the frontendUser-object
         * Given this object has a valid token set
         * Given this object has an expired endtime-property
         * Given setFrontendUserToken has been called with that token
         * When the method is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check60.xml');

        $this->fixture->setFrontendUserToken('test');

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $result */
        $result = $this->fixture->getOptInPersisted();

        static::assertNull($result);
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function createOptInThrowsExceptionOnNonPersistedFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted frontendUser-object
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1659691717
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1659691717);

        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->createOptIn();

    }

    /**
     * @test
     * @throws \Exception
     */
    public function createOptInAddsOptInObjectWithAllData ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object A
         * Given A has a valid value for the email-property set
         * Given A has a valid value for the username-property set
         * Given setFrontendUser has been called before with the A as parameter
         * Given a new frontendUser-object B
         * Given this frontendUser-object has set to properties with new values
         * Given setFrontendUserUpdate has been called before with B
         * Given setCategory has been called before with a string as parameter
         * Given setData has been called before with an array as parameter
         * When the method is called
         * Then an instance of type \RKW\RkwRegistration\Domain\Model\OptIn is returned
         * Then this instance is persisted
         * Then this instance has the frontendUserUid-property set to the given A
         * Then this instance has the frontendUserUpdate-property set as array of the two properties set to B
         * Then this instance has the category-property set to the given category
         * Then this instance has the data-property set to the given data-array
         * Then this instance has the tokenUser-property set
         * Then this instance has the tokenYes-property set
         * Then this instance has the tokenNo-property set
         * Then this instance has the endtime-property set according to the typoscript-configuration
         * Then this instance has the adminApproved-property set to true
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        $this->fixture->setFrontendUser($frontendUser);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUserUpdate = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUserUpdate->setCity('Herborn');
        $frontendUserUpdate->setZip(35745);
        $this->fixture->setFrontendUserUpdate($frontendUserUpdate);

        $this->fixture->setCategory('test');
        $this->fixture->setData(['TestenKey' => 'TestenValue']);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optInReturn = $this->fixture->createOptIn();

        static::assertInstanceOf(OptIn::class, $optInReturn);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(1);
        static::assertEquals($optInReturn->getUid(), $optIn->getUid());

        static::assertEquals(10, $optIn->getFrontendUserUid());
        static::assertEquals(['city' => 'Herborn', 'zip' => '35745'], $optIn->getFrontendUserUpdate());
        static::assertEquals('test', $optIn->getCategory());
        static::assertEquals(['TestenKey' => 'TestenValue'], $optIn->getData());
        static::assertNotEmpty($optIn->getTokenUser());
        static::assertNotEmpty($optIn->getTokenYes());
        static::assertNotEmpty($optIn->getTokenNo());
        static::assertGreaterThan(time() + (intval(7 * 24 * 60 * 60) -5), $optIn->getEndtime());
        static::assertTrue($optIn->getAdminApproved());


    }

    /**
     * @test
     * @throws \Exception
     */
    public function createOptInAddsPrivacyObject ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given setFrontendUser has been called before with the frontendUser-object as parameter
         * Given setCategory has been called before with a string as parameter
         * Given setData has been called before with an array as parameter
         * Given a request-object is set
         * When the method is called
         * Then an instance of type \RKW\RkwRegistration\Domain\Model\OptIn is returned
         * Then this instance is persisted
         * Then a privacy-object is created
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->setCategory('test');
        $this->fixture->setData(['TestenKey' => 'TestenValue']);
        $this->fixture->setRequest(GeneralUtility::makeInstance(Request::class));

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->fixture->createOptIn();

        static::assertInstanceOf(OptIn::class, $optIn);
        static::assertNotEmpty($optIn->getUid());

        self::assertEquals(1, $this->privacyRepository->countAll());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function createOptInUpdatesOptInPersisted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given setFrontendUser has been called before with the frontendUser-object as parameter
         * Given setCategory has been called before with a string as parameter
         * Given setData has been called before with an array as parameter
         * Given getOptInPersisted has been called before
         * Given that call returned null
         * When the method is called
         * Then an instance of type \RKW\RkwRegistration\Domain\Model\OptIn is returned
         * Then getOptInPersisted returns the same instance
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->setCategory('test');
        $this->fixture->setData(['TestenKey' => 'TestenValue']);

        static::assertNull($this->fixture->getOptInPersisted());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->fixture->createOptIn();

        static::assertInstanceOf(OptIn::class, $optIn);
        static::assertSame($optIn, $this->fixture->getOptInPersisted());


    }

    /**
     * @test
     * @throws \Exception
     */
    public function createOptInAddsAdminsTokensIfApprovalsAreSet ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given setFrontendUser has been called before with the frontendUser-object as parameter
         * Given two persisted backendUser-objects
         * Given this to objects are set as admins via setApproval
         * When the method is called
         * Then an instance of type \RKW\RkwRegistration\Domain\Model\OptIn is returned
         * Then this object is persisted
         * Then this optIn-object has the adminTokenYes-property set
         * Then this optIn-object has the adminTokenNo-property set
         * Then this optIn-object has the adminApproved-property set to false
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check100.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(100);

        /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUserOne */
        $backendUserOne = $this->backendUserRepository->findByIdentifier(100);

        /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUserTwo */
        $backendUserTwo = $this->backendUserRepository->findByIdentifier(101);

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->addApproval($backendUserOne);
        $this->fixture->addApproval($backendUserTwo);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optInReturn = $this->fixture->createOptIn();

        static::assertInstanceOf(OptIn::class, $optInReturn);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(1);
        static::assertEquals($optInReturn->getUid(), $optIn->getUid());

        static::assertEquals(100, $optIn->getFrontendUserUid());
        static::assertNotEmpty($optIn->getAdminTokenYes());
        static::assertNotEmpty($optIn->getAdminTokenNo());
        static::assertFalse($optIn->getAdminApproved());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function createOptInSetsForeignTableAndUid ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given setFrontendUser has been called before with the frontendUser-object as parameter
         * Given setCategory has been called before with a string as parameter
         * Given setData has been called before with a persisted frontendUserGroup-object with uid = 1 as parameter
         * Given getOptInPersisted has been called before
         * Given that call returned null
         * When the method is called
         * Then an instance of type \RKW\RkwRegistration\Domain\Model\OptIn is returned
         * Then this instance has the foreignTable-property set to fe_groups
         * Then this instance has the foreignUid-property set to 1
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check140.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(140);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByIdentifier(140);

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->setCategory('test');
        $this->fixture->setData($frontendUserGroup);

        static::assertNull($this->fixture->getOptInPersisted());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->fixture->createOptIn();

        static::assertInstanceOf(OptIn::class, $optIn);
        static::assertEquals('fe_groups', $optIn->getForeignTable());
        static::assertEquals(140, $optIn->getForeignUid());



    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationThrowsExceptionIfNoFrontendUserSet ()
    {
        /**
         * Scenario:
         *
         * Given no frontendUser-object
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1434997734
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1434997734);

        $this->fixture->startRegistration();

    }


    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationThrowsExceptionIfNewRegistrationForLoggedInUser ()
    {
        /**
         * Scenario:
         *
         * Given a non-persistent frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * Given setData has not been called before
         * Given a frontendUser is logged in
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1659691717
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1659691717);

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check30.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(30);
        $frontendUserGroup = $this->frontendUserGroupRepository->findByIdentifier(30);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
        FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@rkw.de');

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->startRegistration();

        FrontendSimulatorUtility::resetFrontendEnvironment();

    }

    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationCreatesNoOptInForExistingUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * Given no additional data is set
         * When the method is called
         * Then false is returned
         * Then no optIn is created
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);

        self::assertFalse($this->fixture->startRegistration());
        self::assertEquals(0, $this->optInRepository->countAll());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationCreatesOptInForExistingUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * Given additional data is set
         * When the method is called
         * Then the password-property is not overridden
         * Then the disable-property is false
         * Then true is returned
         * Then an optIn is created
         * Then no privacy-object is created
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->setData(['test']);

        self::assertTrue($this->fixture->startRegistration());
        self::assertEquals('test', $frontendUser->getPassword());
        self::assertFalse($frontendUser->getDisable());
        self::assertEquals(1, $this->optInRepository->countAll());
        self::assertEquals(0, $this->privacyRepository->countAll());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationCreatesOptInAndPrivacyForExistingUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object has a valid value for the username-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * Given additional data is set
         * Given a request-object is set
         * When the method is called
         * Then true is returned
         * Then the password is not overridden
         * Then the disable-property is false*
         * Then an optIn is created
         * Then a privacy-object is created
         */

        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifier(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail($frontendUserDatabase->getEmail());

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->setData(['test']);
        $this->fixture->setRequest(GeneralUtility::makeInstance(Request::class));

        self::assertTrue($this->fixture->startRegistration());
        self::assertEquals('test', $frontendUser->getPassword());
        self::assertFalse($frontendUser->getDisable());
        self::assertEquals(1, $this->optInRepository->countAll());
        self::assertEquals(1, $this->privacyRepository->countAll());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationCreatesOptInForNewUser ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then true is returned
         * Then the frontendUser-object is persisted
         * Then the password is set to a newly created
         * Then the tempPlaintextPassword-property is set
         * Then the disable-property is set to true
         * Then an optIn-object is created
         * Then no privacy-object is created
         */
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@testen.de');

        $this->fixture->setFrontendUser($frontendUser);
        self::assertTrue($this->fixture->startRegistration());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifier(1);
        self::assertEquals($frontendUserDatabase->getUsername(), $frontendUser->getUsername());
        self::assertNotEmpty($frontendUserDatabase->getPassword());
        self::assertNotEmpty($frontendUserDatabase->getTempPlaintextPassword());
        self::assertTrue($frontendUserDatabase->getDisable());

        self::assertEquals(1, $this->optInRepository->countAll());
        self::assertEquals(0, $this->privacyRepository->countAll());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function startRegistrationCreatesOptInAndPrivacyForNewUser ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * Given a request-object is set
         * When the method is called
         * Then true is returned
         * Then the frontendUser-object is persisted
         * Then the password is set to a newly created
         * Then the tempPlaintextPassword-property is set
         * Then the disable-property is true
         * Then an optIn-object is created
         * Then a privacy-object is created
         */
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@testen.de');
        $this->fixture->setRequest(GeneralUtility::makeInstance(Request::class));
        $this->fixture->setFrontendUser($frontendUser);

        self::assertTrue($this->fixture->startRegistration());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDatabase */
        $frontendUserDatabase = $this->frontendUserRepository->findByIdentifier(1);
        self::assertEquals($frontendUserDatabase->getUsername(), $frontendUser->getUsername());
        self::assertNotEmpty($frontendUserDatabase->getPassword());
        self::assertNotEmpty($frontendUserDatabase->getTempPlaintextPassword());
        self::assertTrue($frontendUserDatabase->getDisable());

        self::assertEquals(1, $this->optInRepository->countAll());
        self::assertEquals(1, $this->privacyRepository->countAll());

    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInThrowsExceptionIfNoFrontendUserTokenSet ()
    {
        /**
         * Scenario:
         *
         * Given no frontendUserToken
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1434997735
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1434997735);

        $this->fixture->validateOptIn('test');

    }

    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns404IfOptInNotFound ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object is disabled
         * Given a persisted optIn-object
         * Given this object has a valid tokenUser
         * Given this object has a valid tokenYes
         * Given this object has a valid tokenNo
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with a random token as parameter
         * When the method is called with the valid tokenYes-value as parameter
         * Then 404 is returned
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check70.xml');

        $this->fixture->setFrontendUserToken('abcdef');
        self::assertEquals(404, $this->fixture->validateOptIn('test_yes'));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns404IfFrontendUserNotFound ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object is deleted
         * Given a persisted optIn-object
         * Given this object has a valid tokenUser
         * Given this object has a valid tokenYes
         * Given this object has a valid tokenNo
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with the valid tokenUser-value as parameter
         * When the method is called with the valid tokenYes-value as parameter
         * Then 404 is returned
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check80.xml');

        $this->fixture->setFrontendUserToken('test');
        self::assertEquals(404, $this->fixture->validateOptIn('test_yes'));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns210IfMatchingTokenYesOnDeleted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given a persisted optIn-object
         * Given this object has a valid tokenUser-property
         * Given this object has a valid tokenYes-property
         * Given this object has a valid tokenNo-property
         * Given this object is deleted
         * Given a request-object is set
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with the valid tokenUser-value as parameter
         * When the method is called with the valid tokenYes-value as parameter
         * Then 210 is returned
         * Then no privacy-object is created
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check90.xml');

        $this->fixture->setFrontendUserToken('test');
        $this->fixture->setRequest(GeneralUtility::makeInstance(Request::class));

        self::assertEquals(210, $this->fixture->validateOptIn('test_yes'));
        self::assertEquals(0, $this->privacyRepository->countAll());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns220IfMatchingTokenYesWithoutAdminApproval ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object is disabled
         * Given a persisted optIn-object
         * Given this object has a valid tokenUser-property
         * Given this object has a valid tokenYes-property
         * Given this object has a valid tokenNo-property
         * Given this object has the adminApproved-property set to false
         * Given this object has the approved-property set to false
         * Given this object is deleted
         * Given a request-object is set
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with the valid tokenUser-value as parameter
         * When the method is called with the valid tokenYes-value as parameter
         * Then 220 is returned
         * Then a privacy-object is created
         * Then the approved-property of the optIn-object is set to true
         * Then the frontendUser is still disabled
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check110.xml');

        $this->fixture->setFrontendUserToken('test');
        $this->fixture->setRequest(GeneralUtility::makeInstance(Request::class));

        self::assertEquals(220, $this->fixture->validateOptIn('test_yes'));
        self::assertEquals(1, $this->privacyRepository->countAll());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifierIncludingDeleted(110);
        self::assertTrue($optIn->getApproved());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(110);
        self::assertTrue($frontendUser->getDisable());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns220IfMatchingAdminTokenYesWithoutApproval ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object is disabled
         * Given a persisted optIn-object
         * Given this object has a valid tokenUser-property
         * Given this object has a valid tokenYes-property
         * Given this object has a valid tokenNo-property
         * Given this object has a valid adminTokenYes-property
         * Given this object has a valid adminTokenNo-property
         * Given this object has the adminApproved-property set to false
         * Given this object has the approved-property set to false
         * Given this object is deleted
         * Given a request-object is set
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with the valid tokenUser-value as parameter
         * When the method is called with the valid adminTokenYes-value as parameter
         * Then 220 is returned
         * Then no privacy-object is created
         * Then the adminApproved-property of the optIn-object is set to true
         * Then the frontendUser is still disabled
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check120.xml');

        $this->fixture->setFrontendUserToken('test');
        $this->fixture->setRequest(GeneralUtility::makeInstance(Request::class));

        self::assertEquals(220, $this->fixture->validateOptIn('test_admin_yes'));
        self::assertEquals(0, $this->privacyRepository->countAll());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifierIncludingDeleted(120);
        self::assertTrue($optIn->getAdminApproved());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(120);
        self::assertTrue($frontendUser->getDisable());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns200IfMatchingTokenYesWithAdminApproval ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object is disabled
         * Given a persisted optIn-object
         * Given this object has a valid frontendUserUpdate-property set
         * Given that frontendUserUpdate-property contains an array with the city- and the zip-property and new values for them
         * Given this object has a valid tokenUser-property
         * Given this object has a valid tokenYes-property
         * Given this object has a valid tokenNo-property
         * Given this object has the adminApproved-property set to true
         * Given this object has the approved-property set to false
         * Given a request-object is set
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with the valid tokenUser-value as parameter
         * When the method is called with the valid tokenYes-value as parameter
         * Then 200 is returned
         * Then a privacy-object is created
         * Then the optIn-object is marked as deleted
         * Then the approved-property of the optIn-object is set to true
         * Then the frontendUser is enabled
         * Then the zip- and the city-property of the frontendUser is update to the values of the array in the frontendUserUpdate-property
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check70.xml');

        $this->fixture->setFrontendUserToken('test');
        $this->fixture->setRequest(GeneralUtility::makeInstance(Request::class));

        self::assertEquals(200, $this->fixture->validateOptIn('test_yes'));
        self::assertEquals(1, $this->privacyRepository->countAll());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifierIncludingDeleted(70);
        self::assertTrue($optIn->getDeleted());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifierIncludingDeleted(70);
        self::assertTrue($optIn->getApproved());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(70);
        self::assertFalse($frontendUser->getDisable());

        self::assertEquals('Herborn', $frontendUser->getCity());
        self::assertEquals('35745', $frontendUser->getZip());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns200IfMatchingAdminTokenYesWithApproval ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given this object is disabled
         * Given a persisted optIn-object
         * Given this object has a valid tokenUser-property
         * Given this object has a valid tokenYes-property
         * Given this object has a valid tokenNo-property
         * Given this object has a valid adminTokenYes-property
         * Given this object has a valid adminTokenNo-property
         * Given this object has the adminApproved-property set to false
         * Given this object has the approved-property set to true
         * Given a request-object is set
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with the valid tokenUser-value as parameter
         * When the method is called with the valid adminTokenYes-value as parameter
         * Then 200 is returned
         * Then no privacy-object is created
         * Then the optIn-object is marked as deleted
         * Then the adminApproved-property of the optIn-object is set to true
         * Then the frontendUser is enabled
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check130.xml');

        $this->fixture->setFrontendUserToken('test');
        $this->fixture->setRequest(GeneralUtility::makeInstance(Request::class));

        self::assertEquals(200, $this->fixture->validateOptIn('test_admin_yes'));
        self::assertEquals(0, $this->privacyRepository->countAll());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $OptIn */
        $optIn = $this->optInRepository->findByIdentifierIncludingDeleted(130);
        self::assertTrue($optIn->getDeleted());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifierIncludingDeleted(130);
        self::assertTrue($optIn->getAdminApproved());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(130);
        self::assertFalse($frontendUser->getDisable());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns300IfMatchingTokenNo ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given a persisted optIn-object
         * Given this object has a valid tokenUser
         * Given this object has a valid tokenYes
         * Given this object has a valid tokenNo
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with the valid tokenUser-value as parameter
         * When the method is called with the valid tokenNo-value as parameter
         * Then 300 is returned
         * Then no privacy-object is created
         * Then the optIn-object is marked as deleted
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check70.xml');

        $this->fixture->setFrontendUserToken('test');
        self::assertEquals(300, $this->fixture->validateOptIn('test_no'));
        self::assertEquals(0, $this->privacyRepository->countAll());

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $OptIn */
        $optIn = $this->optInRepository->findByIdentifierIncludingDeleted(70);
        self::assertTrue($optIn->getDeleted());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function validateOptInReturns310IfMatchingTokenNoOnDeleted ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given a persisted optIn-object
         * Given this object has a valid tokenUser
         * Given this object has a valid tokenYes
         * Given this object has a valid tokenNo
         * Given this object is deleted
         * Given the frontendUserId-property refers to the frontendUser-object
         * Given setFrontendUserToken has been called before with the valid tokenUser-value as parameter
         * When the method is called with the valid tokenNo-value as parameter
         * Then 310 is returned
         * Then no privacy-object is created
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check90.xml');

        $this->fixture->setFrontendUserToken('test');
        self::assertEquals(310, $this->fixture->validateOptIn('test_no'));
        self::assertEquals(0, $this->privacyRepository->countAll());


    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function completeRegistrationThrowsExceptionIfNoFrontendUserPersisted ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1660814408
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1660814408);

        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@testen.de');

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->completeRegistration();

    }

    /**
     * @test
     * @throws \Exception
     */
    public function completeRegistrationReturnsFalseIfFrontendUserEnabled ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given that frontendUser is not disabled
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        self::assertFalse($this->fixture->completeRegistration());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function completeRegistrationReturnsTrueIfFrontendUserDisabled ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given that frontendUser is disabled
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then true is returned
         * Then the disable-property is set to 0
         * Then the tempPlaintextPassword-property is not empty
         * Then the password-property is not empty
         * Then the endtime-property is set to the configured value
         * The frontendUser-groups are set to the configured value
         *
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(20);

        $this->fixture->setFrontendUser($frontendUser);
        self::assertTrue($this->fixture->completeRegistration());

        $frontendUserResult = $this->fixture->getFrontendUserPersisted();
        self::assertEquals(0, $frontendUserResult->getDisable());
        self::assertNotEmpty($frontendUserResult->getTempPlaintextPassword());
        self::assertNotEmpty($frontendUserResult->getPassword());
        self::assertGreaterThan(time(), $frontendUserResult->getEndtime());
        self::assertLessThanOrEqual(time() + 10000, $frontendUserResult->getEndtime());
        self::assertEquals(2, $frontendUserResult->getUsergroup()->count());

        $usergroups = $frontendUserResult->getUsergroup()->toArray();
        self::assertEquals(20, $usergroups[0]->getUid());
        self::assertEquals(21, $usergroups[1]->getUid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function completeRegistrationReturnsTrueAndUpdatesRawFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given that frontendUser is disabled
         * Given the email-address of the object has been changed during object-lifetime without persisting
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then true is returned
         * Then getFrontendUser returns the same object as getFrontendUserPersisted
         * Then the changed email-address is dismissed
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(20);
        $frontendUser->setEmail('merz@cdu.de');

        $this->fixture->setFrontendUser($frontendUser);

        self::assertTrue($this->fixture->completeRegistration());

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $frontendUserPersisted = $this->fixture->getFrontendUserPersisted();
        $frontendUserRaw = $this->fixture->getFrontendUser();

        self::assertSame($frontendUserPersisted, $frontendUserRaw);
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function cancelRegistrationThrowsExceptionIfNoFrontendUserPersisted ()
    {
        /**
         * Scenario:
         *
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1660914940
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1660914940);

        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@testen.de');

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->cancelRegistration();

    }


    /**
     * @test
     * @throws \Exception
     */
    public function cancelRegistrationReturnsFalseIfFrontendUserEnabled ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given that frontendUser is not disabled
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then false is returned
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        $this->fixture->setFrontendUser($frontendUser);
        self::assertFalse($this->fixture->cancelRegistration());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function cancelRegistrationReturnsTrueIfFrontendUserDisabled ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given that frontendUser is disabled
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then true is returned
         * Then the deleted-property is set to 1
         *
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(20);

        $this->fixture->setFrontendUser($frontendUser);
        self::assertTrue($this->fixture->cancelRegistration());

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDeleted(20);
        self::assertTrue($frontendUser->getDeleted());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function cancelRegistrationReturnsTrueAndUpdatesRawFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given that frontendUser is disabled
         * Given the email-address of the object has been changed during object-lifetime without persisting
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then true is returned
         * Then getFrontendUser returns the same object as getFrontendUserPersisted
         * Then the changed email-address is dismissed
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(20);
        $frontendUser->setEmail('merz@cdu.de');

        $this->fixture->setFrontendUser($frontendUser);

        self::assertTrue($this->fixture->cancelRegistration());

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $frontendUserPersisted = $this->fixture->getFrontendUserPersisted();
        $frontendUserRaw = $this->fixture->getFrontendUser();

        self::assertSame($frontendUserPersisted, $frontendUserRaw);
    }

    #==============================================================================
    /**
     * @test
     * @throws \Exception
     */
    public function endRegistrationThrowsExceptionIfNoFrontendUserPersisted ()
    {


        /**
         * Scenario:
         *
         * Given a non-persisted frontendUser-object
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then the exception is an instance of \RKW\RkwRegistration\Exception
         * Then the exception has the code 1661163918
         */
        static::expectException(\RKW\RkwRegistration\Exception::class);
        static::expectExceptionCode(1661163918);

        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail('test@testen.de');

        $this->fixture->setFrontendUser($frontendUser);
        $this->fixture->endRegistration();

    }


    /**
     * @test
     * @throws \Exception
     */
    public function endRegistrationReturnsTrueAndPerformsLogoutForLoggedInUser ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given this frontendUser is not disabled
         * Given a persisted userGroup
         * Given simulateLogin has been called with both as parameters before
         * Given simulateLogin has returned true
         * When the method is called
         * Then true is returned
         * Then the frontendUser is logged out
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(30);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(30);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
        static::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));
        $this->fixture->setFrontendUser($frontendUser);

        static::assertTrue($this->fixture->endRegistration());
        static::assertFalse(FrontendUserSessionUtility::isUserLoggedIn($frontendUser));

        FrontendSimulatorUtility::resetFrontendEnvironment();


    }

    /**
     * @test
     * @throws \Exception
     */
    public function endRegistrationReturnsTrueMarksFrontendUserAsDeleted ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given this frontendUser is not disabled
         * Given a persisted userGroup
         * When the method is called
         * Then true is returned
         * Then the frontendUser is marked as deleted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertTrue($this->fixture->endRegistration());

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDeleted(10);
        self::assertTrue($frontendUser->getDeleted());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function endRegistrationReturnsFalseOnDisabledUser ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given this frontendUser is disabled
         * Given a persisted userGroup
         * When the method is called
         * Then false is returned
         * Then the frontendUser is not marked as deleted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDisabled(20);

        $this->fixture->setFrontendUser($frontendUser);
        static::assertFalse($this->fixture->endRegistration());

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDeleted(20);
        self::assertFalse($frontendUser->getDeleted());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function endRegistrationReturnsTrueAndUpdatesRawFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given that frontendUser is disabled
         * Given the email-address of the object has been changed during object-lifetime without persisting
         * Given this object has a valid value for the email-property set
         * Given setFrontendUser has been called with this frontendUser-object as parameter
         * When the method is called
         * Then true is returned
         * Then the frontendUser is marked as deleted
         * Then getFrontendUser returns the same object as getFrontendUserPersisted
         * Then the changed email-address is dismissed
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);
        $frontendUser->setEmail('merz@cdu.de');

        $this->fixture->setFrontendUser($frontendUser);
        static::assertTrue($this->fixture->endRegistration());

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifierIncludingDeleted(10);
        self::assertTrue($frontendUser->getDeleted());

        $frontendUserPersisted = $this->fixture->getFrontendUserPersisted();
        $frontendUserRaw = $this->fixture->getFrontendUser();

        self::assertSame($frontendUserPersisted, $frontendUserRaw);
    }

    #==============================================================================


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteAnExistingAndEnabledUser ()
    {
        /**
         * Scenario:
         *
         * Given is an enabled frontendUser
         * When the remove function is called
         * Then the frontendUser is deleted
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // BEFORE
        static::assertEquals(0, $frontendUser->getDeleted());

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->delete();

        // AFTER
        // now removed!
        static::assertEquals(1, $frontendUser->getDeleted());
    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteAnExistingAndDisabledUser ()
    {
        /**
         * Scenario:
         *
         * Given is a disabled frontendUser
         * When the remove function is called
         * Then the frontendUser is NOT deleted
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest(1);

        // BEFORE
        static::assertEquals(0, $frontendUser->getDeleted());

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->delete();

        // AFTER
        // now removed!
        static::assertEquals(1, $frontendUser->getDeleted());
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteNotPersistentUser ()
    {
        /**
         * Scenario:
         *
         * Given is a not existing frontendUser
         * When the remove function is called
         * Then the not persistent frontendUser is marked as deleted
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);
        $frontendUser->setEmail('someNewFrontendUser@gmx.de');
        $frontendUser->setUsername('someNewFrontendUser@gmx.de');

        // BEFORE
        static::assertEquals(0, $frontendUser->getDeleted());

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->delete();

        // AFTER
        static::assertEquals(1, $frontendUser->getDeleted());
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteNotPersistentUserArrayWithFakeData ()
    {
        /**
         * Scenario:
         *
         * Given is an array with frontendUser data
         * When the remove function is called
         * Then a new frontendUser is created and marked as deleted
         */

        $dataArray = [
            'email' => 'someNewFrontendUser@gmx.de',
            'username' => 'someNewFrontendUser@gmx.de'
        ];

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $dataArray);
        $register->delete();

        // AFTER
        static::assertEquals(1, $register->getFrontendUser()->getDeleted());

        // Attention: This is NOT the user from the given array; its a completely new created one
        static::assertEquals('someNewFrontendUser@gmx.de', $dataArray['email']);
        static::assertNotEquals($register->getFrontendUser()->getEmail(), $dataArray['email']);
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteNotPersistentUserArrayWithExistingEmail ()
    {
        /**
         * Scenario:
         *
         * Given is an array with frontendUser data which are existing in the database
         * When the remove function is called
         * Then a new frontendUser is created and marked as deleted
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check10.xml');

        $dataArray = [
            'email' => 'lauterbach@spd.de',
            'username' => 'lauterbach@spd.de'
        ];

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $dataArray);
        $register->delete();

        // AFTER
        static::assertEquals(1, $register->getFrontendUser()->getDeleted());

        // Attention: This is NOT the user from the given array; its a completely new created one
        static::assertNotEquals($register->getFrontendUser()->getEmail(), $dataArray['email']);
    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setUserGroupsWithNewFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is a new created frontendUser (will be created in FrontendUserRegister constructor)
         * When the setUserGroups function is called
         * Then the function returns the frontendUser with frontendUserGroups which are set in TypoScript
         */

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class);
        $register->setUserGroups();

        /** @var FrontendUser $newFrontendUser */
        $newFrontendUser = $register->getFrontendUser();

        // first feGroup has ID 55; the second feGroup has ID 56
        $i = 0;
        foreach ($newFrontendUser->getUsergroup() as $group) {
            static::assertInstanceOf(FrontendUserGroup::class, $group);
            static::assertEquals(55 + $i, $group->getUid());
            $i++;
        }

    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setUserGroupsWithExistingAndEnabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing frontendUser
         * When the setUserGroups function is called
         * Then the function will set the in TypoScript defined user groups to that user
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->setUserGroups();

        // first feGroup has ID 55; the second feGroup has ID 56
        $i = 0;
        foreach ($frontendUser->getUsergroup() as $group) {
            static::assertInstanceOf(FrontendUserGroup::class, $group);
            static::assertEquals(55 + $i, $group->getUid());
            $i++;
        }

    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setUserGroupsWithExistingAndDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing (and disabled) frontendUser
         * When the setUserGroups function is called
         * Then the function will set the in TypoScript defined user groups to that user
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest(1);

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->setUserGroups();

        // first feGroup has ID 55; the second feGroup has ID 56
        $i = 0;
        foreach ($frontendUser->getUsergroup() as $group) {
            static::assertInstanceOf(FrontendUserGroup::class, $group);
            static::assertEquals(55 + $i, $group->getUid());
            $i++;
        }

    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setUserGroupsWithGuestUser ()
    {
        /**
         * Scenario:
         *
         * Given is a new created guestUser
         * When the setUserGroups function is called
         * Then the function will set the in TypoScript defined user groups to that user
         */

        /** @var GuestUser $guestUser */
        $guestUser = $this->objectManager->get(GuestUser::class);

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $guestUser);
        $register->setUserGroups();

        static::assertInstanceOf(GuestUser::class, $guestUser);

        foreach ($guestUser->getUsergroup() as $group) {
            static::assertInstanceOf(FrontendUserGroup::class, $group);
            static::assertEquals(56, $group->getUid());
        }
    }


    /**
     * @test
     */
    public function getMandatoryFieldsOfUserFromTypoScript ()
    {
        /**
         * Scenario:
         *
         * Given is only the service
         * When we want to get mandatory fields (for some frontendUser)
         * Then some basic data will set to the frontendUser (via TypoScript)
         */

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class);

        $requiredFields = $register->getMandatoryFields();

        static::assertNotEmpty($requiredFields);
    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getMandatoryFieldsOfUserFromUserGroupOfFrontendUser()
    {
        /**
         * Scenario:
         *
         * Given is only the service
         * When we want to get mandatory fields (for some frontendUser)
         * Then some basic data will set to the frontendUser (via TypoScript AND frontendUserGroup)
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);

        $requiredFields = $register->getMandatoryFields();

        /*
          FrontendUserResult:
          array(4) {
              [0] =>
              string(5) "email"
              [1] =>
              string(9) "firstName"
              [2] =>
              string(8) "lastName"
              [3] =>
              string(9) "something"    // <-- Coming from the user related group!
            }
         */

        static::assertEquals("something", $requiredFields[3]);
    }


    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
