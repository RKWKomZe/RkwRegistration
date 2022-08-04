<?php
namespace RKW\RkwRegistration\Tests\Functional\Register;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\GuestUserRepository;
use RKW\RkwRegistration\Register\GuestUserRegister;
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
 * GuestUserRegisterTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GuestUserRegisterTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/GuestUserRegisterTest/Fixtures';

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
     * @var \RKW\RkwRegistration\Domain\Repository\GuestUserRepository
     */
    private $guestUserRepository = null;

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
        $this->guestUserRepository = $this->objectManager->get(GuestUserRepository::class);
    }



    /**
     * @test
     */
    public function constructAddsBasicDataToNewGuestUser ()
    {
        /**
         * Scenario:
         *
         * Given is a new created GuestUser
         * When the GuestUserRegister is instantiated
         * Then some basic data are added to the new created GuestUser
         */

        // create new GuestUser
        /** @var GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        static::assertEmpty($guestUser->getUsername());
        static::assertEmpty($guestUser->getPassword());

        // Service
        /** @var GuestUserRegister $register */
        $this->objectManager->get(GuestUserRegister::class, $guestUser);

        static::assertNotEmpty($guestUser->getUsername());
        static::assertNotEmpty($guestUser->getPassword());

        // in fact this is testing the protected function "createGuestToken"
        static::assertEquals(strlen($guestUser->getUsername()), GuestUserRegister::GUEST_TOKEN_LENGTH);
    }



    /**
     * @test
     */
    public function constructDoNothingSpecialToExistingGuestUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing GuestUser
         * When the GuestUserRegister is instantiated
         * Then the user is set to the service class
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var GuestUser $guestUser */
        $guestUser = $this->guestUserRepository->findByIdentifier(1);
        $usernameBefore = $guestUser->getUsername();
        $passwordBefore = $guestUser->getPassword();

        // Service
        /** @var GuestUserRegister $register */
        $this->objectManager->get(GuestUserRegister::class, $guestUser);

        static::assertEquals($usernameBefore, $guestUser->getUsername());
        static::assertEquals($passwordBefore, $guestUser->getPassword());
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}
