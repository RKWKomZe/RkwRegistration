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
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class FrontendUserUtility
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserUtility
{

    /**
     * converts an array to an frontendUser-object
     * Hint: By default a new created FrontendUser is DISABLED = 1 !
     *
     * @param array $userData
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public static function convertArrayToObject(array $userData): FrontendUser
    {
        /** @var FrontendUser $frontendUser */
        $frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FrontendUser::class);
        foreach ($userData as $key => $value) {
            $setter = 'set' . ucfirst(GeneralUtility::camelize($key));
            if (method_exists($frontendUser, $setter)) {
                $frontendUser->$setter($value);
            }
        }

        return $frontendUser;
    }

    /**
     * converts a frontendUser to an array
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param bool $dirtyOnly
     * @return array
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\TooDirtyException
     */
    public static function convertObjectToArray(FrontendUser $frontendUser, bool $dirtyOnly = false): array
    {
        $result = [];
        foreach ($frontendUser->_getProperties() as $property => $value) {
            if ($dirtyOnly) {

                // if the object is persisted, we can use _isDirty
                if (! $frontendUser->_isNew()){
                    if ($frontendUser->_isDirty($property)) {
                        $result[$property] = $value;
                    }

                // if the object is new, we need to check for empty values
                } else if (! empty($value)){

                    // ignore empty gender
                    if (
                        ($property == 'txRkwregistrationGender')
                        && ($value == 99)
                    ) {
                        continue;
                    }

                    // ignore empty ObjectStorage
                    if (
                        ($value instanceof ObjectStorage)
                        && (count($value) == 0)
                    ) {
                      continue;
                    }

                    $result[$property] = $value;
                }

            } else {
                $result[$property] = $value;
            }
        }

        return $result;
    }


    /**
     * Checks if email is valid
     * Since we're using the email also as username, this function can also be used as "validateUsername"
     *
     * @param string $email
     * @return boolean
     */
    public static function isEmailValid(string $email): bool
    {
        if (
            (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(strtolower($email)))
            && (strpos(strtolower($email), '@facebook.com') === false)
            && (strpos(strtolower($email), '@twitter.com') === false)
        ) {
            return true;
        }

        return false;
    }


    /**
     * Checks if given plaintext password matches the salted one in the database
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param string $password
     * @return boolean
     */
    public static function isPasswordValid(FrontendUser $frontendUser, string $password): bool
    {

        // Get a hashed password instance for the hash stored in db of this user
        $hashInstance = null;
        $passwordHashInDatabase = $frontendUser->getPassword();

        /** @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory $saltFactory */
        $saltFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(PasswordHashFactory::class);
        try {
            $hashInstance = $saltFactory->get($passwordHashInDatabase, 'FE');
        } catch (InvalidPasswordHashException $invalidPasswordHashException) {
            // nothing to do here - just give up!
        }

        // We found a hash class that can handle this type of hash
        if ($hashInstance instanceof PasswordHashInterface) {
            if ($hashInstance->checkPassword($password, $passwordHashInDatabase)) {
               return true;
            }
        }

        return false;
    }


    /**
     * Checks if an email address is unique and returns true if email is not used by another frontendUser
     *
     * @param string $username
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     * @return bool
     */
    public static function isUsernameUnique(string $username, FrontendUser $frontendUser = null): bool
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserDb */
        $frontendUserDb = $frontendUserRepository->findOneByUsernameIncludingDisabled($username);

        if (!$frontendUserDb) {
            return true;
        }

        // check if the given frontendUser has the same uid
        // this is relevant in edit-mode!
        if (
            ($frontendUser)
            && ($frontendUserDb->getUid() == $frontendUser->getUid())
        ) {
            return true;
        }

        return false;
    }


    /**
     * Get remaining login-attempts based on configuration
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return int
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function getRemainingLoginAttempts(FrontendUser $frontendUser): int
    {
        $settings = self::getSettings();
        $maxLoginErrors = intval($settings['users']['maxLoginErrors']) ?: 10;
        return max($maxLoginErrors - $frontendUser->getTxRkwregistrationLoginErrorCount(), 0);
    }


    /**
     * Get remaining login-attempts based on configuration with an integer value
     *
     * @param int $attempts
     * @return int
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function getRemainingLoginAttemptsNumeric(int $attempts): int
    {
        $frontendUser = GeneralUtility::makeInstance(\RKW\RkwRegistration\Domain\Model\FrontendUser::class);
        $frontendUser->setTxRkwregistrationLoginErrorCount($attempts);
        return self::getRemainingLoginAttempts($frontendUser);
    }


    /**
     * Returns all mandatory properties of user
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function getMandatoryFields(FrontendUser $frontendUser = null): array
    {
        $mandatoryFields = [];
        $settings = self::getSettings();

        if (!$frontendUser) {
            $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);
        }

        // get default mandatory fields
        if ($settings['users']['requiredFormFields']) {

            $mandatoryFieldsTemp = GeneralUtility::trimExplode(',',$settings['users']['requiredFormFields']);
            foreach($mandatoryFieldsTemp as $field) {
                $field = GeneralUtility::camelize($field);
                if (property_exists($frontendUser, $field)) {
                    $mandatoryFields[] = $field;
                }
            }
        }

        if ($frontendUser->getUsergroup()) {

            /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $userGroup */
            foreach($frontendUser->getUsergroup() as $userGroup) {
                $mandatoryFields = array_merge(
                    $mandatoryFields,
                    FrontendUserGroupUtility::getMandatoryFields($userGroup)
                );
            }
        }

        // also check for temporary groups!
        if ($frontendUser->getTempFrontendUserGroup()) {
            $mandatoryFields = array_merge(
                $mandatoryFields,
                FrontendUserGroupUtility::getMandatoryFields(
                    $frontendUser->getTempFrontendUserGroup()
                )
            );
        }

        return $mandatoryFields;
    }



    /**
     * Returns TYPO3 settings
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected static function getSettings(): array
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration');
    }

}
