<?php

namespace RKW\RkwRegistration\Utilities;

use http\Exception;
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
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_ANONYMIZE_FRONTEND_USER = 'anonymizeFrontendUser';

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\ShippingAddressRepository
     * @inject
     */
    protected $shippingAddressRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository
     * @inject
     */
    protected $privacyRepository;

    /**
     * Signal-Slot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * Anonymizes all data of a frontend user that has been deleted or inactive since a given time
     *
     * !!! The user data should not be anonymised before the end of the period stated in your
     * data protection declaration, since the consent must still be proven after this period !!!
     *
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \RKW\RkwRegistration\Exception
     * @return void
     */
    public function anonymizeAll ()
    {

        $settings = $this->getSettings();
        $days = intval($settings['dataProtection.']['anonymizeAfterDays']) ? intval($settings['dataProtection.']['anonymizeAfterDays']) : 365;
        if ($cleanupTimestamp = time() - intval($days) * 24 * 60 * 60){

            if (
                ($frontendUserList = $this->frontendUserRepository->findExpiredOrDeleted($cleanupTimestamp))
                && (count($frontendUserList))
            ) {

                /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
                foreach ($frontendUserList as $frontendUser) {

                    // signal slot
                    $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_ANONYMIZE_FRONTEND_USER, array($frontendUser, $formRequest));


                    $this->anonymize($frontendUser);
                    $this->frontendUserRepository->update($frontendUser);


                    if ($shippingAddresses = $this->shippingAddressRepository->findByFrontendUser($frontendUser)) {
                        foreach ($shippingAddresses as $shippingAddress) {
                            $this->anonymize($shippingAddress);
                            $this->shippingAddressRepository->update($shippingAddress);

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
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \RKW\RkwRegistration\Exception

     * @return void
     */
    public function anonymize(\TYPO3\CMS\Extbase\DomainObject\AbstractEntity $object)
    {

        if ($object->_isNew()) {
            throw new \RKW\RkwRegistration\Exception('Given object is not persisted.');
        }

        $settings = $this->getSettings();
        $mappings = $settings['dataProtection']['classes'];

        var_dump($mappings);
        if (is_array($mappings)) {

            foreach ($mappings as $class => $propertyMap) {
                if (
                    ($object instanceof $class)
                    && (is_array($propertyMap))
                ){
                    foreach ($propertyMap as $property => $newValue) {
                        $setter = 'set' . ucfirst($property);
                        $object->$setter(str_replace($object->getUid(), '{UID}', $newValue));
                    }
                }
            }
        }
    }


    /**
     * anonymize the shipping address
     *
     * According to General Data Protection Regulation (GDPR), we anonymize their data
     * This way we can keep the existing relations without having to delete the user data completely
     *
     * !!! The user data should not be anonymised before the end of the period stated in your
     * data protection declaration, since the consent must still be proven after this period !!!
     *
     * @param \RKW\RkwRegistration\Domain\Model\ShippingAddress $object
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \RKW\RkwRegistration\Exception
     * @return void
     */
    public function anonymizeShippingAddress(\RKW\RkwRegistration\Domain\Model\ShippingAddress $object)
    {

        if ($object->_isNew()) {
            throw new \RKW\RkwRegistration\Exception('Given object is not persisted.');
        }


        $propertiesToAnonymize = [
            'gender' => 99,
            'firstName' => 'Deleted',
            'lastName' => 'Anonymous',
            'company' => '',
            'address' => '',
            'zip' => '',
            'city' => '',
        ];

        foreach ($propertiesToAnonymize as $property => $value) {
            $setter = 'set' . ucfirst($property);
            $object->$setter($value);
        }

        $this->shippingAddressRepository->update($object);
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
        //===
    }

}
