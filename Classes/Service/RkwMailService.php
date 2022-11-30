<?php

namespace RKW\RkwRegistration\Service;

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

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwMailer\Service\MailService;
use RKW\RkwMailer\Utility\FrontendLocalizationUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\OptIn;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * RkwMailService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwMailService implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Handles optIn-event
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendOptInEmail(FrontendUser $frontendUser, OptIn $optIn)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'tokenYes'        => $optIn->getTokenYes(),
                    'tokenNo'         => $optIn->getTokenNo(),
                    'tokenUser'       => $optIn->getTokenUser(),
                    'frontendUser'    => $frontendUser,
                    'settings'        => $settingsDefault,
                    'pageUid'         => intval($GLOBALS['TSFE']->id),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.optIn.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/OptIn');
            $mailService->getQueueMail()->setHtmlTemplate('Email/OptIn');
            $mailService->send();
        }
    }



    /**
     * Handles register user event (after user has done his OptIn)
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendConfirmationEmail(FrontendUser $frontendUser, OptIn $optIn)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();
        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo(
                $frontendUser,
                [
                    'marker' => [
                        'plaintextPasswordForMail'  => $frontendUser->getTempPlaintextPassword(),
                        'frontendUser'              => $frontendUser,
                        'pageUid'                   => intval($GLOBALS['TSFE']->id),
                        'settings'                  => $settingsDefault,
                    ]
                ]
            );

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.confirmation.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/Confirmation');
            $mailService->getQueueMail()->setHtmlTemplate('Email/Confirmation');
            $mailService->send();
        }
    }


    /**
     * Handles optIn-event for groups
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendGroupOptInEmail(FrontendUser $frontendUser, OptIn $optIn)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'tokenYes'          => $optIn->getTokenYes(),
                    'tokenNo'           => $optIn->getTokenNo(),
                    'tokenUser'         => $optIn->getTokenUser(),
                    'frontendUser'      => $frontendUser,
                    'frontendUserGroup' => $optIn->getData(),
                    'settings'          => $settingsDefault,
                    'pageUid'           => intval($GLOBALS['TSFE']->id),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.group.optIn.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/Group/OptIn');
            $mailService->getQueueMail()->setHtmlTemplate('Email/Group/OptIn');
            $mailService->send();
        }
    }


    /**
     * Handles optIn-event for group-admins
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $approvals
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendGroupOptInEmailAdmin(FrontendUser $frontendUser, OptIn $optIn, ObjectStorage $approvals)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if (
            ($settings['view']['templateRootPaths'])
            && (count($approvals))
        ){

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser */
            foreach ($approvals as $backendUser) {

                // send new user an email with token
                $mailService->setTo($backendUser, array(
                    'marker' => array(
                        'tokenYes' => $optIn->getAdminTokenYes(),
                        'tokenNo' => $optIn->getAdminTokenNo(),
                        'tokenUser' => $optIn->getTokenUser(),
                        'frontendUser' => $frontendUser,
                        'backendUser'  => $backendUser,
                        'frontendUserGroup' => $optIn->getData(),
                        'settings' => $settingsDefault,
                        'pageUid' => intval($GLOBALS['TSFE']->id),
                    ),
                    'subject' => FrontendLocalizationUtility::translate(
                        'rkwMailService.group.optInAdmin.subject',
                        'rkw_registration',
                        null,
                        $backendUser->getLang()
                    ),
                ));
            }

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.group.optInAdmin.subject',
                    'rkw_registration',
                    null,
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/Group/OptInAdmin');
            $mailService->getQueueMail()->setHtmlTemplate('Email/Group/OptInAdmin');
            $mailService->send();
        }
    }


    /**
     * Handles completion-event for groups
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendGroupConfirmationEmail(FrontendUser $frontendUser, OptIn $optIn)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'frontendUser'      => $frontendUser,
                    'frontendUserGroup' => $optIn->getData(),
                    'settings'          => $settingsDefault,
                    'pageUid'           => intval($GLOBALS['TSFE']->id),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.group.confirmation.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/Group/Confirmation');
            $mailService->getQueueMail()->setHtmlTemplate('Email/Group/Confirmation');
            $mailService->send();
        }
    }


    /**
     * Handles optIn-withdraw-event for group-admins
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $approvals
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendGroupOptInWithdrawEmailAdmin (FrontendUser $frontendUser, OptIn $optIn, ObjectStorage $approvals)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if (
            ($settings['view']['templateRootPaths'])
            && (count($approvals))
        ){

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            /** @var \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser */
            foreach ($approvals as $backendUser) {

                // send new user an email with token
                $mailService->setTo($backendUser, array(
                    'marker' => array(
                        'frontendUser' => $frontendUser,
                        'backendUser'  => $backendUser,
                        'frontendUserGroup' => $optIn->getData(),
                        'settings' => $settingsDefault,
                        'pageUid' => intval($GLOBALS['TSFE']->id),
                    ),
                    'subject' => FrontendLocalizationUtility::translate(
                        'rkwMailService.group.optInWithdrawAdmin.subject',
                        'rkw_registration',
                        null,
                        $backendUser->getLang()
                    ),
                ));
            }

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.group.optInWithdrawAdmin.subject',
                    'rkw_registration',
                    null,
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/Group/OptInWithdrawAdmin');
            $mailService->getQueueMail()->setHtmlTemplate('Email/Group/OptInWithdrawAdmin');
            $mailService->send();
        }
    }


    /**
     * Handles optIn-denial-event for groups
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendGroupOptInDenialEmail (FrontendUser $frontendUser, OptIn $optIn)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'frontendUser'      => $frontendUser,
                    'frontendUserGroup' => $optIn->getData(),
                    'settings'          => $settingsDefault,
                    'pageUid'           => intval($GLOBALS['TSFE']->id),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.group.optInDenial.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/Group/OptInDenial');
            $mailService->getQueueMail()->setHtmlTemplate('Email/Group/OptInDenial');
            $mailService->send();
        }
    }


    /**
     * Handles password reset event
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param string $plaintextPassword
     * @param string $referrer
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendResetPasswordEmail(FrontendUser $frontendUser, string $plaintextPassword, string $referrer = '')
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo(
                $frontendUser,
                [
                    'marker' => [
                        'plaintextPasswordForMail' => $plaintextPassword,
                        'frontendUser'             => $frontendUser,
                        'settings'                 => $settingsDefault,
                        'pageUid'                  => intval($GLOBALS['TSFE']->id),
                        'referrer'                 => $referrer
                    ],
                ]
            );

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.resetPassword.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/PasswordReset');
            $mailService->getQueueMail()->setHtmlTemplate('Email/PasswordReset');
            $mailService->send();
        }
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration', $which);
    }
}
