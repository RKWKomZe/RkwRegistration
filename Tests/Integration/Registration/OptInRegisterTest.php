<?php
namespace RKW\RkwRegistration\Tests\Integration\Register;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwRegistration\Domain\Model\Title;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use \RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwRegistration\Register\OptInFrontendUser;
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
     * @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration
     */
    private $optInRegister = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
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
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        // Repository
        $this->frontendUserRepository =  $this->objectManager->get(FrontendUserRepository::class);
        $this->registrationRepository =  $this->objectManager->get(RegistrationRepository::class);
        // Service
        $this->optInRegister =  $this->objectManager->get(OptInFrontendUser::class);

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
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());

        self::assertTrue($frontendUser->getDisable() === 0);

        $result = $this->optInRegister->process($register->getTokenYes(), '', $register->getUserSha1());

        self::assertTrue($result === 1);
        self::assertTrue($frontendUser->getDisable() === 0);
        // And check that the feUser entry is still in database
        $frontendUserFromDb = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());
        self::assertNotNull($frontendUserFromDb);
        // the new disable value is also persistent now
        self::assertTrue($frontendUserFromDb->getDisable() === 0);
        self::assertNull($this->registrationRepository->findByIdentifier(1));
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
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());

        self::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->optInRegister->process($register->getTokenYes(), '', $register->getUserSha1());

        self::assertTrue($result === 1);
        self::assertTrue($frontendUser->getDisable() === 0);
        // And check that the feUser entry is still in database
        $frontendUserFromDb = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());
        self::assertNotNull($frontendUserFromDb);
        // the new disable value is also persistent now
        self::assertTrue($frontendUserFromDb->getDisable() === 0);
        self::assertNull($this->registrationRepository->findByIdentifier(1));
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
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());

        $result = $this->optInRegister->process('', $register->getTokenNo(), $register->getUserSha1());

        self::assertTrue($result === 2);
        // but the database entry is completely deleted (because disabled users are removed when using token "No")
        self::assertNotNull($this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser()));
        self::assertNull($this->registrationRepository->findByIdentifier(1));
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
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());

        self::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->optInRegister->process('', $register->getTokenNo(), $register->getUserSha1());

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
        $persistenceManager->clearState();

        self::assertTrue($result === 2);
        // the given dataset is still disabled
        self::assertTrue($frontendUser->getDisable() === 1);
        // but the database entry is completely deleted (because disabled users are removed when using token "No")
        self::assertNull($this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser()));
        self::assertNull($this->registrationRepository->findByIdentifier(1));
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
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());

        self::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->optInRegister->process('thisTokenIsBullshit', '', $register->getUserSha1());

        self::assertTrue($result === 0);
        // the given dataset is still disabled
        self::assertTrue($frontendUser->getDisable() === 1);
        // but nothing is happen. User and registration still exist in database
        self::assertNotNull($this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser()));
        self::assertNotNull($this->registrationRepository->findByIdentifier(1));
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
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());

        self::assertTrue($frontendUser->getDisable() === 1);

        $result = $this->optInRegister->process('', 'thisTokenIsBullshit', $register->getUserSha1());

        self::assertTrue($result === 0);
        // the given dataset is still disabled
        self::assertTrue($frontendUser->getDisable() === 1);
        // but nothing is happen. User and registration still exist in database
        self::assertNotNull($this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser()));
        self::assertNotNull($this->registrationRepository->findByIdentifier(1));
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
        $frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser());

        $result = $this->optInRegister->process('', 'thisTokenIsBullshit', $register->getUserSha1());

        self::assertTrue($result === 400);
        self::assertNull($this->frontendUserRepository->findByUidAlsoInactiveNonGuest($register->getUser()));
        self::assertNull($this->registrationRepository->findByIdentifier(1));
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

        self::assertTrue($result === 500);
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
        self::assertNull($this->frontendUserRepository->findByEmail($userData['email'])->getFirst());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register($userData);

        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        self::assertEquals($userData['email'], $result->getUsername());
        // with UID -> successfully persisted
        self::assertNotNull($result->getUid());
        // disabled
        self::assertEquals(1, $result->getDisable());
        // additional: Query from DB
        self::assertNotNull($this->frontendUserRepository->findOneByEmailOrUsernameAlsoInactive($userData['email']));
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
        self::assertNull($this->frontendUserRepository->findByEmail($userData['email'])->getFirst());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register($userData, true);

        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        self::assertEquals($userData['email'], $result->getUsername());
        // with UID -> successfully persisted
        self::assertNotNull($result->getUid());
        // enabled
        self::assertEquals(0, $result->getDisable());
        // additional: query from db
        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $this->frontendUserRepository->findByEmail($userData['email'])->getFirst());
    }



    /**
     * @test
     */
    public function registerNewFrontendUserWithFaultyEmailAddressThrowsException ()
    {
        /**
         * Scenario:
         *
         * Given is a faulty e-mail address
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1407312133
         */

        $userData = [
            'email' => 'my@fault'
        ];

        static::expectException(\Exception::class);
        static::expectExceptionCode(1407312133);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $this->optInRegister->register($userData);
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
        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        self::assertEquals(0, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()]);

        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);

        // still enabled. It's not an unimportant check, because new user would be disabled by default
        self::assertEquals(0, $result->getDisable());

        // still the same UID
        self::assertEquals($existingFrontendUser->getUid(), $result->getUid());
    }



    /**
     * @test
     */
    public function registerWithAlreadyExistingEmailOfDisabledFrontendUserReturnsUnchangedFrontendUser ()
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
        $existingFrontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest(1);

        // email does not exists
        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        self::assertEquals(1, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()]);

        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        // still disabled. Is a not unimportant check, because new user would be disabled by default
        self::assertEquals(1, $result->getDisable());

        // still the same UID
        self::assertEquals($existingFrontendUser->getUid(), $result->getUid());
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
        $existingFrontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest(1);

        // email does not exists
        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $existingFrontendUser);
        self::assertEquals(1, $existingFrontendUser->getDisable());

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()], true);

        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        // now enabled
        self::assertEquals(0, $result->getDisable());

        // still the same UID
        self::assertEquals($existingFrontendUser->getUid(), $result->getUid());
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

        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $newRegistration */
        $newRegistration = $this->registrationRepository->findByCategory($categoryName)->getFirst();
        // compare given and saved additionalData array
        $diff = array_diff($additionalData, $newRegistration->getData());
        self::assertCount(0, $diff);

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
        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);
        self::assertEquals(1, $result->getDisable());
        // now persisted (user got a uid)
        self::assertNotNull($result->getUid());

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $newRegistration */
        $newRegistration = $this->registrationRepository->findByCategory($categoryName)->getFirst();
        // compare given and saved additionalData array
        $diff = array_diff($additionalData, $newRegistration->getData());
        self::assertCount(0, $diff);
    }




    /**
     * @test
     */
    public function registerAdditionalDataObjectWithRegisteredFrontendUserReturnsFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an existing frontendUser
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

        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $newRegistration */
        $newRegistration = $this->registrationRepository->findByCategory($categoryName)->getFirst();
        // compare given and saved additionalData array
        /** @var \RKW\RkwRegistration\Domain\Model\Title $savedAdditionalData */
        $savedAdditionalData = $newRegistration->getData();
        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Title', $savedAdditionalData);
        self::assertEquals($titleName, $savedAdditionalData->getName());
        // is NOT persistent yet as object itself
        self::assertNull($savedAdditionalData->getUid());
    }


    /**
     * @test
     */
    public function registerDeletedAdditionalDataObjectWithRegisteredFrontendUserReturnsFrontendUser ()
    {
        /**
         * Scenario:
         *
         * Given is an an existing frontendUser
         * Given is an DELETED object as additionalData
         * When a user with additionalData is registered
         * Then the already existing user is returned
         * Then a registration dataset is created with given data object
         * Then the additionalData object is still deleted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $existingFrontendUser */
        $existingFrontendUser = $this->frontendUserRepository->findByUid(1);

        $titleName = 'SomeTitleName';
        /** @var \RKW\RkwRegistration\Domain\Model\Title $titleObject */
        $titleObject = GeneralUtility::makeInstance(Title::class);
        $titleObject->setName($titleName);
        $titleObject->setDeleted(1);

        $additionalData = $titleObject;

        $categoryName = 'testCategory';

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $result */
        $result = $this->optInRegister->register(['email' => $existingFrontendUser->getEmail()], true, $additionalData, $categoryName);

        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\FrontendUser', $result);

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $newRegistration */
        $newRegistration = $this->registrationRepository->findByCategory($categoryName)->getFirst();
        // compare given and saved additionalData array
        /** @var \RKW\RkwRegistration\Domain\Model\Title $savedAdditionalData */
        $savedAdditionalData = $newRegistration->getData();

        //still deleted
        self::assertEquals(1, $savedAdditionalData->getDeleted());
        self::assertInstanceOf('\RKW\RkwRegistration\Domain\Model\Title', $savedAdditionalData);
        self::assertEquals($titleName, $savedAdditionalData->getName());
        // is NOT persistent yet as object itself
        self::assertNull($savedAdditionalData->getUid());
    }



    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
