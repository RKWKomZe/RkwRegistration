<?php

namespace RKW\RkwRegistration\Tests\Integration\ViewHelpers;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\ViewHelpers\GetAllFlashMessageIdentifierViewHelper;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
 * Class GetAllFlashMessageIdentifierViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetAllFlashMessageIdentifierViewHelperTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/GetAllFlashMessageIdentifierViewHelperTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration'
    ];


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(__DIR__ . '/GetAllFlashMessageIdentifierViewHelperTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => __DIR__ . '/GetAllFlashMessageIdentifierViewHelperTest/Fixtures/Frontend/Templates'
            ]
        );

    }

    /**
     * @test
     */
    public function renderReturnsArrayWithFlashMessageIdentifiers ()
    {

         // @toDo: Eigentlich ein UNIT Test, anstatt Integration?

        /**
         * Scenario:
         *
         * Given ViewHelper reads the extensions TypoScript
         * When the ViewHelper is rendered
         * Then an list (array) of flashMessage identifiers is returned
         */

        /** @var GetAllFlashMessageIdentifierViewHelper $viewHelper */
        $viewHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(GetAllFlashMessageIdentifierViewHelper::class);

        $result = $viewHelper->render();

        foreach ($result as $item) {
            static::assertStringStartsWith('extbase.flashmessages.tx_rkwregistration_', $item);
            static::assertStringEndsNotWith('extbase.flashmessages.tx_rkwregistration_', $item);
        }

        $expectedCount = 10;
        static::assertGreaterThanOrEqual($expectedCount, $result);
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}
