<?php

namespace RKW\RkwRegistration\Utilities;

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
 * Class TitleUtility
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TitleUtility
{

    /**
     * Returns \RKW\RkwRegistration\Domain\Model\Title instance
     *
     * @param string $title
     * @return \RKW\RkwRegistration\Domain\Model\Title
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public static function extractTxRegistrationTitle($title = '')  //  @todo: Oder einfach nur den gesuchten title als Parameter übergeben und gar kein komplettes Objekt? Wenn hier das Objekt flexibel sein könnte, wäre es möglich, einen Trait zu nutzen oder eine Static-Function wie \RKW\RkwRegistration\Tools\Registration::validEmail($this->getFrontendUser())
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Domain\Repository\TitleRepository $titleRepository */
        $titleRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\TitleRepository');
        $txRegistrationTitle = $titleRepository->findByName($title)->getFirst();

        if (!$txRegistrationTitle) {

            $txRegistrationTitle = new \RKW\RkwRegistration\Domain\Model\Title;
            $txRegistrationTitle->setName($title);

            $persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');

            $titleRepository->add($txRegistrationTitle);
            $persistenceManager->persistAll();

        }

        return $txRegistrationTitle;
    }

}
