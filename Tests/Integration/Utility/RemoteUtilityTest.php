<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Utility\RedirectUtility;
use RKW\RkwRegistration\Utility\RemoteUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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
 * RemoteUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RemoteUtilityTest extends FunctionalTestCase
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

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
    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/RemoteUtilityTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Tests/Integration/Utility/RemoteUtilityTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    }


    /**
     * @test
     */
    public function getIpReturnsIt ()
    {
        /**
         * Scenario:
         *
         * When an IP is read out
         * An IP is returned
         */

        /** @var RemoteUtility $utility */
        $utility = GeneralUtility::makeInstance(RemoteUtility::class);
        $ipAddress = $utility::getIp();

        static::assertEquals('127.0.0.1', $ipAddress);
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}