<?php

namespace RKW\RkwRegistration\Controller;

use RKW\RkwRegistration\Tools\Password;

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
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.password_empty', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->redirect('editPassword');

            return;
            //===
        }

        // check if password is valid
        /** @var \RKW\RkwRegistration\Tools\Authentication $authentication */
        $authentication = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Authentication');
        if (
            ($username = $frontendUser->getUsername())
            && ($registeredUser = $authentication->validateUser($username, $passwordOld))
            && ($registeredUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser)
        ) {

            // set password to the given one
            Password::generatePassword($registeredUser, $passwordNew['first']);
            $this->frontendUserRepository->update($registeredUser);

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
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
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'registrationController.error.password_not_updated', $this->extensionName
            ),
            '',
            \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
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
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.login_no_username', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->redirect('passwordForgotShow');

            return;
            //===
        }

        // check if user exists
        if ($registeredUser = $this->frontendUserRepository->findOneByUsername(strtolower($username))) {

            // reset password
            $plaintextPassword = Password::generatePassword($registeredUser);
            $this->frontendUserRepository->update($registeredUser);

            // dispatcher for e.g. E-Mail
            $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_USER_PASSWORD_RESET, array($registeredUser, $plaintextPassword));

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.message.new_password', $this->extensionName
                )
            );

            $this->redirect('index', 'Auth', null, array('noRedirect' => 1));

            return;
            //===
        }

        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'registrationController.error.invalid_username', $this->extensionName
            ),
            '',
            \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
        );

        $this->redirect('passwordForgotShow');
    }


}


