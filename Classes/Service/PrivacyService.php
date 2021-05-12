<?php

namespace RKW\RkwRegistration\Service;

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
 * Class PrivacyService
 *
 * @toDo: Services SHOULD NOT be singletons
 * @toDo: Services MUST be used as objects, they are never static
 * (https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/CodingGuidelines/CglPhp/PhpArchitecture/ModelingCrossCuttingConcerns/Services.html)
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PrivacyService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * setPrivacyData
     * Use this function to set basic data
     * The $dataObject is the element for what the privacy dataset will be created for (e.g. an order, or a new alert) !
     * Hint for optIn (two privacy-entries will be created):
     * 1. The first privacy-dataset of the optIn is created by the registration automatically. If the $dataObject is of type
     *    RKW\RkwRegistration\Domain\Model\Registration it will be automatically identified and set below in $this->setDataObject
     * 2. After successful optIn the 5th param is used to create the relationship between the two created privacy-datasets
     *
     * @param \RKW\RkwRegistration\Domain\Model\Privacy $privacy
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\Registration|\TYPO3\CMS\Extbase\DomainObject\AbstractEntity|\TYPO3\CMS\Extbase\Persistence\ObjectStorage $referenceObject
     * @param string $comment
     * @param bool $isOptInFinal
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    protected static function setPrivacyData(
        \RKW\RkwRegistration\Domain\Model\Privacy $privacy,
        \TYPO3\CMS\Extbase\Mvc\Request $request,
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser = null,
        $referenceObject = null,
        $comment = '',
        $isOptInFinal = false
    )
    {

        // set frontendUser
        if ($frontendUser) {
            $privacy->setFrontendUser($frontendUser);
        }

        // set reference object info
        if ($referenceObject) {

            if (
                ($isOptInFinal)
                && ($referenceObject instanceof \RKW\RkwRegistration\Domain\Model\Registration)
            ) {
                self::setReferenceObjectInfo($privacy, $referenceObject->getData());
            } else {
                self::setReferenceObjectInfo($privacy, $referenceObject);
            }
        }

        // set ipAddress
        $remoteAddress = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ips = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ips[0]) {
                $remoteAddress = filter_var($ips[0], FILTER_VALIDATE_IP);
            }
        }
        $privacy->setIpAddress($remoteAddress);

        // set domain name
        $privacy->setServerHost(filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL));

        // set path of url
        $privacy->setServerUri(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL));

        // set referer url
        $privacy->setServerRefererUrl(filter_var($_SERVER["HTTP_REFERER"], FILTER_SANITIZE_URL));

        // set userAgent
        $privacy->setUserAgent(filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING));

        // set extension-, plugin-, controller- and action-name
        $privacy->setExtensionName(filter_var($request->getControllerExtensionName(), FILTER_SANITIZE_STRING));
        $privacy->setPluginName(filter_var($request->getPluginName(), FILTER_SANITIZE_STRING));
        $privacy->setControllerName(filter_var($request->getControllerName(), FILTER_SANITIZE_STRING));
        $privacy->setActionName(filter_var($request->getControllerActionName(), FILTER_SANITIZE_STRING));

        // set informed consent reason - optional freeText field
        $privacy->setComment(filter_var($comment, FILTER_SANITIZE_STRING));

        // set parent privacy entry in final step on opt-in
        if (
            ($isOptInFinal)
            && ($referenceObject instanceof \RKW\RkwRegistration\Domain\Model\Registration)
        ) {

            // get optIn privacy-entry via registrationUserSha1, because uid may be already re-used and cleanup reference in parent here
            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

            /** @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository $privacyRepository */
            $privacyRepository = $objectManager->get(\RKW\RkwRegistration\Domain\Repository\PrivacyRepository::class);
            $privacyParent = $privacyRepository->findOneByRegistration($referenceObject);
            if ($privacyParent) {
                $privacy->setParent($privacyParent);
                $privacyParent->setRegistrationUserSha1('');
                $privacyRepository->update($privacyParent);
            }
        }
    }


    /**
     * setReferenceObjectInfo
     *
     * @param \RKW\RkwRegistration\Domain\Model\Privacy $privacy
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|\TYPO3\CMS\Extbase\Persistence\ObjectStorage $referenceObject
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    protected static function setReferenceObjectInfo(
        \RKW\RkwRegistration\Domain\Model\Privacy $privacy,
        $referenceObject
    )
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper $dataMapper */
        $dataMapper = $objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class);

        // if we get an object storage we take the first item to determine the table and leave the foreignUid
        if ($referenceObject instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
            $referenceObject = $referenceObject->current();

            if ($referenceObject instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
                $privacy->setForeignTable(filter_var($dataMapper->getDataMap(get_class($referenceObject))->getTableName(), FILTER_SANITIZE_STRING));
            }

            // else we determine the concrete foreignTable and foreignUid
        } else {
            if ($referenceObject instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {

                $privacy->setForeignTable(filter_var($dataMapper->getDataMap(get_class($referenceObject))->getTableName(), FILTER_SANITIZE_STRING));
                $privacy->setForeignUid($referenceObject->getUid());

                // additional: Set registration, if $referenceObject is of type \RKW\RkwRegistration\Domain\Model\Registration
                // -> we need to set this to identify it on successful optIn (for creating a parent-relationship)
                if ($referenceObject instanceof \RKW\RkwRegistration\Domain\Model\Registration) {
                    $privacy->setRegistrationUserSha1($referenceObject->getUserSha1());
                }
            }
        }
    }


    /**
     * addPrivacyDataForOptIn
     * normally automatically used by the RkwRegistration while creating optIn if you are using
     * RKW\\RkwRegistration\\Service\\RegistrationService->register You have just to use ->setPrivacyDataBeforeOptIn and
     * ->setPrivacyDataFinal (with registration-object) to complete the procedure
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @param string $comment
     * @return \RKW\RkwRegistration\Domain\Model\Privacy
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @api
     */
    public static function addPrivacyDataForOptIn(
        \TYPO3\CMS\Extbase\Mvc\Request $request,
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser,
        \RKW\RkwRegistration\Domain\Model\Registration $registration,
        $comment = ''
    )
    {

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $privacy */
        $privacy = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\Privacy');
        self::setPrivacyData($privacy, $request, $frontendUser, $registration, $comment);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository $privacyRepository */
        $privacyRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\PrivacyRepository');
        $privacyRepository->add($privacy);

        // @toDo: should normally be called in the context of RKW\RkwRegistration\Service\RegistrationService where already persistence happens
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        // $persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        // $persistenceManager->persistAll();

        return $privacy;
        //===

    }


    /**
     * addPrivacyDataForOptInFinal
     * set the $registration-object if you want to complete an optIn
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @param string $comment
     * @return \RKW\RkwRegistration\Domain\Model\Privacy
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @api
     */
    public static function addPrivacyDataForOptInFinal(
        \TYPO3\CMS\Extbase\Mvc\Request $request,
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser,
        \RKW\RkwRegistration\Domain\Model\Registration $registration = null,
        $comment = ''

    )
    {
        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $privacy */
        $privacy = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\Privacy');

        self::setPrivacyData($privacy, $request, $frontendUser, $registration, $comment, true);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository $privacyRepository */
        $privacyRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\PrivacyRepository');
        $privacyRepository->add($privacy);

        // @toDo: should normally be called in the context of RKW\RkwRegistration\Service\RegistrationService where already persistence happens
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        // $persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        // $persistenceManager->persistAll();

        return $privacy;
        //===
    }


    /**
     * addPrivacyData
     * set the $registration-object if you want to complete an optIn
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|\TYPO3\CMS\Extbase\Persistence\ObjectStorage $dataObject
     * @param string $comment
     * @return \RKW\RkwRegistration\Domain\Model\Privacy
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @api
     */
    public static function addPrivacyData(
        \TYPO3\CMS\Extbase\Mvc\Request $request,
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser,
        $dataObject,
        $comment = ''

    )
    {
        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $privacy */
        $privacy = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\Privacy');

        self::setPrivacyData($privacy, $request, $frontendUser, $dataObject, $comment);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository $privacyRepository */
        $privacyRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\PrivacyRepository');
        $privacyRepository->add($privacy);

        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        $persistenceManager->persistAll();

        return $privacy;
        //===
    }


}