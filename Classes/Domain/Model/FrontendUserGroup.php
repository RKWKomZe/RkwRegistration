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
 * Class FrontendUserGroup
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserGroup extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup
{
    
    /**
     * @var integer
     */
    protected $crdate;

    
    /**
     * @var integer
     */
    protected $tstamp;

    
    /**
     * @var bool
     */
    protected $hidden = true;

    
    /**
     * @var bool
     */
    protected $deleted = false;
    
    
    /**
     * @var bool
     */
    protected $txRkwregistrationIsService = false;

    
    /**
     * @var integer
     */
    protected $txRkwregistrationServiceOpeningDate = 0;


    /**
     * @var integer
     */
    protected $txRkwregistrationServiceClosingDate = 0;


    /**
     * @var string
     */
    protected $txRkwregistrationServiceMandatoryFields = '';


    /**
     * Admins for service
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser>
     */
    protected $txRkwregistrationServiceAdmins;
    
    
    /**
     * @var integer
     */
    protected $txRkwregistrationServicePid = 0;
    

    /**
     * __construct
     */
    public function __construct()
    {

        parent::__construct();

        // Do not remove the next line: It would break the functionality
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
        $this->txRkwregistrationServiceAdmins = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }
    
    
    /**
     * Returns the crdate value
     *
     * @return integer
     * @api
     */
    public function getCrdate(): int
    {
        return $this->crdate;
    }

    /**
     * Returns the tstamp value
     *
     * @return integer
     * @api
     */
    public function getTstamp(): int
    {
        return $this->tstamp;
    }


    /**
     * Returns the hidden value
     *
     * @return bool
     * @api
     */
    public function getHidden(): bool
    {
        return $this->hidden;
    }


    /**
     * Returns the deleted value
     *
     * @return bool
     * @api
     */
    public function getDeleted(): bool
    {
        return $this->deleted;
    }
    

    /**
     * Sets the txRkwregistrationIsService value
     *
     * @param bool $txRkwregistrationIsService
     * @return void
     * @api
     */
    public function setTxRkwregistrationIsService(bool $txRkwregistrationIsService): void
    {
        $this->txRkwregistrationIsUserService = $txRkwregistrationIsService;
    }


    /**
     * Returns the txRkwregistrationIsService value
     *
     * @return bool
     * @api
     */
    public function getTxRkwregistrationIsService(): bool
    {
        return $this->txRkwregistrationIsService;
    }


    /**
     * Sets the txRkwregistrationServiceOpeningDate value
     *
     * @param int $txRkwregistrationServiceOpeningDate
     * @return void
     * @api
     */
    public function setTxRkwregistrationServiceOpeningDate(int $txRkwregistrationServiceOpeningDate): void
    {
        $this->txRkwregistrationServiceOpeningDate = $txRkwregistrationServiceOpeningDate;
    }

    /**
     * Returns the txRkwregistrationOpeningDate value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationServiceOpeningDate(): int
    {
        return $this->txRkwregistrationServiceOpeningDate;
    }


    /**
     * Sets the txRkwregistrationtxRkwregistrationClosingDate value
     *
     * @param int $txRkwregistrationServiceClosingDate
     * @return void
     * @api
     */
    public function setTxRkwregistrationServiceClosingDate(int $txRkwregistrationServiceClosingDate)
    {
        $this->txRkwregistrationServiceClosingDate = $txRkwregistrationServiceClosingDate;
    }


    /**
     * Returns the txRkwregistrationServiceClosingDate value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationServiceClosingDate(): int
    {
        return $this->txRkwregistrationServiceClosingDate;
    }


    /**
     * Sets the txRkwregistrationServiceMandatoryFields
     *
     * @param string $txRkwregistrationServiceMandatoryFields
     * @return void
     * @api
     */
    public function setTxRkwregistrationServiceMandatoryFields(string $txRkwregistrationServiceMandatoryFields): void
    {
        $this->txRkwregistrationServiceMandatoryFields = $txRkwregistrationServiceMandatoryFields;
    }


    /**
     * Returns the txRkwregistrationServiceMandatoryFields
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationServiceMandatoryFields(): string
    {
        return $this->txRkwregistrationServiceMandatoryFields;
    }


    /**
     * Adds a BackendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $admin
     * @return void
     */
    public function addTxRkwregistrationServiceAdmins(BackendUser $admin): void
    {
        $this->txRkwregistrationServiceAdmins->attach($admin);
    }

    
    /**
     * Removes a BackendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $adminToRemove The BackendUser to be removed
     * @return void
     */
    public function removeTxRkwregistrationServiceAdmins(BackendUser $adminToRemove): void
    {
        $this->txRkwregistrationServiceAdmins->detach($adminToRemove);
    }

    
    /**
     * Returns the TxRkwregistrationServiceAdmins
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $admins
     */
    public function getTxRkwregistrationServiceAdmins(): ObjectStorage
    {
        return $this->txRkwregistrationServiceAdmins;
    }

    
    /**
     * Sets the TxRkwregistrationServiceAdmins
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $admins
     * @return void
     */
    public function setTxRkwregistrationServiceAdmins(ObjectStorage $admins): void
    {
        $this->txRkwregistrationServiceAdmins = $admins;
    }


    /**
     * Sets the txRkwregistrationServicePid value
     *
     * @param int $txRkwregistrationServicePid
     * @return void
     * @api
     */
    public function setTxRkwregistrationServicePid(int $txRkwregistrationServicePid): void
    {
        $this->txRkwregistrationServicePid = $txRkwregistrationServicePid;
    }


    /**
     * Returns the txRkwregistrationServicePid value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationServicePid(): int
    {
        return $this->txRkwregistrationServicePid;
    }

}