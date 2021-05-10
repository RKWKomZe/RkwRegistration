<?php

namespace RKW\RkwRegistration\Controller;

use RKW\RkwRegistration\Tools\Password;
use RKW\RkwRegistration\Tools\Authentication;
use RKW\RkwRegistration\Tools\Redirect;
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

        // @toDo: Folgender Block zunächst auskommentiert. Hat diese nach der Umstellung noch einen Nutzen? Inhalt bzw. Nachricht
        // des Blocks ist der bereits erfolgte Login in eine andere RKW-Instanz:
        // -> Sie wurden via <i>%s</i> eingeloggt. Um den Login über eine andere Seite des RKW Netzwerks zu nutzen, loggen Sie sich bitte zunächst aus und rufen Sie anschließend den Login-Bereich über die gewünschte Seite des RKW Netzwerks erneut auf.


        // not for already logged in users!
        /*
        if ($this->getFrontendUserId()) {

            $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
            if (
                ($xdlCookieDomain = $redirectLogin->getXdlDomain())
                && ($this->request->hasArgument('xdlUrl'))
                && ($xdlParamUrl = $this->request->getArgument('xdlUrl'))
                && ($xdlCookieDomain != $redirectLogin->getDomain($xdlParamUrl))
            ) {

                // show link with token to anonymous user
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'registrationController.message.xdl_login_hint',
                        $this->extensionName,
                        array(
                            $xdlCookieDomain,
                        )
                    )
                );
            }

            if ($this->settings['users']['welcomePid']) {
                $this->redirect('index', 'Registration', null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('index', 'Registration');
            //===
        }
        */


        // A Service: Set a register link for the user
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
     */
    public function loginExternalAction($logoutMessage = false)
    {
        // redirect logged-in users to welcome pages
        if ($frontendUser = $this->getFrontendUserAnonymous()) {

            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

            /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');

            // anonymous users
            if (
                ($frontendUser->getTxRkwregistrationIsAnonymous())
                && ($this->settings['users']['anonymousRedirectPid'])
                && (intval($this->settings['users']['anonymousRedirectPid']) != intval($GLOBALS['TSFE']->id))

            ) {

                $redirectUrl = $uriBuilder->reset()
                    ->setTargetPageUid(intval($this->settings['users']['anonymousRedirectPid']))
                    ->setCreateAbsoluteUri(true)
                    ->setLinkAccessRestrictedPages(true)
                    ->setUseCacheHash(false)
                    ->build();

                $this->redirectToUri($redirectUrl);

                // normal users
            } else {
                if (
                    ($this->settings['users']['doXdl'])
                    && ($this->settings['users']['redirectPidAfterXdlLogin'])
                    && (intval($this->settings['users']['redirectPidAfterXdlLogin']) != intval($GLOBALS['TSFE']->id))
                ) {

                    $redirectUrl = $uriBuilder->reset()
                        ->setTargetPageUid(intval($this->settings['users']['redirectPidAfterXdlLogin']))
                        ->setCreateAbsoluteUri(true)
                        ->setLinkAccessRestrictedPages(true)
                        ->setUseCacheHash(false)
                        ->build();

                    $this->redirectToUri($redirectUrl);
                }
            }
        }

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

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'registrationController.message.xdl_login_welcome',
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
                'frontendUser'     => $this->getFrontendUserAnonymous(),
                //'myRkwSysDomain'   => $this->sysDomainRepository->findByUid(intval($this->settings['users']['myRkwSysDomain']))
            )
        );
    }


    /**
     * action loginAnonymous
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

            /** @var \RKW\RkwRegistration\Tools\Authentication $authentication */
            $authentication = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Authentication');

            // check for token
            if (
                ($this->request->hasArgument('token'))
                && ($token = $this->request->getArgument('token'))
            ) {

                // find anonymous user by token and login
                if ($anonymousUser = $authentication->validateAnonymousUser($token)) {
                    $authentication::loginUser($anonymousUser);

                    // redirect user
                    if ($this->settings['users']['anonymousRedirectPid']) {

                        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
                        $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');

                        $sysDomain = $this->sysDomainRepository->findByDomainName(Redirect::getCurrentDomainName())->getFirst();
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

                    $authentication::logoutUser();

                    $this->addFlashMessage(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'registrationController.error.invalid_anonymous_token', $this->extensionName
                        ),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                    );
                }

            } else {

                /** @var \RKW\RkwRegistration\Tools\Registration $registration */
                $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');

                // register anonymous user and login
                $anonymousUser = $registration->registerAnonymous();
                $authentication::loginUser($anonymousUser);

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


        // check if there is a user that matches and log him in
        /** @var \RKW\RkwRegistration\Tools\Authentication $authenticate */
        $authenticate = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Authentication');
        $validateResult = null;
        if (
            ($validateResult = $authenticate->validateUser(strtolower($username), $password))
            && ($validateResult instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser)
        ) {

            $authenticate->loginUser($validateResult);

            // Get SysDomain entry
            $sysDomain = $this->sysDomainRepository->findByDomainName(Redirect::getCurrentDomainName())->getFirst();
            if (
                $sysDomain instanceof \RKW\RkwRegistration\Domain\Model\SysDomain
                && $sysDomain->getTxRkwregistrationPageLogin() instanceof \RKW\RkwRegistration\Domain\Model\Pages
            ) {
                $this->redirectToUri(Redirect::urlToPageUid($sysDomain->getTxRkwregistrationPageLogin()->getUid()));
            }

            if ($this->settings['users']['welcomePid']) {
                $this->redirect('index', 'Registration', null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('index', 'Registration');
            //===
        }

        // user blocked
        if ($validateResult == 2) {

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
            if ($validateResult == 1) {

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
        Authentication::logoutUser();

        // 1. Redirect according to SysDomain entry
        $sysDomain = $this->sysDomainRepository->findByDomainName(Redirect::getCurrentDomainName())->getFirst();
        if (
            $sysDomain instanceof \RKW\RkwRegistration\Domain\Model\SysDomain
            && $sysDomain->getTxRkwregistrationPageLogout() instanceof \RKW\RkwRegistration\Domain\Model\Pages
        ) {
            $this->redirectToUri(Redirect::urlToPageUid($sysDomain->getTxRkwregistrationPageLogout()->getUid()));
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

                Authentication::logoutUser();

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


