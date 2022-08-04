<?php
namespace RKW\RkwRegistration\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
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
 * FrontendUserRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepositoryTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserRepositoryTest/Fixtures';

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
    protected function setUp(): void
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(FrontendUser::class, $result);

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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(FrontendUser::class, $result);

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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(FrontendUser::class, $result);

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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check51.xml');

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
    public function findExpiredAndDisabledSinceDaysReturnsFrontendUsersThatHaveBeenExpiredSinceAGivenDay ()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        $result = $this->subject->findExpiredAndDisabledSinceDays(5, 864000);

        static::assertCount(1,  $result);
        static::assertEquals(2, $result->getFirst()->getUid());

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findExpiredAndDisabledSinceDaysReturnsFrontendUsersThatHaveBeenDisabledSinceAGivenDay ()
    {

        /**
         * Scenario:
         *
         * Given there are two frontend user
         * Given one of the frontend users is disabled since five days
         * Given that the other frontend user is disabled since four days
         * When I fetch the frontend users that have been disabled five days before
         * Then only one frontend user is returned
         * Then the frontend user that has is disabled since five days is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check61.xml');

        $result = $this->subject->findExpiredAndDisabledSinceDays(5, 864000);

        static::assertCount(1,  $result);
        static::assertEquals(2, $result->getFirst()->getUid());

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findExpiredAndDisabledSinceDaysIgnoresStoragePid ()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        $result = $this->subject->findExpiredAndDisabledSinceDays(5, 864000);

        static::assertCount(1,  $result);
        static::assertEquals(2, $result->getFirst()->getUid());

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findExpiredAndDisabledSinceDaysExcludesDeleted ()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        $result = $this->subject->findExpiredAndDisabledSinceDays(5, 864000);

        static::assertCount(0,  $result);

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function removeHardEnabledFrontendUserWithoutPrivacyEntryReturnsFalse()
    {

        /**
         * Scenario:
         *
         * Given is an enabled (not deleted) frontend user
         * When the function removeHard is called
         * Then false is returned
         * Then the frontendUser still exists
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->subject->findByIdentifier(1);

        static::assertInstanceOf(FrontendUser::class, $frontendUser);

        $result = $this->subject->removeHard($frontendUser);

        static::assertFalse($result);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        // user still exists
        $frontendUser = $this->subject->findByIdentifier(1);
        static::assertInstanceOf(FrontendUser::class, $frontendUser);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeHardDisabledFrontendUserReturnsFalse()
    {
        /**
         * Scenario:
         *
         * Given is an disabled (not deleted) frontend user
         * When the function removeHard is called
         * Then false is returned
         * Then the frontendUser still exists
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check91.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->subject->findOneDisabledByUid(1);

        static::assertInstanceOf(FrontendUser::class, $frontendUser);

        $result = $this->subject->removeHard($frontendUser);

        static::assertFalse($result);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        // user still exists
        $frontendUser = $this->subject->findOneDisabledByUid(1);
        static::assertInstanceOf(FrontendUser::class, $frontendUser);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeHardDeletedFrontendUserReturnsTrue()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted frontend user
         * When the function removeHard is called
         * Then true is returned
         * Then the frontendUser is removed from database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check92.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->subject->findOneDeletedByUid(1);

        static::assertInstanceOf(FrontendUser::class, $frontendUser);

        $result = $this->subject->removeHard($frontendUser);

        static::assertTrue($result);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        $frontendUser = $this->subject->findOneDeletedByUid(1);
        static::assertNull($frontendUser);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeHardDeletedFrontendUserWithPrivacyEntryReturnsFalse()
    {

        /**
         * Scenario:
         *
         * Given is a deleted frontend user
         * Given is a privacy entry related to that user
         * When the function removeHard is called
         * Then false is returned
         * Then the frontendUser still exists
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check92.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check95.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->subject->findOneDeletedByUid(1);

        static::assertInstanceOf(FrontendUser::class, $frontendUser);

        $result = $this->subject->removeHard($frontendUser);

        static::assertFalse($result);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        // user still exists
        $frontendUser = $this->subject->findOneDeletedByUid(1);
        static::assertInstanceOf(FrontendUser::class, $frontendUser);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeHardDeletedFrontendUserWithDeletedPrivacyEntryReturnsTrue()
    {

        /**
         * Scenario:
         *
         * Given is a deleted frontend user
         * Given is a -deleted- privacy entry related to that user (still exists in database, but marked as "deleted")
         * When the function removeHard is called
         * Then false is returned
         * Then the frontendUser still exists
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check92.xml');
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check96.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->subject->findOneDeletedByUid(1);

        static::assertInstanceOf(FrontendUser::class, $frontendUser);

        $result = $this->subject->removeHard($frontendUser);

        static::assertTrue($result);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        // user still exists
        $frontendUser = $this->subject->findOneDeletedByUid(1);
        static::assertNull($frontendUser);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeHardNotExistingFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user is deleted
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(FrontendUser::class, $result);

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findOneByEmailOrUsernameAlsoInactiveWithEnabledFrontendUserReturnsFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given is a frontend user
         * When the function is called
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        $result = $this->subject->findOneByEmailOrUsernameAlsoInactive('lauterbach@spd.de');
        static::assertInstanceOf(FrontendUser::class, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOneByEmailOrUsernameAlsoInactiveWithDisabledFrontendUserReturnsFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given is a -disabled- frontend user
         * When the function is called
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check91.xml');

        $result = $this->subject->findOneByEmailOrUsernameAlsoInactive('lauterbach@spd.de');
        static::assertInstanceOf(FrontendUser::class, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findOneByEmailOrUsernameAlsoInactiveWithDeletedFrontendUserReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given is a -deleted- frontend user
         * When the function is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check92.xml');

        $result = $this->subject->findOneByEmailOrUsernameAlsoInactive('lauterbach@spd.de');
        static::assertNull($result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOneByEmailOrUsernameAlsoInactiveWithNotExistingFrontendUserReturnsNull()
    {

        // @toDo: Vielleicht auch etwas arg überflüssig der Test? Das ist ja quasi, als würde man TYPO3 prüfen...

        /**
         * Scenario:
         *
         * Given is a - not existing - frontend user
         * When the function is called
         * Then null is returned
         */

        $result = $this->subject->findOneByEmailOrUsernameAlsoInactive('spass@bei.seite');
        static::assertNull($result);

    }

    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findByUidAlsoInactiveNonGuestWithEnabledFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given is a frontend user
         * When the function is called
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        $result = $this->subject->findByUidAlsoInactiveNonGuest(1);
        static::assertInstanceOf(FrontendUser::class, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByUidAlsoInactiveNonGuestWithDisabledFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a - disabled - frontend user
         * When the function is called
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check91.xml');

        $result = $this->subject->findByUidAlsoInactiveNonGuest(1);
        static::assertInstanceOf(FrontendUser::class, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByUidAlsoInactiveNonGuestWithDeletedFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a - deleted - frontend user
         * When the function is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check92.xml');

        $result = $this->subject->findByUidAlsoInactiveNonGuest(1);
        static::assertNull($result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByUidAlsoInactiveNonGuestWithGuestUser()
    {

        /**
         * Scenario:
         *
         * Given there is a - deleted - frontend user
         * When the function is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check93.xml');

        $result = $this->subject->findByUidAlsoInactiveNonGuest(1);
        static::assertNull($result);

    }



    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findOneDeletedByUidWithDeletedUserReturnsFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given is a deleted frontend user
         * When the function is called
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check92.xml');

        $result = $this->subject->findOneDeletedByUid(1);
        static::assertInstanceOf(FrontendUser::class, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOneDeletedByUidWithDisabledUserReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given is a disabled frontend user
         * When the function is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check91.xml');

        $result = $this->subject->findOneDeletedByUid(1);
        static::assertNull($result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOneDeletedByUidWithEnabledUserReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given is an enabled frontend user
         * When the function is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        $result = $this->subject->findOneDeletedByUid(1);
        static::assertNull($result);

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findOneDisabledByUidWithDeletedUserReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given is a deleted frontend user
         * When the function is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check92.xml');

        $result = $this->subject->findOneDisabledByUid(1);
        static::assertNull($result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOneDisabledByUidWithDisabledUserReturnsFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given is a disabled frontend user
         * When the function is called
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check91.xml');

        $result = $this->subject->findOneDisabledByUid(1);
        static::assertInstanceOf(FrontendUser::class, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOneDisabledByUidWithEnabledUserReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given is an enabled frontend user
         * When the function is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        $result = $this->subject->findOneDisabledByUid(1);
        static::assertNull($result);

    }


    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
