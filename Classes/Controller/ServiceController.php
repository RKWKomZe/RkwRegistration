<?php

namespace RKW\RkwRegistration\Controller;

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

use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Service\GroupService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ServiceController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ServiceController extends AbstractController
{
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_ADMIN_SERVICE_REQUEST = 'afterAdminServiceRequest';

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_SERVICE_DELETE = 'afterServiceDelete';

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     * @inject
     */
    protected $frontendUserGroupRepository;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ServiceRepository
     * @inject
     */
    protected $serviceRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository
     * @inject
     */
    protected $backendUserRepository;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;


    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;


    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;


    /**
     * action list
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function listAction()
    {
        // for logged in users only!
        $this->hasUserValidLoginRedirect();

        // check email!
        $this->hasUserValidEmailRedirect();

        // available services
        $frontendUserGroups = $this->frontendUserGroupRepository->findServices();
        $frontendUser = $this->getFrontendUser();

        // services which the user already belongs
        $groupsOfFrontendUser = $frontendUser->getUsergroup();

        // services where the user is waiting for the release
        $serviceInquiries = $this->serviceRepository->findByUser($this->getFrontendUser());
        $serviceInquiriesAdmin = $this->serviceRepository->findEnabledByAdminByUser($this->getFrontendUser());

        $this->view->assignMultiple(
            array(
                'frontendUserGroups'    => $frontendUserGroups,
                'groupsOfFrontendUser'  => $groupsOfFrontendUser,
                'serviceInquiries'      => $serviceInquiries,
                'serviceInquiriesAdmin' => $serviceInquiriesAdmin,
                'editUserPid'           => intval($this->settings['users']['editUserPid']),
            )
        );
    }


    /**
     * action show
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @return void
     */
    public function showAction(FrontendUserGroup $frontendUserGroup)
    {
        // for logged in users only!
        $this->hasUserValidLoginRedirect();

        $this->view->assignMultiple(
            array(
                'frontendUserGroup' => $frontendUserGroup,
            )
        );
    }


    /**
     * action create
     * creates and processes a user request for a service
     * If no access restriction and no mandatory fields must be filled, the service
     * for the users is directly released and the user as userGroup added
     * Otherwise he has wait for admin grant
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @return void
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function createAction(FrontendUserGroup $frontendUserGroup)
    {
        // for logged in users only!
        $this->hasUserValidLoginRedirect();

        // Get the mandatory fields for the given group?
        $serviceClass = GeneralUtility::makeInstance(GroupService::class);
        $mandatoryFields = $serviceClass->getMandatoryFieldsOfUser($this->getFrontendUser(), $frontendUserGroup);

        // get the admins of the given group (if any)
        $admins = $frontendUserGroup->getTxRkwregistrationServiceAdmins();

        // if at least on of the two cases matches, we have to use an opt-in
        // We can not check here if the mandatory fields of the group are filled out by the user
        // but nevertheless we have to ask him if the data is correct, so that is no real problem here!
        if (
            (count($mandatoryFields) > 0)
            || (count($admins) > 0)
        ) {

            // create new opt-in for service
            $newOptIn = $this->serviceRepository->newOptIn($this->getFrontendUser(), $frontendUserGroup, $this->settings['services']['daysForOptIn']);

            // per default take admin permission for granted
            $newOptIn->setEnabledByAdmin(1);

            // if there are some admins, ask them for permission instead
            if (count($admins) > 0) {

                // disable by admin
                $newOptIn->setEnabledByAdmin(0);

                // dispatcher for e.g. E-Mail
                foreach ($admins as $admin) {
                    $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_ADMIN_SERVICE_REQUEST, array($admin, $this->getFrontendUser(), $frontendUserGroup, $newOptIn, intval($this->settings['services']['adminOptInPid'])));
                }

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'serviceController.message.apply_admin_request', 'rkw_registration'
                    )
                );

                $this->redirect('list');
            }
        }

        // if there is nothing to check - we simply add the user-group to the fe-user's
        $frontendUser = $this->getFrontendUser();
        $frontendUser->addUsergroup($frontendUserGroup);
        $this->frontendUserRepository->update($frontendUser);

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'serviceController.message.apply_successfull', 'rkw_registration'
            )
        );

        $this->redirect('list');
    }


    /**
     * Takes optin parameters and checks them
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function optInAction()
    {
        $tokenYes = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_yes') ? $this->request->getArgument('token_yes') : ''));
        $tokenNo = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_no') ? $this->request->getArgument('token_no') : ''));
        $serviceSha1 = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('service'));

        $service = GeneralUtility::makeInstance(GroupService::class);
        $check = $service->checkTokens($tokenYes, $tokenNo, $serviceSha1);

        if ($check == 1) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'serviceController.message.service_optin_successfull', 'rkw_registration'
                )
            );

        } elseif ($check == 2) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'serviceController.message.service_optin_canceled', 'rkw_registration'
                )
            );


        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'serviceController.error.service_optin_error', 'rkw_registration'
                ),
                '',
                AbstractMessage::ERROR
            );
        }
    }


    /**
     * action delete
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function deleteAction(FrontendUserGroup $frontendUserGroup)
    {
        // for logged in users only!
        $this->hasUserValidLoginRedirect();

        // remove group from user
        $this->getFrontendUser()->removeUsergroup($frontendUserGroup);
        $this->frontendUserRepository->update($this->getFrontendUser());

        // dispatch event
        $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_SERVICE_DELETE, array($this->getFrontendUser(), $frontendUserGroup));


        $this->addFlashMessage(
            LocalizationUtility::translate(
                'serviceController.message.service_delete', 'rkw_registration'
            )
        );

        $this->redirect('list');
    }


}