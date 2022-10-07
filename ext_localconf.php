<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugin
        //=================================================================
        /*\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Register',
            [
                'Registration' => 'index, optIn',
                'Service' => 'index, list, show, create, delete, optIn',
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'index, new, create',
                'Registration' => 'index, optIn',
                'Service' => 'index, list, show, create, delete, optIn',
            ]
        );*/

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'AuthInternal',
            [
                'Auth' => 'index, login, logout, logoutRedirect',
                'AuthGuest' => 'login, loginHint',
                'FrontendUser' => 'index, new, create',
                'Password' => 'new, create',
                'Registration' => 'optIn',

                //'Registration' => 'new, create, optIn, index',
                //'Password' => 'new, create',
            ],
            // non-cacheable actions
            [
                'Auth' => 'index, login, logout, logoutRedirect',
                'AuthGuest' => 'login, loginHint',
                'FrontendUser' => 'index, new, create',
                'Password' => 'new, create',
                'Registration' => 'optIn',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'LogoutInternal',
            [
                'Auth' => 'index, logout, logoutRedirect',
            ],
            // non-cacheable actions
            [
                'Auth' => 'index, logout, logoutRedirect',
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
                'FrontendUser' => 'index, welcome',
                'Auth' => 'index'
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'index, welcome',
                'Auth' => 'index'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserEdit',
            [
                'FrontendUser' => 'index, edit, update',
                'Auth' => 'index'
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'index, edit, update',
                'Auth' => 'index'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserDelete',
            [
                'FrontendUser' => 'index, show, delete',
                'Auth' => 'index'
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'index, show, delete',
                'Auth' => 'index'
            ]
        );






        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Service',
            [
                'Service' => 'index, list, show, create, delete, index',
            ],
            // non-cacheable actions
            [
                'Service' => 'index, list, show, create, delete, index',
            ]
        );

        /*
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'ServiceOptIn',
            [
                'Service' => 'index, optIn, index',
            ],
            // non-cacheable actions
            [
                'Service' => 'index, optIn, index',
            ]
        );*/

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'RkwregistrationAjax',
            [
                'Ajax' => 'index, loginInfoInit, loginInfo'
            ],
            // non-cacheable actions
            [
                'Ajax' => 'index, loginInfo'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'RkwregistrationInfo',
            [
                'Info' => 'index, loginInfo'
            ],
            // non-cacheable actions
            [
                'Info' => 'index, loginInfo'
            ]
        );

        //=================================================================
        // Register CommandController
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'RKW\\RkwRegistration\\Controller\\CleanupCommandController';

        //=================================================================
        // Register Hook for Backend-Delete
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][$extKey] = 'RKW\\RkwRegistration\\Hooks\\DatahandlerHook';

        /*
        if (TYPO3_MODE !== 'BE') {
            // was needed for dynamic myrkw domains
            //$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'RKW\\RkwRegistration\\Hooks\\PseudoBaseUrlHook->hook_contentPostProc';
        }
        */

        //=================================================================
        // Register Signal-Slots
        //=================================================================
        /**
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Registration\\OptInRegistration',
            \RKW\RkwRegistration\Registration\OptInRegistration::SIGNAL_AFTER_CREATING_OPTIN_NEW_USER,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleCreateUserEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Registration\\OptInRegistration',
            \RKW\RkwRegistration\Registration\OptInRegistration::SIGNAL_AFTER_CREATING_FINAL_USER,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleRegisterUserEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Registration\\OptInRegistration',
            \RKW\RkwRegistration\Registration\OptInRegistration::SIGNAL_AFTER_REGISTRATION_COMPLETED,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleRegisterUserEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Controller\\PasswordController',
            \RKW\RkwRegistration\Controller\PasswordController::SIGNAL_AFTER_USER_PASSWORD_RESET,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handlePasswordResetEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Controller\\ServiceController',
            \RKW\RkwRegistration\Controller\ServiceController::SIGNAL_ADMIN_SERVICE_REQUEST,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleAdminServiceEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Registration\\GroupRegister',
            \RKW\RkwRegistration\Registration\GroupRegister::SIGNAL_AFTER_ADMIN_SERVICE_GRANT,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleAdminServiceGrantEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Registration\\GroupRegister',
            \RKW\RkwRegistration\Registration\GroupRegister::SIGNAL_AFTER_ADMIN_SERVICE_DENIAL,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleAdminServiceDenialEvent'
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
        // register update wizard
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\RKW\RkwRegistration\Updates\PluginUpdate::class] = \RKW\RkwRegistration\Updates\PluginUpdate::class;


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

        /*\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            $extKey,
            'auth',
            RKW\RkwRegistration\Service\HashAuthentificationService::class,
            [
                'title' => 'Authentication Service for fe_users',
                'description' => 'Authentication Service for fe_users',
                //'subtype' => 'authUserFE,getGroupsFE,getUserFE,processLoginDataFE',
                'subtype' => '',
                'available' => true,
                'priority' => 80,
                'quality' => 80,
                'os' => '',
                'exec' => '',
                'className' => RKW\RkwRegistration\Service\HashAuthentificationService::class
            ]
        );*/

        # It is possible to force TYPO3 CMS to go through the authentication process for every request no matter any existing session.
        # By setting the following local configuration either for the FE or the BE:
    #    $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;
    #    $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysAuthUser'] = true;


    },
    $_EXTKEY
);
