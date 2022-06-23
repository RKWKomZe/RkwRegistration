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

use RKW\RkwMailer\Utility\FrontendLocalizationUtility;
use RKW\RkwRegistration\Domain\Repository\TitleRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup as CoreFrontendUserGroup;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class FrontendUser
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
{

    /**
     * @var int
     */
    protected $crdate;
    
    
    /**
     * @var int
     */
    protected $tstamp;
    
    
    /**
     * @var int
     */
    protected $starttime = 0;
    
    
    /**
     * @var int
     */
    protected $endtime = 0;
    
    
    /**
     * @var bool
     */
    protected $disable = true;
    
    
    /**
     * @var bool
     */
    protected $deleted = false;
    
    
    /**
     * @var string
     */
    protected $email = '';
    
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\FrontendUserGroup>
     */
    protected $usergroup;
    
    
    /**
     * txRkwregistrationTitle
     *
     * @var \RKW\RkwRegistration\Domain\Model\Title|null
     */
    protected $txRkwregistrationTitle;

    
    /**
     * @var string
     */
    protected $txRkwregistrationMobile = '';

    
    /**
     * @var int
     */
    protected $txRkwregistrationGender = 99;

    
    /**
     * @var string
     */
    protected $txRkwregistrationRegisterRemoteIp = '';

    
    /**
     * @var int
     */
    protected $txRkwregistrationLoginErrorCount = 0;

    
    /**
     * @var string
     */
    protected $txRkwregistrationLanguageKey = '';

    
    /**
     * @var string
     */
    protected $txRkwregistrationFacebookUrl = '';

    
    /**
     * @var string
     */
    protected $txRkwregistrationTwitterUrl = '';

    
    /**
     * @var string
     */
    protected $txRkwregistrationXingUrl = '';

    
    /**
     * @var int
     */
    protected $txRkwregistrationTwitterId = 0;

    
    /**
     * @var string
     */
    protected $txRkwregistrationFacebookId = '';

    
    /**
     * @deprecated Will be removed soon. Use GuestUser Model instead
     * @var boolean
     */
    protected $txRkwregistrationIsAnonymous = false;
    
    
    /**
     * @var bool
     */
    protected $txRkwregistrationDataProtectionStatus = 0;


    /**
     * initialize objectStorage
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->usergroup = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }


    /**
     * Sets the crdate value
     *
     * @param int $crdate
     * @api
     */
    public function setCrdate(int $crdate): void
    {
        $this->crdate = $crdate;
    }


    /**
     * Returns the crdate value
     *
     * @return int
     * @api
     */
    public function getCrdate()
    {
        return $this->crdate;
    }


    /**
     * Sets the tstamp value
     *
     * @param int $tstamp
     * @api
     */
    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
    }


    /**
     * Returns the tstamp value
     *
     * @return int
     * @api
     */
    public function getTstamp(): int
    {
        return $this->tstamp;
    }


    /**
     * Sets the starttime value
     *
     * @param int $starttime
     * @api
     */
    public function setStarttime(int $starttime): void
    {
        $this->starttime = $starttime;
    }


    /**
     * Returns the starttime value
     *
     * @return int
     * @api
     */
    public function getStarttime(): int
    {
        return $this->starttime;
    }


    /**
     * Sets the endtime value
     *
     * @param int $endtime
     * @api
     */
    public function setEndtime(int $endtime): void
    {
        $this->endtime = $endtime;
    }


    /**
     * Returns the endtime value
     *
     * @return int
     * @api
     */
    public function getEndtime(): int
    {
        return $this->endtime;
    }


    /**
     * Sets the disable value
     *
     * @param bool $disable
     * @return void
     *
     */
    public function setDisable(bool $disable)
    {
        $this->disable = $disable;
    }


    /**
     * Returns the disable value
     *
     * @return bool
     */
    public function getDisable(): bool
    {
        return $this->disable;
    }


    /**
     * Sets the deleted value
     *
     * @param bool $deleted
     * @return void
     *
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }


    /**
     * Returns the deleted value
     *
     * @return bool
     *
     */
    public function getDeleted(): bool
    {
        return $this->deleted;
    }
    
    
    /**
     * Sets the username value
     * ! Important: We need to lowercase it !
     *
     * @param string $username
     * @return void
     * @api
     */
    public function setUsername($username): void
    {
        $this->username = strtolower($username);
    }


    /**
     * Sets the email value
     * ! Important: We need to lowercase it !
     *
     * @param string $email
     * @return void
     * @api
     */
    public function setEmail($email): void
    {
        $this->email = strtolower($email);
    }

    /**
     * Returns the txExtbaseType
     *
     * @return string
     * @api
     */
    public function getTxExtbaseType()
    {
        return $this->txExtbaseType;
    }

    
    /**
     * Sets the txExtbaseType
     *
     * @param string $txExtbaseType
     * @api
     */
    public function setTxExtbaseType($txExtbaseType)
    {
        $this->txExtbaseType = $txExtbaseType;
    }
    

    /**
     * Returns the title
     *
     * @return string
     * @api
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    
    /**
     * Sets the title
     *
     * @param string $title
     * @api
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    
    /**
     * Returns the txRkwregistrationTitle
     *
     * @return \RKW\RkwRegistration\Domain\Model\Title $txRkwregistrationTitle
     */
    public function getTxRkwregistrationTitle(): Title
    {
        if ($this->txRkwregistrationTitle === null) {
            $txRkwregistrationTitle = new Title();
            $txRkwregistrationTitle->setName($this->getTitle());

            return $txRkwregistrationTitle;
        }

        return $this->txRkwregistrationTitle;
    }

    
    /**
     * Sets the txRkwregistrationTitle
     *
     * Hint: default "null" is needed to make value in forms optional
     *
     * @param \RKW\RkwRegistration\Domain\Model\Title|null $txRkwregistrationTitle
     * @return void
     */
    public function setTxRkwregistrationTitle(Title $txRkwregistrationTitle = null): void
    {
        if (
            ($txRkwregistrationTitle)
            && ($txRkwregistrationTitle->getName() !== '')
        ){
            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            /** @var \RKW\RkwRegistration\Domain\Repository\TitleRepository $titleRepository */
            $titleRepository = $objectManager->get(TitleRepository::class);

            if ($existingTitle = $titleRepository->findOneByName($txRkwregistrationTitle->getName())) {
                $this->txRkwregistrationTitle = $existingTitle;
            } else {
                $this->setTitle($txRkwregistrationTitle->getName());
            }
        }
    }
    

    /**
     * Returns the title as text
     *
     * @param bool $titleAfter
     * @return string
     */
    public function getTitleText(bool $titleAfter = false): string
    {

        if ($this->getTxRkwregistrationTitle()) {

            if ($this->getTxRkwregistrationTitle()->getIsTitleAfter() == $titleAfter) {
                return $this->getTxRkwregistrationTitle()->getName();
            }
        }

        if (!is_numeric($this->getTitle())) {
            return $this->getTitle();
        }

        return '';
    }


    /**
     * Returns the full salutation including gender, title and name
     *
     * @param bool $checkIncludedInSalutation
     * @return string
     */
    public function getCompleteSalutationText(bool $checkIncludedInSalutation = false): string
    {
        $fullSalutation = $this->getFirstName() . ' ' . $this->getLastName();
        $title = $this->getTxRkwregistrationTitle();

        if ($title && $title->getName()) {

            $titleName = ($this->getTxRkwregistrationGender() === 1 && $title->getNameFemale()) ? $title->getNameFemale() : $title->getName();
            if ($checkIncludedInSalutation) {
                if ($title->getIsIncludedInSalutation()) {
                    $fullSalutation = ($title->getIsTitleAfter()) ? $fullSalutation . ', ' . $titleName : $titleName . ' ' . $fullSalutation;
                }
            } else {
                $fullSalutation = ($title->getIsTitleAfter()) ? $fullSalutation . ', ' . $titleName : $titleName . ' ' . $fullSalutation;
            }
        }

        if ($this->getGenderText()) {
            $fullSalutation = $this->getGenderText() . ' ' . $fullSalutation;
        }

        return $fullSalutation;
    }

    /**
     * Sets the firstName
     *
     * @param string $firstName
     * @api
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;

        if ($this->getLastName()) {
            $this->name = $this->getFirstName() . ' ' . $this->getLastName();
        } else {
            $this->name = $this->getFirstName();
        }
    }


    /**
     * Sets the lastName
     *
     * @param string $lastName
     * @api
     */
    public function setLastName($lastName): void
    {

        $this->lastName = $lastName;

        if ($this->getFirstName()) {
            $this->name = $this->getFirstName() . ' ' . $this->getLastName();
        } else {
            $this->name = $this->getLastName();
        }
    }


    /**
     * Sets the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroup
     * @return void
     * @api
     */
    public function setUsergroup(ObjectStorage $usergroup): void
    {
        $this->usergroup = $usergroup;
    }


    /**
     * Adds a usergroup to the frontend user
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup
     * @return void
     * @api
     */
    public function addUsergroup(CoreFrontendUserGroup $usergroup): void
    {
        $this->usergroup->attach($usergroup);
    }

    /**
     * Removes a usergroup from the frontend user
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup
     * @return void
     * @api
     */
    public function removeUsergroup(CoreFrontendUserGroup $usergroup): void
    {
        $this->usergroup->detach($usergroup);
    }


    /**
     * Returns the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the usergroup
     * @api
     */
    public function getUsergroup(): ObjectStorage
    {
        return $this->usergroup;
    }
    

    /**
     * Sets the mobile value
     *
     * @param string $mobile
     * @return void
     * @api
     */
    public function setTxRkwregistrationMobile(string $mobile): void
    {
        $this->txRkwregistrationMobile = $mobile;
    }


    /**
     * Returns the mobile value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationMobile(): string
    {
        return $this->txRkwregistrationMobile;
    }


    /**
     * Sets the gender value
     *
     * @param int $gender
     * @return void
     * @api
     */
    public function setTxRkwregistrationGender(int $gender): void
    {
        $this->txRkwregistrationGender = $gender;
    }


    /**
     * Returns the gender value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationGender(): int
    {
        return $this->txRkwregistrationGender;
    }
    

    /**
     * Returns the gender as string
     *
     * @return string
     */
    public function getGenderText(): string
    {
        if ($this->getTxRkwregistrationGender() < 99) {

            return FrontendLocalizationUtility::translate(
                'tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_gender.I.' . $this->getTxRkwregistrationGender(),
                'rkw_registration',
                [],
                $this->getTxRkwregistrationLanguageKey()
            );

        }

        return '';
    }
    

    /**
     * Sets the registerRemoteIp value
     *
     * @param string $remoteIp
     * @return void
     *
     */
    public function setTxRkwregistrationRegisterRemoteIp(string $remoteIp): void
    {
        $this->txRkwregistrationRegisterRemoteIp = $remoteIp;
    }

    
    /**
     * Returns the registerRemoteIp value
     *
     * @return string
     *
     */
    public function getTxRkwregistrationRegisterRemoteIp(): string
    {
        return $this->txRkwregistrationRegisterRemoteIp;
    }
    

    /**
     * Sets the loginErrorCount value
     *
     * @param int $count
     * @return void
     *
     */
    public function setTxRkwregistrationLoginErrorCount(int $count): void
    {
        $this->txRkwregistrationLoginErrorCount = $count;
    }

    
    /**
     * Increments the loginErrorCount value
     *
     * @return void
     *
     */
    public function incrementTxRkwregistrationLoginErrorCount(): void
    {
        $this->txRkwregistrationLoginErrorCount++;
    }
    
    
    /**
     * Returns the loginErrorCount value
     *
     * @return int
     *
     */
    public function getTxRkwregistrationLoginErrorCount(): int
    {
        return $this->txRkwregistrationLoginErrorCount;
    }
    
    
    /**
     * Sets the txRkwregistrationLanguageKey value
     *
     * @param string $languageKey
     * @return void
     *
     */
    public function setTxRkwregistrationLanguageKey(string $languageKey): void
    {
        $this->txRkwregistrationLanguageKey = $languageKey;
    }


    /**
     * Returns the txRkwregistrationLanguageKey value
     *
     * @return string
     *
     */
    public function getTxRkwregistrationLanguageKey(): string
    {
        return $this->txRkwregistrationLanguageKey;
    }
    
    
    /**
     * Sets the facebookUrl value
     *
     * @param string $facebookUrl
     * @return void
     * @api
     */
    public function setTxRkwregistrationFacebookUrl(string $facebookUrl): void
    {
        $this->txRkwregistrationFacebookUrl = $facebookUrl;
    }
    
    
    /**
     * Returns the facebookUrl value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationFacebookUrl(): string
    {
        return $this->txRkwregistrationFacebookUrl;
    }

    
    /**
     * Sets the twitterUrl value
     *
     * @param string $twitter
     * @return void
     * @api
     */
    public function setTxRkwregistrationTwitterUrl(string $twitter): void
    {
        $this->txRkwregistrationTwitterUrl = $twitter;
    }

    
    /**
     * Returns the twitterUrl value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationTwitterUrl(): string
    {
        return $this->txRkwregistrationTwitterUrl;
    }
    
    
    /**
     * Sets the xingUrl value
     *
     * @param string $twitter
     * @return void
     * @api
     */
    public function setTxRkwregistrationXingUrl(string $twitter): void
    {
        $this->txRkwregistrationXingUrl = $twitter;
    }
    
    
    /**
     * Returns the xingUrl value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationXingUrl(): string
    {
        return $this->txRkwregistrationXingUrl;
    }
    
    
    /**
     * Sets the twitterId value
     *
     * @param int $twitter
     * @return void
     * @api
     */
    public function setTxRkwregistrationTwitterId(int $twitter): void
    {
        $this->txRkwregistrationTwitterId = $twitter;
    }

    
    /**
     * Returns the twitterId value
     *
     * @return int
     * @api
     */
    public function getTxRkwregistrationTwitterId(): int
    {
        return $this->txRkwregistrationTwitterId;
    }


    /**
     * Sets the facebookId value
     *
     * @param string $facebookId
     * @return void
     * @api
     */
    public function setTxRkwregistrationFacebookId(string $facebookId)
    {
        $this->txRkwregistrationFacebookId = $facebookId;
    }

    
    /**
     * Returns the facebookId value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationFacebookId(): string
    {
        return $this->txRkwregistrationFacebookId;
    }

    
    /**
     * Returns the txRkwregistrationIsAnonymous
     **
     * @return boolean $txRkwregistrationIsAnonymous
     * @deprecated Will be removed soon. Use GuestUser Model instead
     */
    public function getTxRkwregistrationIsAnonymous(): bool
    {
        GeneralUtility::logDeprecatedFunction();

        return $this->txRkwregistrationIsAnonymous;
    }
    

    /**
     * Sets the txRkwregistrationIsAnonymous
     *
     * @param boolean $txRkwregistrationIsAnonymous
     * @return void
     * @deprecated Will be removed soon. Use GuestUser Model instead
     */
    public function setTxRkwregistrationIsAnonymous(bool $txRkwregistrationIsAnonymous): void
    {
        GeneralUtility::logDeprecatedFunction();

        $this->txRkwregistrationIsAnonymous = $txRkwregistrationIsAnonymous;
    }
    
    
    /**
     * Sets the txRkwregistrationDataProtectionStatus value
     *
     * @param int $txRkwregistrationDataProtectionStatus
     * @return void
     *
     */
    public function setTxRkwregistrationDataProtectionStatus(int $txRkwregistrationDataProtectionStatus): void
    {
        $this->txRkwregistrationDataProtectionStatus = $txRkwregistrationDataProtectionStatus;
    }
    

    /**
     * Returns the txRkwregistrationDataProtectionStatus value
     * @return int
     */
    public function getTxRkwregistrationDataProtectionStatus(): int
    {
        return $this->txRkwregistrationDataProtectionStatus;
    }


}
