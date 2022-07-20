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
 * Privacy
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Privacy extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * frontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public $frontendUser;

    /**
     * registrationUserSha1
     *
     * @var string
     */
    public $registrationUserSha1;

    /**
     * parent
     *
     * @var \RKW\RkwRegistration\Domain\Model\Privacy
     */
    public $parent;

    /**
     * foreignTable
     *
     * @var string
     */
    public $foreignTable;

    /**
     * foreignUid
     *
     * @var integer
     */
    public $foreignUid;

    /**
     * ipAddress
     *
     * @var string
     */
    public $ipAddress;

    /**
     * userAgent
     *
     * @var string
     */
    public $userAgent;

    /**
     * extensionName
     *
     * @var string
     */
    public $extensionName;

    /**
     * pluginName
     *
     * @var string
     */
    public $pluginName;

    /**
     * controllerName
     *
     * @var string
     */
    public $controllerName;

    /**
     * actionName
     *
     * @var string
     */
    public $actionName;

    /**
     * comment
     *
     * @var string
     */
    public $comment;

    /**
     * serverHost
     *
     * @var string
     */
    public $serverHost;

    /**
     * serverUri
     *
     * @var string
     */
    public $serverUri;

    /**
     * serverRefererUrl
     *
     * @var string
     */
    public $serverRefererUrl;

    /**
     * Sets the frontendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     */
    public function setFrontendUser($frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * Returns the frontendUser
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }

    /**
     * Sets the registrationUserSha1
     *
     * @param string $registrationUserSha1
     * @return void
     */
    public function setRegistrationUserSha1($registrationUserSha1)
    {
        $this->registrationUserSha1 = $registrationUserSha1;
    }

    /**
     * Returns the registrationUserSha1
     *
     * @return string
     */
    public function getRegistrationUserSha1()
    {
        return $this->registrationUserSha1;
    }

    /**
     * Sets the parent
     *
     * @param \RKW\RkwRegistration\Domain\Model\Privacy $parent
     * @return void
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Returns the parent
     *
     * @return \RKW\RkwRegistration\Domain\Model\Privacy $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the foreignTable value
     *
     * @param string $foreignTable
     * @return void
     */
    public function setForeignTable($foreignTable)
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * Returns the foreignTable value
     *
     * @return string
     */
    public function getForeignTable()
    {
        return $this->foreignTable;
        //===
    }

    /**
     * Sets the foreignUid value
     * ! set an object to save it's UID !
     *
     * @param int $foreignUid
     * @return void
     */
    public function setForeignUid($foreignUid)
    {
        $this->foreignUid = $foreignUid;
    }

    /**
     * Returns the foreignUid value
     *
     * @return integer
     */
    public function getForeignUid()
    {
        return $this->foreignUid;
        //===
    }

    /**
     * Sets the ipAddress value
     *
     * @param string $ipAddress
     * @return void
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * Returns the ipAddress value
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
        //===
    }

    /**
     * Sets the userAgent value
     *
     * @param string $userAgent
     * @return void
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Returns the userAgent value
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
        //===
    }

    /**
     * Sets the extensionName value
     *
     * @param string $extensionName
     * @return void
     */
    public function setExtensionName($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * Returns the extensionName value
     *
     * @return string
     */
    public function getExtensionName()
    {
        return $this->extensionName;
        //===
    }

    /**
     * Sets the pluginName value
     *
     * @param string $pluginName
     * @return void
     */
    public function setPluginName($pluginName)
    {
        $this->pluginName = $pluginName;
    }

    /**
     * Returns the pluginName value
     *
     * @return string
     */
    public function getPluginName()
    {
        return $this->pluginName;
        //===
    }

    /**
     * Sets the controllerName value
     *
     * @param string $controllerName
     * @return void
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
    }

    /**
     * Returns the controllerName value
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
        //===
    }

    /**
     * Sets the actionName value
     *
     * @param string $actionName
     * @return void
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * Returns the actionName value
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
        //===
    }

    /**
     * Sets the comment value
     *
     * @param string $comment
     * @return void
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Returns the comment value
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
        //===
    }

    /**
     * Sets the serverHost value
     *
     * @param string $serverHost
     * @return void
     */
    public function setServerHost($serverHost)
    {
        $this->serverHost = $serverHost;
    }

    /**
     * Returns the serverHost value
     *
     * @return string
     */
    public function getServerHost()
    {
        return $this->serverHost;
        //===
    }

    /**
     * Sets the serverUri value
     *
     * @param string $serverUri
     * @return void
     */
    public function setServerUri($serverUri)
    {
        $this->serverUri = $serverUri;
    }

    /**
     * Returns the serverUri value
     *
     * @return string
     */
    public function getServerUri()
    {
        return $this->serverUri;
        //===
    }

    /**
     * Sets the serverRefererUrl value
     *
     * @param string $serverRefererUrl
     * @return void
     */
    public function setServerRefererUrl($serverRefererUrl)
    {
        $this->serverRefererUrl = $serverRefererUrl;
    }

    /**
     * Returns the serverRefererUrl value
     *
     * @return string
     */
    public function getServerRefererUrl()
    {
        return $this->serverRefererUrl;
        //===
    }


    /**
     * setPrivacyData
     * Use this function to set data
     * ! The $dataObject is the element for what the privacy dataset will be created for (e.g. an order, or a new alert) !
     * Hint for optIn (two privacy-entries will be created):
     * 1. The first privacy-dataset of the optIn is created by the registration automatically. If the $dataObject is of type
     *    RKW\RkwRegistration\Domain\Model\Registration it will be automatically identified and set below in $this->setDataObject
     * 2. After successful optIn this registration-object (RKW\RkwRegistration\Domain\Model\Registration) is to set again
     *    as 5th param in the current extension to create the relationship between the two created privacy-datasets
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $dataObject
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Request|\TYPO3\CMS\Extbase\Mvc\Request $request
     * @param string $comment
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @return void
     * @deprecated since 09/2018 Use RKW\RkwRegistration\Tools\Privacy instead
     */
    public function setPrivacyData(
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser = null,
        \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $dataObject = null,
        \TYPO3\CMS\Extbase\Mvc\Web\Request $request,
        $comment = '',
        \RKW\RkwRegistration\Domain\Model\Registration $registration = null
    )
    {
        trigger_error(__CLASS__ . ': Do not use this method any more. Use RKW\RkwRegistration\Tools\Privacy instead.', E_USER_DEPRECATED);

        // set frontendUser
        if ($frontendUser) {
            $this->setFrontendUser($frontendUser);
        }

        // set foreignTable + foreignUid
        if ($dataObject) {
            $this->setDataObject($dataObject);
        }

        // set ipAddress
        $remoteAddress = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ips = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ips[0]) {
                $remoteAddress = filter_var($ips[0], FILTER_VALIDATE_IP);
            }
        }
        $this->setIpAddress($remoteAddress);

        // set domain name
        $this->setServerHost(filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL));

        // set path of url
        $this->setServerUri(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL));

        // set referer url
        $this->setServerRefererUrl(filter_var($_SERVER["HTTP_REFERER"], FILTER_SANITIZE_URL));

        // set userAgent
        $this->setUserAgent(filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING));

        // set extension-, plugin-, controller- and action-name
        $this->setExtensionName(filter_var($request->getControllerExtensionName(), FILTER_SANITIZE_STRING));
        $this->setPluginName(filter_var($request->getPluginName(), FILTER_SANITIZE_STRING));
        $this->setControllerName(filter_var($request->getControllerName(), FILTER_SANITIZE_STRING));
        $this->setActionName(filter_var($request->getControllerActionName(), FILTER_SANITIZE_STRING));

        // set informed consent reason - optional freeText field
        $this->setComment(filter_var($comment, FILTER_SANITIZE_STRING));

        // set parent privacy entry, if registration is given (we have to create a parent relationship, if an optIn is used)
        if ($registration) {
            // get optIn privacy-entry
            /** @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository $privacyRepository */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $privacyRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\PrivacyRepository');
            $privacyParent = $privacyRepository->findByRegistration($registration);
            if ($privacyParent) {
                $this->setParent($privacyParent);
            }
        }
    }


    /**
     * setDataObject
     * Use this function to set the privacy object afterwards (needed in /Tools/Registration->register() e.g.)
     *
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|\TYPO3\CMS\Extbase\Persistence\ObjectStorage $dataObject
     * @return void
     * @deprecated since 09/2018 Use RKW\RkwRegistration\Tools\Privacy instead
     */
    public function setDataObject($dataObject)
    {
        trigger_error(__CLASS__ . ': Do not use this method any more. Use RKW\RkwRegistration\Tools\Privacy instead.', E_USER_DEPRECATED);

        $dataMapper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class);
        if ($dataObject instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
            $dataObject = $dataObject->current();
        }

        if ($dataObject instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {

            $this->setForeignTable(filter_var($dataMapper->getDataMap(get_class($dataObject))->getTableName(), FILTER_SANITIZE_STRING));
            $this->setForeignUid($dataObject);

            // additional: Set registration, if $dataObject is of type \RKW\RkwRegistration\Domain\Model\Registration
            // -> we need to set this to identify it on successful optIn (for creating a parent-relationship)
            if ($dataObject instanceof \RKW\RkwRegistration\Domain\Model\Registration) {
                $this->setRegistration($dataObject);
            }
        }

    }


    /**
     * setPrivacyDataBeforeOptIn
     * additional service function for more better data consistence (without optional fields)
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Request|\TYPO3\CMS\Extbase\Mvc\Request $request
     * @param string $comment
     * @return void
     * @deprecated since 09/2018 Use RKW\RkwRegistration\Tools\Privacy instead
     */
    public function setPrivacyDataBeforeOptIn(
        \TYPO3\CMS\Extbase\Mvc\Web\Request $request,
        $comment = ''
    )
    {
        trigger_error(__CLASS__ . ': Do not use this method any more. Use RKW\RkwRegistration\Tools\Privacy instead.', E_USER_DEPRECATED);

        $this->setPrivacyData(null, null, $request, $comment);
    }


    /**
     * setPrivacyDataCreateOptIn
     * ! normally automatically used by the RkwRegistration while creating optIn !
     * (if you are using RKW\\RkwRegistration\\Tools\\Registration->register)
     * -> So you have just to use ->setPrivacyDataBeforeOptIn and ->setPrivacyDataFinal (with registration-object) to
     * complete the procedure
     * additional service function for more better data consistence (without optional fields)
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $dataObject
     * @return void
     * @deprecated since 09/2018 Use RKW\RkwRegistration\Tools\Privacy instead
     */
    public function setPrivacyDataCreateOptIn(
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser,
        \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $dataObject
    )
    {
        trigger_error(__CLASS__ . ': Do not use this method any more. Use RKW\RkwRegistration\Tools\Privacy instead.', E_USER_DEPRECATED);

        $this->setFrontendUser($frontendUser);
        $this->setDataObject($dataObject);
    }


    /**
     * setPrivacyDataFinal
     * ! set the $registration-object if you want to complete an optIn !
     * additional service function for more better data consistence (without optional fields)
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $dataObject
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Request $request
     * @param string $comment
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @return void
     * @deprecated since 09/2018 Use RKW\RkwRegistration\Tools\Privacy instead
     */
    public function setPrivacyDataFinal(
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser,
        \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $dataObject,
        \TYPO3\CMS\Extbase\Mvc\Web\Request $request,
        $comment = '',
        \RKW\RkwRegistration\Domain\Model\Registration $registration = null
    )
    {
        trigger_error(__CLASS__ . ': Do not use this method any more. Use RKW\RkwRegistration\Tools\Privacy instead.', E_USER_DEPRECATED);

        $this->setPrivacyData($frontendUser, $dataObject, $request, $comment, $registration);
    }


    /**
     * setPrivacyDataWithoutDataObject
     * ! For general purpose like Wepstra, without specific $dataObject !
     * additional service function for more better data consistence (without optional fields)
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Request|\TYPO3\CMS\Extbase\Mvc\Request $request
     * @param string $comment
     * @return void
     * @deprecated since 09/2018 Use RKW\RkwRegistration\Tools\Privacy instead
     */
    public function setPrivacyDataWithoutDataObject(
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser,
        \TYPO3\CMS\Extbase\Mvc\Web\Request $request,
        $comment = ''
    )
    {
        trigger_error(__CLASS__ . ': Do not use this method any more. Use RKW\RkwRegistration\Tools\Privacy instead.', E_USER_DEPRECATED);

        $this->setPrivacyData($frontendUser, null, $request, $comment);
    }
}
