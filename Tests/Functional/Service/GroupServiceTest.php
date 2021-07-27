<?php
namespace RKW\RkwRegistration\Tests\Functional\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use \RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use RKW\RkwRegistration\Service\FrontendUserRegisterService;
use RKW\RkwRegistration\Service\GroupService;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

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
 * GroupServiceTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GroupServiceTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
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
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    private $frontendUserGroupRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    private $registrationRepository = null;

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
        $this->importDataSet(__DIR__ . '/GroupServiceTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Tests/Functional/Service/GroupServiceTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        // Repository
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->frontendUserGroupRepository = $this->objectManager->get(FrontendUserGroupRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'mail@default.rkw';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW Default';

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }



    /**
     * @test
     */
    public function getMandatoryFieldsOfUserFromTypoScript ()
    {
        /**
         * Scenario:
         *
         * Given is only the service
         * When we want to get mandatory fields (for some frontendUser)
         * Then some basic data will set to the frontendUser (via TypoScript)
         */

        // Service
        /** @var GroupService $groupService */
        $groupService = $this->objectManager->get(GroupService::class);

        $requiredFields = $groupService->getMandatoryFieldsOfUser();

        static::assertNotEmpty($requiredFields);
    }



    /**
     * @test
     */
    public function getMandatoryFieldsOfUserFromUserGroup()
    {
        /**
         * Scenario:
         *
         * Given is only the service
         * When we want to get mandatory fields (for some frontendUser)
         * Then some basic data will set to the frontendUser (via frontendUserGroup)
         */

        /** @var GroupService $groupService */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(55);

        // Service
        /** @var GroupService $groupService */
        $groupService = $this->objectManager->get(GroupService::class);

        $requiredFields = $groupService->getMandatoryFieldsOfUser(null, $frontendUserGroup);

        static::assertEquals("something", $requiredFields[0]);
    }



    /**
     * @test
     */
    public function getMandatoryFieldsOfUserFromUserGroupOfFrontendUser()
    {
        /**
         * Scenario:
         *
         * Given is only the service
         * When we want to get mandatory fields (for some frontendUser)
         * Then some basic data will set to the frontendUser (via TypoScript AND frontendUserGroup)
         */

        $this->importDataSet(__DIR__ . '/GroupServiceTest/Fixtures/Database/Check10.xml');

        /** @var GroupService $groupService */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        // Service
        /** @var GroupService $groupService */
        $groupService = $this->objectManager->get(GroupService::class);

        $requiredFields = $groupService->getMandatoryFieldsOfUser($frontendUser);

        /*
          FrontendUSResult:
          array(4) {
              [0] =>
              string(5) "email"
              [1] =>
              string(9) "firstName"
              [2] =>
              string(8) "lastName"
              [3] =>
              string(9) "something"
            }
         */

        static::assertEquals("something", $requiredFields[3]);
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}