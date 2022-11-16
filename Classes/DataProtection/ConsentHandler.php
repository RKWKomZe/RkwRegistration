<?php

namespace RKW\RkwRegistration\DataProtection;

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
use RKW\RkwRegistration\Domain\Model\OptIn;
use RKW\RkwRegistration\Domain\Model\Consent;
use RKW\RkwRegistration\Domain\Repository\ConsentRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\ClientUtility;
use RKW\RkwRegistration\ViewHelpers\ConsentViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Privacy
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ConsentHandler implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * setObject
     * Use this function to set basic data
     * The $dataObject is the element for what the privacy dataset will be created for (e.g. an order, or a new alert) !
     * Hint for optIn (two privacy-entries will be created):
     * 1. The first privacy-dataset of the optIn is created by the registration automatically. If the $dataObject is of type
     *    RKW\RkwRegistration\Domain\Model\Registration it will be automatically identified and set below in $this->setDataObject
     * 2. After successful optIn the 5th param is used to create the relationship between the two created privacy-datasets
     *
     * @param \RKW\RkwRegistration\Domain\Model\Consent $consent
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|\TYPO3\CMS\Extbase\Persistence\ObjectStorage|null $referenceObject
     * @param string $comment
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected static function setObject(
        Request $request,
        FrontendUser $frontendUser,
        $referenceObject = null,
        string $comment = ''
    ): Consent {

        /** @var \RKW\RkwRegistration\Domain\Model\Consent $consent */
        $consent = GeneralUtility::makeInstance(Consent::class);

        // set frontendUser
        $consent->setFrontendUser($frontendUser);

        // set IP-address
        $consent->setIpAddress(ClientUtility::getIp());

        // set domain name
        $consent->setServerHost(filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL));

        // set path of url
        $consent->setServerUri(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL));

        // set referer url
        $consent->setServerRefererUrl(filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL));

        // set userAgent
        $consent->setUserAgent((string) $_SERVER['HTTP_USER_AGENT']);

        // set extension-, plugin-, controller- and action-name
        $consent->setExtensionName((string) $request->getControllerExtensionName());
        $consent->setPluginName((string) $request->getPluginName());
        $consent->setControllerName((string) $request->getControllerName());
        $consent->setActionName((string) $request->getControllerActionName());

        // set consent-fields
        $formData = GeneralUtility::_GP(ConsentViewHelper::NAMESPACE);
        if ($formData['privacy'] == 1) {
            $consent->setConsentPrivacy(true);
        }
        if ($formData['terms'] == 1) {
            $consent->setConsentTerms(true);
        }
        if ($formData['marketing'] == 1) {
            $consent->setConsentMarketing(true);
        }

        // set informed consent reason - optional freeText field
        $consent->setComment($comment);

        // set reference object and maybe override it
        self::setReference($consent, $referenceObject);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // if we have an optIn here, we can differentiate between the optIn and it's approval
        if ($referenceObject instanceof OptIn) {

            // set parent privacy entry in final step on opt-in
            if (
                ($referenceObject->getApproved())
                && ($referenceObject->getAdminApproved())
            ){

                // get former consent-entry via optIn
                /** @var \RKW\RkwRegistration\Domain\Repository\ConsentRepository $consentRepository */
                $consentRepository = $objectManager->get(ConsentRepository::class);
                $consentParent = $consentRepository->findOneByOptIn($referenceObject);
                if ($consentParent) {
                    $consent->setParent($consentParent);
                    $consentParent->unsetOptIn();
                    $consentRepository->update($consentParent);
                }

                // set consent in frontendUser
                /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
                $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
                $frontendUser->setTxRkwregistrationConsentTerms($consent->getConsentTerms());
                $frontendUser->setTxRkwregistrationConsentMarketing($consent->getConsentMarketing());
                $frontendUserRepository->update($frontendUser);


            // not yet confirmed: set optIn for child-parent-relation after optIn-confirmation
            } else {
                $consent->setOptIn($referenceObject);
            }

            // optionally override reference-object with object from optIn
            if (
                ($referenceObject->getData())
                && ($referenceObject->getData() instanceof AbstractEntity)
            ){
                self::setReference($consent, $referenceObject->getData());
            }

        // normal object
        } else {

            // set consent in frontendUser
            /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
            $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
            $frontendUser->setTxRkwregistrationConsentTerms($consent->getConsentTerms());
            $frontendUser->setTxRkwregistrationConsentMarketing($consent->getConsentMarketing());
            $frontendUserRepository->update($frontendUser);
        }

        return $consent;
    }


    /**
     * setReference
     *
     * @param \RKW\RkwRegistration\Domain\Model\Consent $consent
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|\TYPO3\CMS\Extbase\Persistence\ObjectStorage $referenceObject
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    protected static function setReference(
        Consent $consent,
       $referenceObject
    ): void {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var DataMapper $dataMapper */
        $dataMapper = $objectManager->get(DataMapper::class);

        if ($referenceObject instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {

            $referenceTemp = $referenceObject->current();
            if ($referenceTemp instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
                $consent->setForeignTable(
                    $dataMapper->getDataMap(get_class($referenceTemp))->getTableName(),
                );
            }

            $ids = [];
            foreach ($referenceObject as $referenceTemp) {
                if ($referenceTemp instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
                    $ids[] = $referenceTemp->getUid();
                }
            }

            $consent->setForeignUid(implode(',', $ids));

            // else we determine the concrete foreignTable and foreignUid
        } else if ($referenceObject instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {

            $consent->setForeignTable(
                $dataMapper->getDataMap(get_class($referenceObject))->getTableName(),
            );
            $consent->setForeignUid($referenceObject->getUid() ?? 0);
        }



    }


    /**
     * add consent data
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|\TYPO3\CMS\Extbase\Persistence\ObjectStorage $referenceObject
     * @param string $comment
     * @return \RKW\RkwRegistration\Domain\Model\Consent
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @api
     */
    public static function add
    (
        Request $request,
        FrontendUser $frontendUser,
        $referenceObject,
        string $comment = ''
    ): Consent {

        $consent = self::setObject($request, $frontendUser, $referenceObject, $comment);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\ConsentRepository $consentRepository */
        $consentRepository = $objectManager->get(ConsentRepository::class);
        $consentRepository->add($consent);

        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager  $persistenceManager */
        $persistenceManager = $objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        return $consent;
    }
}
