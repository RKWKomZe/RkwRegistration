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
use Madj2k\CoreExtended\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ConsentViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ConsentViewHelper extends AbstractViewHelper
{

    /**
     * @const string
     */
    const NAMESPACE = 'tx_rkwregistration';


    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;


    /**
     * Initialize arguments.
     *
     * @return void
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('key', 'string', 'The key to use for the text.', false, 'default');
        $this->registerArgument('pageUid', 'int', 'The pid of the first page which will be linked.', false, '0');
        $this->registerArgument('pageUid2', 'int', 'The pid of the second page which will be linked.', false, '0');
        $this->registerArgument('type', 'string', 'The type. Allowed values are: privacy, terms, marketing.', false, 'privacy');
    }


    /**
     * Returns a standard checkbox with text
     * (not a partial because this is more complicated to use it universally in several extensions)
     *
     * @return string
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function render(): string
    {
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        /** @var string $type */
        $type = in_array(strtolower($this->arguments['type']), ['privacy', 'terms', 'marketing'])? strtolower($this->arguments['type']): 'privacy';

        /** @var string $key */
        $key = $this->arguments['key'];

        $formData = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP(self::NAMESPACE);
        $checked = (bool) $formData[$type];

        /** @var int $pageUid */
        $pageUid = $this->arguments['pageUid'];

        /** @var int $pageUid2 */
        $pageUid2 = $this->arguments['pageUid2'];

        // use given privacyPid or just the one which is set in the RkwRegistration-settings
        if (!$pageUid) {
            $pageUid = intval($settings['settings'][$type . 'Pid']);
        }
        if (!$pageUid2) {
            $pageUid2 = intval($settings['settings'][$type . '2Pid']);
        }

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $standaloneView->setLayoutRootPaths($settings['view']['layoutRootPaths']);
        $standaloneView->setPartialRootPaths($settings['view']['partialRootPaths']);
        $standaloneView->setTemplateRootPaths($settings['view']['templateRootPaths']);

        $standaloneView->setTemplate('ViewHelpers/Consent/' . ucfirst($type));
        $standaloneView->assignMultiple(
            [
                'namespace' => self::NAMESPACE,
                'type' => $type,
                'key' => $key,
                'checked' => $checked,
                'pageUid'  => $pageUid,
                'pageUid2'  => $pageUid2
            ]
        );

        return $standaloneView->render();
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return GeneralUtility::getTypoScriptConfiguration('Rkwregistration', $which);
    }

}
