<?php

namespace RKW\RkwRegistration\Tools;

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
 * Class Password
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Password implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     *  Length of password
     *
     * @const integer
     */
    const PASSWORD_LENGTH = 10;


    /**
     * Generates a salted password for the user
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @param string $plaintextPassword
     * @return string
     */
    public static function generatePassword(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser, $plaintextPassword = null)
    {

        // generate password if not already set - we leave out letters that can be confused with numbers
        if (!$plaintextPassword) {
            $characters = '23456789abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
            $plaintextPassword = substr(str_shuffle($characters), 0, self::PASSWORD_LENGTH);
        }

        $saltedPassword = $plaintextPassword;
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
            if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('FE')) {
                $objSalt = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance(null);
                if (is_object($objSalt)) {
                    $saltedPassword = $objSalt->getHashedPassword($plaintextPassword);
                }
            }
        }

        // set password
        $frontendUser->setPassword($saltedPassword);

        return $plaintextPassword;
        //===
    }


}