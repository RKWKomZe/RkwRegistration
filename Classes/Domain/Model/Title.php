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
 * Class Title
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Fäßler Web UG
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Title extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
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
    protected $hidden;


    /**
     * @var integer
     */
    protected $deleted;

    /**
     * name
     *
     * @var string
     */
    protected $name = '';

    /**
     * nameFemale
     *
     * @var string
     */
    protected $nameFemale = '';

    /**
     * nameLong
     *
     * @var string
     */
    protected $nameLong = '';

    /**
     * nameFemaleLong
     *
     * @var string
     */
    protected $nameFemaleLong = '';

    /**
     * isTitleAfter
     *
     * @var boolean
     */
    protected $isTitleAfter = false;

    /**
     * isIncludedInSalutation
     *
     * @var boolean
     */
    protected $isIncludedInSalutation = false;

    /**
     * isChecked
     *
     * @var boolean
     */
    protected $isChecked = false;

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
     * Sets the hidden value
     *
     * @param integer $hidden
     * @api
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
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
     * Sets the deleted value
     *
     * @param integer $deleted
     * @api
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }


    /**
     * Returns the deleted value
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
     * Returns the name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the female variant of the name
     *
     * @return string $nameFemale
     */
    public function getNameFemale()
    {
        return $this->nameFemale;
    }

    /**
     * Sets the female variant of the name
     *
     * @param string $nameFemale
     * @return void
     */
    public function setNameFemale($nameFemale)
    {
        $this->nameFemale = $nameFemale;
    }

    /**
     * Returns the nameLong
     *
     * @return string $nameLong
     */
    public function getNameLong()
    {
        return $this->nameLong;
    }

    /**
     * Sets the nameLong
     *
     * @param string $nameLong
     * @return void
     */
    public function setNameLong($nameLong)
    {
        $this->nameLong = $nameLong;
    }

    /**
     * Returns the long female variant of the name
     *
     * @return string $nameFemaleLong
     */
    public function getNameFemaleLong()
    {
        return $this->nameFemaleLong;
    }

    /**
     * Sets the long female variant of the name
     *
     * @param string $nameFemaleLong
     * @return void
     */
    public function setNameFemaleLong($nameFemaleLong)
    {
        $this->nameFemaleLong = $nameFemaleLong;
    }

    /**
     * Returns the isTitleAfter
     *
     * @return boolean $isTitleAfter
     */
    public function getIsTitleAfter()
    {
        return $this->isTitleAfter;
    }

    /**
     * Sets the isTitleAfter
     *
     * @param boolean $isTitleAfter
     * @return void
     */
    public function setIsTitleAfter($isTitleAfter)
    {
        $this->isTitleAfter = $isTitleAfter;
    }

    /**
     * Returns the isIncludedInSalutation
     *
     * @return boolean $isIncludedInSalutation
     */
    public function getIsIncludedInSalutation()
    {
        return $this->isIncludedInSalutation;
    }

    /**
     * Sets the isIncludedInSalutation
     *
     * @param boolean $isIncludedInSalutation
     * @return void
     */
    public function setIsIncludedInSalutation($isIncludedInSalutation)
    {
        $this->isIncludedInSalutation = $isIncludedInSalutation;
    }

    /**
     * Returns the isChecked
     *
     * @return booelan $isChecked
     */
    public function getIsChecked()
    {
        return $this->isChecked;
    }

    /**
     * Sets the isChecked
     *
     * @param boolean $isChecked
     * @return void
     */
    public function setIsChecked($isChecked)
    {
        $this->isChecked = $isChecked;
    }

}