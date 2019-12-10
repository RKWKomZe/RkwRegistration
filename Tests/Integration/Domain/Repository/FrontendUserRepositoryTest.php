<?php
namespace RKW\RkwRegistration\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

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
 * FrontendUserTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepositoryTest extends FunctionalTestCase
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
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
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
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Global.xml');

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
        $this->subject = $this->objectManager->get(FrontendUserRepository::class);

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findByUidSoapIncludesDeletedFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user is deleted
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check10.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByUidSoapIncludesDisabledFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user is disabled
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check20.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $result);

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByUidSoapIgnoresStoragePid ()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user has a different storage pid
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check30.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwRegistration\Domain\Model\FrontendUser::class, $result);

    }

    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function removeAnonymizesUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * When I delete the frontend user
         * Then the user data is anonymized
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check40.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->subject->findByUid(1);

        $this->subject->remove($frontendUser);

        static::assertEquals('anonymous1@rkw.de', $frontendUser->getUsername());
        static::assertEquals('anonymous1@rkw.de', $frontendUser->getEmail());
        static::assertEquals('Deleted', $frontendUser->getFirstName());
        static::assertEquals('Anonymous', $frontendUser->getLastName());
        static::assertEquals('Deleted Anonymous', $frontendUser->getName());
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

    /**
     * @test
     * @throws \Exception
     */
    public function removeDeletesShippingAddressesOfUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * When I delete the frontend user
         * Then the user data is anonymized
         */
        $this->importDataSet(__DIR__ . '/FrontendUserRepositoryTest/Fixtures/Database/Check50.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->subject->findByUid(1);

        $this->subject->remove($frontendUser);


        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository $shippingAddressRepository */
        $shippingAddressRepository = $objectManager->get(\RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository::class);
        $shippingAddresses = $shippingAddressRepository->findByFrontendUser($frontendUser);

        static::assertEmpty($shippingAddresses);

    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
