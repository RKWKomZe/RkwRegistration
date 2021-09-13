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
use RKW\RkwRegistration\Domain\Repository\TitleRepository;
use RKW\RkwRegistration\Service\GroupService;
use RKW\RkwRegistration\Service\OptInService;
use RKW\RkwRegistration\Service\RegisterFrontendUserService;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\TitleUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class FrontendUserController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserController extends AbstractController
{
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
                $this->redirect('index', 'Auth', null, null, $this->settings['users']['welcomePid']);
            }

            $this->redirect('index');
        }

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var TitleRepository $titleRepository */
        $titleRepository = $objectManager->get(TitleRepository::class);

        $titles = $titleRepository->findAllOfType(true, false, false);

        $this->view->assignMultiple(
            array(
                'newFrontendUser'   => $newFrontendUser,
                'termsPid'          => intval($this->settings['users']['termsPid']),
                'titles'            => $titles
            )
        );
    }


    /**
     * action create
     * created a rkw user
     *
     * @param FrontendUser $newFrontendUser
     * @validate $newFrontendUser \RKW\RkwRegistration\Validation\FrontendUserValidator, \RKW\RkwRegistration\Validation\TermsValidator, \RKW\RkwRegistration\Validation\PrivacyValidator
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function createAction(FrontendUser $newFrontendUser)
    {
        /** @var OptInService $optInService */
        $optInService = $this->objectManager->get(OptInService::class);
        $optInService->register($newFrontendUser, false, null, null, $this->request);

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


    /**
     * action editUser
     *
     * @return void
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function editAction()
    {
        $registeredUser = null;
        if (!$registeredUser = $this->getFrontendUser()) {

            $this->redirectToLogin();

            return;
        }

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var TitleRepository $titleRepository */
        $titleRepository = $objectManager->get(TitleRepository::class);

        $titles = $titleRepository->findAllOfType(true, false, false);

        $this->view->assignMultiple(
            array(
                'frontendUser' => $registeredUser,
                'welcomePid'   => intval($this->settings['users']['welcomePid']),
                'titles' => $titles
            )
        );
    }

    /**
     * action update
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @validate $frontendUser \RKW\RkwRegistration\Validation\FrontendUserValidator
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateAction(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {
        // for logged in users only!
        $this->hasUserValidLoginRedirect();

        // all mandatory fields should be checked here.
        // therefore we can finally add the user to all relevant groups now
        $serviceClass = GeneralUtility::makeInstance(GroupService::class);
        $serviceClass->addUserToAllGrantedGroups($frontendUser);

        if ($frontendUser->getTxRkwregistrationTitle()) {
            $frontendUser->setTxRkwregistrationTitle(TitleUtility::extractTxRegistrationTitle($frontendUser->getTxRkwregistrationTitle()->getName()));
        }

        $this->frontendUserRepository->update($frontendUser);
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.update_successfull', $this->extensionName
            )
        );

        if ($this->settings['users']['welcomePid']) {
            $this->redirect('index', 'Registration', null, null, $this->settings['users']['welcomePid']);
        }

        $this->redirect('edit');
    }

    /**
     * action show
     *
     * @return void
     */
    public function showAction()
    {
        // for logged in users only!
        $this->hasUserValidLoginRedirect();

        $this->view->assignMultiple(
            array(
                'welcomePid' => intval($this->settings['users']['welcomePid']),
            )
        );
    }


    /**
     * action delete
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function deleteAction()
    {

        $frontendUser = null;
        if (!$frontendUser = $this->getFrontendUser()) {
            $this->redirectToLogin();

            return;
        }

        /** @var RegisterFrontendUserService $registerFrontendUserService */
        $registerFrontendUserService = GeneralUtility::makeInstance(RegisterFrontendUserService::class, $frontendUser);
        $registerFrontendUserService->delete();

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.delete_successfull', $this->extensionName
            )
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect('index', 'Auth', null, array('noRedirect' => 1), $this->settings['users']['loginPid']);
        }

        $this->redirect('index', 'Auth');

        return;
    }

}


