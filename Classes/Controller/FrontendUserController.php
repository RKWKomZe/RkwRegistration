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
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\TitleUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class FrontendUserController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserController extends AbstractController
{

    /**
     * @var \RKW\RkwRegistration\Registration\FrontendUserRegistration
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserRegistration;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\TitleRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $titleRepository;


    /**
     * action register
     * RKW own register action
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("frontendUser")
     */
    public function newAction(FrontendUser $frontendUser = null): void
    {
        // not for already logged-in users!
        if (FrontendUserSessionUtility::getLoggedInUserId()) {

            if ($this->settings['welcomePid']) {
                $this->redirect(
                    'index',
                    'Auth',
                    null,
                    null,
                    $this->settings['welcomePid']
                );
            }

            $this->redirect('index');
        }

        if (
            (! $this->getFlashMessageCount())
            && (! $_POST)
        ) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'frontendUserController.notice.newIntroduction',
                    $this->extensionName,
                ),
                '',
                AbstractMessage::NOTICE
            );
        }

        $titles = $this->titleRepository->findAllOfType(true, false, false);
        $this->view->assignMultiple(
            [
                'frontendUser'   => $frontendUser,
                'titles'         => $titles
            ]
        );
    }


    /**
     * action create
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
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
     * @TYPO3\CMS\Extbase\Annotation\Validate("RKW\RkwRegistration\Validation\FrontendUserValidator", param="frontendUser")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\Consent\TermsValidator", param="frontendUser")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\Consent\PrivacyValidator", param="frontendUser")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\Consent\MarketingValidator", param="frontendUser")
     */
    public function createAction(FrontendUser $frontendUser): void
    {
        /** @var \RKW\RkwRegistration\Registration\FrontendUserRegistration */
        $this->frontendUserRegistration->setFrontendUser($frontendUser)
            ->setRequest($this->request)
            ->startRegistration();

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'frontendUserController.message.registrationWatchForEmail',
                $this->extensionName
            )
        );

        if ($this->settings['loginPid']) {
            $this->redirect(
                'index',
                'Auth',
                null,
                [],
                $this->settings['loginPid']
            );
        }

        $this->redirect('index');
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
     */
    public function optInAction(): void
    {
        $token = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('token'));
        $tokenUser = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('user'));

        $check =  $this->frontendUserRegistration->setFrontendUserToken($tokenUser)
            ->setRequest($this->getRequest())
            ->validateOptIn($token);

        if ($check < 300) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'frontendUserController.message.registrationSuccessful',
                    $this->extensionName
                )
            );

        } elseif ($check < 400) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'frontendUserController.message.registrationCanceled',
                    $this->extensionName
                )
            );

        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'frontendUserController.error.registrationError',
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
        }

        $this->redirect(
            'index',
            'Auth',
            null,
            [],
            $this->settings['loginPid']
        );
    }



    /**
     * action welcome
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function welcomeAction(): void
    {
        // only for logged in users!
        $this->redirectIfUserNotLoggedInOrGuest();

        // check fields
        $this->redirectIfUserHasMissingData();

        $this->view->assignMultiple(
            [
                'frontendUser'    => $this->getFrontendUser()
            ]
        );
    }

    /**
     * action editUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup|null $frontendUserGroup
     * @return void
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function editAction(FrontendUser $frontendUser = null, FrontendUserGroup $frontendUserGroup = null): void
    {

        // for logged-in users only!
        $this->redirectIfUserNotLoggedInOrGuest();

        // set temporary usergroup for validation
        $frontendUser = $frontendUser ?: $this->getFrontendUser();
        if ($frontendUserGroup) {
            $frontendUser->setTempFrontendUserGroup($frontendUserGroup);
        }

        if (
            (! $this->getFlashMessageCount())
            && (! $_POST)
            && (! $frontendUserGroup)
        ) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'frontendUserController.notice.editIntroduction',
                    $this->extensionName,
                ),
                '',
                AbstractMessage::NOTICE
            );
        }

        $titles = $this->titleRepository->findAllOfType(true, false, false);
        $this->view->assignMultiple(
            [
                'frontendUser'  => $frontendUser,
                'titles'        => $titles
            ]
        );
    }


    /**
     * action update
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @TYPO3\CMS\Extbase\Annotation\Validate("RKW\RkwRegistration\Validation\FrontendUserValidator", param="frontendUser")
     */
    public function updateAction(FrontendUser $frontendUser): void
    {

        // for logged-in users only!
        $this->redirectIfUserNotLoggedInOrGuest();

        // migrate title-handling
        if ($frontendUser->getTxRkwregistrationTitle()) {
            $frontendUser->setTxRkwregistrationTitle(
                TitleUtility::extractTxRegistrationTitle(
                    $frontendUser->getTxRkwregistrationTitle()->getName()
                )
            );
        }
        $this->frontendUserRepository->update($frontendUser);

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'frontendUserController.message.updateSuccessful', $this->extensionName
            )
        );

        // redirect back to groups when we were originally redirected from there
        if (
            ($this->settings['groupsListPid'])
            && ($frontendUser->getTempFrontendUserGroup())
        ){
            $this->redirect(
                'create',
                'FrontendUserGroup',
                null,
                [
                    'frontendUserGroup' => $frontendUser->getTempFrontendUserGroup()
                ],
                $this->settings['groupsListPid']
            );
        }

        if ($this->settings['welcomePid']) {
            $this->redirect(
                'index',
                'Registration',
                null,
                null,
                $this->settings['welcomePid']
            );
        }

        $this->redirect('edit');
    }


    /**
     * action show
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function showAction(): void
    {
        // for logged-in users only!
        $this->redirectIfUserNotLoggedInOrGuest();

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'frontendUserController.warning.showIntroduction',
                $this->extensionName,
            ),
            '',
            AbstractMessage::WARNING
        );

        $this->view->assignMultiple(
            [
                'frontendUser'  => $this->getFrontendUser(),
            ]
        );
    }


    /**
     * action delete
     *
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function deleteAction(): void
    {
        // for logged-in users only!
        $this->redirectIfUserNotLoggedInOrGuest();

        $this->frontendUserRegistration->setFrontendUser($this->getFrontendUser())
            ->setRequest($this->request)
            ->endRegistration();

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'frontendUserController.message.deletedSuccessful',
                $this->extensionName
            )
        );

        if ($this->settings['loginPid']) {
            $this->redirect(
                'index',
                'Auth',
                null,
                [],
                $this->settings['loginPid']
            );
        }

        $this->redirect('index', 'Auth');

    }

}


