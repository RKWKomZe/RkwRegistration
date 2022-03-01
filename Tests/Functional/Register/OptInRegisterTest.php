<?php
namespace RKW\RkwRegistration\Tests\Functional\Register;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwRegistration\Domain\Model\Title;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use \RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwRegistration\Register\OptInRegister;

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
 * OptInRegisterTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OptInRegisterTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/OptInRegisterTest/Fixtures';

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
     * @var \RKW\RkwRegistration\Register\OptInRegister
     */
    private $optInRegister = null;


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
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/constants.txt',
                 self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        // Repository
        $this->frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
        $this->registrationRepository = $objectManager->get(RegistrationRepository::class);
        // Service
        $this->optInRegister = $objectManager->get(OptInRegister::class);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'mail@default.rkw';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'RKW Default';
    }



    /**
     * @test
     */
    public function processWithTokenYesReturnsSuccess ()
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 0);

        $result = $this->optInRegister->process($register->getTokenYes(), '', $register->getUserSha1());

        static::assertTrue($result === 1);
        static::assertTrue($frontendUser->getDisable() === 0);
        // And check that the feUser entry is still in database
        $frontendUserFromDb = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());
        static::assertNotNull($frontendUserFromDb);
        // the new disable value is also persistent now
        static::assertTrue($frontendUserFromDb->getDisable() === 0);
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processWithTokenYesReturnsSuccessAndEnablesTheDisabledFrontendUser ()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->optInRegister->process($register->getTokenYes(), '', $register->getUserSha1());

        static::assertTrue($result === 1);
        static::assertTrue($frontendUser->getDisable() === 0);
        // And check that the feUser entry is still in database
        $frontendUserFromDb = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());
        static::assertNotNull($frontendUserFromDb);
        // the new disable value is also persistent now
        static::assertTrue($frontendUserFromDb->getDisable() === 0);
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processWithTokenNoReturnsDismissed ()
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());

        $result = $this->optInRegister->process('', $register->getTokenNo(), $register->getUserSha1());

        static::assertTrue($result === 2);
        // but the database entry is completely deleted (because disabled users are removed when using token "No")
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processWithTokenNoReturnsDismissedAndRemovesTheDisabledFrontendUser ()
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->optInRegister->process('', $register->getTokenNo(), $register->getUserSha1());

        static::assertTrue($result === 2);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but the database entry is completely deleted (because disabled users are removed when using token "No")
        static::assertNull($this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processWithWrongYesTokenReturnsFailed ()
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->optInRegister->process('thisTokenIsBullshit', '', $register->getUserSha1());

        static::assertTrue($result === 0);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but nothing is happen. User and registration still exist in database
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser()));
        static::assertNotNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processWithWrongNoTokenReturnsFailed ()
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() + 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());

        static::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->optInRegister->process('', 'thisTokenIsBullshit', $register->getUserSha1());

        static::assertTrue($result === 0);
        // the given dataset is still disabled
        static::assertTrue($frontendUser->getDisable() === 1);
        // but nothing is happen. User and registration still exist in database
        static::assertNotNull($this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser()));
        static::assertNotNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processExpiredReturnsFailedAndRemovesRegistration ()
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $register */
        $register = $this->registrationRepository->findByIdentifier(1);
        // add valid until time somewhere in the future
        $register->setValidUntil(time() - 60);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser());

        $result = $this->optInRegister->process('', 'thisTokenIsBullshit', $register->getUserSha1());

        static::assertTrue($result === 400);
        static::assertNull($this->frontendUserRepository->findByUidInactiveNonGuest($register->getUser()));
        static::assertNull($this->registrationRepository->findByIdentifier(1));
    }



    /**
     * @test
     */
    public function processWhichNotExistsReturnsNotFound ()
    {
        /**
         * Scenario:
         *
         * Given is an EXPIRED event registration
         * When this register is checked with "YES"-token
         * Then the function gives back a not found value ("500")
         */

        $result = $this->optInRegister->process('something', '', 'whatEver :)');

        static::assertTrue($result === 500);
    }



    /**
     * @test
     */
    public function registerNewFrontendUserReturnsPersistentAndDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an e-mail address
         * When a new user is registered
         * Then a new (disabled) frontendUser is created
         */

        $userData = [
            'email' => 'test@email.de'
        ];

        // email does not exists
        static::assertNull($this->frontendUserRepository->findByEmail($userData['email'])->getFirst());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register($userData);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        static::assertEquals($userData['email'], $result->getUsername());
        // with UID -> successfully persisted
        static::assertNotNull($result->getUid());
        // disabled
        static::assertEquals(1, $result->getDisable());
        // additional: Query from DB
        static::assertNotNull($this->frontendUserRepository->findOneByEmailOrUsernameInactive($userData['email']));
    }


    /**
     * @test
     */
    public function registerNewFrontendUserReturnsPersistentAndEnabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an e-mail address
         * When a new user is registered with flag "enabled" true
         * Then a new (enabled) frontendUser is created
         */

        $userData = [
            'email' => 'test@email.de'
        ];

        // email does not exists
        static::assertNull($this->frontendUserRepository->findByEmail($userData['email'])->getFirst());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register($userData, true);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        static::assertEquals($userData['email'], $result->getUsername());
        // with UID -> successfully persisted
        static::assertNotNull($result->getUid());
        // enabled
        static::assertEquals(0, $result->getDisable());
        // additional: query from db
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $this->frontendUserRepository->findByEmail($userData['email'])->getFirst());
    }



    /**
     * @test
     */
    public function registerWithAlreadyExistingEmailOfEnabledFrontendUserReturnsTheExistingFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing e-mail address of an ENABLED frontendUser
         * When a user with existing email is registered
         * Then the already existing user is returned without any changes
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByIdentifier(1);

        // email does not exists
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        static::assertEquals(0, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()]);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);

        // still enabled. It's not an unimportant check, because new user would be disabled by default
        static::assertEquals(0, $result->getDisable());

        // still the same UID
        static::assertEquals($existingFrontendUser->getUid(), $result->getUid());
    }



    /**
     * @test
     */
    public function registerWithAlreadyExistingEmailOfDisabledFrontendUserReturnsUnchancedFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing e-mail address of an DISABLED frontendUser
         * When a user with this email is registered
         * Then the already existing user is returned without any changes (still disabled)
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest(1);

        // email does not exists
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        static::assertEquals(1, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()]);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        // still disabled. Is a not unimportant check, because new user would be disabled by default
        static::assertEquals(1, $result->getDisable());

        // still the same UID
        static::assertEquals($existingFrontendUser->getUid(), $result->getUid());
    }



    /**
     * @test
     */
    public function registerWithEnabledFlagAlreadyExistingEmailOfDisabledFrontendUserReturnsEnabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing e-mail address of an DISABLED frontendUser
         * When a user with this email is registered with ENABLED flag
         * Then the already existing user is returned as enabled user
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByUidInactiveNonGuest(1);

        // email does not exists
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        static::assertEquals(1, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()], true);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        // now enabled
        static::assertEquals(0, $result->getDisable());

        // still the same UID
        static::assertEquals($existingFrontendUser->getUid(), $result->getUid());
    }



    /**
     * @test
     */
    public function registerAdditionalDataArrayWithRegisteredFrontendUserReturnsFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an email address of an existing frontendUser
         * Given is an array as additionalData
         * When a user with additionalData is registered
         * Then the already existing user is returned
         * Then a registration dataset is created with given data array
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByUid(1);

        $additionalData = [
            'some' => 'thing',
            'any' => 'thing',
            'what' => 'ever'
        ];

        $categoryName = 'testCategory';

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()], true, $additionalData, $categoryName);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $newRegistration */
        $newRegistration = $this->registrationRepository->findByCategory($categoryName)->getFirst();
        // compare given and saved additionalData array
        $diff = array_diff($additionalData, $newRegistration->getData());
        static::assertCount(0, $diff);

    }



    /**
     * @test
     */
    public function registerAdditionalDataArrayWithNotRegisteredFrontendUserReturnsDisabledFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an email address of a NOT existing frontendUser
         * Given is an array as additionalData
         * When a user with additionalData is registered
         * Then the new created user is returned
         * Then a registration dataset is created with given data array
         */

        $userData = [
            'email' => 'doesNotExists@email.de'
        ];

        $additionalData = [
            'some' => 'thing',
            'any' => 'thing',
            'what' => 'ever'
        ];

        $categoryName = 'testCategory';

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register($userData, false, $additionalData, $categoryName);
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        static::assertEquals(1, $result->getDisable());
        // now persisted (user got a uid)
        static::assertNotNull($result->getUid());

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $newRegistration */
        $newRegistration = $this->registrationRepository->findByCategory($categoryName)->getFirst();
        // compare given and saved additionalData array
        $diff = array_diff($additionalData, $newRegistration->getData());
        static::assertCount(0, $diff);
    }




    /**
     * @test
     */
    public function registerAdditionalDataObjectWithRegisteredFrontendUserReturnsFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an email address of an existing frontendUser
         * Given is an object as additionalData
         * When a user with additionalData is registered
         * Then the already existing user is returned
         * Then a registration dataset is created with given data object
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByUid(1);

        $titleName = 'SomeTitleName';
        /** @var \RKW\RkwRegistration\Domain\Model\Title $titleObject */
        $titleObject = GeneralUtility::makeInstance(Title::class);
        $titleObject->setName($titleName);

        $additionalData = $titleObject;

        $categoryName = 'testCategory';

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()], true, $additionalData, $categoryName);

        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $newRegistration */
        $newRegistration = $this->registrationRepository->findByCategory($categoryName)->getFirst();
        // compare given and saved additionalData array
        /** @var \RKW\RkwRegistration\Domain\Model\Title $savedAdditionalData */
        $savedAdditionalData = $newRegistration->getData();
        static::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Title', $savedAdditionalData);
        static::assertEquals($titleName, $savedAdditionalData->getName());
        // is NOT persistent yet as object itself
        static::assertNull($savedAdditionalData->getUid());
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}