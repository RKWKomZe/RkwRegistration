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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Utility\ClientUtility;

/**
 * ClientUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ClientUtilityTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ClientUtilityTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_ajax',
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
            ],
            ['rkw-kompetenzzentrum.local' => self::FIXTURE_PATH .  '/Frontend/Configuration/config.yaml']
        );

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);

    }

    #==============================================================================

    /**
     * @test
     */
    public function getIpReturnsLocalHost ()
    {
        /**
         * Scenario:
         *
         * Given a request without a proxy
         * Given no remote-address is set
         * When the method is called
         * Then localhost is returned
         */

        self::assertEquals('127.0.0.1', ClientUtility::getIp());
    }

    /**
     * @test
     */
    public function getIpReturnsClientIp ()
    {
        /**
         * Scenario:
         *
         * Given a request without a proxy
         * Given $_SERVER['REMOTE_ADDR'] is set
         * When the method is called
         * Then the remote-address is returned
         */

        $_SERVER['REMOTE_ADDR'] = '1.1.2.1';
        self::assertEquals('1.1.2.1', ClientUtility::getIp());
    }

    /**
     * @test
     */
    public function getIpReturnsClientIpWithProxy ()
    {
        /**
         * Scenario:
         *
         * Given a request with a proxy
         * Given $_SERVER['HTTP_X_FORWARDED_FOR'] is set
         * When the method is called
         * Then the first IP in $_SERVER['HTTP_X_FORWARDED_FOR']  is returned
         */

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.2.1, 2.2.1.2, 3.3.2.3';
        self::assertEquals('1.1.2.1', ClientUtility::getIp());
    }

    #==============================================================================

    /**
     * @test
     */
    public function isReferrerValidReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a referrer that does not match the current domain
         * When the method is called
         * Then false is returned
         */

        self::assertFalse(ClientUtility::isReferrerValid('https://www.google.de'));
    }


    /**
     * @test
     */
    public function isReferrerValidReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given a referrer that does match the current domain
         * When the method is called
         * Then true is returned
         */

        self::assertTrue(ClientUtility::isReferrerValid('http://www.rkw-kompetenzzentrum.rkw.local/'));
    }

    /**
     * @test
     */
    public function isReferrerValidReturnsTrueAndIgnoresProtocol ()
    {
        /**
         * Scenario:
         *
         * Given a referrer that does match the current domain
         * Given the referrer uses another protocol than the current domain
         * When the method is called
         * Then true is returned
         */

        self::assertTrue(ClientUtility::isReferrerValid('https://www.rkw-kompetenzzentrum.rkw.local/'));
    }

    #==============================================================================


    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
        FrontendSimulatorUtility::resetFrontendEnvironment();

    }

}
