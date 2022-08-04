<?php

namespace RKW\RkwRegistration\ViewHelpers;

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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use RKW\RkwBasics\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
/**
 * Class PrivacyTextViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PrivacyTextViewHelper extends AbstractViewHelper
{

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('textVersion', 'string', 'The key to use for the text.', false, 'default');
        $this->registerArgument('privacyPid', 'int', 'The pid of the page with the privacy terms.', false, '0');
    }

    /**
     * Returns a standard text for the privacy checkbox
     * (not a partial because this is more complicated to use it universally in several extensions)
     *
     * @return string
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function render(): string
    {
        $settingsExtension = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        /** @var string $textVersion */
        $textVersion = $this->arguments['textVersion'];

        /** @var int $privacyPid */
        $privacyPid = $this->arguments['privacyPid'];

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
            [
                'privacyPid'  => $privacyPid,
                'textVersion' => $textVersion,
            ]
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
