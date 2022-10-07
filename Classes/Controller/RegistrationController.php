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

use RKW\RkwRegistration\Register\OptInFrontendUser;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
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
     * @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserRegistration;


    /**
     * PersistenceManager
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
     *
     * @param string $flashMessageToInject
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function indexAction(string $flashMessageToInject = ''): void
    {
        parent::indexAction($flashMessageToInject);

        // only for logged in users!
        $this->redirectIfUserNotLoggedInOrGuest();

        // check email!
        $this->redirectIfUserHasNoValidEmail();

        // check basic mandatory fields
        $this->redirectIfUserHasMissingData();

        // check if there are new services where the user has fill out mandatory fields
     //   $services = $this->serviceRepository->findConfirmedByUser($this->getFrontendUser());

        $this->view->assignMultiple(
            [
        //        'services'        => $services,
                'frontendUser'    => $this->getFrontendUser(),
                'editUserPid'     => intval($this->settings['users']['editUserPid']),
                'deleteUserPid'   => intval($this->settings['users']['deleteUserPid']),
                'editPasswordPid' => intval($this->settings['users']['editPasswordPid']),
                'logoutPid'       => intval($this->settings['users']['logoutPid']),
            ]
        );
    }


    /**
     * Takes optIn parameters and checks them
     *
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function optInAction(): void
    {
        $token = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('token'));
        $tokenUser = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('user'));

        $check =  $this->frontendUserRegistration->setFrontendUserToken($tokenUser)
            ->setRequest($this->getRequest())
            ->validateOptIn($token);

        if ($check < 300) {

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
                    [],
                    $this->settings['users']['loginPid']
                );
            }

        } elseif ($check < 400) {

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
            [],
            $this->settings['users']['loginPid']
        );
    }
}
