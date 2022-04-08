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
    protected $starttime;

    /**
     * @var integer
     */
    protected $endtime;

    /**
     * @var integer
     */
    protected $disable = 1;

    /**
     * @var integer
     */
    protected $deleted = 0;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\FrontendUserGroup>
     */
    protected $usergroup;

    /**
     * txRkwregistrationTitle
     *
     * @var \RKW\RkwRegistration\Domain\Model\Title|null
     */
    protected $txRkwregistrationTitle = null;

    /**
     * @var string
     */
    protected $txRkwregistrationMobile = '';

    /**
     * @var integer
     */
    protected $txRkwregistrationGender = 99;

    /**
     * @var integer
     */
    protected $txRkwregistrationDisabledByOptIn;

    /**
     * @var string
     */
    protected $txRkwregistrationRegisterRemoteIp = '';

    /**
     * @var integer
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
     * @var integer
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
     * @var string
     */
    protected $txRkwregistrationCrossDomainToken = '';

    /**
     * @var string
     */
    protected $txRkwregistrationCrossDomainTokenTstamp;

    /**
     * @var bool
     */
    protected $txRkwregistrationDataProtectionStatus = 0;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\Privacy>
     */
    //protected $txRkwregistrationPrivacy;

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
     * Sets the username value
     * ! Important: We need to lowercase it !
     *
     * @param string $username
     * @return void
     * @api
     */
    public function setUsername($username)
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
    public function setEmail($email)
    {
        $this->email = strtolower($email);
    }

    /**
     * Returns the title
     *
     * @return string
     * @api
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @api
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * Returns the txRkwregistrationTitle
     *
     * @return \RKW\RkwRegistration\Domain\Model\Title $txRkwregistrationTitle
     */
    public function getTxRkwregistrationTitle()
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
     * @param \RKW\RkwRegistration\Domain\Model\Title $txRkwregistrationTitle
     * @return void
     */
    public function setTxRkwregistrationTitle(\RKW\RkwRegistration\Domain\Model\Title $txRkwregistrationTitle = null)
    {
        if (
            ($txRkwregistrationTitle)
            && ($txRkwregistrationTitle->getName() !== '')
        ){
            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
            /** @var \RKW\RkwRegistration\Domain\Repository\TitleRepository $titleRepository */
            $titleRepository = $objectManager->get(\RKW\RkwRegistration\Domain\Repository\TitleRepository::class);

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
    public function getTitleText($titleAfter = false)
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
     * @return string
     */
    public function getCompleteSalutationText($checkIncludedInSalutation = false)
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
    public function setFirstName($firstName)
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
    public function setLastName($lastName)
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
    public function setUsergroup(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroup)
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
    public function addUsergroup(\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup)
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
    public function removeUsergroup(\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroup)
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
    public function getUsergroup()
    {
        return $this->usergroup;
    }


    /**
     * Sets the crdate value
     *
     * @param integer $crdate
     * @api
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }


    /**
     * Returns the crdate value
     *
     * @return integer
     * @api
     */
    public function getCrdate()
    {

        return $this->crdate;
    }

    /**
     * Sets the tstamp value
     *
     * @param integer $tstamp
     * @api
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;
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
    }


    /**
     * Sets the starttime value
     *
     * @param integer $starttime
     * @api
     */
    public function setStarttime($starttime)
    {
        $this->starttime = $starttime;
    }


    /**
     * Returns the starttime value
     *
     * @return integer
     * @api
     */
    public function getStarttime()
    {
        return $this->starttime;
    }


    /**
     * Sets the endtime value
     *
     * @param integer $endtime
     * @api
     */
    public function setEndtime($endtime)
    {
        $this->endtime = $endtime;
    }


    /**
     * Returns the endtime value
     *
     * @return integer
     * @api
     */
    public function getEndtime()
    {
        return $this->endtime;
    }


    /**
     * Sets the mobile value
     *
     * @param integer $mobile
     * @return void
     * @api
     */
    public function setTxRkwregistrationMobile($mobile)
    {
        $this->txRkwregistrationMobile = $mobile;
    }


    /**
     * Returns the mobile value
     *
     * @return integer
     * @api
     */
    public function getTxRkwregistrationMobile()
    {
        return $this->txRkwregistrationMobile;
    }


    /**
     * Sets the gender value
     *
     * @param integer $gender
     * @return void
     * @api
     */
    public function setTxRkwregistrationGender($gender)
    {
        $this->txRkwregistrationGender = $gender;
    }


    /**
     * Returns the gender value
     *
     * @return integer
     * @api
     */
    public function getTxRkwregistrationGender()
    {
        return $this->txRkwregistrationGender;
    }

    /**
     * Returns the gender as string
     *
     * @return string
     */
    public function getGenderText()
    {
        if ($this->getTxRkwregistrationGender() < 99) {

            return \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
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
    public function setTxRkwregistrationRegisterRemoteIp($remoteIp)
    {
        $this->txRkwregistrationRegisterRemoteIp = $remoteIp;
    }

    /**
     * Returns the registerRemoteIp value
     *
     * @return string
     *
     */
    public function getTxRkwregistrationRegisterRemoteIp()
    {
        return $this->txRkwregistrationRegisterRemoteIp;
    }

    /**
     * Sets the loginErrorCount value
     *
     * @param integer $count
     * @return void
     *
     */
    public function setTxRkwregistrationLoginErrorCount($count)
    {
        $this->txRkwregistrationLoginErrorCount = $count;
    }


    /**
     * Increments the loginErrorCount value
     *
     * @return void
     *
     */
    public function incrementTxRkwregistrationLoginErrorCount()
    {
        $this->txRkwregistrationLoginErrorCount++;
    }


    /**
     * Returns the loginErrorCount value
     *
     * @return integer
     *
     */
    public function getTxRkwregistrationLoginErrorCount()
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
    public function setTxRkwregistrationLanguageKey($languageKey)
    {
        $this->txRkwregistrationLanguageKey = $languageKey;
    }


    /**
     * Returns the txRkwregistrationLanguageKey value
     *
     * @return string
     *
     */
    public function getTxRkwregistrationLanguageKey()
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
    public function setTxRkwregistrationFacebookUrl($facebookUrl)
    {
        $this->txRkwregistrationFacebookUrl = $facebookUrl;
    }


    /**
     * Returns the facebookUrl value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationFacebookUrl()
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
    public function setTxRkwregistrationTwitterUrl($twitter)
    {
        $this->txRkwregistrationTwitterUrl = $twitter;
    }

    /**
     * Returns the twitterUrl value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationTwitterUrl()
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
    public function setTxRkwregistrationXingUrl($twitter)
    {
        $this->txRkwregistrationXingUrl = $twitter;
    }


    /**
     * Returns the xingUrl value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationXingUrl()
    {
        return $this->txRkwregistrationXingUrl;
    }


    /**
     * Sets the disable value
     *
     * @param integer $disable
     * @return void
     *
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;
    }


    /**
     * Returns the disable value
     *
     * @return integer
     */
    public function getDisable()
    {
        return $this->disable;
    }


    /**
     * Sets the deleted value
     *
     * @param integer $deleted
     * @return void
     *
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }


    /**
     * Returns the deleted value
     *
     * @return integer
     *
     */
    public function getDeleted()
    {
        return $this->deleted;
    }


    /**
     * Sets the twitterId value
     *
     * @param string $twitter
     * @return void
     * @api
     */
    public function setTxRkwregistrationTwitterId($twitter)
    {
        $this->txRkwregistrationTwitterId = $twitter;
    }

    /**
     * Returns the twitterId value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationTwitterId()
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
    public function setTxRkwregistrationFacebookId($facebookId)
    {
        $this->txRkwregistrationFacebookId = $facebookId;
    }

    /**
     * Returns the facebookId value
     *
     * @return string
     * @api
     */
    public function getTxRkwregistrationFacebookId()
    {
        return $this->txRkwregistrationFacebookId;
    }

    /**
     * Returns the txRkwregistrationIsAnonymous
     *
     * @deprecated Will be removed soon. Use GuestUser Model instead
     *
     * @return boolean $txRkwregistrationIsAnonymous
     */
    public function getTxRkwregistrationIsAnonymous()
    {
        return $this->txRkwregistrationIsAnonymous;
    }

    /**
     * Sets the txRkwregistrationIsAnonymous
     *
     * @deprecated Will be removed soon. Use GuestUser Model instead
     *
     * @param boolean $txRkwregistrationIsAnonymous
     * @return void
     */
    public function setTxRkwregistrationIsAnonymous($txRkwregistrationIsAnonymous)
    {
        $this->txRkwregistrationIsAnonymous = $txRkwregistrationIsAnonymous;
    }


    /**
     * Returns the txRkwregistrationCrossDomainToken
     *
     * @return string $txRkwregistrationCrossDomainToken
     */
    public function getTxRkwregistrationCrossDomainToken()
    {
        return $this->txRkwregistrationCrossDomainToken;
    }

    /**
     * Sets the txRkwregistrationCrossDomainToken
     *
     * @param string $txRkwregistrationCrossDomainToken
     * @return void
     */
    public function setTxRkwregistrationCrossDomainToken($txRkwregistrationCrossDomainToken)
    {
        $this->txRkwregistrationCrossDomainToken = $txRkwregistrationCrossDomainToken;
    }


    /**
     * Returns the txRkwregistrationCrossDomainTokenTstamp
     *
     * @return string $txRkwregistrationCrossDomainTokenTstamp
     */
    public function getTxRkwregistrationCrossDomainTokenTstamp()
    {
        return $this->txRkwregistrationCrossDomainTokenTstamp;
    }

    /**
     * Sets the txRkwregistrationCrossDomainTokenTstamp
     *
     * @param string $txRkwregistrationCrossDomainTokenTstamp
     * @return void
     */
    public function setTxRkwregistrationCrossDomainTokenTstamp($txRkwregistrationCrossDomainTokenTstamp)
    {
        $this->txRkwregistrationCrossDomainTokenTstamp = $txRkwregistrationCrossDomainTokenTstamp;
    }


    /**
     * Sets the txRkwregistrationDataProtectionStatus value
     *
     * @param integer $txRkwregistrationDataProtectionStatus
     * @return void
     *
     */
    public function setTxRkwregistrationDataProtectionStatus($txRkwregistrationDataProtectionStatus)
    {
        $this->txRkwregistrationDataProtectionStatus = $txRkwregistrationDataProtectionStatus;
    }


    /**
     * Returns the txRkwregistrationDataProtectionStatus value
     *
     * @return integer
     *
     */
    public function getTxRkwregistrationDataProtectionStatus()
    {
        return $this->txRkwregistrationDataProtectionStatus;
    }


}
