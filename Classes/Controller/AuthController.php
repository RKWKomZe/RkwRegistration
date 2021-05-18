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
     * FrontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * SysDomainRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\SysDomainRepository
     * @inject
     */
    protected $sysDomainRepository;

    /**
     * action index
     * contains all login forms
     *
     * @param boolean $logoutMessage
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function indexAction($logoutMessage = false)
    {
        // A Service: Set a register link for the not logged in user
        if (
            ($this->controllerContext->getFlashMessageQueue()->isEmpty())
            && (!$logoutMessage)
        ) {
            // set message including link
            $registerLink = '';
            if ($this->settings['users']['registrationPid']) {

                /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
                $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
                $registerLink = $uriBuilder->reset()
                    ->setTargetPageUid(intval($this->settings['users']['registrationPid']))
                    ->setUseCacheHash(false)
                    ->setArguments(
                        array(
                            'tx_rkwregistration_register' => array(
                                'controller' => 'Registration',
                                'action'     => 'new',
                            ),
                        )
                    )
                    ->build();
            }

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.message.login_message',
                    $this->extensionName,
                    array($registerLink)
                )
            );
        }


        // If user is coming back to index from logoutAction or logoutExternalAction: Show logout message
        if ($logoutMessage) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.message.logout_message', $this->extensionName
                )
            );
        }


        // Else: Do nothing and show login form!

    }


    /**
     * action loginExternal
     *
     * @param boolean $logoutMessage
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function loginExternalAction($logoutMessage = false)
    {
        // if user is a GUEST, make a redirect
        /*
        if (
            $this->getFrontendUser() instanceof \RKW\RkwRegistration\Domain\Model\GuestUser
            && RedirectUtility::getGuestRedirectUrl()
        ) {
            $this->redirectToUri(RedirectUtility::getGuestRedirectUrl());
        }
        */

        // If set: Show logout message
        if ($logoutMessage) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.message.logout_message', $this->extensionName
                )
            );
        } else {
            // Else: show welcome message for normal FE-Users
            if ($frontendUser = $this->getFrontendUser()) {

                if ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\GuestUser) {
                    $this->addFlashMessage(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'authController.message.guest_login_welcome',
                            $this->extensionName
                        )
                    );
                } else {
                    $this->addFlashMessage(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'authController.message.login_welcome',
                            $this->extensionName,
                            array($frontendUser->getUsername())
                        )
                    );
                }
            }
        }

        $this->view->assignMultiple(
            array(
              //  'linkParams'       => $linkParams,
               // 'linkTargetLogin'  => $linkTargetLogin,
               // 'linkTargetLogout' => $linkTargetLogout,
                'frontendUser'     => $this->getFrontendUser(),
                //'myRkwSysDomain'   => $this->sysDomainRepository->findByUid(intval($this->settings['users']['myRkwSysDomain']))
            )
        );
    }


    /**
     * action loginAnonymous
     *
     * @deprecated This function is deprecated and will be removed soon. Use AuthGuestController->loginAction instead.
     *
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function loginAnonymousAction()
    {

        if (!$this->getFrontendUser()) {

            /** @var \RKW\RkwRegistration\Service\AuthService $authentication */
            $authentication = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Service\\AuthService');

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

                        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
                        $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');

                        $sysDomain = $this->sysDomainRepository->findByDomainName(RedirectUtility::getCurrentDomainName())->getFirst();
                        if (
                            $sysDomain instanceof \RKW\RkwRegistration\Domain\Model\SysDomain
                            && $sysDomain->getTxRkwregistrationPageLoginAnonymous() instanceof \RKW\RkwRegistration\Domain\Model\Pages
                        ) {
                            $targetPageUid = $sysDomain->getTxRkwregistrationPageLoginAnonymous()->getUid();
                        }

                        $redirectUrl = $uriBuilder->reset()
                            ->setTargetPageUid(intval($this->settings['users']['anonymousRedirectPid'] ? $this->settings['users']['anonymousRedirectPid'] : $targetPageUid))
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
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'registrationController.error.invalid_anonymous_token', $this->extensionName
                        ),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                    );
                }

            } else {

                // ! CREATE NEW ANONYMOUS USER !

                /** @var \RKW\RkwRegistration\Service\RegistrationService $registration */
                $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Service\\RegistrationService');

                // register anonymous user and login
                $anonymousUser = $registration->registerAnonymous();
                FrontendUserSessionUtility::login($anonymousUser);

                $this->redirect('loginHintAnonymous');
            }

        } else {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
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

        if (!$this->getFrontendUserAnonymous()) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->redirect('loginExternal');
        }

        // generate link for copy&paste
        $url = $this->uriBuilder->reset()
            ->setUseCacheHash(false)
            ->setArguments(
                array('tx_rkwregistration_register' =>
                      array(
                          'controller' => 'Registration',
                          'action'     => 'loginAnonymous',
                          'token'      => $this->getFrontendUserAnonymous()->getUsername(),
                      ),
                )
            )
            ->setCreateAbsoluteUri(true)
            ->build();

        // show link with token to anonymous user
        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'registrationController.message.anonymous_link',
                $this->extensionName,
                array(
                    intval(intval($this->settings['users']['lifetimeAnonymous']) / 60 / 60 / 24),
                    $url,
                )
            )
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
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function loginAction($username, $password)
    {
        if (!$username) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.login_no_username', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->redirect('index');
        }

        if (!$password) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.login_no_password', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->redirect('index');
        }

        /** @var \RKW\RkwRegistration\Service\FrontendUserAuthService $authService */
        $authService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\RKW\RkwRegistration\Service\FrontendUserAuthService::class);
        $authService->setLoginData($username, $password);
        $frontendUserArray = $authService->getUser();
        // do it: check given user data
        $authResult = $authService->authUser(is_array($frontendUserArray) ? $frontendUserArray : []);

        if (
            $authResult === 200
            && ($frontendUser = $this->frontendUserRepository->findOneByUsername(strtolower(trim($username))))
            && ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser)
        ) {

            // ! SUCCESS !

            FrontendUserSessionUtility::login($frontendUser);

            // Get SysDomain entry
            $sysDomain = $this->sysDomainRepository->findByDomainName(RedirectUtility::getCurrentDomainName())->getFirst();

            if (
                $sysDomain instanceof \RKW\RkwRegistration\Domain\Model\SysDomain
                && $sysDomain->getTxRkwregistrationPageLogin() instanceof \RKW\RkwRegistration\Domain\Model\Pages
            ) {
                $this->redirectToUri(RedirectUtility::urlToPageUid($sysDomain->getTxRkwregistrationPageLogin()->getUid()));
            }

            if ($this->settings['users']['welcomePid']) {
                $this->redirect('index', 'Registration', null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('index', 'Registration');
            //===
        }

        // ! FAIL !
        // Handle type of returned error message and send the user back where he is come from

        // user blocked
        if ($authService->getAuthStatusResult() == 2) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.login_blocked', $this->extensionName,
                    array(($this->settings['users']['maxLoginErrors'] ? intval($this->settings['users']['maxLoginErrors']) : 10))
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            // wrong login
        } else {
            if ($authService->getAuthStatusResult() == 1) {

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'registrationController.error.wrong_login', $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );

                // user not found
            } else {

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'registrationController.error.user_not_found', $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );
            }
        }

        $this->redirect('index');
    }



    /**
     * action logout
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function logoutAction()
    {
        // do logout here
        FrontendUserSessionUtility::logout();

        // 1. Redirect according to SysDomain entry
        $sysDomain = $this->sysDomainRepository->findByDomainName(RedirectUtility::getCurrentDomainName())->getFirst();
        if (
            $sysDomain instanceof \RKW\RkwRegistration\Domain\Model\SysDomain
            && $sysDomain->getTxRkwregistrationPageLogout() instanceof \RKW\RkwRegistration\Domain\Model\Pages
        ) {
            $this->redirectToUri(RedirectUtility::urlToPageUid($sysDomain->getTxRkwregistrationPageLogout()->getUid()));
        }

        // 2. redirect to login page (including message)
        if ($this->settings['users']['loginPid']) {
            $this->redirect('index', null, null, array('logoutMessage' => 1), $this->settings['users']['loginPid']);
        }

        // 3. Fallback
        $this->redirect('index');
    }


    /**
     * action logoutExternal
     * Hint: primarily for anonymous users
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function logoutExternalAction()
    {
        // redirect logged-in users to welcome pages
        if ($frontendUser = $this->getFrontendUserAnonymous()) {

            // simply logout anonymous users and show hint
            if ($frontendUser->getTxRkwregistrationIsAnonymous()) {

                FrontendUserSessionUtility::logout();

                // redirect to login page including message
                if ($this->settings['users']['loginExternalPid']) {
                    $this->redirect('loginExternal', null, null, array('logoutMessage' => 1), $this->settings['users']['loginExternalPid']);
                }

                if ($this->settings['users']['loginPid']) {
                    $this->redirect('index', null, null, array('logoutMessage' => 1), $this->settings['users']['loginPid']);
                }

                $this->redirect('index');

                // redirect normal users to default logout page
            } else {
                $this->redirect('logout', null, null, null, $this->settings['users']['logoutPid']);
            }
        }
    }

}


