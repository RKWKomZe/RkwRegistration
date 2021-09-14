<?php
namespace RKW\RkwRegistration\Tests\Functional\Service;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use \RKW\RkwRegistration\Domain\Repository\ServiceRepository;
use RKW\RkwRegistration\Service\GroupService;
use RKW\RkwRegistration\Service\PrivacyService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
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
 * PrivacyServiceTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PrivacyServiceTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/PrivacyServiceTest/Fixtures';

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
    protected $coreExtensionsToLoad = [
    ];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;

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
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);

    }



    /**
     * @test
     */
    public function addPrivacyDataForOptIn ()
    {
        /**
         * Scenario:
         *
         * Given is an existing registration with a frontendUser
         * When we create an privacy entry
         * Then a privacy dataset is returned
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        // Service
        /** @var PrivacyService $privacyService */
        $privacyService = $this->objectManager->get(PrivacyService::class);

        // Get FrontendUser
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Get Registration
        $registration = $this->registrationRepository->findByIdentifier(1);

        // @toDo: set an object as data to registration


        // simulate an MVC request
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerActionName('someAction');
        $request->setControllerName('SomeController');

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $result */
        $result = $privacyService->addPrivacyDataForOptIn($request, $frontendUser, $registration, 'hello');

        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\Privacy', $result);
        // the frontendUser is set
        static::assertEquals($frontendUser->getUid(), $result->getFrontendUser()->getUid());

        // @toDo: Check the correct table name of given object
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}
