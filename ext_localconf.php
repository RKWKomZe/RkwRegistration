<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'AuthInternal',
            [
                'Auth' => 'index, login, logout, logoutRedirect',
                'AuthGuest' => 'login, loginHint',
                'FrontendUser' => 'new, create, optIn, index',
                'FrontendUserGroup' => 'optIn',
                'Password' => 'new, create',

                //'Registration' => 'new, create, optIn, index',
                //'Password' => 'new, create',
            ],
            // non-cacheable actions
            [
                'Auth' => 'index, login, logout, logoutRedirect',
                'AuthGuest' => 'login, loginHint',
                'FrontendUser' => 'new, create, optIn, index',
                'FrontendUserGroup' => 'optIn',
                'Password' => 'new, create',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'LogoutInternal',
            [
                'Auth' => 'logout, logoutRedirect, index',
            ],
            // non-cacheable actions
            [
                'Auth' => 'logout, logoutRedirect, index',
            ]
        );


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Password',
            [
                'Password' => 'edit, update, redirectDisabledUser',
                'Auth' => 'index',
                'FrontendUser' => 'index'
            ],
            // non-cacheable actions
            [
                'Password' => 'edit, update, redirectDisabledUser',
                'Auth' => 'index',
                'FrontendUser' => 'index'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserWelcome',
            [
                'FrontendUser' => 'welcome, index',
                'Auth' => 'index'
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'welcome, index',
                'Auth' => 'index'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserEdit',
            [
                'FrontendUser' => 'edit, update, index',
                'Auth' => 'index'
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'edit, update, index',
                'Auth' => 'index'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserDelete',
            [
                'FrontendUser' => 'show, delete, index',
                'Auth' => 'index'
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'show, delete, index',
                'Auth' => 'index'
            ]
        );


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserGroup',
            [
                'FrontendUserGroup' => 'list, show, create, delete',
                'Auth' => 'index',
                'FrontendUser' => 'index'
            ],
            // non-cacheable actions
            [
                'FrontendUserGroup' => 'list, show, create, delete',
                'Auth' => 'index',
                'FrontendUser' => 'index'            ]
        );


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Info',
            [
                'Info' => 'index, loginInfo'
            ],
            // non-cacheable actions
            [
                'Info' => 'index, loginInfo'
            ]
        );

        //=================================================================
        // Register Hook for Backend-Delete
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][$extKey] = 'RKW\\RkwRegistration\\Hooks\\DatahandlerHook';


        //=================================================================
        // Register Signal-Slots
        //=================================================================
        /**
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher->connect(
            RKW\RkwRegistration\Registration\AbstractRegistration::class,
            \RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_CREATING_OPTIN,
            \RKW\RkwRegistration\Service\RkwMailService::class,
            'sendOptInEmail'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwRegistration\Registration\AbstractRegistration::class,
            \RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_REGISTRATION_COMPLETED,
            \RKW\RkwRegistration\Service\RkwMailService::class,
            'sendConfirmationEmail'
        );

        $signalSlotDispatcher->connect(
            \RKW\RkwRegistration\Controller\PasswordController::class,
            \RKW\RkwRegistration\Controller\PasswordController::SIGNAL_AFTER_USER_PASSWORD_RESET,
            \RKW\RkwRegistration\Service\RkwMailService::class,
            'sendResetPasswordEmail'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwRegistration\Registration\AbstractRegistration::class,
            \RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_CREATING_OPTIN . 'RkwRegistrationGroups',
            \RKW\RkwRegistration\Service\RkwMailService::class,
            'sendGroupOptInEmail'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwRegistration\Registration\AbstractRegistration::class,
            \RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_CREATING_OPTIN_ADMIN . 'RkwRegistrationGroups',
            \RKW\RkwRegistration\Service\RkwMailService::class,
            'sendGroupOptInEmailAdmin'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwRegistration\Registration\AbstractRegistration::class,
            \RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_REGISTRATION_COMPLETED . 'RkwRegistrationGroups',
            \RKW\RkwRegistration\Service\RkwMailService::class,
            'createMembership'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwRegistration\Registration\AbstractRegistration::class,
            \RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_DENIAL_OPTIN . 'RkwRegistrationGroups',
            \RKW\RkwRegistration\Service\RkwMailService::class,
            'sendGroupOptInWithdrawEmailAdmin'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwRegistration\Registration\AbstractRegistration::class,
            \RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_DENIAL_OPTIN_ADMIN . 'RkwRegistrationGroups',
            \RKW\RkwRegistration\Service\RkwMailService::class,
            'sendGroupOptInDenialEmail'
        );

        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwRegistration']['writerConfiguration'] = [

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::INFO => [
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
                    // configuration for the writer
                    'logFile' => 'typo3temp/var/logs/tx_rkwregistration.log'
                ]
            ],
        ];


        //=================================================================
        // AuthService
        //=================================================================
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            $_EXTKEY,
            'auth',
            \RKW\RkwRegistration\Service\FrontendUserAuthenticationService::class,
            [
                'title' => 'Authentication Service for fe_users',
                'description' => 'Authentication Service for fe_users',
                'subtype' => 'getUserFE, authUserFE, getGroupsFE, processLoginDataFE',
                'available' => true,
                'priority' => 90,
                'quality' => 90,
                'os' => '',
                'exec' => '',
                'className' => \RKW\RkwRegistration\Service\FrontendUserAuthenticationService::class
            ]
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            $_EXTKEY,
            'auth',
            \RKW\RkwRegistration\Service\GuestUserAuthenticationService::class,
            [
                'title' => 'Authentication Service for fe_users',
                'description' => 'Authentication Service for fe_users',
                'subtype' => 'getUserFE, authUserFE, getGroupsFE, processLoginDataFE',
                'available' => true,
                'priority' => 80,
                'quality' => 80,
                'os' => '',
                'exec' => '',
                'className' => \RKW\RkwRegistration\Service\GuestUserAuthenticationService::class
            ]
        );

    },
    $_EXTKEY
);
