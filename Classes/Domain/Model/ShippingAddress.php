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
 * Class ShippingAddress
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ShippingAddress extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{


    /**
     * gender
     *
     * @var integer
     * @validate \RKW\RkwRegistration\Validation\GenderValidator
     */
    protected $gender = 99;


    /**
     * title
     *
     * @var \RKW\RkwRegistration\Domain\Model\Title
     */
    protected $title = null;


    /**
     * firstName
     *
     * @var string
     * @validate NotEmpty, String
     */
    protected $firstName;

    /**
     * lastName
     *
     * @var string
     * @validate NotEmpty, String
     */
    protected $lastName;

    /**
     * company
     *
     * @var string
     */
    protected $company;

    /**
     * fullName
     *
     * @var string
     */
    protected $fullName;

    /**
     * address
     *
     * @var string
     * @validate NotEmpty, String
     */
    protected $address;

    /**
     * zip
     *
     * @var string
     * @validate \RKW\RkwOrder\Validation\Validator\ZipValidator
     */
    protected $zip;

    /**
     * city
     *
     * @var string
     * @validate NotEmpty, String
     */
    protected $city;


    /**
     * Returns the gender
     *
     * @return integer $gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Sets the gender
     *
     * @param integer $gender
     * @return void
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * Returns the title
     *
     * @return \RKW\RkwRegistration\Domain\Model\Title $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * Hint: default "null" is needed to make value in forms optional
     *
     * @param \RKW\RkwRegistration\Domain\Model\Title $title
     * @return void
     */
    public function setTitle(\RKW\RkwRegistration\Domain\Model\Title $title = null)
    {
        $this->title = $title;
    }

    /**
     * Returns the firstName
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the firstName
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the lastName
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }


    /**
     * Returns the company
     *
     * @return string $company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Sets the company
     *
     * @param string $company
     * @return void
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }


    /**
     * ### Additional getter without database support ###
     * Returns the fullName
     *
     * @return string $fullName
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Returns the address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the address
     *
     * @param string $address
     * @return void
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Returns the zip
     *
     * @return string $zip
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Sets the zip
     *
     * @param string $zip
     * @return void
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Returns the city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city
     *
     * @param string $city
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }


}
