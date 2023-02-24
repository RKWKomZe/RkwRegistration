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
 * AbstractRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * Some important things on init
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function initializeObject(): void
    {

        // Fix: always use your own storagePid - even if called through another extension
        // This is important since the extension's registration feature is used by a lot of other extensions
        // Per default the storagePid of the calling extension is used
        $settings =  \Madj2k\CoreExtended\Utility\GeneralUtility::getTypoScriptConfiguration('rkwRegistration',
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        if ($settings['persistence']['storagePid']) {
            $querySettings = $this->createQuery()->getQuerySettings();
            $querySettings->setStoragePageIds([intval($settings['persistence']['storagePid'])]);
            $this->setDefaultQuerySettings($querySettings);
        }
    }

}
