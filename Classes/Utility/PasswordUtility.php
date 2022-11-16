<?php
namespace RKW\RkwRegistration\Utility;

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

use RKW\RkwRegistration\Exception;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;

/**
 * Class Password
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PasswordUtility implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Default length of password
     *
     * @const integer
     */
    const PASSWORD_DEFAULT_LENGTH = 10;

    /**
     * Min length of password
     *
     * @const integer
     */
    const PASSWORD_MIN_LENGTH = 5;

    /**
     * Max length of password
     * Hint: This value should have maximum the double length of the shortest password generation string
     *
     * @const integer
     */
    const PASSWORD_MAX_LENGTH = 50;


    /**
     * Generates a password
     *
     * @see saltPassword for description
     * @param integer $length
     * @param bool $addNonAlphanumeric
     * @return string
     */
    public static function generatePassword(
        int $length = self::PASSWORD_DEFAULT_LENGTH,
        bool $addNonAlphanumeric = false
    ): string {

        // check for minimum length
        $length = $length > self::PASSWORD_MIN_LENGTH ? $length : self::PASSWORD_MIN_LENGTH;

        // check for maximum length
        $length = $length < self::PASSWORD_MAX_LENGTH ? $length : self::PASSWORD_MAX_LENGTH;

        $letters = '0123456789abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $symbols = ',.;:-_<>|+*~!"§$%&/()=?[]{}';

        if (!$addNonAlphanumeric) {
            return substr(str_shuffle($letters), 0, $length);
        } else {
            return str_shuffle(
                substr(str_shuffle($letters),0, round($length / 2, 0, PHP_ROUND_HALF_UP)) .
                substr(str_shuffle($symbols),0,round($length / 2, 0, PHP_ROUND_HALF_DOWN))
            );
        }
    }


    /**
     * Encrypt a password
     *
     * @param string $plaintextPassword
     * @return string
     * @throws \TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException
     */
    public static function saltPassword(string $plaintextPassword): string
    {
        // fallback: If something went wrong, at least something should be set
        $saltedPassword = $plaintextPassword;
        if (ExtensionManagementUtility::isLoaded('saltedpasswords')) {

            try {
                /** @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory $passwordHashFactory */
                $passwordHashFactory = GeneralUtility::makeInstance( PasswordHashFactory::class);
                $objSalt = $passwordHashFactory->getDefaultHashInstance('FE');
                if (! is_object($objSalt)) {
                    throw new Exception('SaltFactory is not an object!');
                }
                $saltedPassword = $objSalt->getHashedPassword($plaintextPassword);
            } catch (\Exception $e) {

                self::getLogger()->log(
                    LogLevel::ERROR,
                    sprintf('The password cannot be encrypted: %s', $e->getMessage())
                );
            }
        } else {
            self::getLogger()->log(
                LogLevel::WARNING,
                'The password cannot be encrypted. Apparently there are problems with the system extension saltedpasswords.'
            );
        }

        return (string) $saltedPassword;
    }



    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected static function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }



}
