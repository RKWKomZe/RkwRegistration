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
use RKW\RkwRegistration\Register\GroupRegister;
use RKW\RkwRegistration\Register\OptInRegister;
use RKW\RkwRegistration\Register\FrontendUserRegister;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\TitleUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * TitleRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\TitleRepository
     * @inject
     */
    protected $titleRepository;
    
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
    public function newAction(FrontendUser $newFrontendUser = null): void
    {
        // not for already logged in users!
        if (FrontendUserSessionUtility::getFrontendUserId()) {

            if ($this->settings['users']['welcomePid']) {
                $this->redirect(
                    'index', 
                    'Auth', 
                    null, 
                    null, 
                    $this->settings['users']['welcomePid']
                );
            }

            $this->redirect('index');
        }
        
        $titles = $this->titleRepository->findAllOfType(true, false, false);

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
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @validate $newFrontendUser \RKW\RkwRegistration\Validation\FrontendUserValidator
     * @validate \RKW\RkwRegistration\Validation\TermsValidator 
     * @validate \RKW\RkwRegistration\Validation\PrivacyValidator
     */
    public function createAction(FrontendUser $newFrontendUser): void
    {
        /** @var OptInRegister $optInRegister */
        $optInRegister = $this->objectManager->get(OptInRegister::class);
        $optInRegister->register(
            $newFrontendUser, 
            false, 
            null, 
            null, 
            $this->request
        );

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.registration_watch_for_email', 
                $this->extensionName
            )
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect(
                'index', 
                'Auth', 
                null, 
                ['noRedirect' => 1], 
                $this->settings['users']['loginPid']
            );
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
    public function editAction(): void
    {

        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

        $titles = $this->titleRepository->findAllOfType(true, false, false);

        $this->view->assignMultiple(
            array(
                'frontendUser' => $this->getFrontendUser(),
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
    public function updateAction(FrontendUser $frontendUser): void
    {
        
        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

        // all mandatory fields should be checked here.
        // therefor we can finally add the user to all relevant groups now
        $serviceClass = GeneralUtility::makeInstance(GroupRegister::class);
        $serviceClass->addUserToAllGrantedGroups($frontendUser);

        if ($frontendUser->getTxRkwregistrationTitle()) {
            $frontendUser->setTxRkwregistrationTitle(
                TitleUtility::extractTxRegistrationTitle(
                    $frontendUser->getTxRkwregistrationTitle()->getName()
                )
            );
        }

        $this->frontendUserRepository->update($frontendUser);
        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.update_successfull', $this->extensionName
            )
        );

        if ($this->settings['users']['welcomePid']) {
            $this->redirect(
                'index', 
                'Registration', 
                null, 
                null, 
                $this->settings['users']['welcomePid']
            );
        }

        $this->redirect('edit');
    }


    /**
     * action show
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function showAction(): void
    {
        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

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
    public function deleteAction(): void
    {

        // for logged in users only!
        $this->redirectIfUserNotLoggedIn();

        /** @var FrontendUserRegister $frontendUserRegister */
        $frontendUserService = GeneralUtility::makeInstance(
            frontendUserRegister::class,
            $this->getFrontendUser()
        );
        $frontendUserService->delete();

        $this->addFlashMessage(
            LocalizationUtility::translate(
                'registrationController.message.delete_successfull', 
                $this->extensionName
            )
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect(
                'index', 
                'Auth', 
                null, 
                ['noRedirect' => 1], 
                $this->settings['users']['loginPid']
            );
        }

        $this->redirect('index', 'Auth');

    }

}


