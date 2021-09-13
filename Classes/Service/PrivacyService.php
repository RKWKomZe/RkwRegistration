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

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\Privacy;
use RKW\RkwRegistration\Domain\Model\Registration;
use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

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
     * @param Privacy                                   $privacy
     * @param Request                                   $request
     * @param FrontendUser                              $frontendUser
     * @param Registration|AbstractEntity|ObjectStorage $referenceObject
     * @param string                                    $comment
     * @param bool                                      $isOptInFinal
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    protected static function setPrivacyData(
        Privacy $privacy,
        Request $request,
        FrontendUser $frontendUser = null,
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
                && ($referenceObject instanceof Registration)
            ) {
                self::setReferenceObjectInfo($privacy, $referenceObject->getData());
            } else {
                self::setReferenceObjectInfo($privacy, $referenceObject);
            }
        }

        // set ipAddress
        $remoteAddress = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ips = GeneralUtility::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
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
            && ($referenceObject instanceof Registration)
        ) {

            // get optIn privacy-entry via registrationUserSha1, because uid may be already re-used and cleanup reference in parent here
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            /** @var PrivacyRepository $privacyRepository */
            $privacyRepository = $objectManager->get(PrivacyRepository::class);
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
     * @param Privacy                                                      $privacy
     * @param AbstractEntity|ObjectStorage $referenceObject
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    protected static function setReferenceObjectInfo(
        Privacy $privacy,
        $referenceObject
    )
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var DataMapper $dataMapper */
        $dataMapper = $objectManager->get(DataMapper::class);

        // if we get an object storage we take the first item to determine the table and leave the foreignUid
        if ($referenceObject instanceof ObjectStorage) {
            $referenceObject = $referenceObject->current();

            if ($referenceObject instanceof AbstractEntity) {
                $privacy->setForeignTable(filter_var($dataMapper->getDataMap(get_class($referenceObject))->getTableName(), FILTER_SANITIZE_STRING));
            }

            // else we determine the concrete foreignTable and foreignUid
        } else {
            if ($referenceObject instanceof AbstractEntity) {

                $privacy->setForeignTable(filter_var($dataMapper->getDataMap(get_class($referenceObject))->getTableName(), FILTER_SANITIZE_STRING));
                $privacy->setForeignUid($referenceObject->getUid());

                // additional: Set registration, if $referenceObject is of type \RKW\RkwRegistration\Domain\Model\Registration
                // -> we need to set this to identify it on successful optIn (for creating a parent-relationship)
                if ($referenceObject instanceof Registration) {
                    $privacy->setRegistrationUserSha1($referenceObject->getUserSha1());
                }
            }
        }
    }


    /**
     * addPrivacyDataForOptIn
     * normally automatically used by the RkwRegistration while creating optIn if you are using
     * \RKW\RkwRegistration\Service\OptInService->register You have just to use ->setPrivacyDataBeforeOptIn and
     * ->setPrivacyDataFinal (with registration-object) to complete the procedure
     *
     * @param Request $request
     * @param FrontendUser $frontendUser
     * @param Registration $registration
     * @param string $comment
     * @return Privacy
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @api
     */
    public static function addPrivacyDataForOptIn(
        Request $request,
        FrontendUser $frontendUser,
        Registration $registration,
        $comment = ''
    )
    {

        /** @var Privacy $privacy */
        $privacy = GeneralUtility::makeInstance(Privacy::class);
        self::setPrivacyData($privacy, $request, $frontendUser, $registration, $comment);

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var PrivacyRepository $privacyRepository */
        $privacyRepository = $objectManager->get(PrivacyRepository::class);
        $privacyRepository->add($privacy);

        // @toDo: should normally be called in the context of RKW\RkwRegistration\Service\RegistrationService where already persistence happens
        /** @var PersistenceManager $persistenceManager */
        // $persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        // $persistenceManager->persistAll();

        return $privacy;
    }


    /**
     * addPrivacyDataForOptInFinal
     * set the $registration-object if you want to complete an optIn
     *
     * @param Request $request
     * @param FrontendUser $frontendUser
     * @param Registration $registration
     * @param string $comment
     * @return Privacy
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @api
     */
    public static function addPrivacyDataForOptInFinal(
        Request $request,
        FrontendUser $frontendUser,
        Registration $registration = null,
        $comment = ''

    )
    {
        /** @var Privacy $privacy */
        $privacy = GeneralUtility::makeInstance(Privacy::class);

        self::setPrivacyData($privacy, $request, $frontendUser, $registration, $comment, true);

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var PrivacyRepository $privacyRepository */
        $privacyRepository = $objectManager->get(PrivacyRepository::class);
        $privacyRepository->add($privacy);

        // @toDo: should normally be called in the context of RKW\RkwRegistration\Service\RegistrationService where already persistence happens
        /** @var PersistenceManager $persistenceManager */
        // $persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        // $persistenceManager->persistAll();

        return $privacy;
    }


    /**
     * addPrivacyData
     * set the $registration-object if you want to complete an optIn
     *
     * @param Request                                                      $request
     * @param FrontendUser                                                 $frontendUser
     * @param AbstractEntity|ObjectStorage $dataObject
     * @param string                                                       $comment
     * @return Privacy
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @api
     */
    public static function addPrivacyData(
        Request $request,
        FrontendUser $frontendUser,
        $dataObject,
        $comment = ''
    )
    {
        /** @var Privacy $privacy */
        $privacy = GeneralUtility::makeInstance(Privacy::class);

        self::setPrivacyData($privacy, $request, $frontendUser, $dataObject, $comment);

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var PrivacyRepository $privacyRepository */
        $privacyRepository = $objectManager->get(PrivacyRepository::class);
        $privacyRepository->add($privacy);

        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        return $privacy;
    }


}