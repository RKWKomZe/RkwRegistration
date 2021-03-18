<?php

namespace RKW\RkwRegistration\Controller;

use RKW\RkwRegistration\Tools\Password;
use RKW\RkwRegistration\Tools\Authentication;

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
 * Class AuthenticationController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AuthenticationController extends AbstractController
{
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

        // not for already logged in users!
        if ($this->getFrontendUserId()) {

            /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin */
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
                $this->redirect('welcome', null, null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('welcome');
            //===
        }

        // load facebook- object since this needs a special treatment
        /** @var \RKW\RkwRegistration\SocialMedia\Facebook $facebook
        $facebookLogin = null;
        try {

        if ($facebook = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\SocialMedia\\Facebook')) {

        $facebookLogin = $facebook->login();
        }

        } catch (\Exception $e) {
        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Error using Facebook-API for Login. Message: %s', str_replace(array("\n", "\r"), '', $e->getMessage())));
        }*/


        //=============================
        // if user is logged in
        /*
        if (
            (is_object($facebookLogin))
            && ($facebookLogin instanceof \Facebook\GraphNodes\GraphUser)
        ) {

            try {

                /** @var \RKW\RkwRegistration\Tools\Authentication $authentication
                $authentication = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Authentication');

                // load data into model
                /** @var \RKW\RkwRegistration\Domain\Model\FacebookUser $facebookUser
                $facebookUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\FacebookUser');
                $facebookUser->insertData($facebookLogin);

                // check if user exists
                if ($registeredUser = $this->facebookUserRepository->findOneByUsernameInactive(strtolower($facebookUser->getUsername()))) {

                    // check if user is valid (not deactivated)
                    if ($authentication->validateSocialMediaUser($registeredUser)) {

                        // login user
                        $authentication->loginUser($registeredUser);

                        /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin
                        $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
                        if ($url = $redirectLogin->getRedirectUrlLogin()) {
                            $this->redirectToUri($url);
                        }

                        // redirect
                        if ($this->settings['users']['welcomePid']) {
                            $this->redirect('welcome', null, null, null, $this->settings['users']['welcomePid']);
                        }

                        $this->redirect('welcome');
                        //===

                    } else {

                        $this->addFlashMessage(
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'registrationController.error.invalid_socialmedia_login', $this->extensionName
                            ),
                            '',
                            \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                        );
                    }

                    // else register user!
                } else {

                    // register user
                    /** @var \RKW\RkwRegistration\Tools\Registration $registration
                    $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
                    $registeredUser = $registration->register($facebookUser, true);

                    // check if user is valid
                    if ($authentication->validateSocialMediaUser($registeredUser)) {

                        // login user
                        $authentication->loginUser($registeredUser);

                        /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin
                        $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
                        if ($url = $redirectLogin->getRedirectUrlLogin()) {
                            $this->redirectToUri($url);
                        }

                        // redirect new user to login
                        if ($this->settings['users']['welcomePid']) {
                            $this->redirect('welcome', null, null, null, $this->settings['users']['welcomePid']);
                        }

                        $this->redirect('welcome');
                        //===

                    } else {

                        $this->addFlashMessage(
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'registrationController.error.invalid_socialmedia_login', $this->extensionName
                            ),
                            '',
                            \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                        );
                    }
                }

            } catch (\Facebook\Exceptions\FacebookSDKException $e) {

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Error using Facebook-API for Login. Message: %s', str_replace(array("\n", "\r"), '', $e->getMessage())));
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'registrationController.error.facebook_unexpected', $this->extensionName
                    )
                );
            }
        } else {
        */

        /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin */
        $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
        $redirectLogin->setRedirectUrl($this->request);

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
                            'tx_rkwregistration_rkwregistration' => array(
                                'controller' => 'Registration',
                                'action'     => 'registerShow',
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


        if ($logoutMessage) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.message.logout_message', $this->extensionName
                )
            );
        }

        //=============================
        // default
        /*
            $this->view->assignMultiple(
                array(
                    'facebookLogin' => $facebookLogin,
                )
            );
        }*/

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

        // check if XDL is active
        $linkParams = array();
        $linkTargetLogin = '_blank';
        $linkTargetLogout = '_blank';

        if ($this->settings['users']['doXdl']) {

            // build url from pid!
            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

            /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
            $url = $uriBuilder->reset()
                ->setTargetPageUid(intval($GLOBALS["TSFE"]->id))
                ->setCreateAbsoluteUri(true)
                ->setLinkAccessRestrictedPages(true)
                ->setUseCacheHash(false)
                ->build();

            $referrer = '';
            if ($this->settings['users']['redirectPidAfterXdlLogin']) {
                $referrer = $uriBuilder->reset()
                    ->setTargetPageUid(intval($this->settings['users']['redirectPidAfterXdlLogin']))
                    ->setCreateAbsoluteUri(true)
                    ->setLinkAccessRestrictedPages(true)
                    ->setUseCacheHash(false)
                    ->build();

                $linkTargetLogin = '_self';
            }


            if ($this->settings['users']['redirectPidAfterXdlLogout']) {
                $linkTargetLogout = '_self';
            }

            $linkParams = array(
                'tx_rkwregistration_rkwregistration' => array(
                    'xdlUrl' => $url,
                ),
            );

            if ($referrer) {
                $linkParams['tx_rkwregistration_rkwregistration']['referrer'] = $referrer;
            }
        }


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

        // show logout message
        if ($logoutMessage) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.message.logout_message', $this->extensionName
                )
            );

            // show welcome message for normal FE-Users
        } else {
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
                'linkParams'       => $linkParams,
                'linkTargetLogin'  => $linkTargetLogin,
                'linkTargetLogout' => $linkTargetLogout,
                'frontendUser'     => $this->getFrontendUserAnonymous(),
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

        $this->redirect('loginShowExternal');
    }

    /**
     * action loginRedirectAnonymous
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

            $this->redirect('loginShowExternal');
        }

        // generate link for copy&paste
        $url = $this->uriBuilder->reset()
            ->setUseCacheHash(false)
            ->setArguments(
                array('tx_rkwregistration_rkwregistration' =>
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

            $this->redirect('loginShow');
            //===
        }

        if (!$password) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.login_no_password', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->redirect('loginShow');
            //===
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

            /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin */
            $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
            if ($url = $redirectLogin->getRedirectUrlLogin()) {
                $this->redirectToUri($url);
            }

            if ($this->settings['users']['welcomePid']) {
                $this->redirect('welcome', null, null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('welcome');
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

        $this->redirect('loginShow');

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

        // 1. do logout here
        Authentication::logoutUser();

        // 2. check for XDL-logout in cookie- then we log out on foreign page, too!
        /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin */
        $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
        if ($url = $redirectLogin->getRedirectUrlLogout()) {
            $this->redirectToUri($url);
        }

        // 3. redirect to login page including message
        if ($this->settings['users']['loginPid']) {
            $this->redirect('loginShow', null, null, array('logoutMessage' => 1), $this->settings['users']['loginPid']);
        }

        $this->redirect('index');

    }


    /**
     * action logoutAnonymous
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
                    $this->redirect('loginShowExternal', null, null, array('logoutMessage' => 1), $this->settings['users']['loginExternalPid']);
                }

                if ($this->settings['users']['loginPid']) {
                    $this->redirect('loginShow', null, null, array('logoutMessage' => 1), $this->settings['users']['loginPid']);
                }


                $this->redirect('index');

                // redirect normal users to default logout page
            } else {
                $this->redirect('logout', null, null, null, $this->settings['users']['logoutPid']);
            }
        }
    }



    /**
     * action login for cross domain redirect
     *
     * @deprecated
     *
     * @param string $xdlToken
     * @param string $xdlRedirect
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function xdlLoginAction($xdlToken = '', $xdlRedirect = '')
    {
        // if cross domain login is set, we try to login here
        $error = true;
        if ($xdlToken) {

            // 1. validate user by temporary token
            /** @var \RKW\RkwRegistration\Tools\Authentication $authentication */
            $authentication = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Authentication');
            if ($frontendUser = $authentication->validateCrossDomainLoginToken($xdlToken)) {
                $authentication->loginUser($frontendUser);
                $error = false;

                // 2. if there is a valid redirect, go for it!
                if ($xdlRedirect) {

                    /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin */
                    $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
                    if ($url = $redirectLogin->checkRedirectUrl($xdlRedirect)) {
                        $version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
                        if ($version >=  8000000) {
                            // Fix for TYPO3 8.7: Without setting this cookie the user would have to reload the browser manually
                            setcookie('fe_typo_user', $GLOBALS['TSFE']->fe_user->id, null, "/");
                        }
                        $this->redirectToUri($url);
                    } else {
                        $error = true;
                    }
                }
            }
        }

        if ($error) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.xdl_login_error', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }


        $this->redirect('loginShowExternal');

    }


    /**
     * action logout
     *
     * @deprecated
     *
     * @param string $xdlRedirect
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function xdlLogoutAction($xdlRedirect = '')
    {

        // 1. do logout here!
        Authentication::logoutUser();

        // 2. check for redirect page in plugin and override target page if set
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
        if ($this->settings['users']['redirectPidAfterXdlLogout']) {

            // do not redirect to yourself!
            if (intval($this->settings['users']['redirectPidAfterXdlLogout']) == intval($GLOBALS['TSFE']->id)) {
                $xdlRedirect = 0;
            } else {
                $xdlRedirect = $uriBuilder->reset()
                    ->setTargetPageUid(intval($this->settings['users']['redirectPidAfterXdlLogout']))
                    ->setCreateAbsoluteUri(true)
                    ->setLinkAccessRestrictedPages(true)
                    ->setUseCacheHash(false)
                    ->build();
            }

        }

        // 3. do redirect after logout
        $error = false;
        if ($xdlRedirect) {

            /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin */
            $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
            if ($url = $redirectLogin->checkRedirectUrl($xdlRedirect)) {
                $this->redirectToUri($url);
            } else {
                $error = true;
            }
        }

        if ($error) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.xdl_logout_error', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->redirect('loginShowExternal');
        }

        $this->redirect('loginShowExternal', null, null, array('logoutMessage' => 1));

    }
}


