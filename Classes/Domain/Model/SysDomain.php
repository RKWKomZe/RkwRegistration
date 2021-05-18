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
 * Class SysDomain
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SysDomain extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * pid
     *
     * @var integer
     */
    protected $pid;

    /**
     * uid
     *
     * @var integer
     */
    protected $uid;

    /**
     * domainName
     *
     * @var string
     */
    protected $domainName;

    /**
     * txRkwregistrationRelatedSysDomain
     *
     * @var \RKW\RkwRegistration\Domain\Model\SysDomain
     */
    protected $txRkwregistrationRelatedSysDomain = null;

    /**
     * txRkwregistrationPageLogin
     *
     * @var \RKW\RkwRegistration\Domain\Model\Pages
     */
    protected $txRkwregistrationPageLogin = null;

    /**
     * txRkwregistrationPageLogout
     *
     * @var \RKW\RkwRegistration\Domain\Model\Pages
     */
    protected $txRkwregistrationPageLogout = null;

    /**
     * txRkwregistrationPageLoginGuest
     *
     * @var \RKW\RkwRegistration\Domain\Model\Pages
     */
    protected $txRkwregistrationPageLoginGuest = null;

    /**
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @param string $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * @return SysDomain
     */
    public function getTxRkwregistrationRelatedSysDomain()
    {
        return $this->txRkwregistrationRelatedSysDomain;
    }

    /**
     * @param SysDomain $txRkwregistrationRelatedSysDomain
     */
    public function setTxRkwregistrationRelatedSysDomain(SysDomain $txRkwregistrationRelatedSysDomain): void
    {
        $this->txRkwregistrationRelatedSysDomain = $txRkwregistrationRelatedSysDomain;
    }

    /**
     * @return Pages
     */
    public function getTxRkwregistrationPageLogin()
    {
        return $this->txRkwregistrationPageLogin;
    }

    /**
     * @param Pages $txRkwregistrationPageLogin
     */
    public function setTxRkwregistrationPageLogin(Pages $txRkwregistrationPageLogin): void
    {
        $this->txRkwregistrationPageLogin = $txRkwregistrationPageLogin;
    }

    /**
     * @return Pages
     */
    public function getTxRkwregistrationPageLogout()
    {
        return $this->txRkwregistrationPageLogout;
    }

    /**
     * @param Pages $txRkwregistrationPageLogout
     */
    public function setTxRkwregistrationPageLogout(Pages $txRkwregistrationPageLogout): void
    {
        $this->txRkwregistrationPageLogout = $txRkwregistrationPageLogout;
    }

    /**
     * @return Pages
     */
    public function getTxRkwregistrationPageLoginGuest()
    {
        return $this->txRkwregistrationPageLoginGuest;
    }

    /**
     * @param Pages $txRkwregistrationPageLoginGuest
     */
    public function setTxRkwregistrationPageLoginGuest(Pages $txRkwregistrationPageLoginGuest): void
    {
        $this->txRkwregistrationPageLoginGuest = $txRkwregistrationPageLoginGuest;
    }

}