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

use RKW\RkwRegistration\Domain\Model\Registration;

/**
 * PrivacyRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PrivacyRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * function findByRegistration
     *
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @return \RKW\RkwRegistration\Domain\Model\Privacy|object
     */
    public function findOneByRegistration(Registration $registration)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching(
            $query->equals('registrationUserSha1', $registration->getUserSha1())
        );

        return $query->execute()->getFirst();
    }

}