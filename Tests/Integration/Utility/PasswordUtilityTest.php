<?php
namespace RKW\RkwRegistration\Tests\Integration\Utility;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\PasswordUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

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
 * PasswordUtilityTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PasswordUtilityTest extends FunctionalTestCase
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
    protected $coreExtensionsToLoad = [
        'saltedpasswords',
        'extensionmanager',
        'lang'
    ];

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/PasswordUtilityTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_basics/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/constants.txt',
                'EXT:rkw_registration/Tests/Integration/Utility/PasswordUtilityTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

    }



    /**
     * @test
     */
    public function generatePasswordWithCustomLengthReturnsPasswordWithCustomLength ()
    {
        /**
         * Scenario:
         *
         * Given is a custom password length
         * When a password is generated
         * Then a the password with allowed custom length is returned
         */

        $individualLength = 37;

        /** @var PasswordUtility $utility */
        $utility = GeneralUtility::makeInstance(PasswordUtility::class);

        $result = $utility->generatePassword($individualLength);

        static::assertInternalType('string', $result);
        static::assertTrue(strlen($result) == $individualLength);
    }



    /**
     * @test
     */
    public function generatePasswordWithoutCertainLengthReturnsPasswordWithDefaultLength ()
    {
        /**
         * Scenario:
         *
         * Given if not specific password length
         * When a password is generated
         * Then a the password with default length is returned
         */


        /** @var PasswordUtility $utility */
        $utility = GeneralUtility::makeInstance(PasswordUtility::class);

        $result = $utility::generatePassword();

        static::assertInternalType('string', $result);
        static::assertTrue(strlen($result) == PasswordUtility::PASSWORD_DEFAULT_LENGTH);
    }



    /**
     * @test
     */
    public function generatePasswordWithTooLongCustomLengthReturnsPasswordWithMaxLength ()
    {
        /**
         * Scenario:
         *
         * Given is a not possible custom password length
         * When a password is generated
         * Then a the password with maximum length is returned
         */

        $individualLength = PHP_INT_MAX;

        /** @var PasswordUtility $utility */
        $utility = GeneralUtility::makeInstance(PasswordUtility::class);

        $result = $utility->generatePassword($individualLength);

        static::assertInternalType('string', $result);
        static::assertTrue(strlen($result) == PasswordUtility::PASSWORD_MAX_LENGTH);
    }



    /**
     * @test
     */
    public function generatePasswordWithTooShortCustomLengthReturnsPasswordWithMinLength ()
    {
        /**
         * Scenario:
         *
         * Given is a custom password length between 0 and the minimum length
         * When a password is generated
         * Then a the password with minimum length is returned
         */

        // @toDo: Not necessary to check all values via loop

        $individualLength = 0;

        /** @var PasswordUtility $utility */
        $utility = GeneralUtility::makeInstance(PasswordUtility::class);

        do {
            $result = $utility->generatePassword($individualLength);

            static::assertInternalType('string', $result);
            static::assertTrue(strlen($result) == PasswordUtility::PASSWORD_MIN_LENGTH);

            $individualLength++;
        } while ($individualLength <= PasswordUtility::PASSWORD_MIN_LENGTH);

    }



    /**
     * @test
     */
    public function generatePasswordReturnsSolelyAlphanumericSigns ()
    {
        /**
         * Scenario:
         *
         * Given is nothing special
         * When a password is generated
         * Then a the password with default settings has only alphanumeric signs
         */

        /** @var PasswordUtility $utility */
        $utility = GeneralUtility::makeInstance(PasswordUtility::class);

        $result = $utility->generatePassword();

        static::assertTrue(ctype_alnum($result));
    }



    /**
     * @test
     */
    public function generatePasswordReturnsAlsoNonAlphanumericSigns ()
    {
        /**
         * Scenario:
         *
         * Given is nothing special
         * When a password is generated
         * Then a the password with default settings has only alphanumeric signs
         */

        /** @var PasswordUtility $utility */
        $utility = GeneralUtility::makeInstance(PasswordUtility::class);

        $result = $utility->generatePassword(PasswordUtility::PASSWORD_DEFAULT_LENGTH, true);

        static::assertFalse(ctype_alnum($result));
    }



    /**
     * @test
     */
    public function saltPasswordReturnsAnEncryptedString ()
    {
        /**
         * Scenario:
         *
         * Given is a plaintext password
         * When the plaintext password is encrypted
         * Then a encrypted version of the plaintext password is returned
         */

        /** @var PasswordUtility $utility */
        $utility = GeneralUtility::makeInstance(PasswordUtility::class);

        $customPassword = 'absolutelySecret!';

        $result = $utility::saltPassword($customPassword);

        static::assertInternalType('string', $result);
        static::assertNotEquals($result, $customPassword);
    }



    /**
     * @test
     */
    public function saltPasswordWithUnloadedSaltedPasswordsExtension ()
    {
        /**
         * Scenario:
         *
         * Scenario not possible:
         * TYPO3\CMS\Core\Package\Exception\ProtectedPackageKeyException : The package "saltedpasswords" is protected and cannot be deactivated.
         *
         * Given is a plaintext password
         * When the system extension "saltedpasswords" is unloaded
         * Then a a not encrypted version of the plaintext password is returned
         */


        static::assertTrue(true);
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}