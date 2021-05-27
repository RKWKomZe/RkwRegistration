<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use \RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwRegistration\Service\RegistrationService;

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
 * RegistrationServiceTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RegistrationServiceTest extends FunctionalTestCase
{
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
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    private $registrationRepository = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();
        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Tests/Integration/Utility/RegistrationServiceTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'mail@default.rkw';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW Default';

    }



    /**
     * @test
     */
    public function processOptInWithTokenYesReturnsSuccess ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * Given is an ENABLED frontendUser
         * When this register is checked with "YES"-token
         * Then the function gives back a success value ("1")
         * Then the registration entry is removed from database
         * Then the frontendUser is still part of the database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 0);

        /** @var RegistrationService $service */
        // $service = GeneralUtility::makeInstance(RegistrationService::class);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $service = $objectManager->get('RKW\\RkwRegistration\\Service\\RegistrationService');

        $result = $service->processOptIn($register->getTokenYes(), '', $register->getUserSha1());

        static::assertTrue($result === 1);
        static::assertTrue($frontendUser->getDisable() === 0);
        // And check that the feUser entry is still in database
        $frontendUserFromDb = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());
        static::assertNotNull($frontendUserFromDb);
        // the new disable value is also persistent now
        static::assertTrue($frontendUserFromDb->getDisable() === 0);
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithTokenYesReturnsSuccessAndEnablesTheDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * Given is an DISABLED FrontendUser
         * When this register is checked with "YES"-token
         * Then the function gives back a success value ("1")
         * Then the registration entry is removed from database
         * Then the disabled frontendUser is enabled
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        /** @var RegistrationService $service */
       // $service = GeneralUtility::makeInstance(RegistrationService::class);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $service = $objectManager->get('RKW\\RkwRegistration\\Service\\RegistrationService');

        $result = $service->processOptIn($register->getTokenYes(), '', $register->getUserSha1());

        static::assertTrue($result === 1);
        static::assertTrue($frontendUser->getDisable() === 0);
        // And check that the feUser entry is still in database
        $frontendUserFromDb = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());
        static::assertNotNull($frontendUserFromDb);
        // the new disable value is also persistent now
        static::assertTrue($frontendUserFromDb->getDisable() === 0);
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithTokenNoReturnsDismissed ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * Given is an ENABLED frontendUser
         * When this register is checked with "NO"-token
         * Then the function gives back a dismissed by user value ("2")
         * Then the registration entry is removed from database
         * Then the frontendUser is still part of the database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        /** @var RegistrationService $service */
        // $service = GeneralUtility::makeInstance(RegistrationService::class);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $service = $objectManager->get('RKW\\RkwRegistration\\Service\\RegistrationService');

        $result = $service->processOptIn('', $register->getTokenNo(), $register->getUserSha1());

        static::assertTrue($result === 2);
        // but the database entry is completely deleted (because disabled users are removed when using token "No")
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithTokenNoReturnsDismissedAndRemovesTheDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * When this register is checked with "NO"-token
         * Then the function gives back a dismissed by user value ("2")
         * Then the registration entry is removed from database
         * Then the disabled frontendUser is removed from database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        /** @var RegistrationService $service */
        // $service = GeneralUtility::makeInstance(RegistrationService::class);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $service = $objectManager->get('RKW\\RkwRegistration\\Service\\RegistrationService');

        $result = $service->processOptIn('', $register->getTokenNo(), $register->getUserSha1());

        static::assertTrue($result === 2);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but the database entry is completely deleted (because disabled users are removed when using token "No")
        static::assertNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithWrongYesTokenReturnsFailed ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * When this register is checked with WRONG "YES"-token
         * Then the function gives back a unexpected value ("0")
         * Then the registration is NOT removed from database
         * Then the disabled frontendUser is NOT removed from database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        /** @var RegistrationService $service */
        // $service = GeneralUtility::makeInstance(RegistrationService::class);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $service = $objectManager->get('RKW\\RkwRegistration\\Service\\RegistrationService');

        $result = $service->processOptIn('thisTokenIsBullshit', '', $register->getUserSha1());

        static::assertTrue($result === 0);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but nothing is happen. User and registration still exist in database
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNotNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWithWrongNoTokenReturnsFailed ()
    {
        /**
         * Scenario:
         *
         * Given is an event registration
         * When this register is checked with WRONG "NO"-token
         * Then the function gives back a unexpected value ("0")
         * Then the registration is NOT removed from database
         * Then the disabled frontendUser is NOT removed from database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        /** @var RegistrationService $service */
        // $service = GeneralUtility::makeInstance(RegistrationService::class);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $service = $objectManager->get('RKW\\RkwRegistration\\Service\\RegistrationService');

        $result = $service->processOptIn('', 'thisTokenIsBullshit', $register->getUserSha1());

        static::assertTrue($result === 0);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but nothing is happen. User and registration still exist in database
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNotNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInExpiredReturnsFailedAndRemovesRegistration ()
    {
        /**
         * Scenario:
         *
         * Given is an EXPIRED event registration
         * When this register is checked with "YES"-token
         * Then the function gives back an expired value ("400")
         * Then the registration is removed from database
         * Then the disabled frontendUser is removed from database
         */

        $this->importDataSet(__DIR__ . '/RegistrationServiceTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() - 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser());

        /** @var RegistrationService $service */
        // $service = GeneralUtility::makeInstance(RegistrationService::class);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $service = $objectManager->get('RKW\\RkwRegistration\\Service\\RegistrationService');

        $result = $service->processOptIn('', 'thisTokenIsBullshit', $register->getUserSha1());

        static::assertTrue($result === 400);
        static::assertNull($this->frontendUserRepository->findByUidInactiveNonAnonymous($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processOptInWhichNotExistsReturnsNotFound ()
    {
        /**
         * Scenario:
         *
         * Given is an EXPIRED event registration
         * When this register is checked with "YES"-token
         * Then the function gives back an expired value ("500")
         * Then the registration is removed from database
         * Then the disabled frontendUser is removed from database
         */

        /** @var RegistrationService $service */
        // $service = GeneralUtility::makeInstance(RegistrationService::class);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $service = $objectManager->get('RKW\\RkwRegistration\\Service\\RegistrationService');

        $result = $service->processOptIn('something', '', 'whatEver :)');

        static::assertTrue($result === 500);
    }






    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}