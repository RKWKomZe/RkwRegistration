<?php
namespace RKW\RkwRegistration\Tests\Integration\DataProtection;

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
use RKW\RkwRegistration\Domain\Model\Consent;
use RKW\RkwRegistration\Domain\Repository\ConsentRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\OptInRepository;
use RKW\RkwRegistration\DataProtection\ConsentHandler;
use RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * ConsentHandlerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ConsentHandlerTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ConsentHandlerTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/ajax_api',
        'typo3conf/ext/core_extended',
        'typo3conf/ext/rkw_registration',
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [
    ];


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository|null
     */
    private ?FrontendUserRepository $frontendUserRepository = null;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\OptInRepository|null
     */
    private ?OptInRepository $optInRepository = null;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository|null
     */
    private ?ShippingAddressRepository $shippingAddressRepository = null;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ConsentRepository|null
     */
    private ?ConsentRepository $consentRepository = null;


    /**
     * @var ?ObjectManager \TYPO3\CMS\Extbase\Object\ObjectManager|null
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
                'EXT:core_extended/Configuration/TypoScript/setup.txt',
                'EXT:core_extended/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // Repository
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->optInRepository = $this->objectManager->get(OptInRepository::class);
        $this->shippingAddressRepository = $this->objectManager->get(ShippingAddressRepository::class);
        $this->consentRepository = $this->objectManager->get(ConsentRepository::class);


        // some default values for testing
        $_SERVER['HTTP_HOST'] = 'vollhorst.com';
        $_SERVER['REQUEST_URI'] = 'http://request.uri';
        $_SERVER['HTTP_REFERER'] = 'http://referrer.uri';
        $_SERVER['HTTP_USER_AGENT'] = 'Dr. No';
        $_SERVER['REMOTE_ADDR'] = '1.8.4.5';

    }

    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function addSetsDefaultData ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted optIn-object
         * Given this optIn-object has no data-property set
         * Given a request-object
         * Given this request-object has the controllerActionName-property set
         * Given this request-object has the controllerName-property set
         * Given this request-object has the pluginName-property set
         * When the method is called with the optIn-object as referenceObject-parameter
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then this instance has the ipAddress-property from $_SERVER-superglobal set
         * Then this instance has the userAgent-property from $_SERVER-superglobal set
         * Then this instance has the serverHost-property from $_SERVER-superglobal set
         * Then this instance has the serverUri-property from $_SERVER-superglobal set
         * Then this instance has the serverRefererUrl-property from $_SERVER-superglobal set
         * Then this instance has the controllerName-property set to the value of the request-object
         * Then this instance has the actionName-property set to the value of the request-object
         * Then this instance has the pluginName-property set to the value of the request-object
         * Then this instance has frontendUser-property set to the given frontendUser-object
         * Then this instance has the optIn-property is set to the uid of the optIn-object
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(10);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);
        $request->setControllerActionName('someAction');
        $request->setControllerName('SomeController');
        $request->setPluginName('SomePlugin');

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $optIn, 'hello');

        self::assertInstanceOf(Consent::class, $result);

        self::assertEquals('1.8.4.5', $result->getIpAddress());
        self::assertEquals('Dr. No', $result->getUserAgent());
        self::assertEquals('vollhorst.com', $result->getServerHost());
        self::assertEquals('http://request.uri', $result->getServerUri());
        self::assertEquals('http://referrer.uri', $result->getServerRefererUrl());

        self::assertEquals($request->getControllerName(), $result->getControllerName());
        self::assertEquals($request->getControllerActionName(), $result->getActionName());
        self::assertEquals($request->getPluginName(), $result->getPluginName());

        self::assertEquals($frontendUser->getUid(), $result->getFrontendUser()->getUid());
        self::assertEquals($optIn->getUid(), $result->getOptIn()->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addSetsConsentProperties ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted optIn-object
         * Given this optIn-object has no data-property set
         * Given a request-object
         * Given the _POST-superglobal has the tx_rkwregistration[privacy]-argument set to 1
         * Given the _POST-superglobal has the tx_rkwregistration[terms]-argument set to 1
         * Given the _POST-superglobal has the tx_rkwregistration[marketing]-argument set to 1
         * When the method is called with the optIn-object as referenceObject-parameter
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then the consentPrivacy-property is set to true
         * Then the consentTerms-property is set to true
         * Then the consentMarketing-property is set to true
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(10);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        $_POST['tx_rkwregistration']['privacy'] = 1;
        $_POST['tx_rkwregistration']['terms'] = 1;
        $_POST['tx_rkwregistration']['marketing'] = 1;

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $optIn, 'hello');

        self::assertInstanceOf(Consent::class, $result);

        self::assertTrue($result->getConsentPrivacy());
        self::assertTrue($result->getConsentTerms());
        self::assertTrue($result->getConsentMarketing());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addGivenOptInObjectWithoutDataObjectRefersToOptIn ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted optIn-object
         * Given this optIn-object has no data-property set
         * Given a request-object
         * When the method is called with the optIn-object as referenceObject-parameter
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then this instance has the foreignTable-property set to tx_rkwregistration_domain_model_optin
         * Then this instance has the foreignUid-property is set to the uid of the optIn-object
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(10);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(10);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $optIn, 'hello');

        self::assertInstanceOf(Consent::class, $result);

        self::assertEquals('tx_rkwregistration_domain_model_optin', $result->getForeignTable());
        self::assertEquals($optIn->getUid(), $result->getForeignUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addGivenOptInObjectWithDataObjectRefersToThisDataObject ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted optIn-object
         * Given this optIn-object has a persisted shippingAddress-object set as data-property
         * Given a request-object
         * When the method is called with the optIn-object as referenceObject-parameter
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then this instance has the foreignTable-property set to tx_rkwregistration_domain_model_shippingaddress
         * Then this instance has the foreignUid-property set to the uid of the shippingAddress-object
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByIdentifier(20);
        $optIn->setData($shippingAddress);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $optIn, 'hello');

        self::assertInstanceOf(Consent::class, $result);

        self::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $result->getForeignTable());
        self::assertEquals($shippingAddress->getUid(), $result->getForeignUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addGivenNonOptInObjectRefersToThisObject ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted shippingAddress-object
         * Given no optIn-object
         * Given a request-object
         * When the method is called with the shippingAddress-object as referenceObject
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then this instance has the optIn-property set to null
         * Then this instance has the foreignTable-property set to tx_rkwregistration_domain_model_shippingaddress
         * Then this instance has the foreignUid-property set to the uid of the shippingAddress-object
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByIdentifier(20);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $shippingAddress, 'hello');

        self::assertInstanceOf(Consent::class, $result);

        self::assertNull($result->getOptIn());

        self::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $result->getForeignTable());
        self::assertEquals($shippingAddress->getUid(), $result->getForeignUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addGivenObjectStorageRefersToObjectsOfObjectStorage ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given an objectStorage-object
         * Given that objectStorage-object contains two persisted shippingAddress-objects
         * Given no optIn-object
         * Given a request-object
         * When the method is called with the objectStorage-object as referenceObject
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then this instance has the optIn-property set to null
         * Then this instance has the foreignTable-property set to tx_rkwregistration_domain_model_shippingaddress
         * Then this instance has the foreignUid-property set to the uids of the shippingAddress-objects as comma-separated list
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddressTwo */
        $shippingAddressTwo = $this->shippingAddressRepository->findByIdentifier(21);

        $objectStorage = GeneralUtility::makeInstance(ObjectStorage::class);
        $objectStorage->attach($shippingAddress);
        $objectStorage->attach($shippingAddressTwo);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $objectStorage, 'hello');

        self::assertInstanceOf(Consent::class, $result);

        self::assertNull($result->getOptIn());

        self::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $result->getForeignTable());
        self::assertEquals($shippingAddress->getUid() . ',' . $shippingAddressTwo->getUid(), $result->getForeignUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addGivenApprovedOptInObjectWithoutDataObjectRefersToOptInAndLinksToParent ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted optIn-object
         * Given this optIn-object has no data-property set
         * Given this optIn-object is approved by the admins
         * Given this optIn-object is approved by the user
         * Given a request-object
         * Given the method has been called before with the optIn-object as referenceObject-parameter
         * When the method is called with optIn-object as referenceObject-parameter
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then this instance has the optIn-property is set to null
         * Then this instance has the optIn-property the consent-object generated by the first method-call is set to null
         * Then this instance has the foreignTable-property set to tx_rkwregistration_domain_model_optin
         * Then this instance has the foreignUid-property set to the uid of the optIn-object
         * Then this instance has the parent-property set to the consent-object generated by the first method-call
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(20);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        $consentHandler->add($request, $frontendUser, $optIn, 'hello');

        // fake approval by clicking the optIn links
        $optIn->setApproved(true);
        $optIn->setAdminApproved(true);

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $optIn, 'hello final');

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $preResult = $this->consentRepository->findByIdentifier(1);

        self::assertInstanceOf(Consent::class, $result);

        self::assertNull($result->getOptIn());
        self::assertNull($preResult->getOptIn());

        self::assertEquals('tx_rkwregistration_domain_model_optin', $result->getForeignTable());
        self::assertEquals($optIn->getUid(), $result->getForeignUid());

        self::assertInstanceOf(Consent::class, $result->getParent());
        self::assertEquals($preResult->getUid(), $result->getParent()->getUid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function addGivenApprovedOptInObjectWithDataObjectRefersToThisObjectAndLinksToParent ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted optIn-object
         * Given this optIn-object has a persisted shippingAddress-object set as data-property
         * Given this optIn-object is approved by the admins
         * Given this optIn-object is approved by the user
         * Given a request-object
         * Given the method has been called before with the optIn-object as referenceObject-parameter
         * When the method is called with optIn-object as referenceObject-parameter
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then this instance has the optIn-property is set to null
         * Then this instance has the optIn-property the consent-object generated by the first method-call is set to null
         * Then this instance has the foreignTable-property set to tx_rkwregistration_domain_model_shippingaddress
         * Then this instance has the foreignUid-property set to the uid of the shippingAddress-object
         * Then this instance has the parent-property set to the consent-object generated by the first method-call
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByIdentifier(20);
        $optIn->setData($shippingAddress);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        $consentHandler->add($request, $frontendUser, $optIn, 'hello');

        // fake approval by clicking the optIn links
        $optIn->setApproved(true);
        $optIn->setAdminApproved(true);

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $optIn, 'hello final');

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        $preResult = $this->consentRepository->findByIdentifier(1);

        self::assertInstanceOf(Consent::class, $result);

        self::assertNull($result->getOptIn());
        self::assertNull($preResult->getOptIn());

        self::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $result->getForeignTable());
        self::assertEquals($shippingAddress->getUid(), $result->getForeignUid());

        self::assertInstanceOf(Consent::class, $result->getParent());
        self::assertEquals($preResult->getUid(), $result->getParent()->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addGivenApprovedOptInObjectSetsConsentPropertiesOfFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted optIn-object
         * Given this optIn-object is approved by the admins
         * Given this optIn-object is approved by the user
         * Given a request-object
         * Given the _POST-superglobal has the tx_rkwregistration[privacy]-argument set to 1
         * Given the _POST-superglobal has the tx_rkwregistration[terms]-argument set to 1
         * Given the _POST-superglobal has the tx_rkwregistration[marketing]-argument set to 1
         * Given the method has been called before with the optIn-object as referenceObject-parameter
         * When the method is called with optIn-object as referenceObject-parameter and final-parameter equals true
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then txRkwRegistrationConsentTerms-property of the frontendUser is set to true
         * Then txRkwRegistrationConsentMarketing-property of the frontendUser is set to true
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\OptIn $optIn */
        $optIn = $this->optInRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByIdentifier(20);
        $optIn->setData($shippingAddress);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        $_POST['tx_rkwregistration']['privacy'] = 1;
        $_POST['tx_rkwregistration']['terms'] = 1;
        $_POST['tx_rkwregistration']['marketing'] = 1;

        $consentHandler->add($request, $frontendUser, $optIn, 'hello');

        // fake approval by clicking the optIn links
        $optIn->setApproved(true);
        $optIn->setAdminApproved(true);

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $optIn, 'hello final');

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        self::assertInstanceOf(Consent::class, $result);

        self::assertEquals(true, $frontendUser->getTxRkwregistrationConsentTerms());
        self::assertEquals(true, $frontendUser->getTxRkwregistrationConsentMarketing());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function addGivenNonOptInObjectSetsConsentPropertiesOfFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given a persisted shippingAddress-object
         * Given no optIn-object
         * Given a request-object
         * Given the _POST-superglobal has the tx_rkwregistration[privacy]-argument set to 1
         * Given the _POST-superglobal has the tx_rkwregistration[terms]-argument set to 1
         * Given the _POST-superglobal has the tx_rkwregistration[marketing]-argument set to 1
         * When the method is called with the shippingAddress-object as referenceObject
         * Then an instance of RKW\RkwRegistration\Domain\Model\Consent is returned
         * Then txRkwRegistrationConsentTerms-property of the frontendUser is set to true
         * Then txRkwRegistrationConsentMarketing-property of the frontendUser is set to true
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\DataProtection\ConsentHandler $consentHandler */
        $consentHandler = $this->objectManager->get(ConsentHandler::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByIdentifier(20);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        $_POST['tx_rkwregistration']['privacy'] = 1;
        $_POST['tx_rkwregistration']['terms'] = 1;
        $_POST['tx_rkwregistration']['marketing'] = 1;

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $result */
        $result = $consentHandler->add($request, $frontendUser, $shippingAddress, 'hello');

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->clearState();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByIdentifier(20);

        self::assertInstanceOf(Consent::class, $result);

        self::assertEquals(true, $frontendUser->getTxRkwregistrationConsentTerms());
        self::assertEquals(true, $frontendUser->getTxRkwregistrationConsentMarketing());
    }

    //===================================================================

    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
