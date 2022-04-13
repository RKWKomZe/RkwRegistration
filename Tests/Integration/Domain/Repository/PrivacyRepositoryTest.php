<?php
namespace RKW\RkwRegistration\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\Privacy;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;

use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
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
 * PrivacyRepositoryTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PrivacyRepositoryTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/PrivacyRepositoryTest/Fixtures';

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
     * @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository
     */
    private $subject = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    private $registrationRepository = null;

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
        $this->subject = $this->objectManager->get(PrivacyRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);

    }

    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findOneByRegistrationWithRegistrationReturnsPrivacyEntry()
    {

        /**
         * Scenario:
         *
         * Given is a privacy entry
         * Given is a related registration entry
         * When the function is called
         * Then the privacy entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $registration = $this->registrationRepository->findByIdentifier(1);

        $result = $this->subject->findOneByRegistration($registration);

        static::assertInstanceOf(Privacy::class, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOneByRegistrationWithPrivacyWithDifferentPidReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given is a privacy entry
         * Given is a related registration entry
         * When the function is called
         * Then the privacy entry is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $registration = $this->registrationRepository->findByIdentifier(1);

        $result = $this->subject->findOneByRegistration($registration);

        static::assertNull($result);
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
