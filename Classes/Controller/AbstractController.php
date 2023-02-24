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

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\ClientUtility;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Validation\FrontendUserValidator;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractController extends \Madj2k\AjaxApi\Controller\AjaxAbstractController
{

    /**
     * @const string
     */
    const SESSION_KEY_REFERRER = 'tx_rkwregistation_referrer';

    /**
     * @var string
     */
    protected string $referrer = '';


    /**
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     */
    protected ?FrontendUser $frontendUser = null;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected FrontendUserRepository $frontendUserRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected PersistenceManager $persistenceManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * initialize
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    public function initializeView(ViewInterface $view): void
    {
        parent::initializeView($view);

        // set referrer in session when calling Auth:Auth->index
        // this is the main login page
        if (
            ($this->getRequest()->getPluginName() == 'Auth')
            && ($this->getRequest()->getControllerName() == 'Auth')
            && ($this->getRequest()->getControllerActionName() == 'index')
        ) {
            // referrer via variable always takes precedence
            if (ClientUtility::isReferrerValid(GeneralUtility::_GP('referrer'))) {
                $this->referrer = GeneralUtility::_GP('referrer');

            // take referrer from $_SERVER - but only if no referrer has been set yet
            } else if (
                (ClientUtility::isReferrerValid($_SERVER['HTTP_REFERER']))
                && (! $GLOBALS['TSFE']->fe_user->getSessionData(self::SESSION_KEY_REFERRER))
            ) {
                // may lead to unwanted behavior - what if I only want to log in to change my personal data?
                // $this->referrer = $_SERVER['HTTP_REFERER'];
            }

            if ($this->referrer) {
                $GLOBALS['TSFE']->fe_user->setAndSaveSessionData(self::SESSION_KEY_REFERRER, $this->referrer);
            }
        }

        // set this->referrer based on session data and assign it to all actions
        $this->referrer = $GLOBALS['TSFE']->fe_user->getSessionData(self::SESSION_KEY_REFERRER) ?: '';
        if ($this->referrer) {
            $this->view->assign('referrer', $this->referrer);
        }
    }


    /**
     * action index
     * This is the default action
     *
     * @param string $flashMessageToInject
     * @return void
     */
    public function indexAction(string $flashMessageToInject = '')
    {
        if ($flashMessageToInject) {
            $this->addFlashMessage(
                $flashMessageToInject,
                '',
                AbstractMessage::ERROR
            );
        }

        // nothing else to do here - is only a fallback
    }


    /**
     * Remove ErrorFlashMessage
     *
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::getErrorFlashMessage()
     */
    protected function getErrorFlashMessage(): bool
    {
        return false;
    }


    /**
     * Returns current logged in user object
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function getFrontendUser(): ?FrontendUser
    {
        return FrontendUserSessionUtility::getLoggedInUser();
    }


    /**
     * Checks if user is logged in and redirects to login (if defined)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function redirectIfUserNotLoggedIn(): void
    {
        if (!$this->getFrontendUser()) {
            $this->redirectToLogin();
        }
    }


    /**
     * Checks if user is logged in as guest and redirects to login (if defined)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function redirectIfUserNotLoggedInOrGuest(): void
    {
        if (!$this->getFrontendUser()) {
            $this->redirectToLogin();

        } else if ($this->getFrontendUser() instanceof GuestUser) {
            $this->redirectToWelcome();
        }
    }


    /**
     * Checks if user is logged in and redirects to welcome page
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    protected function redirectIfUserLoggedIn(): void
    {

        if ($this->getFrontendUser()) {
            $this->redirectToWelcome();
        }
    }


    /**
     * Redirects to login page (if defined)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function redirectToLogin(): void
    {
        // offer a link for users
        if (! $this->getFlashMessageCount()) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'abstractController.error.userNotLoggedIn',
                    'rkw_registration'
                ),
                '',
                AbstractMessage::ERROR
            );
        }

        $this->redirect(
            'index',
            'Auth',
            null,
            [],
            $this->settings['loginPid']?: 0
        );
    }


    /**
     * Redirects user to welcome page or the referer from the login
     *
     * @param bool $newGuestLogin
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    protected function redirectToWelcome(bool $newGuestLogin = false): void
    {

        // try redirecting to referrer
        $this->redirectToReferer($newGuestLogin);

        $pid = (($newGuestLogin && intval($this->settings['welcomeGuestPid']))
            ? intval($this->settings['welcomeGuestPid'])
            : intval($this->settings['welcomePid']));

        // we need a real redirect for the login to be effective
        /** @var  \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $url = $uriBuilder->reset()
            ->setTargetPageUid($pid)
            ->setLinkAccessRestrictedPages(true)
            ->setCreateAbsoluteUri(true)
            ->setUseCacheHash(false)
            ->setArguments(
                [
                    'tx_rkwregistration_' . ($pid ? 'welcome' : 'auth') => [
                        'action' => ($pid ? 'welcome' : 'index'),
                        'controller' => ($pid ? 'FrontendUser' : 'Auth'),
                    ],
                ]
            )
            ->build();

        $this->redirectToUri($url);
    }


    /**
     * Redirects to the referer from the login
     *
     * @param bool $newGuestLogin
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    protected function redirectToReferer(bool $newGuestLogin = false): void
    {
        if (
            (!$newGuestLogin)
            && (ClientUtility::isReferrerValid($this->referrer))
        ){
            $GLOBALS['TSFE']->fe_user->setAndSaveSessionData(self::SESSION_KEY_REFERRER, '');
            $this->redirectToUri($this->referrer);
        }
    }


    /**
     * Checks if user has filled out all mandatory fields and redirects to profile page (if defined)
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup|null $frontendUserGroup
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @TYPO3\CMS\Extbase\Annotation\Validate("RKW\RkwRegistration\Validation\FrontendUserValidator", param="insecureFrontendUser")
     */
    protected function redirectIfUserHasMissingData(FrontendUserGroup $frontendUserGroup = null): void
    {
        // check if user has all relevant fields filled out
        // if not, redirect to edit form
        if ($this->getFrontendUser()) {

            $insecureFrontendUser = clone $this->getFrontendUser();
            if ($frontendUserGroup) {
                $insecureFrontendUser->setTempFrontendUserGroup($frontendUserGroup);
            }

            $frontendUserValidator = $this->objectManager->get(FrontendUserValidator::class);
            $frontendUserValidator->validate($insecureFrontendUser);

            if (! $frontendUserValidator->isValid($insecureFrontendUser)) {

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'abstractController.warning.missingData',
                        'rkw_registration'
                    ),
                    '',
                    AbstractMessage::WARNING
                );

                 if ($this->settings['editUserPid']) {
                    $this->redirect(
                        'edit',
                        'FrontendUser',
                        null,
                        [
                            'frontendUser' => $this->getFrontendUser(),
                            'frontendUserGroup' => $frontendUserGroup
                        ],
                        $this->settings['editUserPid']
                    );
                }

                $this->redirect(
                    'index',
                    'FrontendUser',
                );
            }
        }
    }


    /**
     * Returns the number of flashMessages of all configured plugins
     * @return int
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getFlashMessageCount(): int
    {
        $frameworkSettings = \Madj2k\CoreExtended\Utility\GeneralUtility::getTypoScriptConfiguration(
            'Rkwregistration',
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        $cnt = 0;
        $pluginList = preg_grep('/^tx_rkwregistration_[\d]*/', array_keys($frameworkSettings['plugin.']));
        foreach ($pluginList as $key => $value) {
            $identifier = 'extbase.flashmessages.'. trim($value, '.');
            $cnt += count($this->controllerContext->getFlashMessageQueue($identifier)->getAllMessages());
        }

        return $cnt;
    }


    /**
     * Returns storagePid
     *
     * @param
     * @return int
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getStoragePid(): int
    {
        $storagePid = 0;
        $settings = \Madj2k\CoreExtended\Utility\GeneralUtility::getTypoScriptConfiguration(
            'Rkwregistration',
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        if (intval($settings['persistence']['storagePid'])) {
            $storagePid = intval($settings['persistence']['storagePid']);
        }

        return $storagePid;
    }

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)
                ->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}
