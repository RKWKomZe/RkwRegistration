<?php
namespace RKW\RkwRegistration\Tests\Functional\Register;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use \RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use RKW\RkwRegistration\Register\FrontendUserRegister;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

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
 * FrontendUserRegisterTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRegisterTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserRegisterTest/Fixtures';

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
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

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
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        // Repository
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'mail@default.rkw';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW Default';

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }



    /**
     * @test
     */
    public function setBasicDataFillsDataToNewCreatedFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is a new created frontendUser
         * When this frontendUser is set to the RegisterService
         * Then some basic data will set to the frontendUser
         */


        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);

        // BEFORE
        static::assertNull($frontendUser->getPid());
        static::assertNull($frontendUser->getCrdate());
        static::assertEmpty($frontendUser->getTxRkwregistrationLanguageKey());
        static::assertEmpty($frontendUser->getTxRkwregistrationRegisterRemoteIp());

        // Service
        $this->objectManager->get(FrontendUserRegister::class, $frontendUser);

        // AFTER
        // frontendUser is not persisted yet
        static::assertNull($frontendUser->getUid());
        // other stats
        static::assertNotNull($frontendUser->getPid());
        static::assertNotNull($frontendUser->getCrdate());
        static::assertEquals('de', $frontendUser->getTxRkwregistrationLanguageKey());
        static::assertEquals('127.0.0.1', $frontendUser->getTxRkwregistrationRegisterRemoteIp());
    }



    /**
     * @test
     */
    public function setBasicDataFillsOnlyMissingFieldsOfExistingFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing frontendUser
         * When the registerService is initialized
         * When the function "setBasicData" is called explicit
         * Then missing basic data will set to the frontendUser
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // BEFORE
        static::assertNotNull($frontendUser->getPid());
        static::assertNotNull($frontendUser->getCrdate());
        static::assertEquals('de', $frontendUser->getTxRkwregistrationLanguageKey());
        // -> empty!
        static::assertEmpty($frontendUser->getTxRkwregistrationRegisterRemoteIp());

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->setBasicData();

        // AFTER

        // frontendUser is already persisted
        static::assertEquals(1, $frontendUser->getUid());
        // other stats
        static::assertNotNull($frontendUser->getPid());
        static::assertNotNull($frontendUser->getCrdate());
        static::assertEquals('de', $frontendUser->getTxRkwregistrationLanguageKey());
        // is also now set
        static::assertEquals('127.0.0.1', $frontendUser->getTxRkwregistrationRegisterRemoteIp());
    }



    /**
     * @test
     */
    public function setClearanceAndLifetimeSetsEndtimeToFrontendUserByDefault ()
    {
        /**
         * Scenario:
         *
         * Given is new created and disabled frontendUser
         * When the clearance function is called
         * Then an endtime is set to the frontendUser
         * Then the frontendUser is still disabled
         */


        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);

        // BEFORE
        static::assertNull($frontendUser->getEndtime());
        static::assertEquals(1, $frontendUser->getDisable());


        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->setClearanceAndLifetime();

        // AFTER
        static::assertNotNull($frontendUser->getEndtime());
        static::assertGreaterThan(time(), $frontendUser->getEndtime());
        static::assertEquals(1, $frontendUser->getDisable());
    }



    /**
     * @test
     */
    public function setClearanceAndLifetimeDisableAnEnabledFrontendUserByDefault ()
    {
        /**
         * Scenario:
         *
         * Given is an existing and enabled frontendUser
         * When the clearance function is called
         * Then an endtime is set to the frontendUser
         * Then the frontendUser is enabled
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // BEFORE
        static::assertEquals(0, $frontendUser->getEndtime());
        static::assertEquals(0, $frontendUser->getDisable());


        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->setClearanceAndLifetime();

        // AFTER
        static::assertNotNull($frontendUser->getEndtime());
        static::assertGreaterThan(time(), $frontendUser->getEndtime());
        // now disabled!
        static::assertEquals(1, $frontendUser->getDisable());
    }



    /**
     * @test
     */
    public function setClearanceAndLifetimeEnablesDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is a created and disabled frontendUser
         * When the clearance function is called
         * Then the frontendUser is enabled
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest(1);

        // BEFORE
        static::assertEquals(1, $frontendUser->getDisable());

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->setClearanceAndLifetime(true);

        // AFTER
        // now disabled!
        static::assertEquals(0, $frontendUser->getDisable());
    }


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
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest(1);

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
    public function validateEmailWithValidEmailReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is an valid email address
         * When the validEmail function is called
         * Then the functions returns true
         */

        $email = 'test@test.de';

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class);
        $result = $register->validateEmail($email);

        static::assertTrue($result);
    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function validateEmailWithInvalidEmailReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is an valid email address
         * When the validEmail function is called
         * Then the functions returns true
         */

        $email = 'test@test';

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class);
        $result = $register->validateEmail($email);

        static::assertFalse($result);
    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function validateEmailWithoutEmailReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is an valid email address
         * When the validEmail function is called
         * Then the functions returns true
         */

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class);
        $result = $register->validateEmail();

        static::assertFalse($result);
    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function uniqueEmailChecksExistingEmail ()
    {
        /**
         * Scenario:
         *
         * Given is an existing Email address
         * When the validEmailUnique function is called (with parameter type FrontendUser)
         * Then the function returns false
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class);
        $result = $register->uniqueEmail($frontendUser);

        static::assertFalse($result);
    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function uniqueEmailChecksValidAndNotExistingEmail ()
    {
        /**
         * Scenario:
         *
         * Given is an existing Email address
         * When the validEmailUnique function is called (with parameter type string)
         * Then the function returns true
         */

        $email = 'test@test.de';

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class);
        $result = $register->uniqueEmail($email);

        static::assertTrue($result);
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function setUserGroupsOnRegisterWithNewFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is a new created frontendUser (will be created in FrontendUserRegister constructor)
         * When the setUserGroupsOnRegister function is called
         * Then the function returns the frontendUser with frontendUserGroups which are set in TypoScript
         */

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class);
        $register->setUserGroupsOnRegister();

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
    public function setUserGroupsOnRegisterWithExistingAndEnabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing frontendUser
         * When the setUserGroupsOnRegister function is called
         * Then the function will set the in TypoScript defined user groups to that user
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->setUserGroupsOnRegister();

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
    public function setUserGroupsOnRegisterWithExistingAndDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing (and disabled) frontendUser
         * When the setUserGroupsOnRegister function is called
         * Then the function will set the in TypoScript defined user groups to that user
         */

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest(1);

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $frontendUser);
        $register->setUserGroupsOnRegister();

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
    public function setUserGroupsOnRegisterWithGuestUser ()
    {
        /**
         * Scenario:
         *
         * Given is a new created guestUser
         * When the setUserGroupsOnRegister function is called
         * Then the function will set the in TypoScript defined user groups to that user
         */

        /** @var GuestUser $guestUser */
        $guestUser = $this->objectManager->get(GuestUser::class);

        // Service
        /** @var FrontendUserRegister $register */
        $register = $this->objectManager->get(FrontendUserRegister::class, $guestUser);
        $register->setUserGroupsOnRegister();

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

        $requiredFields = $register->getMandatoryFieldsOfUser();

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

        $requiredFields = $register->getMandatoryFieldsOfUser();

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
              string(9) "something"
            }
         */

        static::assertEquals("something", $requiredFields[3]);
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}