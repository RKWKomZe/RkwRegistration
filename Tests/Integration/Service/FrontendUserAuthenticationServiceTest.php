<?php
namespace RKW\RkwRegistration\Tests\Integration\Service;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\ClientUtility;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * FrontendUserAuthenticationServiceTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserAuthenticationServiceTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserAuthenticationService/Fixtures';

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
    ];

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

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository frontendUserRepository */
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function itIncrementsLoginErrorCounter ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given that frontendUser is enabled
         * Given the tx_rkwregistration_login_error_count-property of the frontendUser is one
         * When the frontendUser is logging in with wrong credentials
         * Then the login fails
         * Then no login-session is generated
         * Then the tx_rkwregistration_login_error_count-property of the frontendUser is incremented
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        static::assertEquals(1, $frontendUser->getTxRkwregistrationLoginErrorCount());

        $_POST['logintype'] = 'login';
        $_POST['user'] = $frontendUser->getUsername();
        $_POST['pass'] = 'not-test';

        $authService = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $authService->start();

        static::assertTrue($authService->loginFailure);
        static::assertFalse($authService->loginSessionStarted);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        static::assertEquals(2, $frontendUser->getTxRkwregistrationLoginErrorCount());

        FrontendSimulatorUtility::resetFrontendEnvironment();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function itLogsInUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser
         * Given that frontendUser is enabled
         * Given the tx_rkwregistration_login_error_count-property of the frontendUser is one
         * When the frontendUser is logging in with correct credentials
         * Then the login succeeds
         * Then a login-session is generated
         * Then the tx_rkwregistration_login_error_count-property of the frontendUser is reset
         */
        $this->importDataSet(self::FIXTURE_PATH .'/Database/Check10.xml');

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        static::assertEquals(1, $frontendUser->getTxRkwregistrationLoginErrorCount());

        $_POST['logintype'] = 'login';
        $_POST['user'] = $frontendUser->getUsername();
        $_POST['pass'] = 'test';

        $authService = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $authService->start();

        static::assertFalse($authService->loginFailure);
        static::assertTrue($authService->loginSessionStarted);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);
        static::assertEquals(0, $frontendUser->getTxRkwregistrationLoginErrorCount());

        FrontendSimulatorUtility::resetFrontendEnvironment();
    }

    #==============================================================================

    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
