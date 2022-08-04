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
 * RegistrationRepositoryTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RegistrationRepositoryTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RegistrationRepositoryTest/Fixtures';

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
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    private $subject = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;

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
        $this->subject = $this->objectManager->get(RegistrationRepository::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

    }

    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findExpiredWithExpiredRegistrationReturnsRegistration()
    {

        /**
         * Scenario:
         *
         * Given is an expired registration entry
         * When the function is called
         * Then the registration entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $result = $this->subject->findExpired();

        static::assertCount(1, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findExpiredWithNoSpecificExpireDateReturnsRegistration()
    {

        /**
         * Scenario:
         *
         * Given is an expired registration entry
         * When the function is called
         * Then the registration entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $result = $this->subject->findExpired();

        static::assertCount(1, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findExpiredWithNotExpiredReturnsNoResults()
    {

        /**
         * Scenario:
         *
         * Given is a not expired registration entry
         * Test works until: 4079751172 -> Mon Apr 13 2099 08:12:52 GMT+0000
         * When the function is called
         * Then no results returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $result = $this->subject->findExpired();

        static::assertCount(0, $result);

    }



    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function newOptInWithFrontendUserReturnsRegistration()
    {

        /**
         * Scenario:
         *
         * Given is a frontendUser
         * When the function is called
         * Then the registration entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $result = $this->subject->newOptIn($frontendUser, 7);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Registration', $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function newOptInWithDisabledFrontendUserReturnsException()
    {

        /**
         * Scenario:
         *
         * Given is a disabled frontendUser
         * When the function is called
         * Then an error is thrown
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        static::expectException(\TypeError::class);
        static::expectExceptionCode(0);

        $this->subject->newOptIn($frontendUser, 7);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function newOptInWithDeletedFrontendUserReturnsException()
    {

        /**
         * Scenario:
         *
         * Given is a removed frontendUser
         * When the function is called
         * Then an error is thrown
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        static::expectException(\TypeError::class);
        static::expectExceptionCode(0);

        $this->subject->newOptIn($frontendUser, 7);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function newOptInWithNoTimeForOptInReturnsRegistration()
    {
        /**
         * Scenario:
         *
         * Given is a frontendUser
         * Given is $daysForOptIn value 0
         * When the function is called
         * Then the registration entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $result = $this->subject->newOptIn($frontendUser, 0);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Registration', $result);
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        $result = $this->subject->newOptIn($frontendUser, -7);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Registration', $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findConfirmedByUser()
    {
        /**
         * Scenario:
         *
         * Given is a frontendUser
         * Given is $daysForOptIn
         * Given is $additionalData
         * Given is $category
         * When the function is called
         * Then the registration entry is returned
         * Then the registration has a validUntil date for a week in the past
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        /** @var Title $addData */
        $addData = $this->objectManager->get(Title::class);
        $addData->setName('Some title');

        $categoryName = 'some_category';

        $result = $this->subject->newOptIn($frontendUser, 7, $addData, $categoryName);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Registration', $result);
        static::assertEquals(1, $result->getUser());
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Title', $result->getData());
        $time = strtotime("7 day", time());
        static::assertEquals($time, $result->getValidUntil());
        static::assertEquals($categoryName, $result->getCategory());
        static::assertNotEmpty($result->getUserSha1());
        static::assertNotEmpty($result->getTokenYes());
        static::assertNotEmpty($result->getTokenNo());
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
