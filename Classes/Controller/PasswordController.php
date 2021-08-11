<?php

namespace RKW\RkwRegistration\Controller;

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Service\AuthFrontendUserService;
use RKW\RkwRegistration\Utility\PasswordUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * action edit
     *
     * @return void
     */
    public function editAction()
    {
        // for logged in users only!
        $this->hasUserValidLoginRedirect();

        $this->view->assignMultiple(
            array(
                'welcomePid' => intval($this->settings['users']['welcomePid']),
            )
        );
    }


    /**
     * action update password
     *
     * @param string $passwordOld
     * @param array  $passwordNew
     * @validate $passwordNew \RKW\RkwRegistration\Validation\PasswordValidator
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateAction($passwordOld, $passwordNew)
    {
        // check if user is logged in
        $frontendUser = null;
        if (!$frontendUser = $this->getFrontendUser()) {
            $this->redirectToLogin();

            return;
            //===
        }

        if (!$passwordOld) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.password_empty', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

            $this->redirect('editPassword');

            return;
            //===
        }

        // check if password is valid
        /** @var AuthFrontendUserService $authentication */
        $authentication = GeneralUtility::makeInstance(AuthFrontendUserService::class);
        if (
            ($username = $frontendUser->getUsername())
            && ($registeredUser = $authentication->validateUser($username, $passwordOld))
            && ($registeredUser instanceof FrontendUser)
        ) {
            // set password to the given one
            $frontendUser->setPassword(PasswordUtility::saltPassword($passwordNew['first']));

            $this->frontendUserRepository->update($registeredUser);

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.message.update_password', $this->extensionName
                )
            );

            // redirect
            if ($this->settings['users']['welcomePid']) {
                $this->redirect('index', 'Registration', null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('index', 'Registration');

            return;
            //===

        }

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.error.password_not_updated', $this->extensionName
            ),
            '',
            AbstractMessage::ERROR
        );

        $this->redirect('editPassword');

    }


    /**
     * action forgot password show
     *
     * @return void
     */
    public function newAction()
    {
        // nothing to do here
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
    public function createAction($username)
    {

        if (!$username) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.login_no_username', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

            $this->redirect('passwordForgotShow');

            return;
            //===
        }

        // check if user exists
        if ($registeredUser = $this->frontendUserRepository->findOneByUsername(strtolower($username))) {

            // reset password
        //    $plaintextPassword = PasswordUtility::generate($registeredUser);
            $plaintextPassword = PasswordUtility::generatePassword();
            $registeredUser->setPassword(PasswordUtility::saltPassword($plaintextPassword));
            $this->frontendUserRepository->update($registeredUser);

            // dispatcher for e.g. E-Mail
            $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_PASSWORD_RESET, array($registeredUser, $plaintextPassword));

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.message.new_password', $this->extensionName
                )
            );

            $this->redirect('index', 'Auth', null, array('noRedirect' => 1));

            return;
            //===
        }

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.error.invalid_username', $this->extensionName
            ),
            '',
            AbstractMessage::ERROR
        );

        $this->redirect('passwordForgotShow');
    }


}


