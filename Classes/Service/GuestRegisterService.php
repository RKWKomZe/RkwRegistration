<?php

namespace RKW\RkwRegistration\Service;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\GuestUserRepository;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;
use RKW\RkwRegistration\Utility\RemoteUtility;
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
 * FrontendUserRegisterService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GuestRegisterService extends FrontendUserRegisterService
{
    /**
     * GuestUserRepository
     *
     * @var GuestUserRepository
     */
    protected $guestUserRepository;

    /**
     *  Length of token for guest users
     *
     * @const integer
     */
    const GUEST_TOKEN_LENGTH = 20;



    /**
     * @param \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function __construct(GuestUser $guestUser)
    {
        // this works, because a GuestUser is also of type FrontendUser
        parent::__construct($guestUser);

        $this->getGuestUserRepository();

        if ($guestUser->_isNew()) {
            $guestUser->setUsername($this->createGuestToken());
            $guestUser->setPassword(PasswordUtility::saltPassword(PasswordUtility::generatePassword()));
            // initial add it to repo (not persistent yet)
            // in opposite to the standard FrontendUser we will have no further checks with OptIn or similar
            $this->guestUserRepository->add($guestUser);
        }
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
     * persistAll service function
     *
     * @return void
     */
    public function persistAll()
    {
        $this->persistenceManager->persistAll();
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
