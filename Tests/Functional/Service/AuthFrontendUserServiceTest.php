<?php
namespace RKW\RkwRegistration\Tests\Functional\Service;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Service\AuthFrontendUserService;
use RKW\RkwRegistration\Register\GroupFrontendUser;
use RKW\RkwRegistration\DataProtection\PrivacyHandler;
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
 * AuthFrontendUserServiceTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AuthFrontendUserServiceTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/AuthFrontendUserServiceTest/Fixtures';

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
    protected $coreExtensionsToLoad = [
    ];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;

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

    }



    /**
     * @test
     */
    public function getUserWithExistingAndEnabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given are login data for an existing and enabled FrontendUser
         * When we want to get the user with this data
         * Then an array of the existing FrontendUser is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        // Service
        /** @var AuthFrontendUserService $authFrontendUserService */
        $authFrontendUserService = $this->objectManager->get(AuthFrontendUserService::class);
        // the secret is NOT important at this point: This is NOT the auth function. But needed because it's a mandatory value
        $authFrontendUserService->setLoginData('lauterbach@spd.de', 'secret');
        $result = $authFrontendUserService->getUser();

        static::assertArrayHasKey('uid', $result);
        static::assertEquals(1, $result['uid']);
    }



    /**
     * @test
     */
    public function getUserWithExistingAndDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given are login data for an existing and DISABLED FrontendUser
         * When we want to get the user with this data
         * Then an empty string is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        // Service
        /** @var AuthFrontendUserService $authFrontendUserService */
        $authFrontendUserService = $this->objectManager->get(AuthFrontendUserService::class);
        // the secret is NOT important at this point: This is NOT the auth function. But needed because it's a mandatory value
        $authFrontendUserService->setLoginData('spd@test.de', 'secret');
        $result = $authFrontendUserService->getUser();

        static::assertEmpty($result);
    }



    /**
     * @test
     */
    public function getUserWithNotExistingData ()
    {
        /**
         * Scenario:
         *
         * Given are login data for a not existing user
         * When we want to get the user with this data
         * Then an empty string is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        // Service
        /** @var AuthFrontendUserService $authFrontendUserService */
        $authFrontendUserService = $this->objectManager->get(AuthFrontendUserService::class);
        // the secret is NOT important at this point: This is NOT the auth function. But needed because it's a mandatory value
        $authFrontendUserService->setLoginData('sdfhuiaowehuvndfghzr', 'secret');
        $result = $authFrontendUserService->getUser();

        static::assertEmpty($result);
    }



    /**
     * @test
     */
    public function authUserWithExistingAndEnabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given are login data for an existing and enabled FrontendUser
         * When we want to auth the user with this data
         * Then a success code is returned (200)
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        // Service
        /** @var AuthFrontendUserService $authFrontendUserService */
        $authFrontendUserService = $this->objectManager->get(AuthFrontendUserService::class);
        // the secret is NOT important at this point: This is NOT the auth function. But needed because it's a mandatory value
        $authFrontendUserService->setLoginData('lauterbach@spd.de', 'secret');
        $frontendUserArray = $authFrontendUserService->getUser();
        $result = $authFrontendUserService->authUser($frontendUserArray);

        // 200 = success
        static::assertEquals(200, $result);
    }



    /**
     * @test
     */
    public function authUserWithExistingAndDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given are login data for an existing and DISABLED FrontendUser
         * When we want to auth the user with the returned data (empty string)
         * Then an empty string is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        // Service
        /** @var AuthFrontendUserService $authFrontendUserService */
        $authFrontendUserService = $this->objectManager->get(AuthFrontendUserService::class);
        // the secret is NOT important at this point: This is NOT the auth function. But needed because it's a mandatory value
        $authFrontendUserService->setLoginData('spd@test.de', 'secret');
        $frontendUserArray = $authFrontendUserService->getUser();

        static::expectExceptionCode(0);

        $authFrontendUserService->authUser($frontendUserArray);
    }



    /**
     * @test
     */
    public function authGuestWithExistingGuestUser ()
    {
        /**
         * Scenario:
         *
         * Given are login token for an existing GuestUser
         * When we want to auth the guestUser
         * Then a GuestUser object is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        // Service
        /** @var AuthFrontendUserService $authFrontendUserService */
        $authFrontendUserService = $this->objectManager->get(AuthFrontendUserService::class);
        // the secret is NOT important at this point: This is NOT the auth function. But needed because it's a mandatory value
        $result = $authFrontendUserService->authGuest('lfrb0u4ih3k7azqv8yc2');

        // 200 = success
        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\GuestUser', $result);
    }



    /**
     * @test
     */
    public function authGuestWithExistingAndDisabledGuestUser ()
    {
        /**
         * Scenario:
         *
         * Given are login token for an existing GuestUser
         * When we want to auth the guestUser
         * Then a GuestUser object is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        // Service
        /** @var AuthFrontendUserService $authFrontendUserService */
        $authFrontendUserService = $this->objectManager->get(AuthFrontendUserService::class);
        // the secret is NOT important at this point: This is NOT the auth function. But needed because it's a mandatory value
        $result = $authFrontendUserService->authGuest('lfrb0u4ih3k7azqv8yc2');

        // false = fail
        static::assertFalse($result);
    }



    /**
     * @test
     */
    public function authGuestWithExistingFrontendUserEmailAddressReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is the email address of an existing FrontendUser
         * When we want to auth as guestUser with that email address
         * Then false is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $emailAddress = 'lauterbach@spd.de';

        // shows that the user really exists
        $frontendUser = $this->frontendUserRepository->findByEmail($emailAddress)->getFirst();
        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\FrontendUser', $frontendUser);

        // Service
        /** @var AuthFrontendUserService $authFrontendUserService */
        $authFrontendUserService = $this->objectManager->get(AuthFrontendUserService::class);
        // the secret is NOT important at this point: This is NOT the auth function. But needed because it's a mandatory value
        $result = $authFrontendUserService->authGuest($emailAddress);

        // token not found
        static::assertFalse($result);
    }




    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
