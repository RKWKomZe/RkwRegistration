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

use RKW\RkwRegistration\Register\OptInRegister;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
    public function indexAction(): void
    {
        // only for logged in users!
        $this->redirectIfUserNotLoggedIn();

        // check email!
        $this->redirectIfUserHasNoValidEmail();

        // check basic mandatory fields
        $this->redirectIfUserHasMissingData();

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
     * Takes optIn parameters and checks them
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
    public function optInAction(): void
    {
        $tokenYes = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_yes') ? $this->request->getArgument('token_yes') : ''));
        $tokenNo = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_no') ? $this->request->getArgument('token_no') : ''));
        $userSha1 = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('user'));

        /** @var OptInRegister $optInRegister */
        $optInRegister = GeneralUtility::makeInstance(OptInRegister::class);
        $check = $optInRegister->process($tokenYes, $tokenNo, $userSha1, $this->request);

        if ($check == 1) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.message.registration_successfull', 
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

        } elseif ($check == 2) {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.message.registration_canceled', 
                    $this->extensionName
                )
            );

        } else {

            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'registrationController.error.registration_error', 
                    $this->extensionName
                ),
                '',
                AbstractMessage::ERROR
            );
        }

        $this->redirect(
            'index', 
            'Auth', 
            null, 
            ['noRedirect' => 1], 
            $this->settings['users']['loginPid']
        );
    }
}