<?php
namespace RKW\RkwRegistration\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;

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
 * FrontendUserTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepositoryTest extends FunctionalTestCase
{
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
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $subject = null;

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
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(FrontendUserRepository::class);

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findByUidSoapIncludesDeletedFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user is deleted
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check10.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByUidSoapIncludesDisabledFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user is disabled
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check20.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $result);

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByUidSoapIgnoresStoragePid ()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user has a different storage pid
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check30.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $result);

    }

    //===================================================================
    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findDeletedSinceDaysReturnsFrontendUsersThatHaveBeenDeletedSinceAGivenDay ()
    {

        /**
         * Scenario:
         *
         * Given there are two deleted frontend user
         * Given one of the frontend users is deleted since five days
         * Given that the other frontend user is deleted since four days
         * When I fetch the frontend users that have been deleted five days before
         * Then only one frontend user is returned
         * Then the frontend user that has is deleted since five days is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check40.xml');

        $result = $this->subject->findDeletedSinceDays(5, 864000);

        static::assertCount(1,  $result);
        static::assertEquals(2, $result->getFirst()->getUid());

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findDeletedSinceDaysIgnoresStoragePid ()
    {

        /**
         * Scenario:
         *
         * Given there are two deleted frontend user
         * Given one of the frontend users is deleted since five days
         * Given that this frontend user has a different storage pid
         * Given the other frontend user is deleted since four days
         * When I fetch the frontend users that have been deleted five days before
         * Then only one frontend user is returned
         * Then the frontend user that has is deleted since five days is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check50.xml');

        $result = $this->subject->findDeletedSinceDays(5, 864000);

        static::assertCount(1,  $result);
        static::assertEquals(2, $result->getFirst()->getUid());

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findDeletedSinceDaysIgnoresAlreadyAnonymized ()
    {

        /**
         * Scenario:
         *
         * Given there are three deleted frontend user
         * Given one of frontend users is deleted since four days
         * Given two of the frontend users are deleted since five days
         * Given that one of the later has already been anonymized
         * When I fetch the frontend users that have been deleted five days before
         * Then only one frontend user is returned
         * Then the frontend user that has is deleted since five days and not yet anonymized is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check51.xml');

        $result = $this->subject->findDeletedSinceDays(5, 864000);

        static::assertCount(1,  $result);
        static::assertEquals(2, $result->getFirst()->getUid());

    }


    //===================================================================
    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findExpiredSinceDaysReturnsFrontendUsersThatHaveBeenExpiredSinceAGivenDay ()
    {

        /**
         * Scenario:
         *
         * Given there are two frontend user
         * Given one of the frontend users is expired since five days
         * Given that the other frontend user is expired since four days
         * When I fetch the frontend users that have been expired five days before
         * Then only one frontend user is returned
         * Then the frontend user that has is expired since five days is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check60.xml');

        $result = $this->subject->findExpiredSinceDays(5, 864000);

        static::assertCount(1,  $result);
        static::assertEquals(2, $result->getFirst()->getUid());

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findExpiredSinceDaysIgnoresStoragePid ()
    {

        /**
         * Scenario:
         *
         * Given there are two expired frontend user
         * Given one of the frontend users is expired since five days
         * Given that this frontend user has a different storage pid
         * Given the other frontend user is expired since four days
         * When I fetch the frontend users that have been expired five days before
         * Then only one frontend user is returned
         * Then the frontend user that has is expired since five days is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check70.xml');

        $result = $this->subject->findExpiredSinceDays(5, 864000);

        static::assertCount(1,  $result);
        static::assertEquals(2, $result->getFirst()->getUid());

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findExpiredSinceDaysExcludesDeleted ()
    {

        /**
         * Scenario:
         *
         * Given there are two expired frontend user
         * Given one of the frontend users is expired since five days
         * Given that this frontend user is deleted
         * Given the other frontend user is expired since four days
         * When I fetch the frontend users that have been expired five days before
         * Then only one frontend user is returned
         * Then the frontend user that has is expired since five days is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check80.xml');

        $result = $this->subject->findExpiredSinceDays(5, 864000);

        static::assertCount(0,  $result);

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
