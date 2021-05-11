<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwRegistration\Domain\Model\Privacy;
use RKW\RkwRegistration\Utility\DataProtectionUtility;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\BackendUserRepository;
use RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository;
use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use RKW\RkwRegistration\Domain\Repository\EncryptedDataRepository;

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
 * DataProtectionUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DataProtectionUtilityTest extends FunctionalTestCase
{
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
     * @var \RKW\RkwRegistration\Utility\DataProtectionUtility
     */
    private $subject = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\BackendUserRepository
     */
    private $backendUserRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository
     */
    private $shippingAddressRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository
     */
    private $privacyRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\EncryptedDataRepository
     */
    private $encryptedDataRepository = null;

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
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Tests/Integration/Utility/DataProtectionUtilityTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',

            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(DataProtectionUtility::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->backendUserRepository = $this->objectManager->get(BackendUserRepository::class);
        $this->shippingAddressRepository = $this->objectManager->get(ShippingAddressRepository::class);
        $this->privacyRepository = $this->objectManager->get(PrivacyRepository::class);
        $this->encryptedDataRepository = $this->objectManager->get(EncryptedDataRepository::class);

        $this->subject->setEncryptionKey('o4uSZ0oo4zTFIIoN2NkuBBwyS6Lv3v/EYVObucPHcW8=');

    }
    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function deleteAllExpiredAndDisabledDeletesAllExpiredAndDisabledFrontendUsers()
    {

        /**
         * Scenario:
         *
         * Given there are five frontend user
         * Given that one of the frontend users has been expired since more days then configured for deletion
         * Given that one of the frontend users will expire in the future
         * Given that one of the frontend users is disabled since more days then configured for deletion
         * Given that one of the frontend users is disabled since less than days then configured for deletion
         * Given that one of the frontend users is neither disabled nor expired
         * When I delete all expired users
         * Then the frontend user which expired since more days then configured for deletion is deleted
         * Then the frontend user which is disabled since more days then configured for deletion is deleted
         * Then the other three frontend users are not deleted
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check120.xml');

        $this->subject->deleteAllExpiredAndDisabled();
        $this->persistenceManager->persistAll();

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result*/
        $query = $this->frontendUserRepository->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $result = $query->execute()->toArray();

        static::assertCount(3, $result);
        static::assertEquals(2, $result[0]->getUid());
        static::assertEquals(3, $result[1]->getUid());
        static::assertEquals(5, $result[2]->getUid());


    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeAndEncryptAllAnonymizesAndEncryptsFrontendUserData()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user has been deleted since more days then configured for anonymization
         * When I anonymize all deleted users
         * Then the user data is anonymised
         * Then the dataProtectionStatus is set to 1
         * Then the user data is encrypted in separate table
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check30.xml');

        $this->subject->anonymizeAndEncryptAll();
        $this->persistenceManager->persistAll();

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertEquals('anonymous1@rkw.de', $frontendUser->getUsername());
        static::assertEquals('anonymous1@rkw.de', $frontendUser->getEmail());
        static::assertEquals(1, $frontendUser->getTxRkwregistrationDataProtectionStatus());

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(1);

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\EncryptedData::class, $encryptedData);
        static::assertEquals($frontendUser, $encryptedData->getFrontendUser());
        static::assertEquals(hash('sha256', 'lauterbach@spd.de'), $encryptedData->getSearchKey());
        static::assertEquals('fe_users', $encryptedData->getForeignTable());
        static::assertEquals(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $encryptedData->getForeignClass());
        static::assertEquals(1, $encryptedData->getForeignUid());



    }



    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeAndEncryptAllAnonymizesAndEncryptsRelatedData()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given this frontend user has a shipping address
         * Given this frontend user has privacy data according to his order
         * Given the frontend user has been deleted since more days then configured for anonymization
         * When I anonymize all deleted users
         * Then the shipping address of the frontend user is anonymised
         * Then the shipping address of the frontend user is encrypted
         * Then the shipping address of the privacy data is anonymised
         * Then the shipping address of the privacy data is encrypted
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check40.xml');

        $this->subject->anonymizeAndEncryptAll();
        $this->persistenceManager->persistAll();

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress  = $this->shippingAddressRepository->findByUid(1);

        static::assertEquals('Anonymous Anonymous', $shippingAddress->getFullName());

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(2);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\EncryptedData::class, $encryptedData);
        static::assertEquals($frontendUser, $encryptedData->getFrontendUser());
        static::assertEquals(hash('sha256', $frontendUser->getEmail()), $encryptedData->getSearchKey());
        static::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $encryptedData->getForeignTable());
        static::assertEquals(\RKW\RkwRegistration\Domain\Model\ShippingAddress::class, $encryptedData->getForeignClass());
        static::assertEquals(1, $encryptedData->getForeignUid());


        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $privacy */
        $privacy  = $this->privacyRepository->findByUid(1);

        static::assertEquals('127.0.0.1', $privacy->getIpAddress());
        static::assertEquals('Anonymous 1.0', $privacy->getUserAgent());

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(3);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertEquals(1, $encryptedData->getFrontendUser()->getUid());
        static::assertEquals(hash('sha256', $frontendUser->getEmail()), $encryptedData->getSearchKey());
        static::assertEquals(1, $encryptedData->getForeignUid());
        static::assertEquals('tx_rkwregistration_domain_model_privacy', $encryptedData->getForeignTable());
        static::assertEquals('RKW\RkwRegistration\Domain\Model\Privacy', $encryptedData->getForeignClass());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeAndEncryptAllAnonymizesAndEncryptsDeletedRelatedData()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given this frontend user has a deleted shipping address
         * Given the frontend user has been deleted since more days then configured for anonymization
         * When I anonymize all deleted users
         * Then the shipping address of the frontend user is anonymised
         * Then the shipping address of the frontend user is encrypted
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check50.xml');

        $this->subject->anonymizeAndEncryptAll();
        $this->persistenceManager->persistAll();

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress  = $this->shippingAddressRepository->findByUid(1);

        static::assertEquals('Anonymous Anonymous', $shippingAddress->getFullName());

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(2);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\EncryptedData::class, $encryptedData);
        static::assertEquals($frontendUser, $encryptedData->getFrontendUser());
        static::assertEquals(hash('sha256', $frontendUser->getEmail()), $encryptedData->getSearchKey());
        static::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $encryptedData->getForeignTable());
        static::assertEquals(\RKW\RkwRegistration\Domain\Model\ShippingAddress::class, $encryptedData->getForeignClass());
        static::assertEquals(1, $encryptedData->getForeignUid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeAndEncryptAllAnonymizesAndEncryptsHiddenRelatedData()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given this frontend user has a hidden shipping address
         * Given the frontend user has been deleted since more days then configured for anonymization
         * When I anonymize all deleted users
         * Then the shipping address of the frontend user is anonymised
         * Then the shipping address of the frontend user is encrypted
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check60.xml');

        $this->subject->anonymizeAndEncryptAll();
        $this->persistenceManager->persistAll();

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress  = $this->shippingAddressRepository->findByUid(1);

        static::assertEquals('Anonymous Anonymous', $shippingAddress->getFullName());

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(2);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\EncryptedData::class, $encryptedData);
        static::assertEquals($frontendUser, $encryptedData->getFrontendUser());
        static::assertEquals(hash('sha256', $frontendUser->getEmail()), $encryptedData->getSearchKey());
        static::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $encryptedData->getForeignTable());
        static::assertEquals(\RKW\RkwRegistration\Domain\Model\ShippingAddress::class, $encryptedData->getForeignClass());
        static::assertEquals(1, $encryptedData->getForeignUid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeAndEncryptAllAnonymizesIgnoresStoragePidForRelatedData()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given this frontend user has a shipping address with a different storage pid
         * Given the frontend user has been deleted since more days then configured for anonymization
         * When I anonymize all deleted users
         * Then the shipping address of the frontend user is anonymised
         * Then the shipping address of the frontend user is encrypted
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check70.xml');

        $this->subject->anonymizeAndEncryptAll();
        $this->persistenceManager->persistAll();

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress  = $this->shippingAddressRepository->findByUid(1);

        static::assertEquals('Anonymous Anonymous', $shippingAddress->getFullName());

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(2);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\EncryptedData::class, $encryptedData);
        static::assertEquals($frontendUser, $encryptedData->getFrontendUser());
        static::assertEquals(hash('sha256', $frontendUser->getEmail()), $encryptedData->getSearchKey());
        static::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $encryptedData->getForeignTable());
        static::assertEquals(\RKW\RkwRegistration\Domain\Model\ShippingAddress::class, $encryptedData->getForeignClass());
        static::assertEquals(1, $encryptedData->getForeignUid());

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeObjectThrowsExceptionIfFeUserIsNotExisting()
    {

        /**
         * Scenario:
         *
         * Given there is a non persisted frontend user
         * When I anonymize the frontend user
         * Then an error is thrown
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(\RKW\RkwRegistration\Domain\Model\FrontendUser::class);

        static::expectException(\RKW\RkwRegistration\Exception::class);

        $this->subject->anonymizeObject($frontendUser, $frontendUser);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeObjectDoesNotAnonymizeIfClassIsNotConfigured()
    {

        /**
         * Scenario:
         *
         * Given there is dataset for which no configuration is defined
         * When I anonymize this data
         * Then the data is not anonymized
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid(1);

        static::assertFalse($this->subject->anonymizeObject($backendUser, $frontendUser));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeObjectAnonymizesFrontendUserData()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * When I anonymize the frontend user
         * Then the user data is anonymized
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check11.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertTrue($this->subject->anonymizeObject($frontendUser, $frontendUser));

        static::assertEquals('anonymous1@rkw.de', $frontendUser->getUsername());
        static::assertEquals('anonymous1@rkw.de', $frontendUser->getEmail());
        static::assertEquals('Anonymous', $frontendUser->getFirstName());
        static::assertEquals('Anonymous', $frontendUser->getLastName());
        static::assertEquals('Anonymous Anonymous', $frontendUser->getName());
        static::assertEquals('', $frontendUser->getCompany());
        static::assertEquals('', $frontendUser->getAddress());
        static::assertEquals('', $frontendUser->getZip());
        static::assertEquals('', $frontendUser->getCity());
        static::assertEquals('', $frontendUser->getTelephone());
        static::assertEquals('', $frontendUser->getFax());
        static::assertEquals('', $frontendUser->getTitle());
        static::assertEquals('', $frontendUser->getWww());
        static::assertEquals(99, $frontendUser->getTxRkwregistrationGender());
        static::assertEquals('', $frontendUser->getTxRkwregistrationMobile());
        static::assertEquals('', $frontendUser->getTxRkwregistrationFacebookUrl());
        static::assertEquals('', $frontendUser->getTxRkwregistrationTwitterUrl());
        static::assertEquals('', $frontendUser->getTxRkwregistrationXingUrl());
        static::assertEquals(0, $frontendUser->getTxRkwregistrationTwitterId());
        static::assertEquals('', $frontendUser->getTxRkwregistrationFacebookId());

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeObjectThrowsExceptionIfShippingAddressIsNotExisting()
    {

        /**
         * Scenario:
         *
         * Given there is a non persisted shipping address
         * When I anonymize the shipping address
         * Then an error is thrown
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(\RKW\RkwRegistration\Domain\Model\ShippingAddress::class);

        static::expectException(\RKW\RkwRegistration\Exception::class);

        $this->subject->anonymizeObject($shippingAddress, $frontendUser);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeObjectAnonymizesShippingAddressesOfUser()
    {

        /**
         * Scenario:
         *
         * Given there is a shipping address
         * When I anonymize the shipping address
         * Then the shipping address is anonymized
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress  = $this->shippingAddressRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertTrue($this->subject->anonymizeObject($shippingAddress, $frontendUser));
        static::assertEquals(99, $shippingAddress->getGender());
        static::assertEquals('Anonymous', $shippingAddress->getFirstName());
        static::assertEquals('Anonymous', $shippingAddress->getLastName());
        static::assertEquals('Anonymous Anonymous', $shippingAddress->getFullName());
        static::assertEquals('', $shippingAddress->getCompany());
        static::assertEquals('', $shippingAddress->getAddress());
        static::assertEquals('', $shippingAddress->getZip());
        static::assertEquals('', $shippingAddress->getCity());

    }

    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function encryptObjectThrowsExceptionIfFeUserIsNotExisting()
    {

        /**
         * Scenario:
         *
         * Given there is a non persisted frontend user
         * When I encrypt the frontend user
         * Then an error is thrown
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(\RKW\RkwRegistration\Domain\Model\FrontendUser::class);

        static::expectException(\RKW\RkwRegistration\Exception::class);

        $this->subject->encryptObject($frontendUser, $frontendUser);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function encryptObjectEncryptsFrontendUserData()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * When I encrypt the frontend user
         * Then the original object is not encrypted
         * Then the encrypted user data is returned
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $encryptedData = $this->subject->encryptObject($frontendUser, $frontendUser);

        static::assertEquals('spd@test.de', $frontendUser->getUsername());
        static::assertEquals('lauterbach@spd.de', $frontendUser->getEmail());

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\EncryptedData::class, $encryptedData);

        $encryptedDataArray = $encryptedData->getEncryptedData();

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $encryptedData->getFrontendUser());
        static::assertEquals(1, $encryptedData->getFrontendUser()->getUid());
        static::assertEquals(hash('sha256', $frontendUser->getEmail()), $encryptedData->getSearchKey());
        static::assertEquals(1, $encryptedData->getForeignUid());
        static::assertEquals('fe_users', $encryptedData->getForeignTable());
        static::assertEquals('RKW\RkwRegistration\Domain\Model\FrontendUser', $encryptedData->getForeignClass());

        static::assertCount(22, $encryptedDataArray);
        static::assertEquals(49, strlen($encryptedDataArray['username']));

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function encryptObjectThrowsExceptionIfShippingAddressIsNotExisting()
    {

        /**
         * Scenario:
         *
         * Given there is a non persisted shipping address
         * When I encrypt the shipping address
         * Then an error is thrown
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(\RKW\RkwRegistration\Domain\Model\ShippingAddress::class);

        static::expectException(\RKW\RkwRegistration\Exception::class);

        $this->subject->encryptObject($shippingAddress, $frontendUser);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function encryptObjectEncryptsShippingAddressesOfUser()
    {

        /**
         * Scenario:
         *
         * Given there is a shipping address
         * When I encrypt the shipping address
         * Then the original object is not encrypted
         * Then the encrypted user data is returned
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress  = $this->shippingAddressRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $encryptedData = $this->subject->encryptObject($shippingAddress, $frontendUser);

        static::assertEquals('Karl', $shippingAddress->getFirstName());
        static::assertEquals('Lauterbach', $shippingAddress->getLastName());

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\EncryptedData::class, $encryptedData);
        $encryptedDataArray = $encryptedData->getEncryptedData();

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $encryptedData->getFrontendUser());
        static::assertEquals(1, $encryptedData->getFrontendUser()->getUid());
        static::assertEquals(hash('sha256', $frontendUser->getEmail()), $encryptedData->getSearchKey());
        static::assertEquals(1, $encryptedData->getForeignUid());
        static::assertEquals('tx_rkwregistration_domain_model_shippingaddress', $encryptedData->getForeignTable());
        static::assertEquals('RKW\RkwRegistration\Domain\Model\ShippingAddress', $encryptedData->getForeignClass());

        static::assertCount(7, $encryptedDataArray);
        static::assertEquals(49, strlen($encryptedDataArray['firstName']));
        static::assertEquals(49, strlen($encryptedDataArray['lastName']));

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function encryptObjectThrowsExceptionIfPrivacyDataIsNotExisting()
    {

        /**
         * Scenario:
         *
         * Given there is a non persisted privacy data
         * When I encrypt the shipping address
         * Then an error is thrown
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check25.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $privacy*/
        $privacy = GeneralUtility::makeInstance(\RKW\RkwRegistration\Domain\Model\Privacy::class);

        static::expectException(\RKW\RkwRegistration\Exception::class);

        $this->subject->encryptObject($privacy, $frontendUser);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function encryptObjectEncryptsPrivacyDataOfUser()
    {

        /**
         * Scenario:
         *
         * Given there is a shipping address
         * When I encrypt the shipping address
         * Then the original object is not encrypted
         * Then the encrypted user data is returned
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check25.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $privacy */
        $privacy  = $this->privacyRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $encryptedData = $this->subject->encryptObject($privacy, $frontendUser);

        static::assertEquals('172.28.128.1', $privacy->getIpAddress());
        static::assertEquals('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:85.0) Gecko/20100101 Firefox/85.0', $privacy->getUserAgent());

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\EncryptedData::class, $encryptedData);
        $encryptedDataArray = $encryptedData->getEncryptedData();

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $encryptedData->getFrontendUser());
        static::assertEquals(1, $encryptedData->getFrontendUser()->getUid());
        static::assertEquals(hash('sha256', $frontendUser->getEmail()), $encryptedData->getSearchKey());
        static::assertEquals(1, $encryptedData->getForeignUid());
        static::assertEquals('tx_rkwregistration_domain_model_privacy', $encryptedData->getForeignTable());
        static::assertEquals('RKW\RkwRegistration\Domain\Model\Privacy', $encryptedData->getForeignClass());

        static::assertCount(2, $encryptedDataArray);
        static::assertEquals(49, strlen($encryptedDataArray['ipAddress']));
        static::assertEquals(133, strlen($encryptedDataArray['userAgent']));

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function decryptObjectReturnsNullIfForeignClassDoesNotExist()
    {

        /**
         * Scenario:
         *
         * Given there is an encryptedData-object
         * Given that encryptedData-object has no valid foreignClass set
         * When I decrypt the encryptedData-object
         * Then null is returned
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check80.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(1);

        static::assertNull($this->subject->decryptObject($encryptedData, 'lauterbach@spd.de'));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function decryptObjectReturnsNullIfForeignClassHasNoPropertyMap()
    {

        /**
         * Scenario:
         *
         * Given there is an encryptedData-object
         * Given that encryptedData-object has an existing foreignClass set
         * Given that foreignClass has no propertyMap defined
         * When I decrypt the encryptedData-object
         * Then null is returned
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check90.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(1);

        static::assertNull($this->subject->decryptObject($encryptedData, 'lauterbach@spd.de'));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function decryptObjectReturnsNullIfForeignUidDoesNotExists()
    {

        /**
         * Scenario:
         *
         * Given there is an encryptedData-object
         * Given that encryptedData-object has an existing foreignClass set
         * Given that foreignClass has no propertyMap defined
         * When I decrypt the encryptedData-object
         * Then null is returned
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check100.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(1);

        static::assertNull($this->subject->decryptObject($encryptedData, 'lauterbach@spd.de'));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function decryptObjectReturnsDecryptedObject()
    {

        /**
         * Scenario:
         *
         * Given there is an encryptedData-object
         * Given that encryptedData-object has an existing foreignClass set
         * Given that foreignClass has a propertyMap defined
         * When I decrypt the encryptedData-object
         * Then the decrypted object is returned
         * Then all data has been decrypted again
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check110.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
        $encryptedData = $this->encryptedDataRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->subject->decryptObject($encryptedData, 'lauterbach@spd.de');

        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $this->subject->decryptObject($encryptedData, 'lauterbach@spd.de'));
        static::assertEquals($frontendUser->getUid(), $result->getUid());
        static::assertEquals('spd@test.de', $result->getUsername());
        static::assertEquals('lauterbach@spd.de', $result->getEmail());
        static::assertEquals('Karl', $result->getFirstName());
        static::assertEquals('Lauterbach', $result->getLastName());
        static::assertEquals('SPD', $result->getCompany());
        static::assertEquals('Straßenring 123', $result->getAddress());
        static::assertEquals('10969', $result->getZip());
        static::assertEquals('Hamburg', $result->getCity());
        static::assertEquals('069/1346', $result->getTelephone());
        static::assertEquals('069/123456789', $result->getFax());
        static::assertEquals('Dr. Prof.', $result->getTitle());
        static::assertEquals('https://www.spd.de', $result->getWww());
        static::assertEquals(1, $result->getTxRkwregistrationGender());
        static::assertEquals('0179/100224557', $result->getTxRkwregistrationMobile());
        static::assertEquals('https://www.facebook.com/lauterbach', $result->getTxRkwregistrationFacebookUrl());
        static::assertEquals('https://www.twitter.com/lauterbach', $result->getTxRkwregistrationTwitterUrl());
        static::assertEquals('https://www.xing.de/lauterbach', $result->getTxRkwregistrationXingUrl());
        static::assertEquals('12345', $result->getTxRkwregistrationFacebookId());
        static::assertEquals('12345', $result->getTxRkwregistrationTwitterId());

    }

    //===================================================================

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getPropertyMapByModelClassNameChecksForExistingClasses()
    {

        /**
         * Scenario:
         *
         * Given there is no configuration for a model-class
         * When I try to fetch the propertyMap for this model-class
         * Then an empty array is returned
         */
        static::assertEmpty($this->subject->getPropertyMapByModelClassName('Test\Model'));
    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getPropertyMapByModelClassNameReturnsPropertyMap()
    {

        /**
         * Scenario:
         *
         * Given there is a configuration for a model-class
         * When I try to fetch the propertyMap for this model-class
         * Then the propertyMap is returned
         * Then the propertyMap contains the configured properties and their values
         */

        $result = $this->subject->getPropertyMapByModelClassName('RKW\RkwRegistration\Domain\Model\FrontendUser');
        static::assertInternalType('array', $result);
        static::assertEquals($result['username'], 'anonymous{UID}@rkw.de');

    }

    //===================================================================

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getFrontendUserPropertyByModelClassNameChecksForExistingClasses()
    {

        /**
         * Scenario:
         *
         * Given there is a non existing model-class
         * When I try to fetch the frontendUserGetter for this model-class
         * Then empty is returned
         */
        static::assertEmpty($this->subject->getFrontendUserPropertyByModelClassName('Test\Model'));
    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getFrontendUserPropertyByModelClassNameChecksForFrontendUserMethod()
    {

        /**
         * Scenario:
         *
         * Given there is a existing model-class
         * Given this model class has no reference to a frontend user
         * When I try to fetch the frontendUserGetter for this model-class
         * Then empty is returned
         */
        static::assertEmpty($this->subject->getFrontendUserPropertyByModelClassName('RKW\RkwRegistration\Domain\Model\BackendUser'));
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
    */
    public function getFrontendUserPropertyByModelClassNameChecksForValidReference()
    {

        /**
         * Scenario:
         *
         * Given there is a existing model-class
         * Given this model class has a reference to a frontend user
         * Given the configured mapping field does not refer to the fe_user table
         * When I try to fetch the frontendUserGetter for this model-class
         * Then empty is returned
        */
        static::assertEmpty($this->subject->getFrontendUserPropertyByModelClassName('RKW\RkwRegistration\Domain\Model\Service'));
    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getFrontendUserPropertyByModelClassNameReturnsGetterMethod()
    {

        /**
         * Scenario:
         *
         * Given there is a existing model-class
         * Given this model class has a reference to a frontend user
         * When I try to fetch the frontendUserGetter for this model-class
         * Then the corresponding frontendUserGetter is returned
         */
        static::assertEquals('frontendUser', $this->subject->getFrontendUserPropertyByModelClassName('RKW\RkwRegistration\Domain\Model\ShippingAddress'));
    }

    //===================================================================

    /**
     * @test
     */
    public function getRepositoryByModelClassNameChecksForExistingClasses()
    {

        /**
         * Scenario:
         *
         * Given there is a non existing model-class
         * When I try to fetch the repository for this model-class
         * Then null is returned
         */
        static::assertNull($this->subject->getRepositoryByModelClassName('Test\Model'));
    }


    /**
     * @test
     */
    public function getRepositoryByModelClassNameReturnsRepository()
    {

        /**
         * Scenario:
         *
         * Given there is an existing model-class
         * When I try to fetch the repository for this model-class
         * Then the corresponding repository is returned
         */
        static::assertInstanceOf(
            \RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository::class,
            $this->subject->getRepositoryByModelClassName('RKW\RkwRegistration\Domain\Model\ShippingAddress')
        );
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
