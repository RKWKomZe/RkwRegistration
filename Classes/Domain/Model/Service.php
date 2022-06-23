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

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Service
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Service extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    
    /**
     * FrontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    protected $user;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\FrontendUserGroup>
     */
    protected $usergroup;


    /**
     * serviceSha1
     *
     * @var string
     */
    protected $serviceSha1 = '';


    /**
     * tokenYes
     *
     * @var string
     */
    protected $tokenYes = '';


    /**
     * tokenNo
     *
     * @var string
     */
    protected $tokenNo = '';


    /**
     * validUntil
     *
     * @var integer
     */
    protected $validUntil = 0;


    /**
     * enabledByAdmin
     *
     * @var bool
     */
    protected $enabledByAdmin = false;


    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->usergroup = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }


    /**
     * Returns the feuser
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the feuser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $user
     * @return void
     */
    public function setUser(FrontendUser $user): void
    {
        $this->user = $user;
    }


    /**
     * Adds a usergroup to the frontend user
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $userGroup
     * @return void
     * @api
     */
    public function addUsergroup(FrontendUserGroup $userGroup): void
    {
        $this->usergroup->attach($userGroup);
    }


    /**
     * Removes a usergroup from the frontend user
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $userGroup
     * @return void
     * @api
     */
    public function removeUsergroup(FrontendUserGroup $userGroup): void
    {
        $this->usergroup->detach($userGroup);
    }


    /**
     * Sets the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroup
     * @return void
     * @api
     */
    public function setUsergroup(ObjectStorage $userGroup): void
    {
        $this->usergroup = $userGroup;
    }

    /**
     * Returns the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the usergroup
     * @api
     */
    public function getUsergroup(): ObjectStorage
    {
        return $this->usergroup;
    }


    /**
     * Returns the serviceSha1
     *
     * @return string $serviceSha1
     */
    public function getServiceSha1(): string
    {
        return $this->serviceSha1;
    }

    
    /**
     * Sets the serviceSha1
     *
     * @param string $serviceSha1
     * @return void
     */
    public function setServiceSha1(string $serviceSha1): void
    {
        $this->serviceSha1 = $serviceSha1;
    }


    /**
     * Returns the tokenYes
     *
     * @return string $tokenYes
     */
    public function getTokenYes(): string
    {
        return $this->tokenYes;
    }

    
    /**
     * Sets the tokenYes
     *
     * @param string $tokenYes
     * @return void
     */
    public function setTokenYes(string $tokenYes): void
    {
        $this->tokenYes = $tokenYes;
    }


    /**
     * Returns the tokenNo
     *
     * @return string $tokenNo
     */
    public function getTokenNo(): string
    {
        return $this->tokenNo;
    }

    
    /**
     * Sets the tokenNo
     *
     * @param string $tokenNo
     * @return void
     */
    public function setTokenNo(string $tokenNo): void
    {
        $this->tokenNo = $tokenNo;
    }


    /**
     * Returns the validUntil
     *
     * @return int $validUntil
     */
    public function getValidUntil(): int
    {
        return $this->validUntil;
    }


    /**
     * Sets the validUntil
     *
     * @param int $validUntil
     * @return void
     */
    public function setValidUntil(int $validUntil): void
    {
        $this->validUntil = $validUntil;
    }


    /**
     * Returns the enabledByAdmin
     *
     * @return bool $enabledByAdmin
     */
    public function getEnabledByAdmin(): bool
    {
        return $this->enabledByAdmin;
    }

    /**
     * Sets the enabledByAdmin
     *
     * @param bool $enabledByAdmin
     * @return void
     */
    public function setEnabledByAdmin(bool $enabledByAdmin): void
    {
        $this->enabledByAdmin = $enabledByAdmin;
    }

}