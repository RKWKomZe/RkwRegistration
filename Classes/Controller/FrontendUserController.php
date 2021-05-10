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
     * action editUser
     *
     * @return void
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function editAction()
    {
        //DebuggerUtility::var_dump($this->controllerContext->getFlashMessageQueue()->getIdentifier() ); exit;


        $registeredUser = null;
        if (!$registeredUser = $this->getFrontendUser()) {

            $this->redirectToLogin();

            return;
            //===
        }

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Domain\Repository\TitleRepository $titleRepository */
        $titleRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\TitleRepository');

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
     * @validate $frontendUser \RKW\RkwRegistration\Validation\FormValidator
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
        $serviceClass = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Service');
        $serviceClass->addUserToAllGrantedGroups($frontendUser);

        if ($frontendUser->getTxRkwregistrationTitle()) {
            $frontendUser->setTxRkwregistrationTitle(\RKW\RkwRegistration\Utilities\TitleUtility::extractTxRegistrationTitle($frontendUser->getTxRkwregistrationTitle()->getName()));
        }

        $this->frontendUserRepository->update($frontendUser);
        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
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
            //===
        }

        /** @var \RKW\RkwRegistration\Tools\Registration $registration */
        $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
        $registration->delete($frontendUser);

        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'registrationController.message.delete_successfull', $this->extensionName
            )
        );

        if ($this->settings['users']['loginPid']) {
            $this->redirect('index', 'Auth', null, array('noRedirect' => 1), $this->settings['users']['loginPid']);
        }

        $this->redirect('index', 'Auth');

        return;
        //===


    }
}


