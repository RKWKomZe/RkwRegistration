<?php

namespace RKW\RkwRegistration\Service;

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwMailer\Service\MailService;
use RKW\RkwRegistration\Domain\Model\BackendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUserGroup;
use RKW\RkwRegistration\Domain\Model\OptIn;
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
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleCreateUserEvent(FrontendUser $frontendUser, OptIn $optIn, $signalInformation)
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
                    'userSha1'        => $optIn->getTokenUser(), /** @deprecated **/
                    'tokenUser'       => $optIn->getTokenUser(),
                    'frontendUser'    => $frontendUser,
                    'registrationPid' => intval($settingsDefault['users']['registrationPid']),
                    'pageUid'         => intval($GLOBALS['TSFE']->id),
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
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn|null $optIn
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleRegisterUserEvent(FrontendUser $frontendUser, OptIn $optIn = null)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();
        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // create OptIn links here and no more inside the fluid template, which will be used by the RkwMailer
            // Reason: Only this way we get suitable links to the current active dynamic domain
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
            $mailService->setTo(
                $frontendUser,
                [
                    'marker' => [
                        'plaintextPasswordForMail'  => $frontendUser->getTempPlaintextPassword(),
                        'frontendUser'              => $frontendUser,
                        'pageUid'                   => intval($GLOBALS['TSFE']->id),
                        'loginLink'                 => $uriLogin,
                        'loginPid'                  => intval($settingsDefault['users']['loginPid']) /** @deprectated */
                    ]
                ]
            );

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
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param string $plaintextPassword
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handlePasswordResetEvent(FrontendUser $frontendUser, string $plaintextPassword, $signalInformation)
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
                        'pageUid'                  => intval($GLOBALS['TSFE']->id),
                        'loginPid'                 => intval($settingsDefault['users']['loginPid']),
                    ],
                ]
            );

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
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $admin
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @param integer $pid
     * @param mixed $signalInformation
     * @return void
     * @throws \RKW\RkwMailer\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function handleAdminServiceEvent(
        BackendUser $admin,
        FrontendUser $frontendUser,
        FrontendUserGroup $frontendUserGroup,
        OptIn $optIn,
        int $pid,
        $signalInformation
    ) {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo(
                $admin,
                [
                    'marker' => [
                        'tokenYes'          => $optIn->getTokenYes(),
                        'tokenNo'           => $optIn->getTokenNo(),
                        'serviceSha1'       => $optIn->getTokenUser(), /** @deprecated */
                        'tokenUser'         => $optIn->getTokenUser(),
                        'service'           => $optIn, /** @deprecated */
                        'optIn'             => $optIn,
                        'frontendUser'      => $frontendUser,
                        'frontendUserGroup' => $frontendUserGroup,
                        'backendUser'       => $admin,
                        'pageUid'           => intval($pid),
                    ],
                ]
            );

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
    public function handleAdminServiceGrantEvent(FrontendUser $frontendUser, OptIn $optIn, $signalInformation): void
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
                        'service'      => $optIn, /** @deprecated */
                        'optIn'        => $optIn,
                        'frontendUser' => $frontendUser,
                        'pageUid'      => intval($GLOBALS['TSFE']->id),
                        'loginPid'     => intval($settingsDefault['users']['loginPid']),
                    ],
                ]
            );

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
    public function handleAdminServiceDenialEvent(FrontendUser $frontendUser, OptIn $optIn, $signalInformation)
    {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo(
                $frontendUser,
                [
                    'marker' => [
                        'service'      => $optIn, /** @deprecated */
                        'optIn'        => $optIn,
                        'frontendUser' => $frontendUser,
                        'pageUid'      => intval($GLOBALS['TSFE']->id),
                    ]
                ]
            );

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
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return GeneralUtility::getTyposcriptConfiguration('Rkwregistration', $which);
    }
}
