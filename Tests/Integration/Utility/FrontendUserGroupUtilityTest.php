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
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Utility\FrontendUserGroupUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * FrontendUserGroupUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserGroupUtilityTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserGroupUtilityTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/ajax_api',
        'typo3conf/ext/core_extended',
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
                'EXT:core_extended/Configuration/TypoScript/setup.txt',
                'EXT:core_extended/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function getMandatoryFieldsReturnsEmptyArrayIfNoFieldsDefined()
    {
        /**
         * Scenario:
         *
         * Given a frontendUserGroup-object
         * Given that object has no mandatory fields defined
         * When the method is called
         * Then an array is returned
         * Then this array is empty
         */

        $frontendUserGroup = GeneralUtility::makeInstance(FrontendUserGroup::class);
        $result = FrontendUserGroupUtility::getMandatoryFields($frontendUserGroup);
        self::assertIsArray($result);
        self::assertEmpty($result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getMandatoryFieldsReturnsArrayWithValidProperties()
    {
        /**
         * Scenario:
         *
         * Given a frontendUserGroup-object
         * Given that object has no mandatory fields defined
         * Given this definition contains two valid property-names in mixed notation
         * Given this definition contains one invalid property-name
         * When the method is called without a parameter
         * Then an array is returned
         * Then this array contains the two valid property-names
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = GeneralUtility::makeInstance(FrontendUserGroup::class);
        $frontendUserGroup->setTxRkwregistrationMembershipMandatoryFields('hamptyDamty, first_name, LastName');

        $result = FrontendUserGroupUtility::getMandatoryFields($frontendUserGroup);
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertEquals('firstName', $result[0]);
        self::assertEquals('lastName', $result[1]);
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
