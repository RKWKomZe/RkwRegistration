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
                'FrontendUser' => 'new, create, index',
                'Password' => 'new, create',
                'Registration' => 'optIn',

                //'Registration' => 'new, create, optIn, index',
                //'Password' => 'new, create',
            ],
            // non-cacheable actions
            [
                'Auth' => 'index, login, logout, logoutRedirect',
                'AuthGuest' => 'login, loginHint',
                'FrontendUser' => 'new, create, index',
                'Password' => 'new, create',
                'Registration' => 'optIn',
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


        /** @todo set routes for the following plugins - but only if they are still needed! */



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
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Registration\\FrontendUser\\AbstractRegistration',
            \RKW\RkwRegistration\Registration\FrontendUser\AbstractRegistration::SIGNAL_AFTER_CREATING_OPTIN,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'sendOptInEmail'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Registration\\FrontendUser\\AbstractRegistration',
            \RKW\RkwRegistration\Registration\FrontendUser\AbstractRegistration::SIGNAL_AFTER_CREATING_OPTIN . 'RkwRegistrationGroups',
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'sendOptInEmailGroup'
        );


        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Registration\\FrontendUser\\AbstractRegistration',
            \RKW\RkwRegistration\Registration\FrontendUser\AbstractRegistration::SIGNAL_AFTER_REGISTRATION_COMPLETED,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'sendPasswordEmail'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Controller\\PasswordController',
            \RKW\RkwRegistration\Controller\PasswordController::SIGNAL_AFTER_USER_PASSWORD_RESET,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'sendResetPasswordEmail'
        );

        /*
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
*/
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
