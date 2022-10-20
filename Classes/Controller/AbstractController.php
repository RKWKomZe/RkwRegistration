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
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use RKW\RkwRegistration\Validation\FrontendUserValidator;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractController extends \RKW\RkwAjax\Controller\AjaxAbstractController
{

    /**
     * logged in FrontendUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    protected $frontendUser;


    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserRepository;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $objectManager;


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
     */
    protected function redirectIfUserNotLoggedInOrGuest(): void
    {
        if (!$this->getFrontendUser()
            || (
                ($frontendUser = $this->getFrontendUser())
                && ($frontendUser instanceof GuestUser))
        ){
            $this->redirectToLogin();
        }
    }


    /**
     * Redirects to login page (if defined)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    protected function redirectToLogin(): void
    {

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'abstractController.error.userNotLoggedIn',
                'rkw_registration'
            ),
            '',
            AbstractMessage::ERROR
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect(
                'index',
                'Auth',
                null,
                [],
                $this->settings['users']['loginPid']
            );
        }

        $this->redirect(
            'index',
            'Auth',
        );
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

                 if ($this->settings['users']['editUserPid']) {
                    $this->redirect(
                        'edit',
                        'FrontendUser',
                        null,
                        [
                            'frontendUser' => $this->getFrontendUser(),
                            'frontendUserGroup' => $frontendUserGroup
                        ],
                        $this->settings['users']['editUserPid']
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
        $frameworkSettings = \RKW\RkwBasics\Utility\GeneralUtility::getTyposcriptConfiguration(
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
