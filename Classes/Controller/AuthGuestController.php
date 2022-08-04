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
 
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Service\AuthFrontendUserService;
use RKW\RkwRegistration\Service\AuthService as Authentication;
use RKW\RkwRegistration\Register\GuestUserRegister;
use RKW\RkwRegistration\Utility\RedirectUtility;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use Snowflake\Varnish\Hooks\Frontend;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     * action login
     * if the current user is not logged in, create one. Unless a token is given, than re-login guest
     *
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function loginAction(): void
    {
        // a) ERROR: send back already logged in user. Nothing to do here
        if (FrontendUserSessionUtility::isUserLoggedIn()) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible', 
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
            $this->redirect('loginExternal', 'Auth');
        }

        // b) NEW USER: if no token is given, a new guest user will be created
        if (!$this->request->hasArgument('token')) {

            /** @var GuestUser $guestUser */
            $guestUser = GeneralUtility::makeInstance(GuestUser::class);

            /** @var GuestUserRegister $guestUserRegister */
            $guestUserRegister = $this->objectManager->get(GuestUserRegister::class, $guestUser);
            $guestUserRegister->setClearanceAndLifetime(true);
            $guestUserRegister->setUserGroupsOnRegister();
            $guestUserRegister->persistAll();

            FrontendUserSessionUtility::login($guestUser);

            $this->redirect('loginHint');
        }

        // c) LOGIN: if token is given: Re-login guest user
        if (
            $this->request->hasArgument('token')
            && $token = $this->request->getArgument('token')
        ) {

            // @toDo: Is AuthFrontendUserService correct? Or would be AuthGuestUserService the right one?

            /** @var AuthFrontendUserService $authService */
            $authService = GeneralUtility::makeInstance(AuthFrontendUserService::class);
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
                    LocalizationUtility::translate(
                        'registrationController.error.invalid_anonymous_token', $this->extensionName
                    ),
                    '',
                    AbstractMessage::ERROR
                );
            }
        }
    }



    /**
     * action loginHint
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function loginHintAction(): void
    {
        // if user has session AND is of type GuestUser
        if (!$this->getFrontendUser() instanceof GuestUser) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

            $this->redirect('loginExternal', 'Auth');
        }

        // generate link for copy&paste
        $url = $this->uriBuilder->reset()
            ->setUseCacheHash(false)
            ->setArguments(
                ['tx_rkwregistration_authexternal' =>
                      [
                          'controller' => 'AuthGuest',
                          'action'     => 'login',
                          'token'      => $this->getFrontendUser()->getUsername(),
                      ],
                ]
            )
            ->setCreateAbsoluteUri(true)
            ->build();

        // show link with token to anonymous user
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.guest_link',
                $this->extensionName,
                [
                    intval(intval($this->settings['users']['guest']['lifetime']) / 60 / 60 / 24),
                    $url,
                ]
            )
        );

        // for security reasons: redirect after creating special login link
        $this->redirect('loginExternal', 'Auth');
    }
}


