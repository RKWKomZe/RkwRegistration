<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwRegistration\Domain\Repository\TitleRepository;
use RKW\RkwRegistration\Utility\TitleUtility;
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
 * TitleUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TitleUtilityTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/TitleUtilityTest/Fixtures';

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\TitleRepository
     */
    private $titleRepository = null;

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

        // Repository
        $this->titleRepository = $this->objectManager->get(TitleRepository::class);

    }


    /**
     * @test
     */
    public function extractTxRegistrationTitleAddsNotExistingTitle ()
    {
        /**
         * Scenario:
         *
         * Given is a title
         * When the function is called
         * Then a new title is created and added to the database
         */

        $someTitle = 'Master of the Universe';

        TitleUtility::extractTxRegistrationTitle($someTitle);

        $result = $this->titleRepository->findByName($someTitle)->getFirst();

        static::assertEquals($someTitle, $result->getName());
    }



    /**
     * @test
     */
    public function extractTxRegistrationTitleWithExistingTitle ()
    {
        /**
         * Scenario:
         *
         * @existing Title
         *
         * Given is an already existing title
         * When the function is called
         * Then no new title is created
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $alreadyExistingTitle = $this->titleRepository->findByIdentifier(1);

        $countBeforeFunction = $this->titleRepository->findByName($alreadyExistingTitle->getName())->count();

        TitleUtility::extractTxRegistrationTitle($alreadyExistingTitle->getName());

        $countAfterFunction = $this->titleRepository->findByName($alreadyExistingTitle->getName())->count();

        static::assertEquals($countBeforeFunction, $countAfterFunction);
    }


    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
