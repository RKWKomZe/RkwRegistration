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
     * nameLong
     *
     * @var string
     */
    protected $nameLong = '';

    /**
     * isTitleAfter
     *
     * @var boolean
     */
    protected $isTitleAfter = false;


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
     * Returns the isTitleAfter
     *
     * @return string $isTitleAfter
     */
    public function getIsTitleAfter()
    {
        return $this->isTitleAfter;
    }

    /**
     * Sets the isTitleAfter
     *
     * @param string $isTitleAfter
     * @return void
     */
    public function setIsTitleAfter($isTitleAfter)
    {
        $this->isTitleAfter = $isTitleAfter;
    }

}