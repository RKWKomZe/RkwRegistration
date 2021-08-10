<?php

namespace RKW\RkwRegistration\Controller;

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Model\Pages;
use RKW\RkwRegistration\Domain\Model\SysDomain;
use RKW\RkwRegistration\Service\AuthService as Authentication;
use RKW\RkwRegistration\Service\FrontendUserAuthService;
use RKW\RkwRegistration\Utility\RedirectUtility;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
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
    public function indexAction()
    {
        // A Service: Set a register link for the not logged in user
        if ($this->controllerContext->getFlashMessageQueue()->isEmpty()) {

            // set message including link
            $registerLink = '';
            if ($this->settings['users']['registrationPid']) {

                /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

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
                LocalizationUtility::translate(
                    'registrationController.message.login_message',
                    $this->extensionName,
                    array($registerLink)
                )
            );
        }


        // Else: Do nothing and show login form!
    }


    /**
     * action loginExternal
     *
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

            if ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\GuestUser) {
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
                        array($frontendUser->getUsername())
                    )
                );
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

                        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
                        $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');

                        $sysDomain = $this->sysDomainRepository->findByDomainName(RedirectUtility::getCurrentDomainName())->getFirst();
                        if (
                            $sysDomain instanceof SysDomain
                            && $sysDomain->getTxRkwregistrationPageLoginAnonymous() instanceof Pages
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
                        LocalizationUtility::translate(
                            'registrationController.error.invalid_anonymous_token', $this->extensionName
                        ),
                        '',
                        AbstractMessage::ERROR
                    );
                }

            } else {

                // ! CREATE NEW ANONYMOUS USER !

                /** @var \RKW\RkwRegistration\Service\RegistrationService $registration */
                $registration = GeneralUtility::makeInstance('RKW\\RkwRegistration\\Service\\RegistrationService');

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
            LocalizationUtility::translate(
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


        // @toDo: Möglicherweise eine nicht Domain-Gebundene Validierungs-Klasse einfügen, um folgenden Abfragecode auszulagern?

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

        /** @var FrontendUserAuthService $authService */
        $authService = GeneralUtility::makeInstance(FrontendUserAuthService::class);
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

            FrontendUserSessionUtility::login($frontendUser);

            // Get SysDomain entry
            $sysDomain = $this->sysDomainRepository->findByDomainName(RedirectUtility::getCurrentDomainName())->getFirst();

            if (
                $sysDomain instanceof SysDomain
                && $sysDomain->getTxRkwregistrationPageLogin() instanceof Pages
            ) {
                $this->redirectToUri(RedirectUtility::urlToPageUid($sysDomain->getTxRkwregistrationPageLogin()->getUid()));
            }

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
                    array(($this->settings['users']['maxLoginErrors'] ? intval($this->settings['users']['maxLoginErrors']) : 10))
                ),
                '',
                AbstractMessage::ERROR
            );

            // wrong login
        } else {
            if ($authService->getAuthStatusResult() == 1) {

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

        // Important: This redirect is a workaround for setting the "logoutMessage" via flashMessenger
        // Reason: Deleting the whole FeUser Session and setting a FlashMessage in one action does not work!
        $this->redirect('logoutRedirect');
    }


    /**
     * action logoutRedirect
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function logoutRedirectAction()
    {
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.logout_message', $this->extensionName
            )
        );

        // 1. Redirect according to SysDomain entry
        $sysDomain = $this->sysDomainRepository->findByDomainName(RedirectUtility::getCurrentDomainName())->getFirst();
        if (
            $sysDomain instanceof SysDomain
            && $sysDomain->getTxRkwregistrationPageLogout() instanceof Pages
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
     * Primarily handles GuestUser. Is forwarding standard FrontendUsers.
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function logoutExternalAction()
    {
        // simply logout anonymous users and show hint
        if ($this->getFrontendUser() instanceof GuestUser) {

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


