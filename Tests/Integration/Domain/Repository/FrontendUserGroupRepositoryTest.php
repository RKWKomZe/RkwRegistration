<?php
namespace RKW\RkwRegistration\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
 * FrontendUserGroupRepositoryTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserGroupRepositoryTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserGroupRepositoryTest/Fixtures';

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
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    private $subject = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(FrontendUserGroupRepository::class);

    }

    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findServicesWithGroupWithoutClosingDateReturnsGroup()
    {

        /**
         * Scenario:
         *
         * Given is a frontendUserGroup marked as "service"
         * When the function is called
         * Then the feusergroup is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $result = $this->subject->findServices();

        foreach ($result as $userGroup) {
            static::assertInstanceOf(FrontendUserGroup::class, $userGroup);
        }

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findServicesWithMultipleGroupWithoutClosingDateReturnsGroup()
    {

        /**
         * Scenario:
         *
         * Given are two frontendUserGroups (both services)
         * When the function is called
         * Then both groups are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $result = $this->subject->findServices();

        foreach ($result as $userGroup) {
            static::assertInstanceOf(FrontendUserGroup::class, $userGroup);
        }

        static::assertCount(2, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findServicesWithGroupWhichIsNoServiceReturnsNoResults()
    {

        /**
         * Scenario:
         *
         * Given is a non service frontendUserGroup
         * When the function is called
         * Then no results returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $result = $this->subject->findServices();

        static::assertCount(0, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findServicesWithGroupWithPastClosingDAteReturnsNoResults()
    {

        /**
         * Scenario:
         *
         * Given is a closed frontend user group
         * When the function is called
         * Then no results returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        $result = $this->subject->findServices();

        static::assertCount(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findServicesWithGroupWithFutureClosingDateReturnsFrontendUserGroup()
    {

        /**
         * Scenario:
         *
         * Given is a frontendUserGroup-Service with future closing date
         * Test works until: 4079751172 -> Mon Apr 13 2099 08:12:52 GMT+0000
         * When the function is called
         * Then no results returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        // @toDo: Alternative set closing date here and persist

        $result = $this->subject->findServices();

        foreach ($result as $userGroup) {
            static::assertInstanceOf(FrontendUserGroup::class, $userGroup);
        }

    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
