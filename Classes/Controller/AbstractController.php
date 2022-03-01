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
use RKW\RkwRegistration\Register\OptInRegister;
use RKW\RkwRegistration\Register\FrontendUserRegister;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AbstractController extends \RKW\RkwAjax\Controller\AjaxAbstractController
{
    /**
     * ID of logged in FrontendUser
     *
     * @var integer
     */
    protected $frontendUserId;

    /**
     * logged in FrontendUser
     *
     * @var FrontendUser
     */
    protected $frontendUser;


    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;


    /**
     * GuestUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\GuestUserRepository
     * @inject
     */
    protected $guestUserRepository;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;


    /**
     * action index
     * This is the default action
     *
     * @return void
     */
    public function indexAction()
    {
        // nothing to do here - is only a fallback
    }


    /**
     * Remove ErrorFlashMessage
     *
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::getErrorFlashMessage()
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }


    /**
     * Id of logged User
     *
     * @deprecated Will be removed soon. Use FrontendUserSessionUtility::getFrontendUserId() instead
     *
     * @return integer|NULL
     */
    protected function getFrontendUserId()
    {
        // is $GLOBALS set?
        if (
            ($GLOBALS['TSFE'])
            && ($GLOBALS['TSFE']->loginUser)
            && ($GLOBALS['TSFE']->fe_user->user['uid'])
        ) {
            return intval($GLOBALS['TSFE']->fe_user->user['uid']);
        }

        return null;
    }


    /**
     * Returns current logged in user object
     *
     * Hint: Handles GuestUser and normal FrontendUser. Should we split it up?
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|GuestUser|NULL
     */
    protected function getFrontendUser()
    {

        if (!$this->frontendUser) {

            // Guest user?
            $guestUser = $this->guestUserRepository->findByUid(FrontendUserSessionUtility::getFrontendUserId());

            if ($guestUser instanceof GuestUser) {

                $this->frontendUser = $guestUser;
            } else {
                // Or standard user?
                $frontendUser = $this->frontendUserRepository->findByUid(FrontendUserSessionUtility::getFrontendUserId());
                if ($frontendUser instanceof FrontendUser) {
                    $this->frontendUser = $frontendUser;
                }
            }
        }

        return $this->frontendUser;
    }

    /**
     * Returns current logged in user object
     *
     * @deprecated Will be removed soon. Use AbstractController::getFrontendUser() instead and check with instanceof \RKW\RkwRegistration\Domain\Model\GuestUser
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|NULL
     */
    protected function getFrontendUserAnonymous()
    {

        if (!$this->frontendUser) {

            $frontendUser = $this->frontendUserRepository->findByUid(FrontendUserSessionUtility::getFrontendUserId());
            if ($frontendUser instanceof FrontendUser) {
                $this->frontendUser = $frontendUser;
            }
        }

        return $this->frontendUser;
    }


    /**
     * Checks if user has valid email-address and redirects to profile page (if defined)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    protected function hasUserValidEmailRedirect()
    {
        // check if user has a email-address!
        // if not redirect to edit form
        if ($this->getFrontendUser()) {

            /** @var frontendUserRegister $frontendUserRegister */
            $frontendUserRegister = GeneralUtility::makeInstance(FrontendUserRegister::class, $this->getFrontendUser());
            if (!$frontendUserRegister->validateEmail()) {
                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'abstractController.message.enter_valid_email', 'rkw_registration'
                    )
                );

                if ($this->settings['users']['editUserPid']) {
                    $this->redirect('editUser', 'Registration', null, array('noRedirect' => 1), $this->settings['users']['editUserPid']);
                }

                $this->redirect('index');
            }
        }
    }


    /**
     * Checks if user is logged in and redirects to login (if defined)
     *
     * @return void
     */
    protected function hasUserValidLoginRedirect()
    {
        if (!$this->getFrontendUser()) {
            $this->redirectToLogin();
        }
        return;
    }


    /**
     * Checks if user has filled out all mandatory fields and redirects to profile page (if defined)
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    protected function hasUserBasicFieldsRedirect()
    {
        // check if user has a email-address!
        // if not redirect to edit form
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        if ($frontendUser = $this->getFrontendUser()) {

            $requiredFields = array();
            if ($this->settings['users']['requiredFormFields']) {
                $requiredFields = explode(',', str_replace(' ', '', $this->settings['users']['requiredFormFields']));
            }

            foreach ($requiredFields as $field) {
                $getter = 'get' . ucfirst($field);
                if (!$frontendUser->$getter()) {

                    $this->addFlashMessage(
                        LocalizationUtility::translate(
                            'abstractController.message.enter_mandatory_fields', 'rkw_registration'
                        )
                    );

                    if ($this->settings['users']['editUserPid']) {
                        $this->redirect('edit', 'FrontendUser', null, array('noRedirect' => 1), $this->settings['users']['editUserPid']);
                    }

                    $this->redirect('index');
                }
            }
        }
    }


    /**
     * Redirects to login page (if defined)
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    protected function redirectToLogin()
    {

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'abstractController.error.user_not_logged_in', 'rkw_registration'
            ),
            '',
            \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect('index', 'Auth', null, array('noRedirect' => 1), $this->settings['users']['loginPid']);
        }

        $this->redirect('index');
    }


}