<?php

namespace RKW\RkwRegistration\ViewHelpers;

use \RKW\RkwBasics\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * Class GetAllFlashMessageIdentifierViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetAllFlashMessageIdentifierViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    const PREFIX = 'extbase.flashmessages.';


    /**
     * Maybe also an option: https://stackoverflow.com/questions/40194151/how-do-i-show-flash-messages-from-a-different-extension-plugin
     *
     *
     * Returns flashMessage identifier for every plugin with scheme extbase.flashmessages.tx_rkwregistration_XXX
     *
     * Explanation: FlashMessages are working always for one closed plugin. If we're hopping between several plugins, the flash
     * messenger is not responding. So we need to iterate all plugin identifier to simply get every flash message of the extension
     * independent of it's current plugin context
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function render()
    {
        $frameworkSettings = $this->getSettings();

        $pluginList = preg_grep('/^tx_rkwregistration_[\d]*/', array_keys($frameworkSettings['plugin.']));

        foreach ($pluginList as $key => $value) {
            $pluginList[$key] = self::PREFIX . trim($value, '.');
        }

        return $pluginList;
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT)
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration', $which);
    }


}