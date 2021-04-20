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
     * fallback
     *
     * @var bool
     */
    protected $fallback;

    /**
     * txRkwregistrationRelatedSysDomain
     *
     * @var \RKW\RkwRegistration\Domain\Model\SysDomain
     */
    protected $txRkwregistrationRelatedSysDomain;

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
     * @return bool
     */
    public function isFallback(): bool
    {
        return $this->fallback;
    }

    /**
     * @param bool $fallback
     */
    public function setFallback(bool $fallback): void
    {
        $this->fallback = $fallback;
    }

    /**
     * @return SysDomain
     */
    public function getTxRkwregistrationRelatedSysDomain(): SysDomain
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

}