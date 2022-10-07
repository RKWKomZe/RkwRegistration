<?php

namespace RKW\RkwRegistration\Controller;

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use RKW\RkwRegistration\Utility\PasswordUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
 * Class PasswordController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PasswordController extends AbstractController
{

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_USER_PASSWORD_RESET = 'afterUserPasswordReset';

    /**
     * initialize
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function initializeAction()
    {
        // intercept disabled user (e.g. after input too often the wrong password)
        if (
            ($this->getFrontendUser())
            && (! FrontendUserUtility::getRemainingLoginAttempts($this->getFrontendUser()))
        ){

            // This redirect with message is necessary because we've no flash message possibilities at this point
            // we also can't add a FlashMessage object, because it's not persisted and would be completely added to the URL
            $this->redirect(
                'index',
                'Auth',
                null,
                [
                    'flashMessageToInject' => LocalizationUtility::translate(
                        'passwordController.error.locked_account',
                        $this->extensionName
                    )
                ],
                $this->settings['users']['loginPid']
            );
        }
    }



    /**
     * action forgot password show
     *
     * @return void
     */
    public function newAction(): void
    {

        if (
            ($this->controllerContext->getFlashMessageQueue()->isEmpty())
            && (! $_POST)
        ) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'passwordController.message.enter_email',
                    $this->extensionName,
                )
            );
        }
    }


    /**
     * action forgot password
     *
     * @param string $username
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function createAction(string $username): void
    {
        if (!$username) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'passwordController.error.no_username', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

            $this->redirect('new');
            return;
        }

        // check if user exists
        /** @var  @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        if ($frontendUser= $this->frontendUserRepository->findOneByUsername(strtolower($username))) {

            // reset password
            $plaintextPassword = PasswordUtility::generatePassword();
            $frontendUser->setPassword(PasswordUtility::saltPassword($plaintextPassword));
            $this->frontendUserRepository->update($frontendUser);

            // dispatcher for e.g. E-Mail
            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                self::SIGNAL_AFTER_USER_PASSWORD_RESET,
                [$frontendUser, $plaintextPassword]
            );
        }

        // Either user exists or not: Send user back with message
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'passwordController.message.new_password', $this->extensionName
            )
        );

        $this->redirect(
            'index',
            'Auth'
        );
    }


    /**
     * action edit
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function editAction(): void
    {
        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

        $this->view->assignMultiple(
            [
                'welcomePid' => intval($this->settings['users']['welcomePid']),
            ]
        );
    }


    /**
     * action update password
     *
     * @param array  $passwordNew
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\PasswordValidator", param="passwordNew")
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function updateAction(array $passwordNew): void
    {
        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

        if ($this->getFrontendUser() instanceof FrontendUser) {

            // set password to the given one
            $this->getFrontendUser()->setPassword(PasswordUtility::saltPassword($passwordNew['first']));
            $this->frontendUserRepository->update($this->getFrontendUser());

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.message.update_password', $this->extensionName
                )
            );

            // redirect
            if ($this->settings['users']['welcomePid']) {
                $this->redirect(
                    'index',
                    'Registration',
                    null,
                    null,
                    $this->settings['users']['welcomePid']
                );
            }

            $this->redirect('index', 'Registration');
            return;
        }

        // SOMETHING WENT WRONG
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.error.password_not_updated', $this->extensionName
            ),
            '',
            AbstractMessage::ERROR
        );

        $this->redirect('edit');

    }



}


