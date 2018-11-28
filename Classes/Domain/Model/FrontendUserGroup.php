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
     * @var integer
     */
    protected $hidden = 1;

    /**
     * @var integer
     */
    protected $deleted = 0;


    /**
     * @var integer
     */
    protected $txRkwregistrationIsService = 0;


    /**
     * @var integer
     */
    protected $txRkwregistrationServiceOpeningDate = '';


    /**
     * @var integer
     */
    protected $txRkwregistrationServiceClosingDate = '';


    /**
     * @var string
     */
    protected $txRkwregistrationServiceMandatoryFields = '';


    /**
     * Admins for service
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser>
     */
    protected $txRkwregistrationServiceAdmins = null;

    /**
     * @var integer
     */
    protected $txRkwregistrationServicePid = 0;


    /**
     * Returns the crdate value
     *
     * @return integer
     * @api
     */
    public function getCrdate()
    {
        return $this->crdate;
        //===
    }

    /**
     * Returns the tstamp value
     *
     * @return integer
     * @api
     */
    public function getTstamp()
    {
        return $this->tstamp;
        //===
    }


    /**
     * Returns the hidden value
     *
     * @return integer
     * @api
     */
    public function getHidden()
    {
        return $this->hidden;
        //===
    }


    /**
     * Returns the deletedvalue
     *
     * @return integer
     * @api
     */
    public function getDeleted()
    {
        return $this->deleted;
        //===
    }


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
     * Sets the txRkwregistrationIsService value
     *
     * @param int $txRkwregistrationIsService
     * @return void
     * @api
     */
    public function setTxRkwregistrationIsService($txRkwregistrationIsService)
    {
        $this->txRkwregistrationIsUserService = $txRkwregistrationIsService;
    }


    /**
     * Returns the txRkwregistrationIsService value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationIsService()
    {
        return $this->txRkwregistrationIsService;
    }


    /**
     * Sets the txRkwregistrationServiceOpeningDate value
     *
     * @param string $txRkwregistrationServiceOpeningDate
     * @return void
     * @api
     */
    public function setTxRkwregistrationServiceOpeningDate($txRkwregistrationServiceOpeningDate)
    {
        $this->txRkwregistrationServiceOpeningDate = $txRkwregistrationServiceOpeningDate;
    }

    /**
     * Returns the txRkwregistrationOpeningDate value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationServiceOpeningDate()
    {
        return $this->txRkwregistrationServiceOpeningDate;
    }


    /**
     * Sets the txRkwregistrationtxRkwregistrationClosingDate value
     *
     * @param string $txRkwregistrationServiceClosingDate
     * @return void
     * @api
     */
    public function setTxRkwregistrationServiceClosingDate($txRkwregistrationServiceClosingDate)
    {
        $this->txRkwregistrationServiceClosingDate = $txRkwregistrationServiceClosingDate;
    }


    /**
     * Returns the txRkwregistrationServiceClosingDate value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationServiceClosingDate()
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
    public function setTxRkwregistrationServiceMandatoryFields($txRkwregistrationServiceMandatoryFields)
    {
        $this->txRkwregistrationServiceMandatoryFields = $txRkwregistrationServiceMandatoryFields;
    }


    /**
     * Returns the txRkwregistrationServiceMandatoryFields
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationServiceMandatoryFields()
    {
        return $this->txRkwregistrationServiceMandatoryFields;
    }


    /**
     * Adds a BackendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $admin
     * @return void
     */
    public function addTxRkwregistrationServiceAdmins(\RKW\RkwRegistration\Domain\Model\BackendUser $admin)
    {
        $this->txRkwregistrationServiceAdmins->attach($admin);
    }

    /**
     * Removes a BackendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $adminToRemove The BackendUser to be removed
     * @return void
     */
    public function removeTxRkwregistrationServiceAdmins(\RKW\RkwRegistration\Domain\Model\BackendUser $adminToRemove)
    {
        $this->txRkwregistrationServiceAdmins->detach($adminToRemove);
    }

    /**
     * Returns the TxRkwregistrationServiceAdmins
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $admins
     */
    public function getTxRkwregistrationServiceAdmins()
    {
        return $this->txRkwregistrationServiceAdmins;
    }

    /**
     * Sets the TxRkwregistrationServiceAdmins
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $admins
     * @return void
     */
    public function setTxRkwregistrationServiceAdmins(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $admins)
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
    public function setTxRkwregistrationServicePid($txRkwregistrationServicePid)
    {
        $this->txRkwregistrationServicePid = $txRkwregistrationServicePid;
    }


    /**
     * Returns the txRkwregistrationServicePid value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationServicePid()
    {
        return $this->txRkwregistrationServicePid;
    }

}