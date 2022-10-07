<?php
namespace RKW\RkwRegistration\Tests\Integration\Register;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\Service;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use \RKW\RkwRegistration\Domain\Repository\ServiceRepository;
use RKW\RkwRegistration\Register\GroupFrontendUser;
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
 * GroupRegisterTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GroupRegisterTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/GroupRegisterTest/Fixtures';

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
     * @var \RKW\RkwRegistration\Domain\Repository\ServiceRepository
     */
    private $serviceRepository = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

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
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/constants.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        // Repository
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->frontendUserGroupRepository = $this->objectManager->get(FrontendUserGroupRepository::class);
        $this->serviceRepository = $this->objectManager->get(ServiceRepository::class);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'mail@default.rkw';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW Default';

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }


    /**
     * @test
     */
    public function getMandatoryFieldsOfGroupFromUserGroup()
    {
        /**
         * Scenario:
         *
         * Given is only the service
         * When we want to get mandatory fields (for some frontendUser)
         * Then some basic data will set to the frontendUser (via frontendUserGroup)
         */

        /** @var FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(55);

        // Service
        /** @var GroupFrontendUser $register */
        $register = $this->objectManager->get(GroupFrontendUser::class);

        $requiredFields = $register->getMandatoryFieldsOfGroup($frontendUserGroup);

        static::assertEquals("something", $requiredFields[0]);
    }


    /**
     * @test
     */
    public function checkTokensConfirm()
    {
        /**
         * Scenario:
         *
         * Given is a service-registration (sent via mail to an admin)
         * When the admin-user want to confirm
         * Then the service related fe_groups is added to the frontendUser
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Service $serviceRegistration */
        $serviceRegistration = $this->serviceRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $serviceRegistration->setValidUntil(time() + 60);

        // before: The user owns one usergroup with UID 55
        static::assertEquals(count($serviceRegistration->getUser()->getUsergroup()), 1);

        // Service
        /** @var GroupFrontendUser $register */
        $register = $this->objectManager->get(GroupFrontendUser::class);
        $result = $register->checkTokens($serviceRegistration->getTokenYes(), '', $serviceRegistration->getServiceSha1());

        static::assertEquals(1, $result);
        // after: The new service related usergroup with UID 1 is added
        static::assertEquals(count($serviceRegistration->getUser()->getUsergroup()), 2);
        // service registration dataset is now deleted
        static::assertNull($serviceRegistration = $this->serviceRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function checkTokensConfirmOutdatedServiceRegistration()
    {
        /**
         * Scenario:
         *
         * Given is a outdated service-registration (sent via mail to an admin)
         * When the admin-user want to confirm
         * Then the service registration is refused and deleted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Service $serviceRegistration */
        $serviceRegistration = $this->serviceRepository->findByIdentifier(1);

        // Service
        /** @var GroupFrontendUser $register */
        $register = $this->objectManager->get(GroupFrontendUser::class);
        $result = $register->checkTokens($serviceRegistration->getTokenYes(), '', $serviceRegistration->getServiceSha1());

        static::assertEquals(0, $result);
        // service registration dataset is now deleted
        static::assertNull($serviceRegistration = $this->serviceRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function checkTokensConfirmNotExistingServiceRegistration()
    {
        /**
         * Scenario:
         *
         * Given is a not existing service-registration (maybe deleted, after email with tokens was send)
         * When the admin-user want to confirm
         * Then the token check fails
         */

        /** @var \RKW\RkwRegistration\Domain\Model\Service $serviceRegistration */
        $serviceRegistration = $this->objectManager->get(Service::class);

        $serviceRegistration->setTokenYes('abcdefghi2a75d52e66cb4e2d4c37a8a36af9201');
        $serviceRegistration->setServiceSha1('123456789b4c03dfdb074910f5ae3537fba7d8c2');

        // simulate a still valid registration
        $serviceRegistration->setValidUntil(time() + 60);

        // Service
        /** @var GroupFrontendUser $register */
        $register = $this->objectManager->get(GroupFrontendUser::class);
        $result = $register->checkTokens($serviceRegistration->getTokenYes(), '', $serviceRegistration->getServiceSha1());

        static::assertEquals(0, $result);

    }



    /**
     * @test
     */
    public function checkTokensRefuse()
    {
        /**
         * Scenario:
         *
         * Given is a registration (sent via mail to an admin)
         * When the admin-user want to refuse
         * Then some basic data will set to the frontendUser (via TypoScript AND frontendUserGroup)
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Service $serviceRegistration */
        $serviceRegistration = $this->serviceRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $serviceRegistration->setValidUntil(time() + 60);

        // before: The user owns one usergroup
        static::assertEquals(count($serviceRegistration->getUser()->getUsergroup()), 1);

        // Service
        /** @var GroupFrontendUser $register */
        $register = $this->objectManager->get(GroupFrontendUser::class);
        $result = $register->checkTokens('', $serviceRegistration->getTokenNo(), $serviceRegistration->getServiceSha1());

        static::assertEquals(2, $result);
        // after: No group was added
        static::assertEquals(count($serviceRegistration->getUser()->getUsergroup()), 1);
        // service registration dataset is now deleted
        static::assertNull($serviceRegistration = $this->serviceRepository->findByIdentifier(1));
    }


    /**
     * @test
     */
    public function addUserToAllGrantedGroups()
    {
        /**
         * Scenario:
         *
         * Given is a service-registration which is enabled by admin
         * When the "addUserToAllGrantedGroups" function is called
         * Then one service is added to the user
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);


        // Service
        /** @var GroupFrontendUser $register */
        $register = $this->objectManager->get(GroupFrontendUser::class);
        $result = $register->addUserToAllGrantedGroups($frontendUser);

        static::assertEquals(1, $result);
    }


    /**
     * @test
     */
    public function addUserToAllGrantedGroupsWithNotPersistentFrontendUser()
    {
        /**
         * Scenario:
         *
         * Given is a service-registration which is enabled by admin
         * Given is a not persistent frontendUser
         * When the "addUserToAllGrantedGroups" function is called
         * Then false is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);

        // Service
        /** @var GroupFrontendUser $register */
        $register = $this->objectManager->get(GroupFrontendUser::class);
        $result = $register->addUserToAllGrantedGroups($frontendUser);

        static::assertFalse($result);
    }


    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
