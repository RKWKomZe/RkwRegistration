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
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\TitleUtility;
use RKW\RkwRegistration\Validation\FrontendUserValidator;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class FrontendUserController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserController extends AbstractController
{

    /**
     * @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration
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
     * @param FrontendUser|null $newFrontendUser
     * @ignorevalidation $newFrontendUser
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function newAction(FrontendUser $newFrontendUser = null): void
    {
        // not for already logged-in users!
        if (FrontendUserSessionUtility::getLoggedInUserId()) {

            if ($this->settings['users']['welcomePid']) {
                $this->redirect(
                    'index',
                    'Auth',
                    null,
                    null,
                    $this->settings['users']['welcomePid']
                );
            }

            $this->redirect('index');
        }

        $titles = $this->titleRepository->findAllOfType(true, false, false);
        $this->view->assignMultiple(
            [
                'newFrontendUser'   => $newFrontendUser,
                'termsPid'          => intval($this->settings['users']['termsPid']),
                'titles'            => $titles
            ]
        );
    }


    /**
     * action create
     *
     * @param FrontendUser $newFrontendUser
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
     * @TYPO3\CMS\Extbase\Annotation\Validate("RKW\RkwRegistration\Validation\FrontendUserValidator", param="newFrontendUser")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\TermsValidator", param="newFrontendUser")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\PrivacyValidator", param="newFrontendUser")
     */
    public function createAction(FrontendUser $newFrontendUser): void
    {
        /** @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration */
        $this->frontendUserRegistration->setFrontendUser($newFrontendUser)
            ->setRequest($this->request)
            ->startRegistration();

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'frontendUserController.message.registration_watch_for_email',
                $this->extensionName
            )
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect(
                'index',
                'Auth',
                null,
                [],
                $this->settings['users']['loginPid']
            );
        }

        $this->redirect('index');
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

        // check email!
        $this->redirectIfUserHasNoValidEmail();

        // check basic mandatory fields
        $this->redirectIfUserHasMissingData();

        $this->view->assignMultiple(
            [
                'frontendUser'    => $this->getFrontendUser(),
                'editUserPid'     => intval($this->settings['users']['editUserPid']),
                'deleteUserPid'   => intval($this->settings['users']['deleteUserPid']),
                'editPasswordPid' => intval($this->settings['users']['editPasswordPid']),
                'logoutPid'       => intval($this->settings['users']['logoutPid']),
            ]
        );
    }

    /**
     * action editUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     * @TYPO3\CMS\Extbase\Annotation\Validate("RKW\RkwRegistration\Validation\FrontendUserValidator", param="frontendUser")
     * @return void
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function editAction(FrontendUser $frontendUser = null): void
    {

        // for logged-in users only!
        $this->redirectIfUserNotLoggedInOrGuest();

        $titles = $this->titleRepository->findAllOfType(true, false, false);
        $this->view->assignMultiple(
            [
                'frontendUser' => $frontendUser,
                'welcomePid'   => intval($this->settings['users']['welcomePid']),
                'titles' => $titles
            ]
        );
    }


    /**
     * action update
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("RKW\RkwRegistration\Validation\FrontendUserValidator", param="frontendUser")
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
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
                'frontendUserController.message.update_successful', $this->extensionName
            )
        );

        if ($this->settings['users']['welcomePid']) {
            $this->redirect(
                'index',
                'Registration',
                null,
                null,
                $this->settings['users']['welcomePid']
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

        $this->view->assignMultiple(
            [
                'welcomePid' => intval($this->settings['users']['welcomePid']),
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
                'frontendUserController.message.delete_successful',
                $this->extensionName
            )
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect(
                'index',
                'Auth',
                null,
                [],
                $this->settings['users']['loginPid']
            );
        }

        $this->redirect('index', 'Auth');

    }

}


