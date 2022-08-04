<?php

namespace RKW\RkwRegistration\Register;

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\Service;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\ServiceRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

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
 * Class GroupRegister
 *
 * This service manage fe_groups, which are an explicit "Service".
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GroupRegister extends AbstractRegister
{
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ADMIN_SERVICE_GRANT = 'afterAdminServiceGrant';


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ADMIN_SERVICE_DENIAL = 'afterAdminServiceDenial';



    /**
     * getMandatoryFieldsOfGroup
     * gives the required fields back that needs to fill out a user in the light of its service affiliation
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getMandatoryFieldsOfGroup(FrontendUserGroup $frontendUserGroup)
    {
        $requiredFields = [];
        if ($groupMandatoryFields = $frontendUserGroup->getTxRkwregistrationServiceMandatoryFields()) {
            $requiredFields = explode(',', str_replace(' ', '', $groupMandatoryFields));
        }

        return $requiredFields;
    }



    /**
     * getMandatoryFieldsOfGroupList
     * gives the required fields back that needs to fill out a user in the light of its service affiliation
     *
     * @param array $groupList
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getMandatoryFieldsOfGroupList(array $groupList)
    {
        $requiredFields = [];

        foreach ($groupList as $group) {
            if ($group instanceof FrontendUserGroup) {
                $requiredFields = array_merge($requiredFields, $this->getMandatoryFieldsOfGroup($group));
            }
        }

        return $requiredFields;
    }



     /**
     * Checks given tokens from E-mail
     *
     * 0 = error
     * 1 = success token (created)
     * 2 = remove token (deleted)
     *
     * @param string $tokenYes
     * @param string $tokenNo
     * @param string $serviceSha1
     * @return integer
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function checkTokens(string $tokenYes, string $tokenNo, string $serviceSha1): int
    {
        // load service by SHA-token
        $service = $this->serviceRepository->findOneByServiceSha1($serviceSha1);

        if (!$service instanceof Service) {
            return 0;
        }

        // is token already invalid?
        if (
            (!$service->getValidUntil())
            || ($service->getValidUntil() < time())
        ) {
            $this->serviceRepository->remove($service);
            $this->persistenceManager->persistAll();
            return 0;
        }

        // load fe-user
        if (
            ($service->getUser())
            && ($frontendUser = $this->frontendUserRepository->findByUidAlsoInactiveNonGuest($service->getUser()->getUid()))
            && ($frontendUserGroups = $service->getUsergroup())
        ) {

            // check yes-token
            if ($service->getTokenYes() == $tokenYes) {

                // set enabled by admin- flag and unset tokens
                $service->setEnabledByAdmin(true);
                $service->setTokenYes(null);
                $service->setTokenNo(null);
                $service->setServiceSha1(null);
                $service->setValidUntil(0);


                // check if there are mandatory fields for the service
                $mandatoryFields = [];
                foreach ($frontendUserGroups as $frontendUserGroup) {
                    if ($frontendUserGroup instanceof FrontendUserGroup) {
                        $mandatoryFields = array_merge($mandatoryFields, $this->getMandatoryFieldsOfGroup($frontendUserGroup));
                    }
                }

                // if there are mandatory fields, update service request in database
                if (count($mandatoryFields) > 0) {

                    // @toDo by MF: Wann genau soll man hier rein? Wenn eine Gruppe grundsätzlich Pflichtfelder hat?
                    // ---> Ooooder eigentlich nur, wenn eine Gruppe Pflichtfelder hat, die ein Nutzer noch nicht ausgefüllt hat?!
                    // -> UNKLAR!!!

                    // @toDo by MF: Should we log this? This is a "hidden" sub-routine I've debugged to, to understand what happen
                    $this->serviceRepository->update($service);

                    // if there is none, we finally add the user to the fe-groups and remove the service request
                } else {
                    foreach ($frontendUserGroups as $frontendUserGroup) {
                        if ($frontendUserGroup instanceof FrontendUserGroup) {
                            $frontendUser->addUsergroup($frontendUserGroup);
                        }
                    }
                    $this->frontendUserRepository->update($frontendUser);
                    $this->serviceRepository->remove($service);
                }

                // Signal for E-Mails
                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    self::SIGNAL_AFTER_ADMIN_SERVICE_GRANT,
                    [$frontendUser, $service]
                );

                $this->persistenceManager->persistAll();

                return 1;

                // check no-token
            } elseif ($service->getTokenNo() == $tokenNo) {

                // delete service request from database
                $this->serviceRepository->remove($service);

                // Signal for E-Mails
                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    self::SIGNAL_AFTER_ADMIN_SERVICE_DENIAL,
                    [$frontendUser, $service]
                );

                $this->persistenceManager->persistAll();

                return 2;
            }
        }

        // token mismatch or something strange happened - kill that beast!!!
        $this->serviceRepository->remove($service);
        $this->persistenceManager->persistAll();

        return 0;
    }


    /**
     * Adds user to all granted groups
     * IMPORTANT: No more checks are done here! This method should be called only when all mandatory fields are set!
     *
     * @param FrontendUser $frontendUser
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function addUserToAllGrantedGroups(FrontendUser $frontendUser)
    {
        // find all services which have been granted by admin
        $cnt = 0;
        if ($services = $this->serviceRepository->findConfirmedByUser($frontendUser)) {

            // go through all found services...
            foreach ($services as $service) {
                if ($service instanceof Service) {

                    // get frontend user groups...
                    if (($frontendUserGroups = $service->getUsergroup())) {

                        // go through all groups of every service...
                        foreach ($frontendUserGroups as $frontendUserGroup) {

                            // add user to group
                            if ($frontendUserGroup instanceof FrontendUserGroup) {
                                $frontendUser->addUsergroup($frontendUserGroup);
                                $cnt++;
                            }
                        }
                    }

                    // remove service and update user
                    $this->serviceRepository->remove($service);
                    $this->frontendUserRepository->update($frontendUser);
                }
            }

            // persist all
            $this->persistenceManager->persistAll();
        }

        return (boolean)$cnt;
    }


    /**
     * function getMandatoryFieldsOfUser
     * gives the required fields back that needs to fill out a user in the light of its service affiliation
     *
     * @deprecated Function is split in FrontendUserRegister->getMandatoryFieldsOfUser and $this->getMandatoryFieldsForUserOfSpecificGroup
     *
     * @param FrontendUser|null      $frontendUser
     * @param FrontendUserGroup|null $frontendUserGroup
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getMandatoryFieldsOfUser(FrontendUser $frontendUser = null, FrontendUserGroup $frontendUserGroup = null)
    {
        // get mandatory fields from TypoScript
        $settings = $this->getSettings();
        $requiredFields = [];

        // 1. get mandatory fields of given user-group
        if ($frontendUserGroup) {

            if ($groupMandatoryFields = $frontendUserGroup->getTxRkwregistrationServiceMandatoryFields()) {
                $requiredFields = explode(',', str_replace(' ', '', $groupMandatoryFields));
            }

            // 2. else try to get all relevant data from database and TypoScript
        } else {

            //=======================================
            // get default mandatory fields
            if ($settings['users']['requiredFormFields']) {
                $requiredFields = explode(',', str_replace(' ', '', $settings['users']['requiredFormFields']));
            }


            if ($frontendUser instanceof FrontendUser) {

                //=======================================
                // get mandatory fields by fe_groups the user is registered for
                $groupsOfUser = $frontendUser->getUsergroup();
                foreach ($groupsOfUser as $group) {
                    if ($group instanceof FrontendUserGroup) {
                        if ($groupMandatoryFields = $group->getTxRkwregistrationServiceMandatoryFields()) {
                            $requiredFields = array_merge($requiredFields, explode(',', str_replace(' ', '', $groupMandatoryFields)));
                        }
                    }
                }

                //=======================================
                // get mandatory fields by fe_groups the user is still waiting to be registered but admin has already granted him access
                /** @var ObjectManager $objectManager */
                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

                /** @var ServiceRepository $serviceRepository */
                $serviceRepository = $objectManager->get(ServiceRepository::class);

                $serviceInquiries = $serviceRepository->findConfirmedByUser($frontendUser);
                foreach ($serviceInquiries as $serviceInquiry) {

                    if ($groups = $serviceInquiry->getUsergroup()) {
                        foreach ($groups as $group) {
                            if ($group instanceof FrontendUserGroup) {
                                if ($groupMandatoryFields = $group->getTxRkwregistrationServiceMandatoryFields()) {
                                    $requiredFields = array_merge($requiredFields, explode(',', str_replace(' ', '', $groupMandatoryFields)));
                                }
                            }
                        }
                    }
                }
            }
        }

        return $requiredFields;
    }


}