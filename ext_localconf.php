<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugin
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Register',
            [
                'FrontendUser' => 'new, create',
                'Registration' => 'optIn',
                'Service' => 'list, show, create, delete, optIn',
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'new, create',
                'Registration' => 'optIn',
                'Service' => 'list, show, create, delete, optIn',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Welcome',
            [
                'Registration' => 'index',
                //'FrontendUser' => 'edit, update',
            ],
            // non-cacheable actions
            [
                'Registration' => 'index',
                //'FrontendUser' => 'edit, update',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'AuthInternal',
            [
                'Auth' => 'index, login, logoutExternal, loginAnonymous, loginHintAnonymous',
                'Password' => 'new, create',
                //'Registration' => 'new, create, optIn, index',
                //'Password' => 'new, create',
            ],
            // non-cacheable actions
            [
                'Auth' => 'index, login, logoutExternal, loginAnonymous, loginHintAnonymous',
                'Password' => 'new, create',
                //'Registration' => 'new, create, optIn, index',
                //'Password' => 'new, create',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'AuthExternal',
            [
                'Auth' => 'loginExternal, logoutExternal, loginAnonymous, loginHintAnonymous',
                'AuthGuest' => 'login, loginHint',
            ],
            // non-cacheable actions
            [
                'Auth' => 'loginExternal, logoutExternal, loginAnonymous, loginHintAnonymous',
                'AuthGuest' => 'login, loginHint',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'LogoutInternal',
            [
                'Auth' => 'logout, logoutRedirect',
            ],
            // non-cacheable actions
            [
                'Auth' => 'logout, logoutRedirect',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'LogoutExternal',
            [
                'Auth' => 'logoutExternal',
            ],
            // non-cacheable actions
            [
                'Auth' => 'logoutExternal',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Password',
            [
                'Password' => 'edit, update',
            ],
            // non-cacheable actions
            [
                'Password' => 'edit, update',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserEdit',
            [
                'FrontendUser' => 'edit, update',
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'edit, update',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserDelete',
            [
                'FrontendUser' => 'show, delete',
            ],
            // non-cacheable actions
            [
                'FrontendUser' => 'show, delete',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Service',
            [
                'Service' => 'list, show, create, delete, index',
            ],
            // non-cacheable actions
            [
                'Service' => 'list, show, create, delete, index',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'ServiceOptIn',
            [
                'Service' => 'optIn, index',
            ],
            // non-cacheable actions
            [
                'Service' => 'optIn, index',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'RkwregistrationAjax',
            [
                'Ajax' => 'loginInfoInit, loginInfo'
            ],
            // non-cacheable actions
            [
                'Ajax' => 'loginInfo'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'RkwregistrationInfo',
            [
                'Info' => 'loginInfo'
            ],
            // non-cacheable actions
            [
                'Info' => 'loginInfo'
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

        if (TYPO3_MODE !== 'BE') {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'RKW\\RkwRegistration\\Hooks\\PseudoBaseUrlHook->hook_contentPostProc';
        }

        //=================================================================
        // Register Signal-Slots
        //=================================================================
        /**
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Register\\OptInRegister',
            \RKW\RkwRegistration\Register\OptInRegister::SIGNAL_AFTER_CREATING_OPTIN_USER,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleCreateUserEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Register\\OptInRegister',
            \RKW\RkwRegistration\Register\OptInRegister::SIGNAL_AFTER_CREATING_FINAL_USER,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleRegisterUserEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Register\\OptInRegister',
            \RKW\RkwRegistration\Register\OptInRegister::SIGNAL_AFTER_USER_REGISTER_GRANT,
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
            'RKW\\RkwRegistration\\Register\\GroupRegister',
            \RKW\RkwRegistration\Register\GroupRegister::SIGNAL_AFTER_ADMIN_SERVICE_GRANT,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleAdminServiceGrantEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Register\\GroupRegister',
            \RKW\RkwRegistration\Register\GroupRegister::SIGNAL_AFTER_ADMIN_SERVICE_DENIAL,
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
            $extKey,
            'auth',
            RKW\RkwRegistration\Service\AuthFrontendUserService::class,
            [
                'title' => 'Authentication Service for fe_users',
                'description' => 'Authentication Service for fe_users',
                //'subtype' => 'authUserFE,getGroupsFE,getUserFE,processLoginDataFE',
                'subtype' => 'authUserFE, getUserFE',
                'available' => true,
                'priority' => 80,
                'quality' => 80,
                'os' => '',
                'exec' => '',
                'className' => RKW\RkwRegistration\Service\AuthFrontendUserService::class
            ]
        );

        # It is possible to force TYPO3 CMS to go through the authentication process for every request no matter any existing session.
        # By setting the following local configuration either for the FE or the BE:
    #    $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;
    #    $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysAuthUser'] = true;


    },
    $_EXTKEY
);
