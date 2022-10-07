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

use RKW\RkwBasics\Persistence\MarkerReducer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


/**
 * OptIn
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OptIn extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * FrontendUserUid
     * Has to be an uid only because disabled objects are not loaded via extbase
     *
     * @var int
     */
    protected $frontendUserUid = 0;


    /**
     * frontendUserUpdate
     *
     * @var string
     */
    protected $frontendUserUpdate = '';


    /**
     * tokenUser
     *
     * @var string
     */
    protected $tokenUser = '';


    /**
     * adminTokenYes
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
     * adminTokenYes
     *
     * @var string
     */
    protected $adminTokenYes = '';


    /**
     * adminTokenNo
     *
     * @var string
     */
    protected $adminTokenNo = '';


    /**
     * category
     *
     * @var string
     */
    protected $category = '';


    /**
     * starttime
     *
     * @var integer
     */
    protected $starttime = 0;


    /**
     * endtime
     *
     * @var integer
     */
    protected $endtime = 0;


    /**
     * deleted
     *
     * @var bool
     */
    protected $deleted = false;


    /**
     * approved
     *
     * @var bool
     */
    protected $approved = false;


    /**
     * adminApproved
     *
     * @var bool
     */
    protected $adminApproved = false;


    /**
     * data
     *
     * @var string
     */
    protected $data = '';


    /**
     * !!! Should never be persisted!!! !!!
     * dataRaw
     *
     * @var string
     */
    protected $_rawdata = '';


    /**
     * Returns the frontendUserId
     *
     * @return int $user
     */
    public function getFrontendUserUid (): int
    {
        return $this->frontendUserUid;
    }


    /**
     * Sets the frontendUserUid
     *
     * @param int $frontendUserUid
     * @return void
     */
    public function setFrontendUserUid(int $frontendUserUid): void
    {
        $this->frontendUserUid = $frontendUserUid;
    }


    /**
     * Returns the frontendUserUpdate
     *
     * @return array $frontendUserUpdate
     */
    public function getFrontendUserUpdate(): array
    {
        if ($this->frontendUserUpdate) {
            return unserialize($this->frontendUserUpdate);
        }

        return [];
    }


    /**
     * Sets the frontendUserUpdate
     *
     * @param array $frontendUserUpdate
     * @return void
     */
    public function setFrontendUserUpdate(array $frontendUserUpdate): void
    {
        $this->frontendUserUpdate = serialize($frontendUserUpdate);
    }


    /**
     * Returns the tokenUser
     *
     * @return string $tokenUser
     */
    public function getTokenUser(): string
    {
        return $this->tokenUser;
    }


    /**
     * Sets the tokenUser
     *
     * @param string $tokenUser
     * @return void
     */
    public function setTokenUser(string $tokenUser): void
    {
        $this->tokenUser = $tokenUser;
    }


    /**
     * Returns the yesToken
     *
     * @return string $tokenYes
     */
    public function getTokenYes(): string
    {
        return $this->tokenYes;
    }


    /**
     * Sets the yesToken
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
     * Returns the yesAdminToken
     *
     * @return string $adminTokenYes
     */
    public function getAdminTokenYes(): string
    {
        return $this->adminTokenYes;
    }


    /**
     * Sets the yesAdminToken
     *
     * @param string $adminTokenYes
     * @return void
     */
    public function setAdminTokenYes(string $adminTokenYes): void
    {
        $this->adminTokenYes = $adminTokenYes;
    }


    /**
     * Returns the adminTokenNo
     *
     * @return string $adminTokenNo
     */
    public function getAdminTokenNo(): string
    {
        return $this->adminTokenNo;
    }


    /**
     * Sets the adminTokenNo
     *
     * @param string $adminTokenNo
     * @return void
     */
    public function setAdminTokenNo(string $adminTokenNo): void
    {
        $this->adminTokenNo = $adminTokenNo;
    }


    /**
     * Returns the category
     *
     * @return string $category
     */
    public function getCategory(): string
    {
        return $this->category;
    }


    /**
     * Sets the category
     *
     * @param string $category
     * @return void
     */
    public function setCategory(string $category)
    {
        $this->category = $category;
    }

    /**
     * Returns the starttime
     *
     * @return int $starttime
     */
    public function getStarttime(): int
    {
        return $this->starttime;
    }


    /**
     * Sets the starttime
     *
     * @param int $starttime
     * @return void
     */
    public function setStarttime(int $starttime)
    {
        $this->starttime = $starttime;
    }


    /**
     * Returns the endtime
     *
     * @return int $endtime
     */
    public function getEndtime(): int
    {
        return $this->endtime;
    }


    /**
     * Sets the endtime
     *
     * @param int $endtime
     * @return void
     */
    public function setEndtime(int $endtime)
    {
        $this->endtime = $endtime;
    }


    /**
     * Sets the deleted value
     *
     * @param bool $deleted
     * @return void
     *
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }


    /**
     * Returns the deleted value
     *
     * @return bool
     *
     */
    public function getDeleted(): bool
    {
        return $this->deleted;
    }


    /**
     * Returns the approved
     *
     * @return bool $approved
     */
    public function getApproved(): bool
    {
        return $this->approved;
    }


    /**
     * Sets the approved
     *
     * @param bool $approved
     * @return void
     */
    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }


    /**
     * Returns the adminApproved
     *
     * @return bool $adminApproved
     */
    public function getAdminApproved(): bool
    {
        return $this->adminApproved;
    }


    /**
     * Sets the adminApproved
     *
     * @param bool $adminApproved
     * @return void
     */
    public function setAdminApproved(bool $adminApproved): void
    {
        $this->adminApproved = $adminApproved;
    }


    /**
     * Returns the data
     *
     * @return mixed $data
     */
    public function getData()
    {
        if ($this->data) {

            if (! $this->_dataRaw) {
                $tempData = MarkerReducer::explode(unserialize($this->data));
                $this->_dataRaw = $tempData['key'];
            }

            return $this->_dataRaw;
        }

        return false;
    }


    /**
     * Sets the data
     *
     * @param mixed $data
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public function setData($data): void
    {
        if ($data) {
            $this->_dataRaw = $data;
            $this->data = serialize(MarkerReducer::implode(['key' => $data]));
        }
    }
}
