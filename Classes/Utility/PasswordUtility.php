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

use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Saltedpasswords\Salt\SaltFactory;
use TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility;

/**
 * Class Password
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
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
     * Generates a salted password for the user
     *
     * @deprecated This function will be removed soon. Use generatePassword and saltPassword instead
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @param string $plaintextPassword
     * @return string
     */
    public static function generate(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser, $plaintextPassword = null): ?string
    {
        if (!$plaintextPassword) {
            $plaintextPassword = self::generatePassword();
        }

        $saltedPassword = $plaintextPassword;
        if (self::saltPassword($plaintextPassword)) {
            $saltedPassword = self::saltPassword($plaintextPassword);
        }

        $frontendUser->setPassword($saltedPassword);

        return $plaintextPassword;
        //===
    }



    /**
     * Generates a password
     *
     * @see saltPassword for decryption
     * @param integer $length
     * @param bool $addNonAlphanumeric
     * @return string
     */
    public static function generatePassword($length = self::PASSWORD_DEFAULT_LENGTH, $addNonAlphanumeric = false): string
    {
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
     */
    public static function saltPassword($plaintextPassword): string
    {
        // fallback: If something went wrong, at least something should be set
        $saltedPassword = $plaintextPassword;
        if (
            ExtensionManagementUtility::isLoaded('saltedpasswords')
            && SaltedPasswordsUtility::isUsageEnabled('FE')
        ) {
            $objSalt = SaltFactory::getSaltingInstance(null);
            if (is_object($objSalt)) {
                $saltedPassword = $objSalt->getHashedPassword($plaintextPassword);
            } else {
                self::getLogger()->log(LogLevel::ERROR, sprintf('The password cannot be encrypted. SaltFactory is not an object!'));
            }
        } else {
            self::getLogger()->log(LogLevel::WARNING, sprintf('The password cannot be encrypted. Apparently there are problems with the system extension saltedpasswords'));
        }

        return $saltedPassword;
    }



    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected static function getLogger()
    {
        return GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
    }


}