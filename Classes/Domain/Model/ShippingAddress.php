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
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ShippingAddress extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * frontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    protected $frontendUser;


    /**
     * gender
     *
     * @var integer
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\GenderValidator")
     */
    protected $gender = 99;


    /**
     * title
     *
     * @var \RKW\RkwRegistration\Domain\Model\Title
     */
    protected $title;


    /**
     * firstName
     *
     * @var string
     */
    protected $firstName = '';


    /**
     * lastName
     *
     * @var string
     */
    protected $lastName = '';


    /**
     * company
     *
     * @var string
     */
    protected $company = '';


    /**
     * fullName
     *
     * @var string
     */
    protected $fullName = '';


    /**
     * address
     *
     * @var string
     */
    protected $address = '';


    /**
     * zip
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\ZipValidator")
     */
    protected $zip = '';


    /**
     * city
     *
     * @var string
     */
    protected $city = '';


    /**
     * Returns the frontendUser
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     */
    public function getFrontendUser() :? FrontendUser
    {
        return $this->frontendUser;
    }


    /**
     * Sets the frontendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     */
    public function setFrontendUser(FrontendUser $frontendUser): void
    {
        $this->frontendUser = $frontendUser;
    }


    /**
     * Returns the gender
     *
     * @return integer $gender
     */
    public function getGender(): int
    {
        return $this->gender;
    }


    /**
     * Sets the gender
     *
     * @param integer $gender
     * @return void
     */
    public function setGender(int $gender): void
    {
        $this->gender = $gender;
    }


    /**
     * Returns the title
     *
     * @return \RKW\RkwRegistration\Domain\Model\Title $title
     */
    public function getTitle() :? Title
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
    public function setTitle(Title $title): void
    {
        $this->title = $title;
    }


    /**
     * Returns the firstName
     *
     * @return string $firstName
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }


    /**
     * Sets the firstName
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }


    /**
     * Returns the lastName
     *
     * @return string $lastName
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }


    /**
     * Sets the lastName
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }


    /**
     * Returns the company
     *
     * @return string $company
     */
    public function getCompany(): string
    {
        return $this->company;
    }


    /**
     * Sets the company
     *
     * @param string $company
     * @return void
     */
    public function setCompany(string $company): void
    {
        $this->company = $company;
    }


    /**
     * Additional getter without database support
     *
     * @return string $fullName
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }


    /**
     * Returns the address
     *
     * @return string $address
     */
    public function getAddress(): string
    {
        return $this->address;
    }


    /**
     * Sets the address
     *
     * @param string $address
     * @return void
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }


    /**
     * Returns the zip
     *
     * @return string $zip
     */
    public function getZip(): string
    {
        return $this->zip;
    }


    /**
     * Sets the zip
     *
     * @param string $zip
     * @return void
     */
    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }


    /**
     * Returns the city
     *
     * @return string $city
     */
    public function getCity(): string
    {
        return $this->city;
    }


    /**
     * Sets the city
     *
     * @param string $city
     * @return void
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }
}
