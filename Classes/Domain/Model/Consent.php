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
 * Consent
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Consent extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * frontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public $frontendUser;


    /**
     * optIn
     *
     * @var \RKW\RkwRegistration\Domain\Model\OptIn
     */
    public $optIn;


    /**
     * parent
     *
     * @var \RKW\RkwRegistration\Domain\Model\Consent
     */
    public $parent;


    /**
     * foreignTable
     *
     * @var string
     */
    public $foreignTable = '';


    /**
     * foreignUid
     *
     * @var string
     */
    public $foreignUid = '';


    /**
     * ipAddress
     *
     * @var string
     */
    public $ipAddress = '';


    /**
     * userAgent
     *
     * @var string
     */
    public $userAgent = '';


    /**
     * extensionName
     *
     * @var string
     */
    public $extensionName = '';


    /**
     * pluginName
     *
     * @var string
     */
    public $pluginName = '';


    /**
     * controllerName
     *
     * @var string
     */
    public $controllerName = '';


    /**
     * actionName
     *
     * @var string
     */
    public $actionName = '';


    /**
     * comment
     *
     * @var string
     */
    public $comment = '';


    /**
     * serverHost
     *
     * @var string
     */
    public $serverHost = '';


    /**
     * serverUri
     *
     * @var string
     */
    public $serverUri = '';


    /**
     * serverRefererUrl
     *
     * @var string
     */
    public $serverRefererUrl = '';


    /**
     * consentPrivacy
     *
     * @var bool
     */
    public $consentPrivacy = false;


    /**
     * consentTerms
     *
     * @var bool
     */
    public $consentTerms = false;


    /**
     * consentMarketing
     *
     * @var bool
     */
    public $consentMarketing = false;






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
     * Returns the frontendUser
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     */
    public function getFrontendUser() :? FrontendUser
    {
        return $this->frontendUser;
    }


    /**
     * Sets the optIn
     *
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @return void
     */
    public function setOptIn(OptIn $optIn): void
    {
        $this->optIn = $optIn;
    }


    /**
     * Unsets the optIn
     *
     * @return void
     */
    public function unsetOptIn(): void
    {
        $this->optIn = null;
    }


    /**
     * Returns the optIn
     *
     * @return \RKW\RkwRegistration\Domain\Model\OptIn
     */
    public function getOptIn() :? OptIn
    {
        return $this->optIn;
    }


    /**
     * Sets the parent
     *
     * @param \RKW\RkwRegistration\Domain\Model\Consent $parent
     * @return void
     */
    public function setParent(Consent $parent): void
    {
        $this->parent = $parent;
    }


    /**
     * Returns the parent
     *
     * @return \RKW\RkwRegistration\Domain\Model\Consent $parent
     */
    public function getParent() :? Consent
    {
        return $this->parent;
    }

    /**
     * Sets the foreignTable value
     *
     * @param string $foreignTable
     * @return void
     */
    public function setForeignTable(string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
    }


    /**
     * Returns the foreignTable value
     *
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }


    /**
     * Sets the foreignUid value
     *
     * @param string $foreignUid
     * @return void
     */
    public function setForeignUid(string $foreignUid): void
    {
        $this->foreignUid = $foreignUid;
    }


    /**
     * Returns the foreignUid value
     *
     * @return string
     */
    public function getForeignUid(): string
    {
        return $this->foreignUid;
    }


    /**
     * Sets the ipAddress value
     *
     * @param string $ipAddress
     * @return void
     */
    public function setIpAddress(string $ipAddress):void
    {
        $this->ipAddress = $ipAddress;
    }


    /**
     * Returns the ipAddress value
     *
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }


    /**
     * Sets the userAgent value
     *
     * @param string $userAgent
     * @return void
     */
    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }


    /**
     * Returns the userAgent value
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }


    /**
     * Sets the extensionName value
     *
     * @param string $extensionName
     * @return void
     */
    public function setExtensionName(string $extensionName): void
    {
        $this->extensionName = $extensionName;
    }


    /**
     * Returns the extensionName value
     *
     * @return string
     */
    public function getExtensionName(): string
    {
        return $this->extensionName;
    }


    /**
     * Sets the pluginName value
     *
     * @param string $pluginName
     * @return void
     */
    public function setPluginName(string $pluginName): void
    {
        $this->pluginName = $pluginName;
    }


    /**
     * Returns the pluginName value
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return $this->pluginName;
    }


    /**
     * Sets the controllerName value
     *
     * @param string $controllerName
     * @return void
     */
    public function setControllerName(string $controllerName): void
    {
        $this->controllerName = $controllerName;
    }


    /**
     * Returns the controllerName value
     *
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }


    /**
     * Sets the actionName value
     *
     * @param string $actionName
     * @return void
     */
    public function setActionName(string $actionName): void
    {
        $this->actionName = $actionName;
    }


    /**
     * Returns the actionName value
     *
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }


    /**
     * Sets the comment value
     *
     * @param string $comment
     * @return void
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }


    /**
     * Returns the comment value
     *
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }


    /**
     * Sets the serverHost value
     *
     * @param string $serverHost
     * @return void
     */
    public function setServerHost(string $serverHost): void
    {
        $this->serverHost = $serverHost;
    }


    /**
     * Returns the serverHost value
     *
     * @return string
     */
    public function getServerHost(): string
    {
        return $this->serverHost;
    }


    /**
     * Sets the serverUri value
     *
     * @param string $serverUri
     * @return void
     */
    public function setServerUri(string $serverUri): void
    {
        $this->serverUri = $serverUri;
    }


    /**
     * Returns the serverUri value
     *
     * @return string
     */
    public function getServerUri(): string
    {
        return $this->serverUri;
    }


    /**
     * Sets the serverRefererUrl value
     *
     * @param string $serverRefererUrl
     * @return void
     */
    public function setServerRefererUrl(string $serverRefererUrl): void
    {
        $this->serverRefererUrl = $serverRefererUrl;
    }


    /**
     * Returns the serverRefererUrl value
     *
     * @return string
     */
    public function getServerRefererUrl(): string
    {
        return $this->serverRefererUrl;
    }



    /**
     * Sets the consentPrivacy value
     *
     * @param bool $consentPrivacy
     * @return void
     */
    public function setConsentPrivacy(bool $consentPrivacy): void
    {
        $this->consentPrivacy = $consentPrivacy;
    }


    /**
     * Returns the consentPrivacy value
     *
     * @return bool
     */
    public function getConsentPrivacy(): bool
    {
        return $this->consentPrivacy;
    }

    /**
     * Sets the consentTerms value
     *
     * @param bool $consentTerms
     * @return void
     */
    public function setConsentTerms(bool $consentTerms): void
    {
        $this->consentTerms = $consentTerms;
    }

    /**
     * Returns the consentTerms value
     *
     * @return bool
     */
    public function getConsentTerms(): bool
    {
        return $this->consentTerms;
    }


    /**
     * Sets the consentMarketing value
     *
     * @param bool $consentMarketing
     * @return void
     */
    public function setConsentMarketing(bool $consentMarketing): void
    {
        $this->consentMarketing = $consentMarketing;
    }


    /**
     * Returns the consentMarketing value
     *
     * @return bool
     */
    public function getConsentMarketing(): bool
    {
        return $this->consentMarketing;
    }

}
