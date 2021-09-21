<?php
namespace RKW\RkwRegistration\Tests\Functional\Service;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwEvents\Domain\Model\Event;
use RKW\RkwEvents\Domain\Model\EventReservation;
use RKW\RkwRegistration\Domain\Model\Registration;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use \RKW\RkwRegistration\Domain\Repository\ServiceRepository;
use RKW\RkwRegistration\Service\GroupService;
use RKW\RkwRegistration\Service\PrivacyService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
    public function addPrivacyDataForOptInWithoutAnySpecialObject ()
    {
        /**
         * Scenario:
         *
         * Given is an existing registration with a frontendUser
         * When we create an privacy entry
         * Then a privacy dataset is returned with registration-table relation
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        // Service
        /** @var PrivacyService $privacyService */
        $privacyService = $this->objectManager->get(PrivacyService::class);

        // Get FrontendUser
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Get Registration
        /** @var Registration $registration */
        $registration = $this->registrationRepository->findByIdentifier(1);

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
        // before final saving: We only have a reference to the registration-table
        static::assertEquals('tx_rkwregistration_domain_model_registration', $result->getForeignTable());
    }


    /**
     * @test
     */
    public function addPrivacyDataForOptInWithDisabledUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing registration with a DISABLED frontendUser
         * When we want to create an privacy entry
         * Then an exception is thrown
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        // Service
        /** @var PrivacyService $privacyService */
        $privacyService = $this->objectManager->get(PrivacyService::class);

        // Get FrontendUser
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Get Registration
        /** @var Registration $registration */
        $registration = $this->registrationRepository->findByIdentifier(1);

        // simulate an MVC request
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerActionName('someAction');
        $request->setControllerName('SomeController');

        static::expectExceptionCode(0);

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $result */
        $privacyService->addPrivacyDataForOptIn($request, $frontendUser, $registration, 'hello');
    }



    /**
     * @test
     */
    public function addPrivacyDataForOptInFinalWithEventReservation ()
    {
        /**
         * Scenario:
         *
         * Given is an existing registration with a frontendUser
         * When we finalize an privacy entry
         * Then a privacy dataset is returned with eventReservation-table relation
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        // Service
        /** @var PrivacyService $privacyService */
        $privacyService = $this->objectManager->get(PrivacyService::class);

        // Get FrontendUser
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Get Registration
        /** @var Registration $registration */
        $registration = $this->registrationRepository->findByIdentifier(1);

        // set an object as data to registration
        /** @var EventReservation $eventReservation */
        $eventReservation = GeneralUtility::makeInstance(EventReservation::class);
        $registration->setData($eventReservation);

        // simulate an MVC request
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerActionName('someAction');
        $request->setControllerName('SomeController');

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $result */
        $result = $privacyService->addPrivacyDataForOptInFinal($request, $frontendUser, $registration, 'hello');

        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\Privacy', $result);
        // the frontendUser is set
        static::assertEquals($frontendUser->getUid(), $result->getFrontendUser()->getUid());
        // after final saving: We only have a reference to the origin data-table (here: event)
        static::assertEquals('tx_rkwevents_domain_model_eventreservation', $result->getForeignTable());
    }



    /**
     * @test
     */
    public function addPrivacyDataForOptInFinalWithoutSpecificData ()
    {
        /**
         * Scenario:
         *
         * Given is an existing registration with a frontendUser
         * When we finalize an privacy entry
         * Then a privacy dataset is returned WITHOUT table relation
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        // Service
        /** @var PrivacyService $privacyService */
        $privacyService = $this->objectManager->get(PrivacyService::class);

        // Get FrontendUser
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // Get Registration
        /** @var Registration $registration */
        $registration = $this->registrationRepository->findByIdentifier(1);

        // to not set any further data to registration

        // simulate an MVC request
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerActionName('someAction');
        $request->setControllerName('SomeController');

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $result */
        $result = $privacyService->addPrivacyDataForOptInFinal($request, $frontendUser, $registration, 'hello');

        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\Privacy', $result);
        // the frontendUser is set
        static::assertEquals($frontendUser->getUid(), $result->getFrontendUser()->getUid());
        // after final saving: We only have a reference to the origin data-table (here: event)
        static::assertEquals(null, $result->getForeignTable());
    }



    /**
     * @test
     */
    public function addPrivacyDataForEventReservation ()
    {
        /**
         * Scenario:
         *
         * Given is an existing frontendUser
         * When we create an EventReservation
         * Then a privacy dataset is returned with eventReservation-table relation
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        // Service
        /** @var PrivacyService $privacyService */
        $privacyService = $this->objectManager->get(PrivacyService::class);

        // Get FrontendUser
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // set an object as data to registration
        /** @var EventReservation $eventReservation */
        $eventReservation = GeneralUtility::makeInstance(EventReservation::class);

        // simulate an MVC request
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerActionName('someAction');
        $request->setControllerName('SomeController');

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $result */
        $result = $privacyService->addPrivacyData($request, $frontendUser, $eventReservation, 'hello');

        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\Privacy', $result);
        // the frontendUser is set
        static::assertEquals($frontendUser->getUid(), $result->getFrontendUser()->getUid());
        // after final saving: We only have a reference to the origin data-table (here: event)
        static::assertEquals('tx_rkwevents_domain_model_eventreservation', $result->getForeignTable());
    }



    /**
     * @test
     */
    public function addPrivacyDataForEventReservationWithDisabledUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing and DISABLED frontendUser
         * When we create an EventReservation
         * Then a privacy dataset is returned with eventReservation-table relation
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        // Service
        /** @var PrivacyService $privacyService */
        $privacyService = $this->objectManager->get(PrivacyService::class);

        // Get FrontendUser
        $frontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // set an object as data to registration
        /** @var EventReservation $eventReservation */
        $eventReservation = GeneralUtility::makeInstance(EventReservation::class);

        // simulate an MVC request
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerActionName('someAction');
        $request->setControllerName('SomeController');

        static::expectExceptionCode(0);

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $result */
        $privacyService->addPrivacyData($request, $frontendUser, $eventReservation, 'hello');
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}
