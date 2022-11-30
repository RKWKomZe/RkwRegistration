<?php
namespace RKW\RkwRegistration\Service;

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

use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Registration\AbstractRegistration;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use RKW\RkwRegistration\Utility\PasswordUtility;
use RKW\RkwRegistration\Utility\StringUtility;
use TYPO3\CMS\Core\Authentication\LoginType;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * Class FrontendUserAuthenticationService
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GuestUserAuthenticationService extends AbstractAuthenticationService
{


    /**
     * Find a user (eg. look up the user record in database when a login is sent)
     *
     * @return mixed User array or FALSE
     */
    public function getUser()
    {

        if ($this->login['status'] !== LoginType::LOGIN) {
            return false;
        }


        $user = $this->fetchUserRecord($this->login['uname']);
        if (!is_array($user)) {
            // Failed login attempt (no username found)
            $this->writelog(255, 3, 3, 2, 'Login-attempt from ###IP###, username \'%s\' not found!!', [$this->login['uname']]);
            $this->logger->info('Login-attempt from username \'' . $this->login['uname'] . '\' not found!', [
                'REMOTE_ADDR' => $this->authInfo['REMOTE_ADDR']
            ]);
        } else {
            $this->logger->debug('User found', [
                $this->db_user['userid_column'] => $user[$this->db_user['userid_column']],
                $this->db_user['username_column'] => $user[$this->db_user['username_column']]
            ]);
        }

        return $user;
    }

    /**
     * Authenticate a user: Check submitted user credentials against stored hashed password,
     * check domain lock if configured.
     *
     * Returns one of the following status codes:
     *  >= 200: User authenticated successfully. No more checking is needed by other auth services.
     *  >= 100: User not authenticated; this service is not responsible. Other auth services will be asked.
     *  > 0:    User authenticated successfully. Other auth services will still be asked.
     *  <= 0:   Authentication failed, no more checking needed by other auth services.
     *
     * @param array $user User data
     * @return int Authentication status code, one of 0, 100, 200
     * @throws InvalidPasswordHashException
     */
    public function authUser(array $user): int
    {

        // first we need to check if we have a guest-user loaded.
        // otherwise everybody could login into an account only by knowing the email-address!!!
        // Therefore, in that case we return a vehement "none of my business!"
        if (
            ($user[$this->db_user['type_column']] != '\\' . GuestUser::class)
            || (FrontendUserUtility::isEmailValid($user[$this->db_user['username_column']]))
            || (strlen($user[$this->db_user['username_column']]) != StringUtility::RANDOM_STRING_LENGTH)
        ){
            return 100;
        }

        // Since the guest-user does not submit a password, we need to set it before - but only temporarily
        $this->login['uident'] = $this->login['uident_text'] = PasswordUtility::generatePassword();
        $user[$this->db_user['userpassword_column']] = PasswordUtility::saltPassword($this->login['uident_text']);

        $result = parent::authUser($user);
        if ($result >= 200) {

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'User "%s" (uid=%s) has successfully been logged in as guest.',
                    $user['username'],
                    $user['uid'],
                )
            );
        }

        return $result;
    }

}
