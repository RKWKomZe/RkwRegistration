<?php

namespace RKW\RkwRegistration\ContentProcessing;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class PseudoBaseUrl
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwBasics
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PseudoBaseUrl
{

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->settings = $this->getSettings();
        $this->config = $this->getConfig();
    }


    /**
     * Adds PseudoCDN into content
     *
     * @param string $content content to replace
     * @return string new content
     */
    public function process($content)
    {
        // 1. check if current domain is a defined domain of this root
        $settings = $this->settings;
        $config = $this->config;

        //if (intval($settings['users']['myRkwSysDomain'])) {

            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            /** @var \RKW\RkwRegistration\Domain\Repository\SysDomainRepository $sysDomainRepository */
            $sysDomainRepository = $objectManager->get('RKW\\RkwRegistration\\Domain\\Repository\\SysDomainRepository');
            //$sysDomain = $sysDomainRepository->findByIdentifier(intval($settings['users']['myRkwSysDomain']));
            $sysDomain = $sysDomainRepository->findByDomainName(strval($_SERVER['HTTP_HOST']))->getFirst();

            if (
                $sysDomain instanceof \RKW\RkwRegistration\Domain\Model\SysDomain
                //&& strcmp($sysDomain->getDomainName(), $_SERVER['HTTP_HOST']) === 0
            ) {

                // 2. Replace content
                $domain = $sysDomain->getDomainName();

                $replacement = '<base href="' . $config['protocol'] . $domain . '/">';

                if (preg_match($this->config['search'], $content)) {

                    // 2.1 return content with overwritten <base href="xyz"> tag
                    return preg_replace($this->config['search'], $replacement, $content);
                } else {
                    // 2.2 no base url found. Add it
                    $insertAfter = '<head>';
                    $position = strpos($content, $insertAfter) + strlen($insertAfter);

                    $content = substr_replace($content, $replacement, $position, 0);

                }
            }
        //}

        // If edit or not: Return the content for FE rendering
        return $content;
    }


    /**
     * Loads settings
     *
     * @return array
     */
    public function getSettings()
    {
        /** @var $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
        $settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Rkwregistration'
        );

        return $settings;
    }



    /**
     * Loads config
     *
     * @return array
     */
    public function getConfig()
    {

        $config = [
            'search' => '/(<base href="[^"]*">)/i',
            'baseDomain' => preg_replace('/^http(s)?:\/\/(www\.)?([^\/]+)\/?$/i', '$3', $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL']),
            'protocol' => (($_SERVER['HTTPS']) || ($_SERVER['SERVER_PORT'] == '443')) ? 'https://' : 'http://'
        ];

        return $config;
    }


} 