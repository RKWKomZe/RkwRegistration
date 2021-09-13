<?php

namespace RKW\RkwRegistration\Service;

use \RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwMailer\Service\MailService;
use RKW\RkwRegistration\Domain\Model\BackendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\Registration;
use RKW\RkwRegistration\Domain\Model\Service;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
 * RkwMailService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwMailService implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Handles create user event
     *
     * @param FrontendUser $frontendUser
     * @param Registration $registration
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleCreateUserEvent(FrontendUser $frontendUser, Registration $registration, $signalInformation)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);


            // create OptIn links now and not inside the fluid template, which will be used by the RkwMailer
            // Reason: Only via this way we get suitable links to the current active dynamic domain

            /** @var ObjectManager objectManager*/
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get(UriBuilder::class);
            $uriTokenYes = $uriBuilder->reset()
                ->setTargetPageUid(intval($settingsDefault['users']['registrationPid']))
                ->setCreateAbsoluteUri(true)
                ->setUseCacheHash(false)
                ->uriFor(
                    'optIn',
                    [
                        'token_yes' => $registration->getTokenYes(),
                        'user' => $registration->getUserSha1()
                    ],
                    'Registration',
                    'RkwRegistration',
                    'Register'
                );

            $uriTokenNo = $uriBuilder->reset()
                ->setTargetPageUid(intval($settingsDefault['users']['registrationPid']))
                ->setCreateAbsoluteUri(true)
                ->setUseCacheHash(false)
                ->uriFor(
                    'optIn',
                    [
                        'token_no' => $registration->getTokenNo(),
                        'user' => $registration->getUserSha1()
                    ],
                    'Registration',
                    'RkwRegistration',
                    'Register'
                );

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'linkTokenYes' => $uriTokenYes,
                    'linkTokenNo' => $uriTokenNo,
                    'frontendUser'    => $frontendUser,

                    // old
                    #'tokenYes'        => $registration->getTokenYes(),
                    #'tokenNo'         => $registration->getTokenNo(),
                    #'userSha1'        => $registration->getUserSha1(),
                    #'registrationPid' => intval($settingsDefault['users']['registrationPid']),
                    #'pageUid'         => intval($GLOBALS['TSFE']->id),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.createUserEvent.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/RegisterOptInRequest');
            $mailService->getQueueMail()->setHtmlTemplate('Email/RegisterOptInRequest');
            $mailService->send();
        }
    }



    /**
     * Handles register user event (after user has done his OptIn)
     *
     * @param FrontendUser $frontendUser
     * @param Registration $registration
     * @param string $plaintextPassword
     * @return void
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleRegisterUserEvent(FrontendUser $frontendUser, $plaintextPassword, Registration $registration = null)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();
        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // create OptIn links now and not inside the fluid template, which will be used by the RkwMailer
            // Reason: Only via this way we get suitable links to the current active dynamic domain

            /** @var ObjectManager objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get(UriBuilder::class);
            $uriLogin = $uriBuilder->reset()
                ->setTargetPageUid(intval($settingsDefault['users']['loginPid']))
                ->setCreateAbsoluteUri(true)
                ->setUseCacheHash(false)
                ->build();

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'plaintextPasswordForMail'  => $plaintextPassword,
                    'frontendUser'              => $frontendUser,
                    'pageUid'                   => intval($GLOBALS['TSFE']->id),
                    'loginLink'                 => $uriLogin

                    // old
                    #'loginPid'                 => intval($settingsDefault['users']['loginPid']),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.registerUserEvent.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/RegisterOptInSuccess');
            $mailService->getQueueMail()->setHtmlTemplate('Email/RegisterOptInSuccess');
            $mailService->send();
        }
    }

    /**
     * Handles password reset event
     *
     * @param FrontendUser $frontendUser
     * @param string $plaintextPassword
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handlePasswordResetEvent(FrontendUser $frontendUser, $plaintextPassword, $signalInformation)
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
                    'plaintextPasswordForMail' => $plaintextPassword,
                    'frontendUser'             => $frontendUser,
                    'pageUid'                  => intval($GLOBALS['TSFE']->id),
                    'loginPid'                 => intval($settingsDefault['users']['loginPid']),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.passwordResetEvent.subject',
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
     * Handles register user event
     *
     * @param BackendUser $admin
     * @param FrontendUser $frontendUser
     * @param FrontendUserGroup $frontendUserGroup
     * @param Service $serviceOptIn
     * @param integer $pid
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleAdminServiceEvent(BackendUser $admin, FrontendUser $frontendUser, FrontendUserGroup $frontendUserGroup, Service $serviceOptIn, $pid, $signalInformation)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo($admin, array(
                'marker' => array(
                    'tokenYes'          => $serviceOptIn->getTokenYes(),
                    'tokenNo'           => $serviceOptIn->getTokenNo(),
                    'serviceSha1'       => $serviceOptIn->getServiceSha1(),
                    'service'           => $serviceOptIn,
                    'frontendUser'      => $frontendUser,
                    'frontendUserGroup' => $frontendUserGroup,
                    'backendUser'       => $admin,
                    'pageUid'           => intval($pid),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.adminServiceEvent.subject',
                    'rkw_registration',
                    null,
                    $admin->getLang()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/ServiceOptInAdminRequest');
            $mailService->getQueueMail()->setHtmlTemplate('Email/ServiceOptInAdminRequest');
            $mailService->send();
        }
    }

    /**
     * Handles register user event
     *
     * @param FrontendUser $frontendUser
     * @param Service $service
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleAdminServiceGrantEvent(FrontendUser $frontendUser, Service $service, $signalInformation)
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
                    'service'      => $service,
                    'frontendUser' => $frontendUser,
                    'pageUid'      => intval($GLOBALS['TSFE']->id),
                    'loginPid'     => intval($settingsDefault['users']['loginPid']),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.adminServiceGrantEvent.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/ServiceOptInSuccess');
            $mailService->getQueueMail()->setHtmlTemplate('Email/ServiceOptInSuccess');
            $mailService->send();
        }
    }


    /**
     * Handles register user event
     *
     * @param FrontendUser $frontendUser
     * @param Service $service
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleAdminServiceDenialEvent(FrontendUser $frontendUser, Service $service, $signalInformation)
    {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'service'      => $service,
                    'frontendUser' => $frontendUser,
                    'pageUid'      => intval($GLOBALS['TSFE']->id),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.adminServiceDenialEvent.subject',
                    'rkw_registration',
                    null,
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->setPlaintextTemplate('Email/ServiceOptInDenial');
            $mailService->getQueueMail()->setHtmlTemplate('Email/ServiceOptInDenial');
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
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration', $which);
    }
}
