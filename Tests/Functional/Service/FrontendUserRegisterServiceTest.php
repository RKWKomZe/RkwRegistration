<?php
namespace RKW\RkwRegistration\Tests\Functional\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use \RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use RKW\RkwRegistration\Service\FrontendUserRegisterService;
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
 * FrontendUserRegisterServiceTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRegisterServiceTest extends FunctionalTestCase
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
        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Tests/Functional/Service/FrontendUserRegisterServiceTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
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
        $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);

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

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // BEFORE
        static::assertNotNull($frontendUser->getPid());
        static::assertNotNull($frontendUser->getCrdate());
        static::assertEquals('de', $frontendUser->getTxRkwregistrationLanguageKey());
        // -> empty!
        static::assertEmpty($frontendUser->getTxRkwregistrationRegisterRemoteIp());

        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);
        $frontendUserRegisterService->setBasicData();

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
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);
        $frontendUserRegisterService->setClearanceAndLifetime();

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

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // BEFORE
        static::assertEquals(0, $frontendUser->getEndtime());
        static::assertEquals(0, $frontendUser->getDisable());


        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);
        $frontendUserRegisterService->setClearanceAndLifetime();

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

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous(1);

        // BEFORE
        static::assertEquals(1, $frontendUser->getDisable());


        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);
        $frontendUserRegisterService->setClearanceAndLifetime(true);

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

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // BEFORE
        static::assertEquals(0, $frontendUser->getDeleted());

        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);
        $frontendUserRegisterService->delete();

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

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous(1);

        // BEFORE
        static::assertEquals(0, $frontendUser->getDeleted());

        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);
        $frontendUserRegisterService->delete();

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
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class);
        $result = $frontendUserRegisterService->validateEmail($email);

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
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class);
        $result = $frontendUserRegisterService->validateEmail($email);

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
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class);
        $result = $frontendUserRegisterService->validateEmail();

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

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class);
        $result = $frontendUserRegisterService->uniqueEmail($frontendUser);

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
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class);
        $result = $frontendUserRegisterService->uniqueEmail($email);

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
         * Given is a new created frontendUser (will be created in FrontendUserRegisterService constructor)
         * When the setUserGroupsOnRegister function is called
         * Then the function returns the frontendUser with frontendUserGroups which are set in TypoScript
         */

        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class);
        $frontendUserRegisterService->setUserGroupsOnRegister();

        /** @var FrontendUser $newFrontendUser */
        $newFrontendUser = $frontendUserRegisterService->getFrontendUser();

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

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);
        $frontendUserRegisterService->setUserGroupsOnRegister();

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

        $this->importDataSet(__DIR__ . '/FrontendUserRegisterServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous(1);

        // Service
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $frontendUser);
        $frontendUserRegisterService->setUserGroupsOnRegister();

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
        /** @var FrontendUserRegisterService $frontendUserRegisterService */
        $frontendUserRegisterService = $this->objectManager->get(FrontendUserRegisterService::class, $guestUser);
        $frontendUserRegisterService->setUserGroupsOnRegister();

        static::assertInstanceOf(GuestUser::class, $guestUser);

        foreach ($guestUser->getUsergroup() as $group) {
            static::assertInstanceOf(FrontendUserGroup::class, $group);
            static::assertEquals(56, $group->getUid());
        }

    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}