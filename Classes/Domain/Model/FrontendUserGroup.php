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
    protected $txRkwregistrationIsMembership = false;


    /**
     * @var integer
     */
    protected $txRkwregistrationMembershipOpeningDate = 0;


    /**
     * @var integer
     */
    protected $txRkwregistrationMembershipClosingDate = 0;


    /**
     * @var string
     */
    protected $txRkwregistrationMembershipMandatoryFields = '';


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser>
     */
    protected $txRkwregistrationMembershipAdmins;


    /**
     * @var integer
     */
    protected $txRkwregistrationMembershipPid = 0;


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
        $this->txRkwregistrationMembershipAdmins = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
     * Sets the txRkwregistrationIsMembership value
     *
     * @param bool $txRkwregistrationIsMembership
     * @return void
     * @api
     */
    public function setTxRkwregistrationIsMembership(bool $txRkwregistrationIsMembership): void
    {
        $this->txRkwregistrationIsUserMembership = $txRkwregistrationIsMembership;
    }


    /**
     * Returns the txRkwregistrationIsMembership value
     *
     * @return bool
     * @api
     */
    public function getTxRkwregistrationIsMembership(): bool
    {
        return $this->txRkwregistrationIsMembership;
    }


    /**
     * Sets the txRkwregistrationMembershipOpeningDate value
     *
     * @param int $txRkwregistrationMembershipOpeningDate
     * @return void
     * @api
     */
    public function setTxRkwregistrationMembershipOpeningDate(int $txRkwregistrationMembershipOpeningDate): void
    {
        $this->txRkwregistrationMembershipOpeningDate = $txRkwregistrationMembershipOpeningDate;
    }

    /**
     * Returns the txRkwregistrationOpeningDate value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationMembershipOpeningDate(): int
    {
        return $this->txRkwregistrationMembershipOpeningDate;
    }


    /**
     * Sets the txRkwregistrationtxRkwregistrationClosingDate value
     *
     * @param int $txRkwregistrationMembershipClosingDate
     * @return void
     * @api
     */
    public function setTxRkwregistrationMembershipClosingDate(int $txRkwregistrationMembershipClosingDate)
    {
        $this->txRkwregistrationMembershipClosingDate = $txRkwregistrationMembershipClosingDate;
    }


    /**
     * Returns the txRkwregistrationMembershipClosingDate value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationMembershipClosingDate(): int
    {
        return $this->txRkwregistrationMembershipClosingDate;
    }


    /**
     * Sets the txRkwregistrationMembershipMandatoryFields
     *
     * @param string $txRkwregistrationMembershipMandatoryFields
     * @return void
     * @api
     */
    public function setTxRkwregistrationMembershipMandatoryFields(string $txRkwregistrationMembershipMandatoryFields): void
    {
        $this->txRkwregistrationMembershipMandatoryFields = $txRkwregistrationMembershipMandatoryFields;
    }


    /**
     * Returns the txRkwregistrationMembershipMandatoryFields
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationMembershipMandatoryFields(): string
    {
        return $this->txRkwregistrationMembershipMandatoryFields;
    }


    /**
     * Adds a BackendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $admin
     * @return void
     */
    public function addTxRkwregistrationMembershipAdmins(BackendUser $admin): void
    {
        $this->txRkwregistrationMembershipAdmins->attach($admin);
    }


    /**
     * Removes a BackendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $adminToRemove The BackendUser to be removed
     * @return void
     */
    public function removeTxRkwregistrationMembershipAdmins(BackendUser $adminToRemove): void
    {
        $this->txRkwregistrationMembershipAdmins->detach($adminToRemove);
    }


    /**
     * Returns the TxRkwregistrationMembershipAdmins
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $admins
     */
    public function getTxRkwregistrationMembershipAdmins(): ObjectStorage
    {
        return $this->txRkwregistrationMembershipAdmins;
    }


    /**
     * Sets the TxRkwregistrationMembershipAdmins
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $admins
     * @return void
     */
    public function setTxRkwregistrationMembershipAdmins(ObjectStorage $admins): void
    {
        $this->txRkwregistrationMembershipAdmins = $admins;
    }


    /**
     * Sets the txRkwregistrationMembershipPid value
     *
     * @param int $txRkwregistrationMembershipPid
     * @return void
     * @api
     */
    public function setTxRkwregistrationMembershipPid(int $txRkwregistrationMembershipPid): void
    {
        $this->txRkwregistrationMembershipPid = $txRkwregistrationMembershipPid;
    }


    /**
     * Returns the txRkwregistrationMembershipPid value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationMembershipPid(): int
    {
        return $this->txRkwregistrationMembershipPid;
    }

}
