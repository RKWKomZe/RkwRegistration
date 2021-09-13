<?php

namespace RKW\RkwRegistration\ViewHelpers;

use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \RKW\RkwBasics\Utility\GeneralUtility;

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
 * Class PrivacyTextViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PrivacyTextViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Returns a standard text for the privacy checkbox
     * (not a partial because this is more complicated to use it universally in several extensions)
     *
     * @param string $textVersion
     * @param integer $privacyPid
     * @return string
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function render($textVersion = 'default', $privacyPid = null)
    {
        $settingsExtension = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        // use given privacyPid or just the one which is set in the RkwRegistration-settings
        if (!$privacyPid) {
            $privacyPid = intval($settingsExtension['settings']['users']['privacyPid']);
        }

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $template */
        $template = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $template->setLayoutRootPaths($settingsExtension['view']['layoutRootPaths']);
        $template->setPartialRootPaths($settingsExtension['view']['partialRootPaths']);
        $template->setTemplateRootPaths($settingsExtension['view']['templateRootPaths']);
        $template->getRequest()->setControllerExtensionName(GeneralUtility::underscoredToUpperCamelCase('rkw_registration'));

        $template->setTemplate('Registration/Privacy');
        $template->assignMultiple(
            array(
                'privacyPid'  => $privacyPid,
                'textVersion' => $textVersion,
            )
        );

        return $template->render();
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration', $which);
    }

}
