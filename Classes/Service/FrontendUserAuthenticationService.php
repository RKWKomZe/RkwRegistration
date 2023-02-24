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

use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * Class FrontendUserAuthenticationService
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserAuthenticationService extends AbstractAuthenticationService
{


    /**
     * Find a user (eg. look up the user record in database when a login is sent)
     *
     * @return mixed User array or FALSE
     */
    public function getUser()
    {
        return parent::getUser();
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
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function authUser(array $user): int
    {
        $currentErrorCounterValue = $user[$this->db_user['usercounter_column']];
        $newErrorCounterValue = $currentErrorCounterValue;

        // check for login count
        if (! FrontendUserUtility::getRemainingLoginAttemptsNumeric($currentErrorCounterValue)){
            $this->getLogger()->log(
                LogLevel::WARNING,
                sprintf(
                    'User "%s" (uid=%s) has exceeded the maximum number of login errors. No login possible.',
                    $user['username'],
                    $user['uid'],
                )
            );

            return 0;
        }

        $result = parent::authUser($user);

        // if there was an error we increment the error-counter
        if ($result <= 0) {

            $newErrorCounterValue = intval($user[$this->db_user['usercounter_column']]) + 1;

        // if the login was successful we reset the error-counter
        } else if ($result >= 200) {

            $newErrorCounterValue = 0;
            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'User "%s" (uid=%s) has successfully been logged in as normal user.',
                    $user['username'],
                    $user['uid'],
                )
            );
        }

        // update the error-counter in database if something has changed
        if ($newErrorCounterValue !== $currentErrorCounterValue) {
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($this->db_user['table']);
            $queryBuilder
                ->update($this->db_user['table'])
                ->where(
                    $queryBuilder->expr()->eq(
                        $this->db_user['userid_column'],
                        $queryBuilder->createNamedParameter($user[$this->db_user['userid_column']])
                    )
                )
                ->set($this->db_user['usercounter_column'], intval($newErrorCounterValue))
                ->execute();
        }

        return $result;

    }

}
