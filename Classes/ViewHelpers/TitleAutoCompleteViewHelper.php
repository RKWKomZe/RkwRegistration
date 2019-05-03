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
 * Class TitleAutoCompleteViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TitleAutoCompleteViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns a dropdown select to serve as an autocomplete list
     * (not a partial because this is more complicated to use it universally in several extensions)
     *
     * @param string $options
     * @param string $container
     * @param string $list
     * @param integer $uid
     * @return string
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function render($options = '', $container = '', $list = '', $uid = '')
    {

        $settingsExtension = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

//        var_dump($settingsExtension);
//        exit();
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $template */
        $template = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $template->setLayoutRootPaths($settingsExtension['view']['layoutRootPaths']);
        $template->setPartialRootPaths($settingsExtension['view']['partialRootPaths']);
        $template->setTemplateRootPaths($settingsExtension['view']['templateRootPaths']);
//        $template->getRequest()->setControllerExtensionName(GeneralUtility::underscoredToUpperCamelCase('rkw_registration'));

        $template->setTemplate('Registration/TitleAutoComplete');
        $template->assignMultiple(
            array(
                'type' => 'text/javascript',
                'options'  => json_encode($this->getOptions(true,true,'name')),
                'container' => $container,
                'list' => $list,
                'uid' => $uid,
            )
        );

        return $template->render();
        //===
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
        return Common::getTyposcriptConfiguration('Rkwregistration', $which);
        //===
    }

    /**
     * @param bool   $returnArray
     * @param string $mapProperty
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    private function getOptions($showTitleBefore = true, $returnArray = true, $mapProperty = '')
    {

        // a) This avoids possible empty results by calling <rkwRegistration:titleList showTitleBefore='false' showTitleAfter='false' />
        // b) Makes a shorter invoke possible for showing up only "isTitleAfter"-Elements (see PHPdocs example above)
        if (!$showTitleBefore) {
            $showTitleAfter = true;
        }

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \RKW\RkwRegistration\Domain\Repository\TitleRepository $titleRepository */
        $titleRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\TitleRepository');

        $titles = $titleRepository->findAllOfType($showTitleBefore, $showTitleAfter, $returnArray);

        if ($mapProperty) {
            $mappedTitles = array_map(function($item) use ($mapProperty) {
                return $item[$mapProperty];
            }, $titles);

            $titles = $mappedTitles;
        }

        return $titles;
    }


}