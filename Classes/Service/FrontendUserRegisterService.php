<?php

namespace RKW\RkwRegistration\Service;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use \RKW\RkwRegistration\Utility\PasswordUtility;
use TYPO3\CMS\Core\Log\LogLevel;

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
class FrontendUserRegisterService extends AbstractService
{
    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * sets some basic data to a new frontendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser A not persistent frontendUser
     * @param integer $enable if the user should be enabled or not
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser A persistent frontendUser
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function createNewFrontendUser($frontendUser = null, $enable = 0)
    {
        // set pid and crdate
        $frontendUser->setPid(intval($this->settings['users']['storagePid']));
        $frontendUser->setCrdate(time());

        // enable or disable
        if ($enable) {
            $frontendUser->setDisable(0);

            // set normal lifetime
            if (intval($this->settings['users']['lifetime'])) {
                $frontendUser->setEndtime(time() + intval($this->settings['users']['lifetime']));
            }

        } else {
            $frontendUser->setDisable(1);

            // set opt-in lifetime
            if (intval($this->settings['users']['daysForOptIn'])) {
                $frontendUser->setEndtime(time() + (intval($this->settings['users']['daysForOptIn']) * 24 * 60 * 60));
            }
        }

        // set languageKey
        if ($this->settings['users']['languageKeyOnRegister']) {
            $frontendUser->setTxRkwregistrationLanguageKey($this->settings['users']['languageKeyOnRegister']);
        }

        // set users server ip-address
        $remoteAddr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ips = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ips[0]) {
                $remoteAddr = filter_var($ips[0], FILTER_VALIDATE_IP);
            }
        }
        $frontendUser->setTxRkwregistrationRegisterRemoteIp($remoteAddr);

        // generate and set password
        $plaintextPassword = PasswordUtility::generatePassword();
        $frontendUser->setPassword(PasswordUtility::saltPassword($plaintextPassword));

        // set user groups
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Service\FrontendUserGroupService $frontendUserGroupService */
        $frontendUserGroupService = $objectManager->get('RKW\\RkwRegistration\\Service\\FrontendUserGroupService');

        $frontendUserGroupService->setUserGroupsOnRegister($frontendUser);

        // add user and persist!
        $this->getFrontendUserRepository()->add($frontendUser);
        $this->getPersistenceManager()->persistAll();

        return $frontendUser;
    }



    /**
     * enables a newly created frontendUser (if not enabled yet)
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return string returns the new created password OR an empty string if user is already enabled
     */
    public function enableNewFrontendUser($frontendUser)
    {
        if (!$frontendUser->getDisable()) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Cannot enable active user.', $frontendUser->getUid()));
            return '';
        }

        $settings = $this->getSettings();

        // generate new password and update user
        $plaintextPassword = PasswordUtility::generatePassword();
        $frontendUser->setPassword(PasswordUtility::saltPassword($plaintextPassword));
        $frontendUser->setDisable(0);

        // set normal lifetime
        $frontendUser->setEndtime(0);
        if (intval($settings['users']['lifetime'])) {
            $frontendUser->setEndtime(time() + intval($settings['users']['lifetime']));
        }

        $this->getFrontendUserRepository()->update($frontendUser);
        $this->getPersistenceManager()->persistAll();

        return $plaintextPassword;
    }



    /**
     * Removes existing account of FE-user
     *
     * @param FrontendUser $frontendUser
     * @param string $category
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function delete(FrontendUser $frontendUser, $category = null)
    {
        // check if user is logged in - only the user himself can delete his account!
        if (FrontendUserSessionUtility::isUserLoggedIn($frontendUser)) {

            FrontendUserSessionUtility::logout();
            $this->getFrontendUserRepository()->remove($frontendUser);
            $this->getPersistenceManager()->persistAll();

            // Signal for e.g. E-Mails or other extensions
            $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_AFTER_DELETING_USER . ucfirst($category), array($frontendUser));

            $this->getLogger()->log(LogLevel::INFO, sprintf('Successfully logged out and deleted user "%s".', strtolower($frontendUser->getUsername())));

            return true;
            //===
        }

        $this->getLogger()->log(LogLevel::WARNING, sprintf('Could not delete user "%s". User is not logged in.', strtolower($frontendUser->getUsername())));

        return false;
        //===
    }



    /**
     * Checks if FE-User has valid email
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @return boolean
     */
    public function validEmail($email = null)
    {

        if ($email) {

            if ($email instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
                $email = $email->getEmail();
            }

            if (
                (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($email)))
                && (strpos(strtolower($email), '@facebook.com') === false)
                && (strpos(strtolower($email), '@twitter.com') === false)
            ) {
                return true;
            }
            //===
        }

        return false;
        //===
    }



    /**
     * Checks if FE-User has valid email
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     */
    public function validEmailUnique($email, $frontendUser)
    {
        if ($email instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
            $email = $email->getEmail();
        }

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\FrontendUserRepository');
        $dbFrontendUser = $frontendUserRepository->findOneByEmailOrUsernameInactive(strtolower($email));

        if (
            (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($email)))
            && (strpos(strtolower($email), '@facebook.com') === false)
            && (strpos(strtolower($email), '@twitter.com') === false)
            && (
                (!$dbFrontendUser)
                || (
                    ($dbFrontendUser)
                    && ($dbFrontendUser->getUsername() == $frontendUser->getUsername()))
            )
        ) {
            return true;
            //===
        }

        return false;
        //===
    }



    /**
     * Checks if FE-User has a a valid username
     *
     * @param string | \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $email
     * @return boolean
     */
    public function validUsername($email)
    {

        if ($email instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser) {
            $email = $email->getEmail();
        }

        if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($email))) {
            return true;
        }

        return false;
    }



    /**
     * converts an feUser array to an object
     *
     * validUsername
     * array $userData
     * @return FrontendUser
     */
    public function convertFrontendUserArrayToObject($userData)
    {
        $frontendUser = $userData;
        if (is_array($userData)) {
            /** @var FrontendUser $frontendUser */
            $frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Domain\\Model\\FrontendUser');
            foreach ($userData as $key => $value) {
                $setter = 'set' . ucfirst(GeneralUtility::camelize($key));
                if (method_exists($frontendUser, $setter)) {
                    $frontendUser->$setter($value);
                }
            }
        }
        return $frontendUser;
    }



    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
