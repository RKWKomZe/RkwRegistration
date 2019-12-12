<?php
namespace RKW\RkwRegistration\Tests\Integration\Utilities;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwRegistration\Utilities\DataProtectionUtility;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository;
use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;

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
     * @var \RKW\RkwRegistration\Utilities\DataProtectionUtility
     */
    private $subject = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository
     */
    private $shippingAddressRepository = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository
     */
    private $privacyRepository = null;

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
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(DataProtectionUtility::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->shippingAddressRepository = $this->objectManager->get(ShippingAddressRepository::class);
        $this->privacyRepository = $this->objectManager->get(PrivacyRepository::class);

    }



    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeThrowsExceptionIfFeUserIsNotExisting()
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

        $this->subject->anonymize($frontendUser);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeAnonymizesFrontendUserData()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * When I anonymize the frontend user
         * Then the user data is anonymized
         */
        $this->importDataSet(__DIR__ . '/DataProtectionUtilityTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $this->subject->anonymize($frontendUser);

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
    public function anonymizeShippingAddressThrowsExceptionIfShippingAddressIsNotExisting()
    {

        /**
         * Scenario:
         *
         * Given there is a non persisted shipping address
         * When I anonymize the shipping address
         * Then an error is thrown
         */

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(\RKW\RkwRegistration\Domain\Model\ShippingAddress::class);

        static::expectException(\RKW\RkwRegistration\Exception::class);

        $this->subject->anonymizeShippingAddress($shippingAddress);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function anonymizeShippingAddressAnonymizesShippingAddressesOfUser()
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

        $this->subject->anonymizeShippingAddress($shippingAddress);

        static::assertEquals(99, $shippingAddress->getGender());
        static::assertEquals('Deleted', $shippingAddress->getFirstName());
        static::assertEquals('Anonymous', $shippingAddress->getLastName());
        static::assertEquals('Deleted Anonymous', $shippingAddress->getFullName());
        static::assertEquals('', $shippingAddress->getCompany());
        static::assertEquals('', $shippingAddress->getAddress());
        static::assertEquals('', $shippingAddress->getZip());
        static::assertEquals('', $shippingAddress->getCity());

    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
