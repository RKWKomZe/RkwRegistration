<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Utility\RedirectUtility;
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
 * RedirectUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RedirectUtilityTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RedirectUtilityTest/Fixtures';

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
        'typo3conf/ext/realurl'
    ];

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        // define realUrl-config
        define('TX_REALURL_AUTOCONF_FILE', 'typo3conf/ext/rkw_mailer/Tests/Integration/ViewHelpers/Frontend/LinkViewHelperTest/Fixtures/RealUrlConfiguration.php');

        parent::setUp();
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:realurl/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        FrontendSimulatorUtility::simulateFrontendEnvironment();

        // important to complete FE for using UriBuilder
        // Solves: "Error : Call to a member function typoLink_URL() on null"
        // Solution from last post: https://forge.typo3.org/issues/67355
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);
        /** @var ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = $this->objectManager->get(ContentObjectRenderer::class);
        $configurationManager->setContentObject($contentObjectRenderer);

        // (Also needed for FE uri building: sys_domain entry & initialized page object in TS)
    }


    /**
     * @test
     */
    public function urlToPageUidReturnsUriToGivenPage ()
    {
        /**
         * Scenario:
         *
         * Given is a page and it's UID
         * When a link is created
         * An URI to the given page is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $url = $utility::urlToPageUid(100);

        static::assertEquals('http://rkw-kompetenzzentrum.rkw.local/testpage/', $url);
    }



    /**
     * @test
     */
    public function urlToPageUidToRootPageReturnsUriWithoutPath ()
    {
        /**
         * Scenario:
         *
         * Given is a page and it's UID
         * When a link is created
         * An URI to the given page is returned
         */


        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $url = $utility::urlToPageUid(1);

        static::assertEquals('http://rkw-kompetenzzentrum.rkw.local/', $url);
    }



    /**
     * @test
     */
    public function urlToPageUidWithInvlidPidReturnsEmptyString ()
    {
        /**
         * Scenario:
         *
         * Given is a PID of a not existing page
         * When a link is created
         * An empty string is returned
         */

        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $url = $utility::urlToPageUid(111);

        static::assertEmpty($url);
    }



    /**
     * @test
     */
    public function checkRedirectUrlWithValidUrlReturnsGivenUrl ()
    {
        /**
         * Scenario:
         *
         * Given is a valid uri string
         * When this uri is checked
         * The given uri is returned
         */

        $uri = 'http://rkw-kompetenzzentrum.rkw.local/testpage/';

        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $result = $utility::checkRedirectUrl($uri);

        static::assertEquals($uri, $result);
    }



    /**
     * @test
     */
    public function checkRedirectUrlWithInvalidUrlReturnsGivenUrl ()
    {
        /**
         * Scenario:
         *
         * Given is a not valid uri string
         * When this uri is checked
         * The given uri is returned
         */

        // @toDo: The functions does only log something. Can we check it?

        $uri = 'http://rkw-kompetenzzentrum.rkw.local/testpage/';

        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $result = $utility::checkRedirectUrl($uri);

        static::assertEquals($uri, $result);
    }



    /**
     * @test
     */
    public function getDomainFromUrlReturnsBaseUrl ()
    {
        /**
         * Scenario:
         *
         * Given is a valid uri string
         * When the domain is extracted from it
         * The baseUrl string part of the uri is returned
         */

        $uri = 'http://rkw-kompetenzzentrum.rkw.local/testpage/';

        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $result = $utility::getDomainFromUrl($uri);

        static::assertEquals('rkw-kompetenzzentrum.rkw.local', $result);
    }



    /**
     * @test
     */
    public function getCurrentDomainNameReturnsIt ()
    {
        /**
         * Scenario:
         *
         * Given is nothing special
         * When the function is called
         * The current domain name is returned
         */

        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $result = $utility::getCurrentDomainName();

        static::assertEquals('rkw-kompetenzzentrum.rkw.local', $result);
    }



    /**
     * @test
     */
    public function getGuestRedirectUrlWithSysDomainEntry ()
    {
        /**
         * Scenario:
         *
         * Given is a page and it's UID in database
         * When a link is created
         * An URI to the given page is returned
         */


        // Problem: If we set the sys_domain entry to "Check20.xml", the link generation does not work:
        // Result: http://var/www/rkw-website-composer/vendor/bin/index.php?id=50'
        // Impact: We can't test several cases :-(

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $url = $utility::getGuestRedirectUrl();

        static::assertEquals('http://rkw-kompetenzzentrum.rkw.local/guestpage/', $url);
    }



    /**
     * @test
     */
    public function getGuestRedirectUrlWithTypoScriptEntry ()
    {
        /**
         * Scenario:
         *
         * Given is a page and it's UID in database
         * Given is special TypoScript value "users.guestRedirectPid = 50"
         * When a link is created
         * An URI to the given page is returned
         */

        // re-initialize for special TypoScript settings
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:realurl/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/RootpageCheck10.typoscript',
            ]
        );

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var RedirectUtility $utility */
        $utility = GeneralUtility::makeInstance(RedirectUtility::class);
        $url = $utility::getGuestRedirectUrl();

        static::assertEquals('http://rkw-kompetenzzentrum.rkw.local/guestpage/', $url);
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}