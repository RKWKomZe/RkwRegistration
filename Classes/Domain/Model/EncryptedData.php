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
 * EncryptedData
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EncryptedData extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * frontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public $frontendUser;


    /**
     * @var string
     */
    protected $searchKey;

    /**
     * foreignUid
     *
     * @var integer
     */
    public $foreignUid;

    /**
     * foreignTable
     *
     * @var string
     */
    public $foreignTable;

    /**
     * foreignClass
     *
     * @var string
     */
    public $foreignClass;


    /**
     * encryptedData
     *
     * @var string
     */
    public $encryptedData;    
    

    /**
     * Sets the frontendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     */
    public function setFrontendUser($frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * Returns the frontendUser
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }


    /**
     * Sets the searchKey value
     *
     * @param integer $searchKey
     * @return void
     *
     */
    public function setSearchKey($searchKey)
    {
        $this->searchKey = $searchKey;
    }


    /**
     * Returns the searchKey value
     *
     * @return integer
     *
     */
    public function getSearchKey()
    {
        return $this->searchKey;
    }

    /**
     * Sets the foreignUid value
     *
     * @param int $foreignUid
     * @return void
     */
    public function setForeignUid($foreignUid)
    {
        $this->foreignUid = $foreignUid;
    }

    /**
     * Returns the foreignUid value
     *
     * @return integer
     */
    public function getForeignUid()
    {
        return $this->foreignUid;
    }

    /**
     * Sets the foreignTable value
     *
     * @param string $foreignTable
     * @return void
     */
    public function setForeignTable($foreignTable)
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * Returns the foreignTable value
     *
     * @return string
     */
    public function getForeignTable()
    {
        return $this->foreignTable;
    }

    /**
     * Sets the foreignClass value
     *
     * @param string $foreignClass
     * @return void
     */
    public function setForeignClass($foreignClass)
    {
        $this->foreignClass = $foreignClass;
    }

    /**
     * Returns the foreignClass value
     *
     * @return string
     */
    public function getForeignClass()
    {
        return $this->foreignClass;
    }


    /**
     * Sets the encryptedValue value
     *
     * @param array $encryptedData
     * @return void
     */
    public function setEncryptedData($encryptedData)
    {
        $this->encryptedData = serialize($encryptedData);
    }

    /**
     * Returns the encryptedData value
     *
     * @return array
     */
    public function getEncryptedData()
    {
        return unserialize($this->encryptedData);
    }

}