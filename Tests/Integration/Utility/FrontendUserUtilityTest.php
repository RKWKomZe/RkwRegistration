<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;

use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
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
 * FrontendUserUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserUtilityTest extends FunctionalTestCase
{
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
        $this->importDataSet(__DIR__ . '/FrontendUserUtilityTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Tests/Integration/Utility/FrontendUserUtilityTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
    }


    /**
     * @test
     */
    public function convertArrayToObjectWithGivenArrayReturnsFrontendUserObject ()
    {
        /**
         * Scenario:
         *
         * Given is an array with frontendUser values
         * When he is converted to an frontendUser object
         * Then a FrontendUser object is returned
         */

        $frontendUserArray = [
            'firstName' => 'Klaus',
            'lastName' => 'Schröder',
            'email' => 'klaus@schroeder.de'
        ];

        /** @var FrontendUserUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserUtility::class);
        $frontendUserObject = $utility::convertArrayToObject($frontendUserArray);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $frontendUserObject);
    }



    /**
     * @test
     */
    public function convertArrayToObjectWithoutFilledArrayReturnsFrontendUserObject ()
    {
        /**
         * Scenario:
         *
         * Given is an array without any values
         * When he is anyway converted to an frontendUser object
         * Then a FrontendUser object is returned
         */

        // empty
        $frontendUserArray = [];

        /** @var FrontendUserUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserUtility::class);
        $frontendUserObject = $utility::convertArrayToObject($frontendUserArray);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $frontendUserObject);
    }



    /**
     * @test
     */
    public function convertArrayToObjectReturnsFrontendUserObjectWithGivenDataFromArray ()
    {
        /**
         * Scenario:
         *
         * Given is an array with frontendUser values
         * When he is converted to an frontendUser object
         * Then a FrontendUser object is returned with given data from array
         */

        $frontendUserArray = [
            'firstName' => 'Klaus',
            'lastName' => 'Schröder',
            'email' => 'klaus@schroeder.de'
        ];

        /** @var FrontendUserUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserUtility::class);
        $frontendUserObject = $utility::convertArrayToObject($frontendUserArray);

        static::assertEquals($frontendUserArray['firstName'], $frontendUserObject->getFirstName());
        static::assertEquals($frontendUserArray['lastName'], $frontendUserObject->getLastName());
        static::assertEquals($frontendUserArray['email'], $frontendUserObject->getEmail());
    }



    /**
     * @test
     */
    public function convertArrayToObjectIgnoresWrongArrayValues()
    {
        /**
         * Scenario:
         *
         * Given is an array with frontendUser values (1 faulty)
         * When he is converted to an frontendUser object
         * Then a FrontendUser object is returned with given data from array, except the faulty one
         */

        // key "abcdefghijklm" does not exists an FrontendUser property
        $frontendUserArray = [
            'firstName' => 'Klaus',
            'lastName' => 'Schröder',
            'abcdefghijklm' => 'klaus@schroeder.de'
        ];

        /** @var FrontendUserUtility $utility */
        $utility = GeneralUtility::makeInstance(FrontendUserUtility::class);
        $frontendUserObject = $utility::convertArrayToObject($frontendUserArray);
        static::assertEquals($frontendUserArray['firstName'], $frontendUserObject->getFirstName());
        static::assertEquals($frontendUserArray['lastName'], $frontendUserObject->getLastName());
        // -> NOT converted (no equal named frontendUser property given)
        static::assertNotEquals($frontendUserArray['abcdefghijklm'], $frontendUserObject->getEmail());
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}