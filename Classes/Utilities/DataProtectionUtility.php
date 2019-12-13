<?php

namespace RKW\RkwRegistration\Utilities;

use \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use \RKW\RkwBasics\Helper\Common;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * Class DataProtectionUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DataProtectionUtility
{


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;



    /**
     * Anonymizes all data of a frontend user that has been deleted or inactive since a given time
     *
     * !!! The user data should not be anonymised before the end of the period stated in your
     * data protection declaration, since the consent must still be proven after this period !!!
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \RKW\RkwRegistration\Exception
     * @return void
     */
    public function anonymizeAll ()
    {

        $settings = $this->getSettings();
        $days = intval($settings['dataProtection']['anonymizeAfterDays']) ? intval($settings['dataProtection']['anonymizeAfterDays']) : 365;
        $mappings = $settings['dataProtection']['classes'];
        if (
            (is_array($mappings))
            && (count($mappings))
            && ($frontendUserList = $this->frontendUserRepository->findDeletedSinceDays($days))
            && (count($frontendUserList))
        ) {

            /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
            foreach ($frontendUserList as $frontendUser) {

                foreach ($mappings as $modelClassName => $propertyMap) {

                    if ($modelClassName == 'RKW\RkwRegistration\Domain\Model\FrontendUser') {

                        $this->anonymize($frontendUser);
                        $this->frontendUserRepository->update($frontendUser);

                    } else {

                        /** @var \TYPO3\CMS\Extbase\Persistence\Repository $repository */
                        if (
                            ($frontendUserProperty = $this->getFrontendUserPropertyByModelClassName($modelClassName))
                            && ($repository = $this->getRepositoryByModelClassName($modelClassName))
                        ) {

                            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result */
                            if ($result = $this->getRepositoryResults($repository, $frontendUser, $frontendUserProperty)) {

                                /** @var \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $object */
                                foreach ($result as $object) {
                                    $this->anonymize($object, $frontendUser);
                                    $repository->update($object);
                                }
                            }
                        }
                    }
                }
            }
        }
    }



    /**
     * Anonymizes data of a given object
     *
     * !!! The user data should not be anonymised before the end of the period stated in your
     * data protection declaration, since the consent must still be proven after this period !!!
     *
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $object
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \RKW\RkwRegistration\Exception
     * @return void
     */
    public function anonymize(\TYPO3\CMS\Extbase\DomainObject\AbstractEntity $object, $frontendUser = null)
    {

        if ($object->_isNew()) {
            throw new \RKW\RkwRegistration\Exception('Given object is not persisted.');
        }

        if (! $frontendUser) {
            $frontendUser = $object;
        }

        $settings = $this->getSettings();
        $mappings = $settings['dataProtection']['classes'];
        if (
            (is_array($mappings))
            && ($class = get_class($object))
            && (in_array($class, array_keys($mappings)))
            && ($propertyMap = $mappings[$class]['fields'])
            && (is_array($propertyMap))
        ){

            foreach ($propertyMap as $property => $newValue) {
                $setter = 'set' . ucfirst($property);
                $object->$setter(str_replace('{UID}', $frontendUser->getUid(), $newValue));
            }
        }
    }



    /**
     * Get frontend user property for given model class name
     *
     * @param $modelClassName
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getFrontendUserPropertyByModelClassName ($modelClassName)
    {

        $frontendUserProperty = null;
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
                $columnMap = $dataMap->getColumnMap($mappingField);
                if (
                    ($columnMap->getTypeOfRelation() == ColumnMap::RELATION_HAS_ONE)
                    && ($columnMap->getChildTableName() == 'fe_users')) {
                    $frontendUserProperty = $mappingField;
                }
            }
        }

        return $frontendUserProperty;
    }



    /**
     * Get repository of given model class name
     *
     * @param $modelClassName
     * @return \TYPO3\CMS\Extbase\Persistence\Repository|object|null
     */
    public function getRepositoryByModelClassName ($modelClassName)
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
     * Get results from repository
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Repository $repository
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param string $frontendUserProperty
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getRepositoryResults(\TYPO3\CMS\Extbase\Persistence\Repository $repository, \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser , $frontendUserProperty)
    {
        $query  = $repository->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->equals($frontendUserProperty, $frontendUser->getUid())
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
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('rkwregistration', $which);
    }

}
