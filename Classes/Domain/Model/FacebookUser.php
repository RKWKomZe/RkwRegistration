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
 * FacebookUser
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FacebookUser extends \RKW\RkwRegistration\Domain\Model\FrontendUser implements SocialMediaInterface
{
    /**
     * insert data from facebook
     *
     * @param \Facebook\GraphNodes\GraphUser $facebookUserGraph
     * @return $this
     * @throws \RKW\RkwRegistration\Exception
     */
    public function insertData(\Facebook\GraphNodes\GraphUser $facebookUserGraph)
    {

        // check e-mail and build it via user-id if no email given
        $email = $facebookUserGraph->getField('email');
        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($email)) {
            $email = intval($facebookUserGraph->getField('id')) . '@facebook.com';
        }

        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($email)) {
            throw new \RKW\RkwRegistration\Exception('Invalid e-mail given.', 1407749412);
            //===
        }


        // set basics!
        $this->setUsername($email);

        // set other values
        $this->setTxRkwregistrationFacebookId(intval($facebookUserGraph->getField('id')));
        $this->setFirstName(trim(strip_tags($facebookUserGraph->getField('first_name'))));
        $this->setLastName(trim(strip_tags($facebookUserGraph->getField('last_name'))));
        $this->setName(trim(strip_tags($facebookUserGraph->getField('name'))));
        $this->setTxRkwregistrationFacebookUrl(trim(strip_tags($facebookUserGraph->getField('link'))));

        if ($facebookUserGraph->getField('gender') == 'female') {
            $this->setTxRkwregistrationGender(1);
        }

        if ($facebookUserGraph->getField('gender') == 'male') {
            $this->setTxRkwregistrationGender(0);
        }

        return $this;
        //===
    }


}