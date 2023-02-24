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
class FrontendUser extends \Madj2k\CoreExtended\Domain\Model\FrontendUser
{

    /**
     * !!!! THIS SHOULD NEVER BE PERSISTED !!!!
     *
     * @var string
     */
    protected string $_tempPlaintextPassword = '';


    /**
     * !!!! THIS SHOULD NEVER BE PERSISTED !!!!
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup
     */
    protected string $_tempFrontendUserGroup;


    /**
     * @var string
     * @validate \SJBR\SrFreecap\Validation\Validator\CaptchaValidator
     */
    protected string $captchaResponse = '';


    /**
     * @var string
     */
    protected string $txRkwregistrationMobile = '';


    /**
     * @var int
     */
    protected int $txRkwregistrationGender = 99;


    /**
     * @var string
     */
    protected string $txRkwregistrationRegisterRemoteIp = '';


    /**
     * @var int
     */
    protected int $txRkwregistrationLoginErrorCount = 0;


    /**
     * @var string
     */
    protected string $txRkwregistrationLanguageKey = '';


    /**
     * @var string
     */
    protected string $txRkwregistrationFacebookUrl = '';


    /**
     * @var string
     */
    protected string $txRkwregistrationTwitterUrl = '';


    /**
     * @var string
     */
    protected string $txRkwregistrationXingUrl = '';


    /**
     * @var int
     */
    protected int $txRkwregistrationDataProtectionStatus = 0;


    /**
     * @var bool
     */
    protected bool $txRkwregistrationConsentTerms = false;


    /**
     * @var bool
     */
    protected bool $txRkwregistrationConsentMarketing = false;


    /**
     * @var \RKW\RkwRegistration\Domain\Model\Title|null
     */
    protected ?Title $txRkwregistrationTitle = null;


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
     * Sets the captchaResponse
     *
     * @param string $captchaResponse
     * @return void
     */
    public function setCaptchaResponse(string $captchaResponse): void {
        $this->captchaResponse = $captchaResponse;
    }


    /**
     * Getter for captchaResponse
     *
     * @return string
     */
    public function getCaptchaResponse(): string {
        return $this->captchaResponse;
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
