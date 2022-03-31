<?php

namespace RKW\RkwRegistration\Register;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\GuestUserRepository;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;
use RKW\RkwRegistration\Utility\ClientUtility;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * GuestUserRegister
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GuestUserRegister extends FrontendUserRegister
{
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_REGISTER_GUEST = 'afterRegisterGuest';

    /**
     *  Length of token for guest users
     *
     * @const integer
     */
    const GUEST_TOKEN_LENGTH = 20;

    /**
     * GuestUser
     *
     * @var \RKW\RkwRegistration\Domain\Model\GuestUser
     */
    protected $guestUser;

    /**
     * GuestUserRepository
     *
     * @var GuestUserRepository
     */
    protected $guestUserRepository;


    /**
     * @param \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function __construct(GuestUser $guestUser)
    {
        // this works, because a GuestUser is also of type FrontendUser
        parent::__construct($guestUser);

        $this->getGuestUserRepository();

        $this->guestUser = $guestUser;

        if ($this->guestUser->_isNew()) {
            $this->guestUser->setUsername($this->createGuestToken());
            $this->guestUser->setPassword(PasswordUtility::saltPassword(PasswordUtility::generatePassword()));
            // initial add it to repo (not persistent yet)
            // in opposite to the standard FrontendUser we will have no further checks with OptIn or similar
            $this->guestUserRepository->add($this->guestUser);
        }
    }


    /**
     * persistAll
     *
     * Triggers also a signalSlot
     *
     * @param string $category
     * @return void
     */
    public function persistAll($category = null)
    {
        $this->persistenceManager->persistAll();

        $this->getSignalSlotDispatcher()->dispatch(
            __CLASS__,
            self::SIGNAL_AFTER_REGISTER_GUEST . ucfirst($category),
            [$this->guestUser]
        );
    }



    /**
     * creates a valid token for a guest user
     *
     * @return string
     */
    protected function createGuestToken()
    {
        /** @var \RKW\RkwWepstra\Domain\Repository\FrontendUserRepository $guestUserRepository */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        $guestUserRepository = $objectManager->get(GuestUserRepository::class);

        // create a token for anonymous login and check if this token already exists
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        do {
            $token = substr(str_shuffle($characters), 0, self::GUEST_TOKEN_LENGTH);
        } while (count($guestUserRepository->findByUsername($token)));

        return $token;
    }



    /**
     * Returns GuestUserRepository
     *
     * @return GuestUserRepository
     */
    protected function getGuestUserRepository()
    {
        if (!$this->guestUserRepository) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->guestUserRepository = $objectManager->get(GuestUserRepository::class);
        }
        return $this->guestUserRepository;
    }
}
