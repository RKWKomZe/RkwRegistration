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
use RKW\RkwRegistration\Service\AbstractAuthenticationService;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class AuthGuestController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AuthGuestController extends AbstractController
{

    /**
     * @var \RKW\RkwRegistration\Registration\GuestUserRegistration
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
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException
     */
    public function loginAction(string $token = ''): void
    {
        // send back already logged-in user. Nothing to do here
        if (FrontendUserSessionUtility::getLoggedInUserId()) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'authGuestController.error.guestLoginImpossible',
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
            $this->redirect('index');
        }

        // if no token is given, a new guest user will be created
        // then we use his token
        $newLogin = empty($token);
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

        // save storagePid in session because we have no access to the extension settings in the authentication service
        $GLOBALS['TSFE']->fe_user->setAndSaveSessionData(AbstractAuthenticationService::SESSION_KEY, $this->getStoragePid());

        $authService = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $authService->start();

        if (
            !$authService->loginFailure
            && $authService->loginSessionStarted
        ) {
            $this->redirectToWelcome($newLogin);
        }

        // if something went wrong on the way...
        if ($newLogin) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'authGuestController.error.guestLoginImpossible',
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'authGuestController.error.invalidGuestToken',
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
        }

        $this->redirect('index', 'Auth');
    }


}


