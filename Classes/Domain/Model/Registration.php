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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Registration
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Registration extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * category
     *
     * @var string
     */
    protected $category;

    /**
     * FrontendUser
     *
     * @var int
     */
    protected $user;


    /**
     * userSha1
     *
     * @var string
     */
    protected $userSha1;


    /**
     * tokenYes
     *
     * @var string
     */
    protected $tokenYes;


    /**
     * tokenNo
     *
     * @var string
     */
    protected $tokenNo;


    /**
     * validUntil
     *
     * @var integer
     */
    protected $validUntil;


    /**
     * data
     *
     * @var string
     */
    protected $data;


    /**
     * Returns the category
     *
     * @return string $category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Sets the category
     *
     * @param string $category
     * @return void
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }


    /**
     * Returns the feuser
     *
     * @return int $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the feuser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|int $user
     * @return void
     */
    public function setUser($user)
    {
        if ($user instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser) {
            $this->user = $user->getUid();
        } else {
            $this->user = $user;
        }
    }


    /**
     * Returns the userSha1
     *
     * @return string $userSha1
     */
    public function getUserSha1()
    {
        return $this->userSha1;
    }

    /**
     * Sets the userSha1
     *
     * @param string $userSha1
     * @return void
     */
    public function setUserSha1($userSha1)
    {
        $this->userSha1 = $userSha1;
    }


    /**
     * Returns the tokenYes
     *
     * @return string $tokenYes
     */
    public function getTokenYes()
    {
        return $this->tokenYes;
    }

    /**
     * Sets the tokenYes
     *
     * @param string $tokenYes
     * @return void
     */
    public function setTokenYes($tokenYes)
    {
        $this->tokenYes = $tokenYes;
    }


    /**
     * Returns the tokenNo
     *
     * @return string $tokenNo
     */
    public function getTokenNo()
    {
        return $this->tokenNo;
    }

    /**
     * Sets the tokenNo
     *
     * @param string $tokenNo
     * @return void
     */
    public function setTokenNo($tokenNo)
    {
        $this->tokenNo = $tokenNo;
    }


    /**
     * Returns the validUntil
     *
     * @return string $validUntil
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Sets the validUntil
     *
     * @param string $validUntil
     * @return void
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;
    }


    /**
     * Returns the data
     *
     * @return mixed $data
     */
    public function getData()
    {
        if ($this->data) {
            return unserialize($this->data);
        }
        return null;
    }

    /**
     * Sets the data
     *
     * @param \RKW\RkwEvents\Domain\Model\EventReservation $data
     * @return void
     */
    public function setData($data)
    {
        if ($data) {
            $this->data = serialize($data);
        }
    }

}