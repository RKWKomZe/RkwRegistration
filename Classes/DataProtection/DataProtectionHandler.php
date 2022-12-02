<?php
namespace RKW\RkwRegistration\DataProtection;

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

use RKW\RkwRegistration\Domain\Model\EncryptedData;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Exception;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use Madj2k\CoreExtended\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class DataProtectionHandler
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DataProtectionHandler
{
    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\EncryptedDataRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $encryptedDataRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $objectManager;


    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @var string
     */
    protected $encryptionKey;


    /***
     * @param string $encryptionKey
     * @return void
     */
    public function setEncryptionKey (string $encryptionKey): void
    {
        $this->encryptionKey = $encryptionKey;
    }


    /**
     * Deletes expired and disabled frontend users after x days (only sets deleted = 1)
     *
     * @param int $daysExpired
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @return int
     */
    public function deleteAllExpiredAndDisabled (int $daysExpired = 7): int
    {
        $cnt = 0;
        $settings = $this->getSettings();

        if (
            ($frontendUserList = $this->frontendUserRepository->findExpired($daysExpired))
            && (count($frontendUserList))
        ) {
            /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
            foreach ($frontendUserList as $frontendUser) {
                $this->frontendUserRepository->remove($frontendUser);
                $this->getLogger()->log(LogLevel::INFO, sprintf(
                    'Deleted expired or disabled user with id %s.',
                    $frontendUser->getUid())
                );
                $cnt++;
            }
            $this->persistenceManager->persistAll();
        }

        return $cnt;
    }


    /**
     * Anonymize and encrypts all data of a frontend user that has been deleted x days before
     *
     * Also includes user-related data if configured
     *
     * @param int $anonymizeAfterDays
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \RKW\RkwRegistration\Exception
     * @return bool
     */
    public function anonymizeAndEncryptAll (int $anonymizeAfterDays = 30): bool
    {
        $settings = $this->getSettings();
        $mappings = $settings['dataProtection']['classes'];

        if (
            (is_array($mappings))
            && (count($mappings))
            && ($frontendUserList = $this->frontendUserRepository->findDeleted($anonymizeAfterDays))
            && (count($frontendUserList))
        ) {

            /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
            foreach ($frontendUserList as $frontendUser) {

                $updates = [];
                $adds = [];
                foreach ($mappings as $modelClassName => $propertyMap) {

                    // anonymize and encrypt the frontend user
                    if ($modelClassName == 'RKW\RkwRegistration\Domain\Model\FrontendUser') {

                        /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
                        if (
                            ($encryptedData = $this->encryptObject($frontendUser, $frontendUser))
                            && ($this->anonymizeObject($frontendUser, $frontendUser))
                        ){

                            $frontendUser->setTxRkwregistrationDataProtectionStatus(1);

                            // store changes temporarily. Only if no error occurs we will persist it
                            $updates[] = [
                                'repository' =>$this->frontendUserRepository,
                                'data' => $frontendUser
                            ];
                            $adds[] = $encryptedData;

                            $this->getLogger()->log(LogLevel::INFO, sprintf('Anonymized and encrypted data of main-model "%s" of user-id %s.', $modelClassName, $frontendUser->getUid()));

                        } else {
                            $this->getLogger()->log(LogLevel::WARNING, sprintf('Could not anonymize and encrypt data of main-model "%s" of user-id %s.', $modelClassName, $frontendUser->getUid()));
                            continue(2);
                        }

                    } else {

                        /** @var Repository $repository */
                        if (
                            ($frontendUserProperty = $this->getFrontendUserPropertyByModelClassName($modelClassName))
                            && ($repository = $this->getRepositoryByModelClassName($modelClassName))
                        ) {

                            // find all by mappingProperty and frontendUser
                            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result */
                            if ($result = $this->getRepositoryResults($repository, $frontendUserProperty, $frontendUser->getUid())) {

                                /** @var \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $object */
                                foreach ($result as $object) {

                                    /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
                                    if (
                                        ($encryptedData = $this->encryptObject($object, $frontendUser))
                                        && ($this->anonymizeObject($object, $frontendUser))
                                    ){

                                        // store changes temporarily. Only if no error occurs we will persist it
                                        $updates[] = [
                                            'repository' => $repository,
                                            'data' => $object
                                        ];
                                        $adds[] = $encryptedData;

                                        $this->getLogger()->log(
                                            LogLevel::INFO,
                                            sprintf(
                                                'Anonymized and encrypted data of model "%s" of user-id %s.',
                                                $modelClassName,
                                                $frontendUser->getUid()
                                            )
                                        );
                                    } else {
                                        $this->getLogger()->log(LogLevel::WARNING,
                                            sprintf(
                                                'Could not anonymize and encrypt data of model "%s" of user-id %s.',
                                                $modelClassName,
                                                $frontendUser->getUid()
                                            )
                                        );
                                        continue(2);
                                    }
                                }
                            }
                        } else {
                            $this->getLogger()->log(
                                LogLevel::WARNING,
                                sprintf(
                                    'Configuration for model %s seems to be incorrect. Please check your TypoScript.',
                                    $modelClassName
                                )
                            );
                        }
                    }
                }

                // now save everything
                foreach ($adds as $data) {
                    $this->encryptedDataRepository->add($data);
                }
                $this->persistenceManager->persistAll();

                foreach ($updates as $subArray) {
                    /** @var Repository $repository */
                    if (
                        ($repository = $subArray['repository'])
                        && ($data = $subArray['data'])
                    ) {
                       $repository->update($data);
                    }
                }
                $this->persistenceManager->persistAll();

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Saved and updated all data for user-id %s.',
                        $frontendUser->getUid()
                    )
                );
            }

            return true;
        }

        return false;
    }



    /**
     * Anonymizes data of a given object
     *
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $object
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \RKW\RkwRegistration\Exception
     * @return bool
     */
    public function anonymizeObject(AbstractEntity $object, FrontendUser $frontendUser): bool
    {
        if ($object->_isNew()) {
            throw new Exception('Given object is not persisted.');
        }

        // try property-mapping with current and parent class
        if (
            ($propertyMap = $this->getPropertyMapByModelClassName(get_class($object)))
            || ($propertyMap = $this->getPropertyMapByModelClassName(get_parent_class($object)))
        ){
            foreach ($propertyMap as $property => $newValue) {
                $setter = 'set' . ucfirst($property);
                if (method_exists($object, $setter)) {
                    $object->$setter(str_replace('{UID}', $frontendUser->getUid(), $newValue));
                }
            }
            return true;
        }

        return false;
    }


    /**
     * Encrypts data of a given object
     **
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $object
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \RKW\RkwRegistration\Exception
     * @return \RKW\RkwRegistration\Domain\Model\EncryptedData|null
     */
    public function encryptObject(AbstractEntity $object, FrontendUser $frontendUser) :? EncryptedData
    {
        if ($object->_isNew()) {
            throw new Exception('Given object is not persisted.');
        }

        // try property-mapping with current and parent class
        $propertyMap = null;
        $className = null;
        if ($propertyMap = $this->getPropertyMapByModelClassName(get_class($object))){
            $className = get_class($object);
        } else if ($propertyMap = $this->getPropertyMapByModelClassName(get_parent_class($object))){
            $className = get_parent_class($object);
        }

        if (
            ($propertyMap)
            && ($className)
        ){
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper $dataMapper */
            $dataMapper = $this->objectManager->get(DataMapper::class);
            $tableName = $dataMapper->getDataMap($className)->getTableName();

            /** @var \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData */
            $encryptedData = GeneralUtility::makeInstance(EncryptedData::class);
            $encryptedData->setFrontendUser($frontendUser);
            $encryptedData->setSearchKey(hash('sha256', $frontendUser->getEmail()));
            $encryptedData->setForeignUid($object->getUid());
            $encryptedData->setForeignTable($tableName);
            $encryptedData->setForeignClass($className);

            $data = [];
            foreach ($propertyMap as $property => $newValue) {
                $getter = 'get' . ucfirst($property);
                if (method_exists($object, $getter)) {
                    $data[$property] = $this->getEncryptedString($object->$getter(), $frontendUser->getEmail());
                }
            }

            $encryptedData->setEncryptedData($data);
            return $encryptedData;
        }

        return null;
    }


    /**
     * Decrypts data for given object
     **
     * @param \RKW\RkwRegistration\Domain\Model\EncryptedData $encryptedData
     * @param string $email
     * @return \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|null
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \RKW\RkwRegistration\Exception
     */
    public function decryptObject(EncryptedData $encryptedData, string $email) :? AbstractEntity
    {
        if (
            (class_exists($encryptedData->getForeignClass()))
            && ($propertyMap = $this->getPropertyMapByModelClassName($encryptedData->getForeignClass()))
        ){

            $data = $encryptedData->getEncryptedData();
            if (is_array($data)) {

                /** @var \TYPO3\CMS\Extbase\Persistence\Repository $repository */
                /** @var \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $object */
                if (
                    ($repository = $this->getRepositoryByModelClassName($encryptedData->getForeignClass()))
                    && ($object = $this->getRepositoryResults($repository, 'uid', $encryptedData->getForeignUid())->getFirst())
                ){
                    foreach ($data as $property => $value) {
                        $setter = 'set' . ucfirst($property);
                        if(method_exists($object, $setter)) {
                            $object->$setter($this->getDecryptedString($value, $email));
                        }
                    }
                    return $object;
                }
            }
        }

        return null;
    }


    /**
     * Get property map for given model class name
     *
     * @param string $modelClassName
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getPropertyMapByModelClassName (string $modelClassName): array
    {
        $settings = $this->getSettings();
        $mappings = $settings['dataProtection']['classes'];
        if (
            (is_array($mappings))
            && (in_array($modelClassName, array_keys($mappings)))
            && ($propertyMap = $mappings[$modelClassName]['fields'])
            && (is_array($propertyMap))
        ) {
            return $propertyMap;
        }

        return [];
    }


    /**
     * Get frontend user property for given model class name
     *
     * @param string $modelClassName
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getFrontendUserPropertyByModelClassName (string $modelClassName): string
    {
        $frontendUserProperty = '';
        $settings = $this->getSettings();

        if (
            (class_exists($modelClassName))
            && ($mappingField = $settings['dataProtection']['classes'][$modelClassName]['mappingField'])
        ) {

            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper $dataMapper */
            $dataMapper = $this->objectManager->get(DataMapper::class);

            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap $dataMap */
            if ($dataMap = $dataMapper->getDataMap($modelClassName)) {

                /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap $columnMap */
                if (
                    ( $columnMap = $dataMap->getColumnMap($mappingField))
                    && ($columnMap->getTypeOfRelation() == ColumnMap::RELATION_HAS_ONE)
                    && (
                        ($columnMap->getChildTableName() == 'fe_users')
                        || (
                            (! $columnMap->getChildTableName())
                            && ($columnMap->getType()->equals('PASSTHROUGH'))
                        )
                    )
                ) {
                    $frontendUserProperty = $mappingField;
                }
            }
        }

        return $frontendUserProperty;
    }


    /**
     * Get repository of given model class name
     *
     * @param string $modelClassName
     * @return \TYPO3\CMS\Extbase\Persistence\Repository|object|null
     */
    public function getRepositoryByModelClassName (string $modelClassName)
    {
        // get repository class
        $repositoryClassName = str_replace('Model', 'Repository', $modelClassName) . 'Repository';
        if (
            (class_exists($repositoryClassName))
            && (class_exists($modelClassName))
        ){
            return $this->objectManager->get($repositoryClassName);
        }

        return null;
    }


    /**
     * Get encrypted string using a given key
     *
     * @param mixed $data
     * @param string $email
     * @return string
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @see https://gist.github.com/turret-io/957e82d44fd6f4493533, thanks!
     */
    public function getEncryptedString($data, string $email): string
    {
        define('AES_256_CBC', 'aes-256-cbc');

        if (! $this->encryptionKey) {
            throw new Exception('No encryption key configured.');
        }
        $hash = hash('md5', $this->encryptionKey . $email);

        // Generate an initialization vector
        // This *MUST* be available for decryption as well
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(AES_256_CBC));

        // Encrypt $data using aes-256-cbc cipher with the given encryption key and
        // our initialization vector. The 0 gives us the default options, but can
        // be changed to OPENSSL_RAW_DATA or OPENSSL_ZERO_PADDING
        $encrypted = openssl_encrypt($data, AES_256_CBC, base64_decode($hash), 0, $iv);

        // If we lose the $iv variable, we can't decrypt this, so:
        // - $encrypted is already base64-encoded from openssl_encrypt
        // - Append a separator that we know won't exist in base64, ":"
        // - And then append a base64-encoded $iv
        return $encrypted . ':' . base64_encode($iv);
    }


    /**
     * Get decrypted string using a given key
     *
     * @param string $data
     * @param string $email
     * @return string
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @see https://gist.github.com/turret-io/957e82d44fd6f4493533, thanks!
     */
    public function getDecryptedString(string $data, string $email): string
    {
        define('AES_256_CBC', 'aes-256-cbc');

        if (! $this->encryptionKey) {
            throw new Exception('No encryption key configured.');
        }
        $hash = hash('md5', $this->encryptionKey . $email);

        // To decrypt, separate the encrypted data from the initialization vector ($iv).
        $parts = explode(':', $data);

        // $parts[0] = encrypted data
        // $parts[1] = base-64 encoded initialization vector
        // Don't forget to base64-decode the $iv before feeding it back to openssl_decrypt
        return openssl_decrypt($parts[0], AES_256_CBC, base64_decode($hash), 0, base64_decode($parts[1]));
    }


    /**
     * Get results from repository
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Repository $repository
     * @param string $property
     * @param integer $uid
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getRepositoryResults(Repository $repository, string $property, int $uid): QueryResultInterface
    {
        $query  = $repository->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->equals($property, $uid)
        );

        return $query->execute();
    }



    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return GeneralUtility::getTypoScriptConfiguration('rkwregistration', $which);
    }



    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {

        if (!$this->logger instanceof Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}
