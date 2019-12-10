<?php
namespace RKW\RkwRegistration\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository;

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
 * ShippingAddressTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ShippingAddressTest extends FunctionalTestCase
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
     * @var \RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository
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
        $this->importDataSet(__DIR__ . '/ShippingAddressRepositoryTest/Fixtures/Database/Global.xml');

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
        $this->subject = $this->objectManager->get(ShippingAddressRepository::class);

    }



    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function removeAnonymizesAddress()
    {

        /**
         * Scenario:
         *
         * Given there is a shipping address
         * Given the frontend user is deleted
         * When I delete the shipping address
         * Then the shipping address is anonymized
         */
        $this->importDataSet(__DIR__ . '/ShippingAddressRepositoryTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->subject->findByUid(1);

        $this->subject->remove($shippingAddress);

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
