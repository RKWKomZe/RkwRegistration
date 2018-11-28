<?php

namespace RKW\RkwRegistration\Domain\Model;

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
 * TwitterUser
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TwitterUser extends \RKW\RkwRegistration\Domain\Model\FrontendUser implements SocialMediaInterface
{
    /**
     * Insert data from Twitter
     *
     * @param array $twitterUserData
     * @return $this
     * @throws  \RKW\RkwRegistration\Exception
     */
    public function insertData($twitterUserData)
    {


        if ((!$twitterUserData)
            || (!is_array($twitterUserData))
        ) {
            throw new \RKW\RkwRegistration\Exception('Invalid user-data given.', 1407749166);
            //===
        }


        // check e-mail and build it via user-id if no email given
        $email = $twitterUserData['email'];
        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($email)) {
            $email = intval($twitterUserData['user_id']) . '@twitter.com';
        }

        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($email)) {
            throw new \RKW\RkwRegistration\Exception('Invalid e-mail given.', 1407749412);
            //===
        }


        // set basics!
        $this->setUsername($email);

        // set other values
        $this->setTxRkwregistrationTwitterId(intval($twitterUserData['user_id']));
        $this->setName(trim(strip_tags($twitterUserData['screen_name'])));
        $this->setLastName(trim(strip_tags($twitterUserData['screen_name'])));

        // generate url with delivered screen-name
        $this->setTxRkwregistrationTwitterUrl('https://twitter.com/' . trim(strip_tags($twitterUserData['screen_name'])));

        return $this;
        //===
    }


}