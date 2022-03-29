<?php

namespace RKW\RkwRegistration\Controller;

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Service\AuthFrontendUserService;
use RKW\RkwRegistration\Register\OptInRegister;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
 * Class AuthController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AuthController extends AbstractController
{

    
    /**
     * action index - contains all login forms
     *
     * @return void
     */
    public function indexAction(): void
    {
        // A Service: Set a register link for the not logged in user
        if ($this->controllerContext->getFlashMessageQueue()->isEmpty()) {

            // set message including link
            $registerLink = '';

            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            if ($this->getFrontendUser() instanceof GuestUser) {

                /** @var UriBuilder $uriBuilder */
                $uriBuilder = $objectManager->get(UriBuilder::class);
                $registerLink = $uriBuilder->reset()
                    ->setTargetPageUid(intval($this->settings['users']['logoutPid']))
                    ->setUseCacheHash(false)
                    ->setArguments(
                        [
                            'tx_rkwregistration_logoutinternal' => [
                                'action'         => 'logout',
                                'redirectAction' => 'new',
                                'redirectController' => 'FrontendUser',
                                'pageUid' => intval($this->settings['users']['registrationPid'])
                            ],
                        ]
                    )
                    ->build();

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'registrationController.message.login_message_guest',
                        $this->extensionName,
                        [$registerLink]
                    )
                );

            } else {

                if ($this->settings['users']['registrationPid']) {

                    /** @var UriBuilder $uriBuilder */
                    $uriBuilder = $objectManager->get(UriBuilder::class);
                    $registerLink = $uriBuilder->reset()
                        ->setTargetPageUid(intval($this->settings['users']['registrationPid']))
                        ->setUseCacheHash(false)
                        ->setArguments(
                            [
                                'tx_rkwregistration_register' => [
                                    'controller' => 'FrontendUser',
                                    'action'     => 'new',
                                ],
                            ]
                        )
                        ->build();
                }

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'registrationController.message.login_message',
                        $this->extensionName,
                        [$registerLink]
                    )
                );
            }

        }

        $this->view->assignMultiple(
            [
                'frontendUser' => $this->getFrontendUser()
            ]
        );
    }

    

    /**
     * action login
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function loginAction(string $username, string $password): void
    {

        // @toDo: Möglicherweise eine nicht Domain-Gebundene Validierungs-Klasse einfügen, um folgenden Abfragecode auszulagern?
        // siehe "PrivacyValidator" bzw. auch "TermsValidator"

        if (!$username) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.login_no_username', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
            $this->redirect('index');
        }

        if (!$password) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.login_no_password', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
            $this->redirect('index');
        }

        /** @var AuthFrontendUserService $authService */
        $authService = GeneralUtility::makeInstance(AuthFrontendUserService::class);
        $authService->setLoginData($username, $password);
        $frontendUserArray = $authService->getUser();
        
        // do it: check given user data
        $authResult = $authService->authUser(is_array($frontendUserArray) ? $frontendUserArray : []);

        if (
            $authResult === 200
            && ($frontendUser = $this->frontendUserRepository->findOneByUsername(strtolower(trim($username))))
            && ($frontendUser instanceof FrontendUser)
        ) {

            // ! LOGIN SUCCESS !
            // before login: Do logout. The user could currently by logged in as GuestUser
            FrontendUserSessionUtility::logout();
            FrontendUserSessionUtility::login($frontendUser);

            if ($this->settings['users']['welcomePid']) {
                $this->redirect('index', 'Registration', null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('index', 'Registration');
        }

        // ! LOGIN FAILED !
        // Handle type of returned error message and send the user back where he is come from

        // user blocked
        if ($authService->getAuthStatusResult() == 2) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.login_blocked', $this->extensionName,
                    [($this->settings['users']['maxLoginErrors'] ? intval($this->settings['users']['maxLoginErrors']) : 10)]
                ),
                '',
                AbstractMessage::ERROR
            );

        // wrong login
        } else if ($authService->getAuthStatusResult() == 1) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.wrong_login', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

        // user not found
        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.user_not_found', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
        }
        

        $this->redirect('index');
    }


    /**
     * logout
     *
     * Important: This action with redirect is a workaround for setting the "logoutMessage" via flashMessenger
     * Reason: Deleting the FeUser-Session AND setting a FlashMessage in one action DOES NOT WORK! (this kills the message..)
     *
     * @param string $redirectAction Optional redirect parameter
     * @param string $redirectController Optional redirect parameter
     * @param string $extensionName Optional redirect parameter
     * @param array  $arguments Optional redirect parameter
     * @param int   $pageUid Optional redirect parameter
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function logoutAction(
        string $redirectAction = 'logoutRedirect',
        string $redirectController = null,
        string $extensionName = null,
        array $arguments = null, 
        int $pageUid = null
    ) : void {
        
        // do logout here
        FrontendUserSessionUtility::logout();
        $this->redirect($redirectAction, $redirectController, $extensionName, $arguments, $pageUid);
    }


    /**
     * action logoutRedirect
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function logoutRedirectAction(): void
    {
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.logout_message', $this->extensionName
            )
        );

        // 2. redirect to login page (including message)
        if ($this->settings['users']['loginPid']) {
            $this->redirect(
                'index', 
                null, 
                null, 
                ['logoutMessage' => 1], 
                $this->settings['users']['loginPid']
            );
        }

        // 3. Fallback
        $this->redirect('index');
    }

    
    
    
    /**
     * action loginExternal
     *
     * @deprecated Should now be deprecated (every site get its own login)
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function loginExternalAction()
    {
        // if user is a GUEST, make a redirect
        // @toDo: WHY?
        /*
        if (
            $this->getFrontendUser() instanceof \RKW\RkwRegistration\Domain\Model\GuestUser
            && RedirectUtility::getGuestRedirectUrl()
        ) {
            $this->redirectToUri(RedirectUtility::getGuestRedirectUrl());
        }
        */


        // Show welcome message for normal and logged in FrontendUsers
        if ($frontendUser = $this->getFrontendUser()) {

            if ($frontendUser instanceof GuestUser) {
                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'authController.message.guest_login_welcome',
                        $this->extensionName
                    )
                );
            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'authController.message.login_welcome',
                        $this->extensionName,
                        [$frontendUser->getUsername()]
                    )
                );
            }
        }

        $this->view->assignMultiple(
            [
                'frontendUser' => $this->getFrontendUser(),
            ]
        );
    }


    /**
     * action loginAnonymous
     *
     * @deprecated This function is deprecated and will be removed soon. Use AuthGuestController->loginAction instead.
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function loginAnonymousAction()
    {

        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();

        if (!$this->getFrontendUser()) {

            /** @var \RKW\RkwRegistration\Service\AuthService $authentication */
            $authentication = GeneralUtility::makeInstance('RKW\\RkwRegistration\\Service\\AuthService');

            // check for token
            if (
                ($this->request->hasArgument('token'))
                && ($token = $this->request->getArgument('token'))
            ) {

                // ! RE-LOGIN OF ANONYMOUS USER !

                // find anonymous user by token and login
                if ($anonymousUser = $authentication->validateAnonymousUser($token)) {
                    FrontendUserSessionUtility::login($anonymousUser);

                    // redirect user
                    if ($this->settings['users']['anonymousRedirectPid']) {

                        /** @var ObjectManager $objectManager */
                        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
                        /** @var UriBuilder $uriBuilder */
                        $uriBuilder = $objectManager->get(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);

                        $redirectUrl = $uriBuilder->reset()
                            ->setTargetPageUid(intval($this->settings['users']['anonymousRedirectPid']))
                            ->setCreateAbsoluteUri(true)
                            ->setLinkAccessRestrictedPages(true)
                            ->setUseCacheHash(false)
                            ->build();

                        $this->redirectToUri($redirectUrl);

                    } else {
                        $this->redirect('index');
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

            } else {

                // ! CREATE NEW ANONYMOUS USER !

                /** @var OptInRegister $registration */
                $registration = GeneralUtility::makeInstance(OptInRegister::class);

                // register anonymous user and login
                $anonymousUser = $registration->registerAnonymous();
                FrontendUserSessionUtility::login($anonymousUser);

                $this->redirect('loginHintAnonymous');
            }

        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

        }

        $this->redirect('loginExternal');
    }

    /**
     * action loginHintAnonymous
     *
     * @deprecated This function is deprecated and will be removed soon. Use AuthGuestController->loginHintAction instead.
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function loginHintAnonymousAction()
    {

        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();

        if (!$this->getFrontendUserAnonymous()) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

            $this->redirect('loginExternal');
        }

        // generate link for copy&paste
        $url = $this->uriBuilder->reset()
            ->setUseCacheHash(false)
            ->setArguments(
                ['tx_rkwregistration_register' =>
                      [
                          'controller' => 'Registration',
                          'action'     => 'loginAnonymous',
                          'token'      => $this->getFrontendUserAnonymous()->getUsername(),
                      ],
                ]
            )
            ->setCreateAbsoluteUri(true)
            ->build();

        // show link with token to anonymous user
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.anonymous_link',
                $this->extensionName,
                [
                    intval(intval($this->settings['users']['lifetimeAnonymous']) / 60 / 60 / 24),
                    $url,
                ]
            )
        );

    }

    /**
     * action logoutExternal
     * Primarily handles GuestUser. Is forwarding standard FrontendUsers.
     *
     * @deprecated Should now be deprecated (every site get its own login)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function logoutExternalAction()
    {

        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        
        // simply logout anonymous users and show hint
        if ($this->getFrontendUser() instanceof GuestUser) {

            FrontendUserSessionUtility::logout();

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.message.logout_message', $this->extensionName
                )
            );

            // redirect to login page including message
            if ($this->settings['users']['loginExternalPid']) {
                $this->redirect('loginExternal', null, null, null, $this->settings['users']['loginExternalPid']);
            }

            if ($this->settings['users']['loginPid']) {
                $this->redirect('index', null, null, null, $this->settings['users']['loginPid']);
            }

            $this->redirect('index');

            // redirect normal users to default logout page
        } else {
            $this->redirect('logout', null, null, null, $this->settings['users']['logoutPid']);
        }
    }
}


