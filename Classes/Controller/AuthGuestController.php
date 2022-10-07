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
use RKW\RkwRegistration\Register\GuestUserRegisterRegister;
use RKW\RkwRegistration\Utility\RedirectUtility;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use Snowflake\Varnish\Hooks\Frontend;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
     * @var \RKW\RkwRegistration\Registration\FrontendUser\GuestUserRegistration
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $guestUserRegistration;

    

    /**
     * action login
     *
     * @param string $token
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function loginAction(string $token = ''): void
    {
        // send back already logged in user. Nothing to do here
        if (FrontendUserSessionUtility::isUserLoggedIn()) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible',
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
            $this->redirect('index');
        }

        // if no token is given, a new guest user will be created
        // then we use his token
        $newLogin = (bool) $token;
        if ($newLogin) {

            if ($this->guestUserRegistration->setRequest($this->request)->startRegistration()) {
                if ($this->guestUserRegistration->completeRegistration()) {

                    /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
                    if ($frontendUser = $this->guestUserRegistration->getFrontendUserPersisted()) {
                        $token = $frontendUser->getUsername();
                    }
                }
            }
        }

        // do login
        $_POST['logintype'] = 'login';
        $_POST['user'] = $token;
        $_POST['pass'] = '';

        $authService = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $authService->start();

        if (
            !$authService->loginFailure
            && $authService->loginSessionStarted
        ) {

            if ($newLogin) {
                $this->redirect('loginHint');
            } else {
                if ($this->settings['users']['guestRedirectPid']) {

                    /** @var UriBuilder $uriBuilder */
                    $uriBuilder = $this->objectManager->get(UriBuilder::class);
                    $redirectUrl = $uriBuilder->reset()
                        ->setTargetPageUid(intval($this->settings['users']['guestRedirectPid']))
                        ->setCreateAbsoluteUri(true)
                        ->setLinkAccessRestrictedPages(true)
                        ->setUseCacheHash(false)
                        ->buildFrontendUri();

                    $this->redirectToUri($redirectUrl);
                }
            }
        }

        // if something went wrong on the way...
        if ($newLogin) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.anonymous_login_impossible',
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.invalid_anonymous_token',
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
        }

        $this->redirect('index');
    }


    /**
     * action loginHint
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
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


