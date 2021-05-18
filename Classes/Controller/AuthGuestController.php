<?php

namespace RKW\RkwRegistration\Controller;

use RKW\RkwRegistration\Service\AuthService as Authentication;
use RKW\RkwRegistration\Utility\RedirectUtility;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * Class AuthGuestController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AuthGuestController extends AbstractController
{
    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;


    /**
     * action login
     * if the current user is not logged in, create one. Unless a token is given, than re-login guest
     *
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function loginAction()
    {
        // ERROR: send back logged user. Nothing to do here
        if ($this->getFrontendUser()) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->redirect('loginExternal', 'Auth');
        }


        // LOGIN: if token is given: Re-login guest user
        if (
            $this->request->hasArgument('token')
            && $token = $this->request->getArgument('token')
        ) {

            /** @var \RKW\RkwRegistration\Service\FrontendUserAuthService $authService */
            $authService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\RKW\RkwRegistration\Service\FrontendUserAuthService::class);
            if ($guestUser = $authService->authGuest($token)) {

                FrontendUserSessionUtility::login($guestUser);

                // redirect user
                if (RedirectUtility::getGuestRedirectUrl()) {
                    $this->redirectToUri(RedirectUtility::getGuestRedirectUrl());
                } else {
                    $this->redirect('index', 'Auth');
                }
            } else {

                FrontendUserSessionUtility::logout();

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'registrationController.error.invalid_anonymous_token', $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );
            }
        }


        // NEW USER: if no token is given, a new guest user will be created
        if (!$this->request->hasArgument('token')) {
            /** @var \RKW\RkwRegistration\Service\RegistrationService $registration */
            $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Service\\RegistrationService');
            $guestUser = $registration->registerGuest();

            FrontendUserSessionUtility::login($guestUser);

            $this->redirect('loginHint');
        }
    }



    /**
     * action loginHint
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */

    public function loginHintAction()
    {

        if (!$this->getFrontendUser() instanceof \RKW\RkwRegistration\Domain\Model\GuestUser) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->redirect('loginExternal', 'Auth');
        }

        // generate link for copy&paste
        $url = $this->uriBuilder->reset()
            ->setUseCacheHash(false)
            ->setArguments(
                array('tx_rkwregistration_loginexternal' =>
                      array(
                          'controller' => 'AuthGuest',
                          'action'     => 'login',
                          'token'      => $this->getFrontendUser()->getUsername(),
                      ),
                )
            )
            ->setCreateAbsoluteUri(true)
            ->build();

        // show link with token to anonymous user
        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'registrationController.message.guest_link',
                $this->extensionName,
                array(
                    intval(intval($this->settings['users']['lifetimeGuest']) / 60 / 60 / 24),
                    $url,
                )
            )
        );

    }



    /**
     * action forget
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */

    public function forgetAction()
    {
        // @toDo: Idea: Guest user with email has lost his token. Send the token again to it's email address, if available?
        // Or should this also done by the normal password forget function?
    }

}


