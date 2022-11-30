<?php

namespace RKW\RkwRegistration\Utility;

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

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\GuestUserRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class FrontendUserSessionUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserSessionUtility
{

    /**
     * Simulates a frontend-login - this is NOT a real login!!!
     * !!! WARNING !!! This method is only to be used for previews or testing purposes!
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup
     * @return bool
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @see \TYPO3\CMS\Adminpanel\Modules\PreviewModule::initializeFrontendPreview()
     */
    public static function simulateLogin (
        FrontendUser $frontendUser,
        FrontendUserGroup $frontendUserGroup
    ): bool {

        // no login simulation if a user is logged in
        // this would kill his session!
        if (self::getLoggedInUserId()) {
            return false;
        }

        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Context::class);

        $GLOBALS['TSFE']->fePreview = 1;
        $GLOBALS['TSFE']->clear_preview();
        $GLOBALS['TSFE']->simUserGroup = $frontendUserGroup->getUid();

        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUserAuthentication */
        $frontendUserAuthentication = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $frontendUserAuthentication->user = [$frontendUserAuthentication->userid_column => $frontendUser->getUid()];
        $frontendUserAuthentication->user[$frontendUserAuthentication->usergroup_column] = $frontendUserGroup->getUid();

        // New random session-$id is made
        $frontendUserAuthentication->id = $frontendUserAuthentication->createSessionId();
        $frontendUserAuthentication->newSessionID = true;

        // Load groupData
        $frontendUserAuthentication->fetchGroupData();

        $GLOBALS['TSFE']->fe_user = $frontendUserAuthentication;
        $context->setAspect('frontend.user',
            GeneralUtility::makeInstance(
                UserAspect::class,
                $frontendUserAuthentication,
                [$frontendUserGroup->getUid()]
            )
        );

        return $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }


    /**
     * Performs a logout for the active frontendUser - this is a REAL logout-method
     *
     * @return bool
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public static function logout(): bool
    {

        // no logout if no user is logged in
        if (!self::getLoggedInUserId()) {
            return false;
        }

        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Context::class);

        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUserAuthentication */
        $frontendUserAuthentication = $GLOBALS['TSFE']->fe_user;
        $frontendUserAuthentication->logoff();

        $GLOBALS['TSFE']->fe_user = $frontendUserAuthentication;

        $context->setAspect('frontend.user',
            GeneralUtility::makeInstance(
                UserAspect::class,
                $frontendUserAuthentication,
                []
            )
        );

        return !$context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }


    /**
     * Id of logged-in User
     *
     * @return integer
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public static function getLoggedInUserId(): int
    {
        // is user logged in
        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Context::class);
        if (
            ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn'))
            && ($frontendUserId = $context->getPropertyFromAspect('frontend.user', 'id'))
        ){
            return intval($frontendUserId);
        }

        return 0;
    }

    /**
     * Id of logged-in User
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public static function getLoggedInUser(): ?FrontendUser
    {

        if ($uid = self::getLoggedInUserId()) {

            /** @var }TYPO3\CMS\Extbase\Object\ObjectManager\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

            /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
            $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);

            /** @var \RKW\RkwRegistration\Domain\Repository\GuestUserRepository $guestUserRepository */
            $guestUserRepository = $objectManager->get(GuestUserRepository::class);

            /** @var \RKW\RkwRegistration\Domain\Model\GuestUser $frontendUser */
            if ($frontendUser = $guestUserRepository->findByIdentifier($uid)) {
                return $frontendUser;
            }

            /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
            if ($frontendUser = $frontendUserRepository->findByIdentifier($uid)) {
                return $frontendUser;
            }
        }

        return null;
    }


    /**
     * Is a frontendUser logged in?
     *
     * @return bool
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public static function isLoggedIn(): bool
    {
        return (bool) self::getLoggedInUserId();
    }


    /**
     * Checks if a given frontendUser is logged in
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public static function isUserLoggedIn(FrontendUser $frontendUser): bool
    {
        // check which id is logged in and compare it with given user
        if ($frontendUserId = self::getLoggedInUserId()) {
            if ($frontendUser->getUid() == $frontendUserId) {
                return true;
            }
        }

        return false;
    }
}
