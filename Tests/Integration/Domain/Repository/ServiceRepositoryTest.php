<?php
namespace RKW\RkwRegistration\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\Privacy;
use RKW\RkwRegistration\Domain\Model\Registration;
use RKW\RkwRegistration\Domain\Model\Title;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;

use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use RKW\RkwRegistration\Domain\Repository\ServiceRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
 * ServiceRepositoryTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ServiceRepositoryTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ServiceRepositoryTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
    ];
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ServiceRepository
     */
    private $subject = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    private $frontendUserGroupRepository = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

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
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(ServiceRepository::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->frontendUserGroupRepository = $this->objectManager->get(FrontendUserGroupRepository::class);

    }

    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findConfirmedByUserWithEnabledServiceRegisterReturnsServiceRegister()
    {

        /**
         * Scenario:
         *
         * Given is a frontendUser
         * Given is an enabled service registration
         * When the function is called
         * Then the service register entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check5.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $result = $this->subject->findConfirmedByUser($frontendUser);

        static::assertCount(1, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function findConfirmedByUserWithNotEnabledServiceRegisterReturnsNoResults()
    {

        /**
         * Scenario:
         *
         * Given is a frontendUser
         * Given is a NOT enabled service registration
         * When the function is called
         * Then nothing is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check5.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $result = $this->subject->findConfirmedByUser($frontendUser);

        static::assertCount(0, $result);
    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findExpiredWithExpiredServiceRegisterReturnsExpiredServiceRegister()
    {

        /**
         * Scenario:
         *
         * Given is a frontendUser
         * Given is an enabled service registration
         * When the function is called
         * Then the service register entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check5.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $result = $this->subject->findExpired($frontendUser);

        static::assertCount(1, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findExpiredWithNotExpiredServiceRegisterReturnsNothing()
    {

        /**
         * Scenario:
         *
         * Given is a frontendUser
         * Given is a not expired service registration
         * Test works until: 4079751172 -> Mon Apr 13 2099 08:12:52 GMT+0000
         * When the function is called
         * Then nothing is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check5.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        // @toDo: Alternative set closing date here and persist

        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $result = $this->subject->findExpired($frontendUser);

        static::assertCount(0, $result);
    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function newOptInWithFrontendUserReturnsServiceRegister()
    {

        /**
         * Scenario:
         *
         * Given is a frontendUser
         * When the function is called
         * Then the registration entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check1.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check5.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);
        /** @var FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByIdentifier(1);

        $result = $this->subject->newOptIn($frontendUser, $frontendUserGroup, 7);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Service', $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function newOptInWithNoTimeForOptInReturnsServiceRegister()
    {
        /**
         * Scenario:
         *
         * Given is a frontendUser
         * Given is $daysForOptIn value 0
         * When the function is called
         * Then the registration entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check1.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check5.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);
        /** @var FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByIdentifier(1);

        $result = $this->subject->newOptIn($frontendUser, $frontendUserGroup, 0);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Service', $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function newOptInWithMinusTimeForOptInReturnsRegistration()
    {
        /**
         * Scenario:
         *
         * Given is a frontendUser
         * Given is $daysForOptIn value -7
         * When the function is called
         * Then the registration entry is returned
         * Then the registration has a validUntil date for a week in the past
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check1.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check5.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);
        /** @var FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByIdentifier(1);

        $result = $this->subject->newOptIn($frontendUser, $frontendUserGroup, -7);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Service', $result);
    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}

