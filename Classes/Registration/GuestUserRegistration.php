<?php

namespace RKW\RkwRegistration\Registration;

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

use Madj2k\CoreExtended\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Exception;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;

/**
 * GuestUserRegistration
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GuestUserRegistration extends AbstractRegistration
{

    /**
     *
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function __construct()
    {
        parent::__construct();

        /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $guestUser */
        $guestUser = GeneralUtility::makeInstance(GuestUser::class);
        $this->setFrontendUser($guestUser);
    }


    /**
     * Registers new guestUser
     *
     * @return bool
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException
     * @api
     */
    public function startRegistration(): bool
    {
        // check for frontendUser-object
        if (!$this->getFrontendUser()) {
            throw new Exception('No frontendUser-object set.', 1661332326);
        }

        // check if a user is logged in. In this case no registration is needed!
        if (FrontendUserSessionUtility::getLoggedInUserId()) {
            throw new Exception('Registration is not necessary for logged in users.', 1661332376);
        }

        $frontendUser = $this->getFrontendUser();
        if ($frontendUser->_isNew()) {
            $this->frontendUser->setPassword(PasswordUtility::saltPassword(PasswordUtility::generatePassword()));

            $this->getContextAwareFrontendUserRepository()->add($frontendUser);
            $this->persistenceManager->persistAll();

            return true;
        }

        return false;
    }

}
