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
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class AuthController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AuthController extends AbstractController
{


    /**
     * action index - contains all login forms
     *
     * @param string $flashMessageToInject
     * @return void
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function indexAction(string $flashMessageToInject = ''): void
    {
        parent::indexAction($flashMessageToInject);

        // offer a link for users
        if (
            (! $this->getFlashMessageCount())
            && (! $_POST)
        ) {

            // offer a logout and re-login for guest-users
            $registerLink = '';
            if ($this->getFrontendUser() instanceof GuestUser) {

                /** @var UriBuilder $uriBuilder */
                $uriBuilder = $this->objectManager->get(UriBuilder::class);
                $registerLink = $uriBuilder->reset()
                    ->setTargetPageUid(intval($this->settings['logoutPid']))
                    ->setUseCacheHash(false)
                    ->setArguments(
                        [
                            'tx_rkwregistration_logoutinternal' => [
                                'action' => 'logout',
                                'redirectAction' => 'index',
                                'redirectController' => 'Auth',
                                'pageUid' => intval($this->settings['loginPid'])
                            ],
                        ]
                    )
                    ->build();

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'authController.warning.loginMessageGuest',
                        $this->extensionName,
                        [$registerLink]
                    ),
                    '',
                    AbstractMessage::WARNING
                );

            // user is logged in as normal user
            } else if ($frontendUser = $this->getFrontendUser()) {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'authController.message.loggedIn',
                        $this->extensionName,
                        [$frontendUser->getUsername()]
                    )
                );

                if ($this->settings['welcomePid']) {
                    $this->redirect(
                        'welcome',
                        'FrontendUser',
                        null,
                        null,
                        $this->settings['welcomePid']
                    );
                }

            // offer a registration link for not logged-in users
            } else if (! $this->getFrontendUser()) {

                if ($this->settings['registrationPid']) {

                    /** @var UriBuilder $uriBuilder */
                    $uriBuilder = $this->objectManager->get(UriBuilder::class);
                    $registerLink = $uriBuilder->reset()
                        ->setTargetPageUid(intval($this->settings['registrationPid']))
                        ->setUseCacheHash(false)
                        ->setArguments(
                            [
                                'tx_rkwregistration_authinternal' => [
                                    'controller' => 'FrontendUser',
                                    'action'     => 'new',
                                ],
                            ]
                        )
                        ->build();
                }

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'authController.notice.loginMessage',
                        $this->extensionName,
                        [$registerLink]
                    ),
                    '',
                    AbstractMessage::NOTICE
                );
            }
        }
    }


    /**
     * action login
     *
     * @param array $login
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\LoginValidator", param="login")
     */
    public function loginAction(array $login): void
    {
        $_POST['logintype'] = 'login';
        $_POST['user'] = $login['username'];
        $_POST['pass'] = $login['password'];

        $authService = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $authService->start();

        if (
            !$authService->loginFailure
            && $authService->loginSessionStarted
        ) {
            if ($this->settings['welcomePid']) {
                $this->redirect(
                    'welcome',
                    'FrontendUser',
                    null,
                    null,
                    $this->settings['welcomePid']
                );
            }

            $this->redirect('index');
        }

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $maxErrors = intval($this->settings['users']['maxLoginErrors']) ?: 10;
        if (
            ($frontendUser = $this->frontendUserRepository->findOneByUsername($login['username']))
            && ($frontendUser->getTxRkwregistrationLoginErrorCount() >= $maxErrors)
        ) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'authController.error.loginBlocked', $this->extensionName,
                    [$maxErrors]
                ),
                '',
                AbstractMessage::ERROR
            );

        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'authController.error.wrongLogin', $this->extensionName
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
     * @param string|null $redirectController Optional redirect parameter
     * @param string|null $extensionName Optional redirect parameter
     * @param array|null $arguments Optional redirect parameter
     * @param int|null $pageUid Optional redirect parameter
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
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

        // do log-out here
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
                'authController.message.logoutMessage', $this->extensionName
            )
        );

        // redirect to login page (including message)
        if ($this->settings['loginPid']) {
            $this->redirect(
                'index',
                null,
                null,
                ['logoutMessage' => 1],
                $this->settings['loginPid']
            );
        }

        $this->redirect('index');
    }

}


