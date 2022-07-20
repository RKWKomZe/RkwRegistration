<?php

namespace RKW\RkwRegistration\ViewHelpers;

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Class XdlLinkViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class XdlLinkViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns a link for XDL-login with redirection back to the original page
     *
     * @param string $referrer
     * @param integer $loginPid
     * @param integer $xdlPid
     * @return string
     */
    public function render($referrer, $loginPid = 0, $xdlPid = 0)
    {
        $settingsExtension = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');

        if (!$loginPid) {
            $loginPid = $settingsExtension['settings']['loginPid'];
        }
        if (!$xdlPid) {
            $xdlPid = $settingsExtension['settings']['xdlPid'];
        }

        $params = array(
            'tx_rkwregistration_rkwregistration' => array(
                'referrer' => $referrer,
            ),
        );

        if ($xdlPid) {
            $xdlUrl = $uriBuilder->reset()
                ->setTargetPageUid(intval($xdlPid))
                ->setCreateAbsoluteUri(true)
                ->setLinkAccessRestrictedPages(true)
                ->setUseCacheHash(false)
                ->build();

            $params['tx_rkwregistration_rkwregistration']['xdlUrl'] = $xdlUrl;
        }

        $url = $uriBuilder->reset()
            ->setTargetPageUid(intval($loginPid))
            ->setCreateAbsoluteUri(true)
            ->setLinkAccessRestrictedPages(true)
            ->setUseCacheHash(false)
            ->setArguments($params)
            ->build();

        return $url;
        //===

    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     */
    public function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('Rkwregistration', $which);
        //===
    }


}
