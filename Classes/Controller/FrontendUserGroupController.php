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

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\OptIn;
use RKW\RkwRegistration\Service\RkwMailService;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ServiceController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
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
     * @var \RKW\RkwRegistration\Registration\FrontendUserRegistration
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

        /** @var \RKW\RkwRegistration\Registration\FrontendUserRegistration */
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
     * Takes optIn parameters and checks them
     *
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function optInAction(): void
    {
        $token = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('token'));
        $tokenUser = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('user'));

        $check =  $this->frontendUserRegistration->setFrontendUserToken($tokenUser)
            ->setRequest($this->getRequest())
            ->setCategory('rkwRegistrationGroups')
            ->validateOptIn($token);

        if ($check < 300) {

            if ($check == 201) {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.warning.successfulButWaitingForAdmin',
                        $this->extensionName
                    ),
                    '',
                    AbstractMessage::WARNING
                );

            } elseif ($check == 202) {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.warning.successfulButWaitingForUser',
                        $this->extensionName
                    ),
                    '',
                    AbstractMessage::WARNING
                );

            } elseif ($check == 299) {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.warning.successfulFinished',
                        $this->extensionName
                    ),
                    '',
                    AbstractMessage::WARNING
                );

            } else {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.message.successful',
                        $this->extensionName
                    )
                );
            }


        } elseif ($check < 400) {

            if ($check == 301) {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.warning.canceledByAdmin',
                        $this->extensionName
                    ),
                    '',
                    AbstractMessage::WARNING
                );

            } elseif ($check == 302) {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.warning.canceledByUser',
                        $this->extensionName
                    ),
                    '',
                    AbstractMessage::WARNING
                );

            } elseif ($check == 399) {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.warning.cancelingFinished',
                        $this->extensionName
                    ),
                    '',
                    AbstractMessage::WARNING
                );

            } else {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'frontendUserGroupController.message.canceled',
                        $this->extensionName
                    )
                );
            }

        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'frontendUserGroupController.error.unexpected',
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
        }


        // logged in user?
        if (
            (FrontendUserSessionUtility::getLoggedInUser())
            && ($this->settings['groupsListPid'])
        ){
            $this->redirect(
                'list',
                'FrontendUserGroup',
                null,
                null,
                $this->settings['groupsListPid']
            );
        }

        // nah...
        $this->redirect(
            'index',
            'Auth',
            null,
            [],
            intval($this->settings['loginPid']) ?: null
        );
    }


    /**
     * Add user to frontendUserGroup
     *
     * ! used via SignalSlot !
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function createMembership (FrontendUser $frontendUser, OptIn $optIn): void
    {

        if (
            ($frontendGroup = $optIn->getData())
            && ($frontendGroup instanceof FrontendUserGroup)
        ) {

            // add user to group
            $frontendUser->addUsergroup($frontendGroup);
            $this->frontendUserRepository->update($frontendUser);
            $this->persistenceManager->persistAll();

            // trigger email
            /** @var \RKW\RkwRegistration\Service\RkwMailService $mailService */
            $mailService = GeneralUtility::makeInstance(RkwMailService::class);
            $mailService->sendGroupConfirmationEmail($frontendUser, $optIn);
        }
    }


    /**
     * action delete
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function deleteAction(FrontendUserGroup $frontendUserGroup): void
    {
        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

        // remove group from user
        // we need to do this in a more complicated way, because the groups are based on the core-models here
        $frontendUser = $this->getFrontendUser();
        $objectStorage = GeneralUtility::makeInstance(ObjectStorage::class);

        /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $userGroup */
        foreach ($frontendUser->getUsergroup() as $userGroup) {
            if ($userGroup->getUid() != $frontendUserGroup->getUid()) {
                $objectStorage->attach($userGroup);
            }
        }
        $frontendUser->setUsergroup($objectStorage);
        $this->frontendUserRepository->update($frontendUser);

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'frontendUserGroupController.message.membershipEnded',
                $this->extensionName
            )
        );

        $this->redirect('list');
    }


}
