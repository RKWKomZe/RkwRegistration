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
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class FrontendUser
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
{

    /**
     * !!!! THIS SHOULD NEVER BE PERSISTED !!!!
     *
     * @var string
     */
    protected $_tempPlaintextPassword = '';


    /**
     * !!!! THIS SHOULD NEVER BE PERSISTED !!!!
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup
     */
    protected $_tempFrontendUserGroup;


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
    protected $disable = false;


    /**
     * @var bool
     */
    protected $deleted = false;


    /**
     * @var string
     */
    protected $email = '';



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
    protected $txRkwregistrationDataProtectionStatus = 0;


    /**
     * @var bool
     */
    protected $txRkwregistrationConsentTerms = 0;


    /**
     * @var bool
     */
    protected $txRkwregistrationConsentMarketing = 0;



    /**
     * Gets the plaintext password
     * !!! SHOULD NEVER BE PERSISTED!!!
     *
     * @return string
     * @api
     */
    public function getTempPlaintextPassword(): string
    {
        return $this->_tempPlaintextPassword;
    }


    /**
     * Sets the plaintext password
     * !!! SHOULD NEVER BE PERSISTED!!!
     *
     * @param string $tempPlaintextPassword
     * @api
     */
    public function setTempPlaintextPassword(string $tempPlaintextPassword): void
    {
        $this->_tempPlaintextPassword = $tempPlaintextPassword;
    }


    /**
     * Gets the tempFrontendUserGroup
     * !!! SHOULD NEVER BE PERSISTED!!!
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUserGroup|null
     * @api
     */
    public function getTempFrontendUserGroup() :? \RKW\RkwRegistration\Domain\Model\FrontendUserGroup
    {
        return $this->_tempFrontendUserGroup;
    }


    /**
     * Sets the tempFrontendUserGroup
     * !!! SHOULD NEVER BE PERSISTED!!!
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $tempFrontendUserGroup
     * @api
     */
    public function setTempFrontendUserGroup(FrontendUserGroup $tempFrontendUserGroup): void
    {
        $this->_tempFrontendUserGroup = $tempFrontendUserGroup;
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
    public function getCrdate(): int
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
    public function setDisable(bool $disable): void
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


    /**
     * Sets the txRkwregistrationConsentTerms value
     *
     * @param bool $txRkwregistrationConsentTerms
     * @return void
     *
     */
    public function setTxRkwregistrationConsentTerms(bool $txRkwregistrationConsentTerms): void
    {
        $this->txRkwregistrationConsentTerms = $txRkwregistrationConsentTerms;
    }


    /**
     * Returns the txRkwregistrationConsentTerms value
     * @return bool
     */
    public function getTxRkwregistrationConsentTerms(): bool
    {
        return $this->txRkwregistrationConsentTerms;
    }


    /**
     * Sets the txRkwregistrationConsentMarketing value
     *
     * @param bool $txRkwregistrationConsentMarketing
     * @return void
     *
     */
    public function setTxRkwregistrationConsentMarketing(bool $txRkwregistrationConsentMarketing): void
    {
        $this->txRkwregistrationConsentMarketing = $txRkwregistrationConsentMarketing;
    }


    /**
     * Returns the txRkwregistrationConsentMarketing value
     * @return bool
     */
    public function getTxRkwregistrationConsentMarketing(): bool
    {
        return $this->txRkwregistrationConsentMarketing;
    }


    //=================================================================================
    // Special-methods that are NOT simply getter or setter below
    //=================================================================================

    /**
     * Increments the loginErrorCount value
     *
     * @return void
     */
    public function incrementTxRkwregistrationLoginErrorCount(): void
    {
        $this->txRkwregistrationLoginErrorCount++;
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

}
