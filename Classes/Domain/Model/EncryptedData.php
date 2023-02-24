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
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EncryptedData extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     */
    public ?FrontendUser $frontendUser = null;


    /**
     * @var string
     */
    protected string $searchKey = '';


    /**
     * @var int
     */
    public int $foreignUid = 0;


    /**
     * @var string
     */
    public string $foreignTable = '';


    /**
     * @var string
     */
    public string $foreignClass = '';


    /**
     * @var string
     */
    public string $encryptedData = '';


    /**
     * Sets the frontendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     */
    public function setFrontendUser(FrontendUser $frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }


    /**
     * Returns the frontendUser
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     */
    public function getFrontendUser() :? FrontendUser
    {
        return $this->frontendUser;
    }


    /**
     * Sets the searchKey value
     *
     * @param string $searchKey
     * @return void
     */
    public function setSearchKey(string $searchKey)
    {
        $this->searchKey = $searchKey;
    }


    /**
     * Returns the searchKey value
     *
     * @return string
     */
    public function getSearchKey(): string
    {
        return $this->searchKey;
    }


    /**
     * Sets the foreignUid value
     *
     * @param int $foreignUid
     * @return void
     */
    public function setForeignUid(int $foreignUid)
    {
        $this->foreignUid = $foreignUid;
    }


    /**
     * Returns the foreignUid value
     *
     * @return int
     */
    public function getForeignUid(): int
    {
        return $this->foreignUid;
    }


    /**
     * Sets the foreignTable value
     *
     * @param string $foreignTable
     * @return void
     */
    public function setForeignTable(string $foreignTable)
    {
        $this->foreignTable = $foreignTable;
    }


    /**
     * Returns the foreignTable value
     *
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }


    /**
     * Sets the foreignClass value
     *
     * @param string $foreignClass
     * @return void
     */
    public function setForeignClass(string $foreignClass)
    {
        $this->foreignClass = $foreignClass;
    }


    /**
     * Returns the foreignClass value
     *
     * @return string
     */
    public function getForeignClass(): string
    {
        return $this->foreignClass;
    }


    /**
     * Sets the encryptedValue value
     *
     * @param array $encryptedData
     * @return void
     */
    public function setEncryptedData(array $encryptedData)
    {
        $this->encryptedData = serialize($encryptedData);
    }


    /**
     * Returns the encryptedData value
     *
     * @return array
     */
    public function getEncryptedData(): array
    {
        return unserialize($this->encryptedData);
    }

}
