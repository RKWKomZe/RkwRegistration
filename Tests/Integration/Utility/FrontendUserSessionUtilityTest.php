<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;

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

use Exception;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;

use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * FrontendUserSessionUtilityTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
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
        'typo3conf/ext/rkw_ajax',
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
    ];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    private $frontendUserGroupRepository = null;


    /**
     * Setup
     * @throws Exception
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
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->frontendUserGroupRepository = $this->objectManager->get(FrontendUserGroupRepository::class);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
    }

    #====================================================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function simulateLoginReturnsTrueAndCreatesSessionForGivenFrontendUser ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given a persisted userGroup
         * When the method is called with both as parameters
         * Then true is returned
         * Then the $GLOBALS['TSFE']->fe_user is set
         * Then the $GLOBALS['TSFE']->fe_user is an instance of \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
         * Then $GLOBALS['TSFE']->fe_user ->user has the uid of the given frontendUser
         * Then $GLOBALS['TSFE']->fe_user->id has a session-id set
         * Then the frontendUser-Aspect is set
         * Then the frontendUser-Aspect is an instance of \TYPO3\CMS\Core\Context\UserAspect
         * Then the frontendUser-Aspect has the uid for the given frontendUser
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(10);

        self::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));

        self::assertNotNull($GLOBALS['TSFE']->fe_user);
        self::assertInstanceOf(FrontendUserAuthentication::class, $GLOBALS['TSFE']->fe_user);
        self::assertEquals($frontendUser->getUid(), $GLOBALS['TSFE']->fe_user->user[$GLOBALS['TSFE']->fe_user->userid_column]);
        self::assertNotEmpty($GLOBALS['TSFE']->fe_user->id);

        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Context::class);
        self::assertNotNull($context->getAspect('frontend.user'));
        self::assertInstanceOf(UserAspect::class, $context->getAspect('frontend.user'));
        self::assertEquals($frontendUser->getUid(), $context->getPropertyFromAspect('frontend.user', 'id'));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function simulateLoginReturnsFalseIfAlreadyLoggedInUser ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given a persisted userGroup
         * Given the method has been called with both as parameters before
         * Given the method has returned true
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(10);

        self::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));
        self::assertFalse(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));

    }

    #====================================================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function logoutReturnsTrueAndDeletesSessionForCurrentFrontendUser ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given a persisted userGroup
         * Given simulateLogin has been called with both as parameters before
         * Given simulateLogin has returned true
         * When the method is called
         * Then true is returned
         * Then the $GLOBALS['TSFE']->fe_user is set
         * Then the $GLOBALS['TSFE']->fe_user is an instance of \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
         * Then $frontendUserAuthentication->user has no uid ist
         * Then the frontendUser-Aspect is set
         * Then the frontendUser-Aspect is an instance of \TYPO3\CMS\Core\Context\UserAspect
         * Then the frontendUser-Aspect has no uid set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(10);

        self::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));
        self::assertTrue(FrontendUserSessionUtility::logout());

        self::assertNotNull($GLOBALS['TSFE']->fe_user);
        self::assertInstanceOf(FrontendUserAuthentication::class, $GLOBALS['TSFE']->fe_user);
        self::assertEquals(0, $GLOBALS['TSFE']->fe_user->user[$GLOBALS['TSFE']->fe_user->userid_column]);

        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Context::class);
        self::assertNotNull($context->getAspect('frontend.user'));
        self::assertInstanceOf(UserAspect::class, $context->getAspect('frontend.user'));
        self::assertEquals(0, $context->getPropertyFromAspect('frontend.user', 'id'));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function logoutReturnsFalseIfNoLoggedInFrontendUser ()
    {

        /**
         * Scenario:
         *
         * Given simulateLogin has not been called before
         * When the method is called
         * Then false is returned
         */

        self::assertFalse(FrontendUserSessionUtility::logout());
    }


    #====================================================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function getLoggedInUserIdReturnsUidIfUserLoggedIn ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given a persisted userGroup
         * Given simulateLogin has been called with both as parameters before
         * Given simulateLogin has returned true
         * When the method is called
         * Then the uid of the given frontendUser is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(10);

        self::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));
        self::assertEquals($frontendUser->getUid(), FrontendUserSessionUtility::getLoggedInUserId());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getLoggedInUserIdReturnsZeroIfNoUserLoggedIn ()
    {

        /**
         * Scenario:
         *
         * Given no user is logged in
         * When the method is called
         * Then zero is returned
         */

        self::assertEquals(0, FrontendUserSessionUtility::getLoggedInUserId());
    }

    #====================================================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function getLoggedInUserReturnsNullIfNoUserLoggedIn ()
    {

        /**
         * Scenario:
         *
         * Given no user is logged in
         * When the method is called
         * Then zero is returned
         */

        self::assertNull(FrontendUserSessionUtility::getLoggedInUser());
    }




    /**
     * @test
     * @throws \Exception
     */
    public function getLoggedInUserWithNormalUserReturnsUserOfRightType ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given this frontendUser is an instance of \RKW\RkwRegistration\Domain\Model\FrontendUser
         * Given a persisted userGroup
         * Given simulateLogin has been called with both as parameters before
         * Given simulateLogin has returned true
         * When the method is called
         * Then an instance of \RKW\RkwRegistration\Domain\Model\FrontendUser is returned
         * Then the uid of the returned instance is the one of the given frontendUser
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(10);

        self::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $result = FrontendUserSessionUtility::getLoggedInUser();
        self::assertInstanceOf(FrontendUser::class, $result);
        self::assertEquals($frontendUser->getUid(), $result->getUid());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getLoggedInUserWithGuestUserReturnsUserOfRightType ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given this frontendUser is an instance of \RKW\RkwRegistration\Domain\Model\GuestUser
         * Given a persisted userGroup
         * Given simulateLogin has been called with both as parameters before
         * Given simulateLogin has returned true
         * When the method is called
         * Then an instance of \RKW\RkwRegistration\Domain\Model\GuestUser is returned
         * Then the uid of the returned instance is the one of the given frontendUser
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(20);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(20);

        self::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $frontendUser */
        $result = FrontendUserSessionUtility::getLoggedInUser();
        self::assertInstanceOf(GuestUser::class, $result);
        self::assertEquals($frontendUser->getUid(), $result->getUid());
    }

    #====================================================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function isUserLoggedInReturnsTrueIfUserIsLoggedIn ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser A
         * Given a persisted userGroup X
         * Given simulateLogin has been called withu serGroup X and frontendUser A before
         * Given simulateLogin has returned true
         * When the method is called with the logged in user as parameter
         * Then true is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(10);

        self::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));
        self::assertTrue(FrontendUserSessionUtility::isUserLoggedIn($frontendUser));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function isUserLoggedInReturnsFalseIfAnotherUserIsLoggedIn ()
    {

        /**
         * Scenario:
         *
         * Given a persisted frontendUser A
         * Given a persisted frontendUser B
         * Given a persisted userGroup X
         * Given simulateLogin has been called with userGroup X and frontendUser A before
         * Given simulateLogin has returned true
         * When the method is called with the frontendUser B as parameter
         * Then false is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUserTwo = $this->frontendUserRepository->findByUid(20);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(10);

        self::assertTrue(FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup));
        self::assertFalse(FrontendUserSessionUtility::isUserLoggedIn($frontendUserTwo));

    }

    #====================================================================================================

    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
