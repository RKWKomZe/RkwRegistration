<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Exception;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;

use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
 * FrontendUserSessionUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserSessionUtilityTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserSessionUtilityTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
    ];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;


    /**
     * Setup
     * @throws Exception
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
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

        FrontendSimulatorUtility::simulateFrontendEnvironment();
    }


    /**
     * @test
     */
    public function loginCreatesFrontendUserSessionForGivenFrontendUser ()
    {

        // @toDo: Is this a functional test?

        /**
         * Scenario:
         *
         * Given is a FrontendUser
         * When he is logged in
         * Then a session is created
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var FrontendUserSessionUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserSessionUtility::class);

    //    static::assertFalse($utility->isUserLoggedIn($frontendUser));
        static::assertNull($GLOBALS['TSFE']->fe_user->user);
        static::assertNotInternalType('array', $GLOBALS['TSFE']->fe_user->user);
        static::assertNotInternalType('integer', $GLOBALS['TSFE']->fe_user->user['uid']);

        $utility->login($frontendUser);

    //    static::assertTrue($utility->isUserLoggedIn($frontendUser));
        static::assertInternalType('array', $GLOBALS['TSFE']->fe_user->user);
        static::assertInternalType('integer', $GLOBALS['TSFE']->fe_user->user['uid']);
    }


    /**
     * @test
     */
    public function loginThrowsExceptionIfANotPersistedFrontendUserIsGiven ()
    {
        /**
         * Scenario:
         *
         * Given is a not persisted FrontendUser (without ID / database entry)
         * When I want to log in this FrontendUser
         * Then an error is thrown
         */

        /** @var FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);

        /** @var FrontendUserSessionUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserSessionUtility::class);

        static::expectException(\RKW\RkwRegistration\Exception::class);

        $utility->login($frontendUser);
    }



    /**
     * @test
     */
    public function logoutDestroyFrontendUserSessionForGivenFrontendUser ()
    {

        // @toDo: Is this a functional test?

        /**
         * Scenario:
         *
         * Given is a logged in FrontendUser
         * When he is logged out
         * Then his session is destroyed
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var FrontendUserSessionUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserSessionUtility::class);
        $utility->login($frontendUser);

        static::assertInternalType('array', $GLOBALS['TSFE']->fe_user->user);
        static::assertInternalType('integer', $GLOBALS['TSFE']->fe_user->user['uid']);

        $utility->logout();

        static::assertNull($GLOBALS['TSFE']->fe_user->user);
        static::assertNotInternalType('array', $GLOBALS['TSFE']->fe_user->user);
        static::assertNotInternalType('integer', $GLOBALS['TSFE']->fe_user->user['uid']);
    }


    /**
     * @test
     */
    public function logoutDestroyFrontendUserSessionWithoutGivenFrontendUser ()
    {

        // @toDo: Is this a functional test?

        /**
         * Scenario:
         *
         * Given is no active sesseion
         * When the logout function is triggered
         * Then nothing is happen
         */

        /** @var FrontendUserSessionUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserSessionUtility::class);

        static::assertNull($GLOBALS['TSFE']->fe_user->user);
        static::assertNotInternalType('array', $GLOBALS['TSFE']->fe_user->user);
        static::assertNotInternalType('int', $GLOBALS['TSFE']->fe_user->user['uid']);

        $utility->logout();

        static::assertNull($GLOBALS['TSFE']->fe_user->user);
        static::assertNotInternalType('array', $GLOBALS['TSFE']->fe_user->user);
        static::assertNotInternalType('int', $GLOBALS['TSFE']->fe_user->user['uid']);
    }


    /**
     * @test
     */
    public function isUserLoggedInWithActiveSession ()
    {

        /**
         * Scenario:
         *
         * Given is a FrontendUser
         * When he is logged in
         * When the session is checked
         * Then the function returns true
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var FrontendUserSessionUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserSessionUtility::class);
        $utility->login($frontendUser);

        $result = $utility->isUserLoggedIn($frontendUser);

        static::assertTrue($result);
        static::assertInternalType('bool', $result);
    }



    /**
     * @test
     */
    public function isUserLoggedInWithoutActiveSession ()
    {

        /**
         * Scenario:
         *
         * Given is a (not logged in) FrontendUser
         * When the session is checked
         * Then the function returns false
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var FrontendUserSessionUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserSessionUtility::class);

        $result = $utility->isUserLoggedIn($frontendUser);

        static::assertFalse($result);
        static::assertInternalType('bool', $result);
    }



    /**
     * @test
     */
    public function getFrontendUserIdWithActiveSession ()
    {

        /**
         * Scenario:
         *
         * Given is a FrontendUser
         * When he is logged in
         * Then the function returns the frontendUser id
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var FrontendUserSessionUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserSessionUtility::class);
        $utility->login($frontendUser);

        $result = $utility->getFrontendUserId();

        static::assertEquals(1, $result);
        static::assertInternalType('integer', $result);
    }



    /**
     * @test
     */
    public function getFrontendUserIdWithoutActiveSession ()
    {
        /**
         * Scenario:
         *
         * Given is a (not logged in) FrontendUser
         * When I want to get the ID of this FrontendUser
         * Then the function returns null
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var FrontendUserSessionUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserSessionUtility::class);

        $result = $utility->getFrontendUserId();

        static::assertEquals(0, $result);
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}