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
 * Class RegistrationController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RegistrationController extends AbstractController
{
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_USER_PASSWORD_RESET = 'afterUserPasswordReset';


    /**
     * RegistrationRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     * @inject
     */
    protected $registrationRepository;


    /**
     * ServiceRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\ServiceRepository
     * @inject
     */
    protected $serviceRepository;


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
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function indexAction()
    {

        // only for logged in users!
        $this->hasUserValidLoginRedirect();

        // check email!
        $this->hasUserValidEmailRedirect();

        // check basic mandatory fields
        $this->hasUserBasicFieldsRedirect();

        // check if there are new services where the user has fill out mandatory fields
        $services = $this->serviceRepository->findEnabledByAdminByUser($this->getFrontendUser());

        $this->view->assignMultiple(
            array(
                'services'        => $services,
                'frontendUser'    => $this->getFrontendUser(),
                'editUserPid'     => intval($this->settings['users']['editUserPid']),
                'deleteUserPid'   => intval($this->settings['users']['deleteUserPid']),
                'editPasswordPid' => intval($this->settings['users']['editPasswordPid']),
                'logoutPid'       => intval($this->settings['users']['logoutPid']),
            )
        );
    }








    /**
     * Takes optin parameters and checks them
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function optInAction()
    {

        $tokenYes = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_yes') ? $this->request->getArgument('token_yes') : ''));
        $tokenNo = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_no') ? $this->request->getArgument('token_no') : ''));
        $userSha1 = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('user'));

        /** @var \RKW\RkwRegistration\Tools\Registration $register */
        $register = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
        $check = $register->checkTokens($tokenYes, $tokenNo, $userSha1, $this->request);

        if ($check == 1) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.message.registration_successfull', $this->extensionName
                )
            );

            if ($this->settings['users']['loginPid']) {
                $this->redirect('index', 'Authentication', null, array('noRedirect' => 1), $this->settings['users']['loginPid']);
            }


        } elseif ($check == 2) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.message.registration_canceled', $this->extensionName
                )
            );


        } else {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.registration_error', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }


        $this->redirect('new');
        //====

    }


    /**
     * action register
     * RKW own register action
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $newFrontendUser
     * @ignorevalidation $newFrontendUser
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function newAction(\RKW\RkwRegistration\Domain\Model\FrontendUser $newFrontendUser = null)
    {
        // not for already logged in users!
        if ($this->getFrontendUserId()) {

            if ($this->settings['users']['welcomePid']) {
                $this->redirect('index', null, null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('index');
            //===
        }

        /** @var \RKW\RkwRegistration\Tools\RedirectLogin $redirectLogin */
        $redirectLogin = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\RedirectLogin');
        $redirectLogin->setRedirectUrl($this->request);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Domain\Repository\TitleRepository $titleRepository */
        $titleRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\TitleRepository');

        $titles = $titleRepository->findAllOfType(true, false, false);

        $this->view->assignMultiple(
            array(
                'newFrontendUser' => $newFrontendUser,
                'termsPid'        => intval($this->settings['users']['termsPid']),
                'titles' => $titles
            )
        );
    }


    /**
     * action create
     * created a rkw user
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $newFrontendUser
     * @param integer                                        $terms
     * @param integer                                        $privacy
     * @validate $newFrontendUser \RKW\RkwRegistration\Validation\FormValidator
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function createAction(\RKW\RkwRegistration\Domain\Model\FrontendUser $newFrontendUser, $terms, $privacy)
    {
        if (!$terms) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.accept_terms', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('new', null, null, array('newFrontendUser' => $newFrontendUser));
            //===
        }

        if (!$privacy) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.accept_privacy', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('new', null, null, array('newFrontendUser' => $newFrontendUser));
            //===
        }

        if ($this->frontendUserRepository->findOneByUsernameInactive($newFrontendUser->getEmail())) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.username_exists', $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->forward('new', null, null, array('newFrontendUser' => $newFrontendUser));
            //===
        }

        // register new user
        /** @var \RKW\RkwRegistration\Tools\Registration $registration */
        $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
        $registration->register($newFrontendUser, false, null, null, $this->request);

        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'registrationController.message.registration_watch_for_email', $this->extensionName
            )
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect('index', 'Authentication', null, array('noRedirect' => 1), $this->settings['users']['loginPid']);
        }

        $this->redirect('new');
    }


}