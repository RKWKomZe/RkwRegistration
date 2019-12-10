<?php

namespace RKW\RkwRegistration\Domain\Repository;

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
 * ShippingAddressRepository
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ShippingAddressRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * initializeObject
     *
     * @return void
     */
    public function initializeObject()
    {
        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

        // don't add the pid constraint
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * Remove shipping address
     *
     * According to General Data Protection Regulation (GDPR), we anonymize their data
     * This way we can keep the existing relations without having to delete the user data completely
     *
     * @param \RKW\RkwRegistration\Domain\Model\ShippingAddress $object
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @return void
     */
    public function remove($object)
    {

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

        $this->update($object);
        parent::remove($object);
    }

}