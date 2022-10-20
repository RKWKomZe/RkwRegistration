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
use RKW\RkwRegistration\Register\GroupFrontendUser;
use RKW\RkwRegistration\Utility\FrontendUserGroupUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use RKW\RkwRegistration\Validation\FrontendUserValidator;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
class FrontendUserGroupController extends AbstractController
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
     * @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserRegistration;



    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserGroupRepository;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\OptInRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $optInRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $backendUserRepository;


    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $objectManager;


    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $signalSlotDispatcher;


    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $configurationManager;


    /**
     * action list
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listAction(): void
    {
        // only for logged in users!
        $this->redirectIfUserNotLoggedInOrGuest();

        // check basic fields
        $this->redirectIfUserHasMissingData();

        $membershipable = $this->frontendUserGroupRepository->findMembershipable();
        $membershipsRequested = $this->optInRepository->findPendingGroupMembershipsByFrontendUser($this->getFrontendUser());

        if (! count($membershipable)) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'frontendUserGroupController.warning.noMembershipableGroups',
                    $this->extensionName,
                ),
                '',
                AbstractMessage::WARNING
            );
        } else {
            if (
                (! $this->getFlashMessageCount())
                && (! $_POST)
            ) {
                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.notice.selectGroup',
                        $this->extensionName,
                    ),
                    '',
                    AbstractMessage::NOTICE
                );
            }
        }

        $this->view->assignMultiple(
            [
                'frontendUser'           => $this->getFrontendUser(),
                'membershipable'         => $membershipable,
                'membershipsRequested'   => $membershipsRequested,
            ]
        );
    }


    /**
     * action create
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function createAction(FrontendUserGroup $frontendUserGroup): void
    {
        // for logged in users only!
        $this->redirectIfUserNotLoggedInOrGuest();

        // check if all required fields are set!
        // to do this, we hypothetically set the new frontendUserGroup and evaluate against it
        $this->redirectIfUserHasMissingData($frontendUserGroup);

        /** @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration */
        $this->frontendUserRegistration->setFrontendUser($this->getFrontendUser())
            ->setData($frontendUserGroup)
            ->setApproval($frontendUserGroup->getTxRkwregistrationMembershipAdmins())
            ->setRequest($this->request)
            ->setCategory('rkwRegistrationGroups')
            ->startRegistration();

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'frontendUserGroupController.message.registrationWatchForEmail',
                $this->extensionName
            )
        );

        $this->redirect('list');
    }






    /**
     * action show
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function showAction(FrontendUserGroup $frontendUserGroup): void
    {
        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

        $this->view->assignMultiple(
            [
                'frontendUserGroup' => $frontendUserGroup,
            ]
        );
    }



    /**
     * Takes opt-in parameters and checks them
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function optInAction(): void
    {
        $tokenYes = preg_replace(
            '/[^a-zA-Z0-9]/',
            '',
            ($this->request->hasArgument('token_yes') ? $this->request->getArgument('token_yes') : '')
        );
        $tokenNo = preg_replace(
            '/[^a-zA-Z0-9]/',
            '',
            ($this->request->hasArgument('token_no') ? $this->request->getArgument('token_no') : '')
        );
        $serviceSha1 = preg_replace(
            '/[^a-zA-Z0-9]/',
            '',
            $this->request->getArgument('service')
        );

        $service = $this->objectManager->get(GroupFrontendUser::class);
        $check = $service->checkTokens($tokenYes, $tokenNo, $serviceSha1);

        if ($check == 1) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'serviceController.message.service_optin_successfull',
                    $this->extensionName
                )
            );

        } elseif ($check == 2) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'serviceController.message.service_optin_canceled',
                    $this->extensionName
                )
            );


        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'serviceController.error.service_optin_error',
                    $this->extensionName
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
    public function deleteAction(FrontendUserGroup $frontendUserGroup): void
    {
        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

        // remove group from user
        $this->getFrontendUser()->removeUsergroup($frontendUserGroup);
        $this->frontendUserRepository->update($this->getFrontendUser());

        // dispatch event
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            self::SIGNAL_SERVICE_DELETE,
            [
                $this->getFrontendUser(),
                $frontendUserGroup
            ]
        );


        $this->addFlashMessage(
            LocalizationUtility::translate(
                'serviceController.message.service_delete',
                $this->extensionName
            )
        );

        $this->redirect('list');
    }


}
