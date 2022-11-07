<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;
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
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Register\FrontendUserRegister;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * FrontendUserUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserUtilityTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserUtilityTest/Fixtures';

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
    ];

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
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository frontendUserRepository */
        $this->frontendUserRepository = $objectManager->get(FrontendUserRepository::class);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    #==============================================================================


    /**
     * @test
     */
    public function convertArrayToObjectWithGivenArrayReturnsFrontendUserObject ()
    {
        /**
         * Scenario:
         *
         * Given is an array with frontendUser values
         * When the method is called
         * Then a frontendUser object is returned
         */

        $frontendUserArray = [
            'firstName' => 'Klaus',
            'lastName' => 'Schröder',
            'email' => 'klaus@schroeder.de'
        ];

        $frontendUserObject = FrontendUserUtility::convertArrayToObject($frontendUserArray);
        self::assertInstanceOf(FrontendUser::class, $frontendUserObject);
    }



    /**
     * @test
     */
    public function convertArrayToObjectWithoutFilledArrayReturnsFrontendUserObject ()
    {
        /**
         * Scenario:
         *
         * Given is an array without any values
         * When the method is called
         * Then a frontendUser object is returned
         */

        // empty
        $frontendUserArray = [];

        $frontendUserObject = FrontendUserUtility::convertArrayToObject($frontendUserArray);
        self::assertInstanceOf(FrontendUser::class, $frontendUserObject);
    }



    /**
     * @test
     */
    public function convertArrayToObjectReturnsFrontendUserObjectWithGivenDataFromArray ()
    {
        /**
         * Scenario:
         *
         * Given is an array with frontendUser values
         * When the method is called
         * Then a FrontendUser object is returned
         * Then the object's properties are set according to the array
         */

        $frontendUserArray = [
            'firstName' => 'Klaus',
            'lastName' => 'Schröder',
            'email' => 'klaus@schroeder.de'
        ];

        $frontendUserObject = FrontendUserUtility::convertArrayToObject($frontendUserArray);

        self::assertEquals($frontendUserArray['firstName'], $frontendUserObject->getFirstName());
        self::assertEquals($frontendUserArray['lastName'], $frontendUserObject->getLastName());
        self::assertEquals($frontendUserArray['email'], $frontendUserObject->getEmail());
    }


    #==============================================================================


    /**
     * @test
     * @throws \Exception
     */
    public function convertObjectToArrayReturnsAllProperties ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given two properties of this object are set to new values
         * When the method is called
         * Then an array is returned
         * Then this array contains all properties regardless if set or not set newly
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);
        $frontendUser->setUsername('test');
        $frontendUser->setZip(123456);

        $result = FrontendUserUtility::convertObjectToArray($frontendUser);
        self::assertIsArray($result);
        self::assertCount(38, $result);
        self::assertEquals('test', $result['username']);
        self::assertEquals(123456, $result['zip']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function convertObjectToArrayReturnsArrayWithOnlyDirtyProperties ()
    {
        /**
         * Scenario:
         *
         * Given a persisted frontendUser-object
         * Given two properties of this object are set to new values
         * When the method is called with dirtyOnly-parameter set to true
         * Then an array is returned
         * Then this array contains only the new set properties
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);
        $frontendUser->setUsername('test');
        $frontendUser->setZip(123456);

        $result = FrontendUserUtility::convertObjectToArray($frontendUser, true);
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertEquals('test', $result['username']);
        self::assertEquals(123456, $result['zip']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function convertObjectToArrayReturnsOnlyDirtyPropertiesForNewObject ()
    {
        /**
         * Scenario:
         *
         * Given a new frontendUser-object
         * Given two properties of this object are set to new values
         * When the method is called with dirtyOnly-parameter set to true
         * Then an array is returned
         * Then this array contains only the new set properties
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setUsername('test');
        $frontendUser->setZip(123456);

        $result = FrontendUserUtility::convertObjectToArray($frontendUser, true);
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertEquals('test', $result['username']);
        self::assertEquals(123456, $result['zip']);
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function isEmailValidWithValidEmailReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is a valid email address
         * When the method is called with that email as parameter
         * Then the functions returns true
         */

        $email = 'test@test.de';
        self::assertTrue(FrontendUserUtility::isEmailValid($email));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function isEmailValidWithInvalidEmailReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is an invalid email address
         * When the method is called with that email as parameter
         * Then the functions returns false
         */

        $email = 'test@test';
        self::assertFalse(FrontendUserUtility::isEmailValid($email));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function isEmailValidWithSocialMediaEmailReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is a valid email address from Facebook or Twitter
         * When the method is called with that email as parameter
         * Then the functions returns false
         */

        $email = 'test@facebook.com';
        self::assertFalse(FrontendUserUtility::isEmailValid($email));

        $email = 'test@twitter.com';
        self::assertFalse(FrontendUserUtility::isEmailValid($email));
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function isUsernameUniqueChecksExistingEmailAndReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is an email-address that is already in use as email in an frontendUser object
         * When the method is called with that email as parameter
         * Then the function returns false
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check10.xml');
        self::assertFalse(FrontendUserUtility::isUsernameUnique('lauterbach@spd.de'));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function isUsernameUniqueChecksExistingEmailAndReturnsTrueIfSameObject ()
    {
        /**
         * Scenario:
         *
         * Given is an email-address that is already in use as email in an frontendUser object
         * When the method is called with that email and that frontendUser-Objects as parameters
         * Then the function returns false
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        self::assertTrue(FrontendUserUtility::isUsernameUnique('lauterbach@spd.de', $frontendUser));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function isUsernameUniqueChecksExistingUsernameAndReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given is an email-address that is already in use as username in an frontendUser object
         * When the method is called with that username as parameter
         * Then the function returns false
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check10.xml');
        self::assertFalse(FrontendUserUtility::isUsernameUnique('lauterbachUsername@spd.de'));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function isUsernameUniqueChecksExistingUsernameAndReturnsTrueIfSameObject ()
    {
        /**
         * Scenario:
         *
         * Given is an email-address that is already in use as username in an frontendUser object
         * When the method is called with that username and that frontendUser-Objects as parameters
         * Then the function returns false
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(10);

        self::assertTrue(FrontendUserUtility::isUsernameUnique('lauterbachUsername@spd.de', $frontendUser));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function isUsernameUniqueReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is an email-address that not already in use as username or email in an frontendUser object
         * When the method is called with that email as parameter
         * Then the function returns true
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check10.xml');
        self::assertTrue(FrontendUserUtility::isUsernameUnique('scholz@spd.de'));
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function isPasswordValidReturnsFalse ()
    {
        /**
         * Scenario:
         *
         * Given a plaintext password
         * Given a frontend-user object with a password in the password-property salted with the default salt-factory
         * Given the salted plaintext-password does not match the salted password of the frontendUser-object
         * When the method is called
         * Then the function returns false
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setPassword('$P$C0NF2OEhmo92K6mOzQz4S8VuaDJEb.1'); //Password = testtest

        self::assertFalse(FrontendUserUtility::isPasswordValid($frontendUser, 'blaböa'));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function isPasswordValidReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given a plaintext password
         * Given a frontend-user object with a password in the password-property salted with the default salt-factory
         * Given the salted plaintext-password does match the salted password of the frontendUser-object
         * When the method is called
         * Then the function returns true
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setPassword('$P$C0NF2OEhmo92K6mOzQz4S8VuaDJEb.1'); //Password = testtest

        self::assertTrue(FrontendUserUtility::isPasswordValid($frontendUser, 'testtest'));
    }


    #==============================================================================
    /**
     * @test
     * @throws \Exception
     */
    public function getRemainingLoginAttemptsReturnsDefaultValue()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser
         * Given that frontendUser has made no login attempts before
         * Given there is no configuration for the maximum number of login errors
         * When the method is called with that frontendUser as parameter
         * Then the functions returns the maximum number of possible login attempts from typoscript-configuration
         */
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
            ]
        );


        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);

        self::assertEquals(10 ,FrontendUserUtility::getRemainingLoginAttempts($frontendUser));
    }
    /**
     * @test
     * @throws \Exception
     */
    public function getRemainingLoginAttemptsReturnsMaximumBasedOnConfig ()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser
         * Given that frontendUser has made no login attempts before
         * Given there is a configuration for the maximum number of login errors (value=8)
         * When the method is called with that frontendUser as parameter
         * Then the functions returns the maximum number of possible login attempts from typoscript-configuration
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);

        self::assertEquals(8 ,FrontendUserUtility::getRemainingLoginAttempts($frontendUser));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getRemainingLoginAttemptsReturnsRemainingNumber ()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser
         * Given that frontendUser has made 5 login attempts before
         * When the method is called with that frontendUser as parameter
         * Given there is a configuration for the maximum number of login errors (value=8)
         * Then the functions returns the remaining number of possible login attempts from typoscript-configuration
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setTxRkwregistrationLoginErrorCount(5);

        self::assertEquals(3 ,FrontendUserUtility::getRemainingLoginAttempts($frontendUser));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getRemainingLoginAttemptsReturnsZeroIfNegative()
    {
        /**
         * Scenario:
         *
         * Given a frontendUser
         * Given that frontendUser has made 10 login attempts before
         * When the method is called with that frontendUser as parameter
         * Given there is a configuration for the maximum number of login errors (value=8)
         * Then zero is returned
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setTxRkwregistrationLoginErrorCount(10);

        self::assertEquals(0 ,FrontendUserUtility::getRemainingLoginAttempts($frontendUser));
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function getRemainingLoginAttemptsNumericReturnsDefaultValue()
    {
        /**
         * Scenario:
         *
         * Given there is no configuration for the maximum number of login errors
         * When the method is called with value 0 as parameter
         * Then the functions returns the maximum number of possible login attempts from typoscript-configuration
         */
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
            ]
        );

        self::assertEquals(10 ,FrontendUserUtility::getRemainingLoginAttemptsNumeric(0));
    }
    /**
     * @test
     * @throws \Exception
     */
    public function getRemainingLoginAttemptsNumericReturnsMaximumBasedOnConfig ()
    {
        /**
         * Scenario:
         *
         * Given there is a configuration for the maximum number of login errors (value=8)
         * When the method is called with value 0 as parameter
         * Then the functions returns the maximum number of possible login attempts from typoscript-configuration
         */

        self::assertEquals(8 ,FrontendUserUtility::getRemainingLoginAttemptsNumeric(0));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getRemainingLoginAttemptsNumericReturnsRemainingNumber ()
    {
        /**
         * Scenario:
         *
         * Given there is no configuration for the maximum number of login errors (value=8)
         * When the method is called with value 5 as parameter
         * Then the functions returns the remaining number of possible login attempts from typoscript-configuration
         */

        self::assertEquals(3 ,FrontendUserUtility::getRemainingLoginAttemptsNumeric(5));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getRemainingLoginAttemptsNumericReturnsZeroIfNegative()
    {
        /**
         * Scenario:
         *
         * Given there is no configuration for the maximum number of login errors (value=8)
         * When the method is called with value 10 as parameter
         * Then zero is returned
         */

        self::assertEquals(0 ,FrontendUserUtility::getRemainingLoginAttemptsNumeric(10));
    }

    #==============================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function getMandatoryFieldsReturnsEmptyArrayIfNothingConfigured()
    {
        /**
         * Scenario:
         *
         * Given there is no configuration for the mandatory fields
         * When the method is called without parameter
         * Then an array is returned
         * Then this array is empty
         */

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
            ]
        );

        $result = FrontendUserUtility::getMandatoryFields();
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getMandatoryFieldsReturnsArrayWithValidProperties()
    {
        /**
         * Scenario:
         *
         * Given there is a configuration for the mandatory fields
         * Given this configuration contains two valid property-names in mixed notation
         * Given this configuration contains one invalid property-name
         * When the method is called without a parameter
         * Then an array is returned
         * Then this array contains the two valid property-names
         */

        $result = FrontendUserUtility::getMandatoryFields();
        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertEquals('firstName', $result[0]);
        self::assertEquals('lastName', $result[1]);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getMandatoryFieldsReturnsArrayWithGroupProperties()
    {
        /**
         * Scenario:
         *
         * Given there is a configuration for the mandatory fields
         * Given this configuration contains two valid property-names in mixed notation
         * Given this configuration contains one invalid property-name
         * Given there is a persisted frontendUser
         * Given that frontendUser is member of two frontendUserGroups
         * Given each frontendUserGroup has two different mandatory fields defined
         * When the method is called with the frontendUser as parameter
         * Then an array is returned
         * Then this array contains six property-names
         */

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(20);

        $result = FrontendUserUtility::getMandatoryFields($frontendUser);
        self::assertIsArray($result);
        self::assertCount(6, $result);
        self::assertEquals('firstName', $result[0]);
        self::assertEquals('lastName', $result[1]);
        self::assertEquals('email', $result[2]);
        self::assertEquals('middleName', $result[3]);
        self::assertEquals('zip', $result[4]);
        self::assertEquals('city', $result[5]);
    }

    #==============================================================================

    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
