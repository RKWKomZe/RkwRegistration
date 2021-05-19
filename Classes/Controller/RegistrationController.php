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
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \RKW\RkwRegistration\Utility\FrontendUserSessionUtility;

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

        /** @var \RKW\RkwRegistration\Service\RegistrationService $register */
        $register = GeneralUtility::makeInstance('RKW\\RkwRegistration\\Service\\RegistrationService');
        $check = $register->checkTokens($tokenYes, $tokenNo, $userSha1, $this->request);

        if ($check == 1) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.message.registration_successfull', $this->extensionName
                )
            );

            if ($this->settings['users']['loginPid']) {
                $this->redirect('index', 'Auth', null, array('noRedirect' => 1), $this->settings['users']['loginPid']);
            }

        } elseif ($check == 2) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.message.registration_canceled', $this->extensionName
                )
            );

        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.registration_error', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
        }

        $this->redirect('new');
    }


    /**
     * action register
     * RKW own register action
     *
     * @param FrontendUser $newFrontendUser
     * @ignorevalidation $newFrontendUser
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function newAction(FrontendUser $newFrontendUser = null)
    {
        // not for already logged in users!
        if (FrontendUserSessionUtility::getFrontendUserId()) {

            if ($this->settings['users']['welcomePid']) {
                $this->redirect('index', null, null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('index');
            //===
        }

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
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
     * @param FrontendUser $newFrontendUser
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
    public function createAction(FrontendUser $newFrontendUser, $terms, $privacy)
    {
        if (!$terms) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.accept_terms', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
            $this->forward('new', null, null, array('newFrontendUser' => $newFrontendUser));
            //===
        }

        if (!$privacy) {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.accept_privacy', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
            $this->forward('new', null, null, array('newFrontendUser' => $newFrontendUser));
            //===
        }

        if ($this->frontendUserRepository->findOneByUsernameInactive($newFrontendUser->getEmail())) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.username_exists', $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );

            $this->forward('new', null, null, array('newFrontendUser' => $newFrontendUser));
            //===
        }

        // register new user
        /** @var \RKW\RkwRegistration\Service\RegistrationService $registration */
        $registration = GeneralUtility::makeInstance('RKW\\RkwRegistration\\Service\\RegistrationService');
        $registration->register($newFrontendUser, false, null, null, $this->request);

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.registration_watch_for_email', $this->extensionName
            )
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect('index', 'Auth', null, array('noRedirect' => 1), $this->settings['users']['loginPid']);
        }

        $this->redirect('new');
    }


}