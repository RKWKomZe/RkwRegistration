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
            array(
                'Registration' => 'new, create, optIn',
                'Service' => 'list, show, create, delete, optIn',
            ),
            // non-cacheable actions
            array(
                'Registration' => 'new, create, optIn',
                'Service' => 'list, show, create, delete, optIn',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Welcome',
            array(
                'Registration' => 'index',
                //'FrontendUser' => 'edit, update',
            ),
            // non-cacheable actions
            array(
                'Registration' => 'index',
                //'FrontendUser' => 'edit, update',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'AuthInternal',
            array(
                'Auth' => 'index, login, logout, logoutExternal, loginAnonymous, loginHintAnonymous',
                //'Registration' => 'new, create, optIn, index',
                //'Password' => 'new, create',
            ),
            // non-cacheable actions
            array(
                'Auth' => 'index, login, logout, logoutExternal, loginAnonymous, loginHintAnonymous',
                //'Registration' => 'new, create, optIn, index',
                //'Password' => 'new, create',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'AuthExternal',
            array(
                'Auth' => 'loginExternal, logoutExternal, loginAnonymous, loginHintAnonymous, xdlLogin, xdlLogout',
            ),
            // non-cacheable actions
            array(
                'Auth' => 'loginExternal, logoutExternal, loginAnonymous, loginHintAnonymous, xdlLogin, xdlLogout',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'LogoutInternal',
            array(
                'Auth' => 'logout',
            ),
            // non-cacheable actions
            array(
                'Auth' => 'logout',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'LogoutExternal',
            array(
                'Auth' => 'logoutExternal',
            ),
            // non-cacheable actions
            array(
                'Auth' => 'logoutExternal',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'GoBack',
            array(
                'Registration' => 'goBack'
            ),
            // non-cacheable actions
            array(
                'Registration' => 'goBack'
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Password',
            array(
                'Password' => 'edit, update, new, create',
                //'Authentication' => 'logout, index',
            ),
            // non-cacheable actions
            array(
                'Password' => 'edit, update, new, create',
                //'Authentication' => 'logout, index',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserEdit',
            array(
                'FrontendUser' => 'edit, update',
                //'Registration' => 'index',
                //'Authentication' => 'logout',
            ),
            // non-cacheable actions
            array(
                'FrontendUser' => 'edit, update',
                //'Registration' => 'index',
                //'Authentication' => 'logout',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'FrontendUserDelete',
            array(
                'FrontendUser' => 'show, delete',
                //'Registration' => 'index',
                //'Authentication' => 'logout',
            ),
            // non-cacheable actions
            array(
                'FrontendUser' => 'show, delete',
                //'Registration' => 'index',
                //'Authentication' => 'logout',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Service',
            array(
                'Service' => 'list, show, create, delete, index',
            ),
            // non-cacheable actions
            array(
                'Service' => 'list, show, create, delete, index',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'ServiceOptIn',
            array(
                'Service' => 'optIn, index',
            ),
            // non-cacheable actions
            array(
                'Service' => 'optIn, index',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'RkwregistrationAjax',
            array(
                'Ajax' => 'loginInfoInit, loginInfo'
            ),
            // non-cacheable actions
            array(
                'Ajax' => 'loginInfo'
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'RkwregistrationInfo',
            array(
                'Info' => 'loginInfo'
            ),
            // non-cacheable actions
            array(
                'Info' => 'loginInfo'
            )
        );

        //=================================================================
        // Register CommandController
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'RKW\\RkwRegistration\\Controller\\CleanupCommandController';

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
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_OPTIN_USER,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleCreateUserEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_FINAL_USER,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleRegisterUserEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_USER_REGISTER_GRANT,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleRegisterUserEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Controller\\RegistrationController',
            \RKW\RkwRegistration\Controller\RegistrationController::SIGNAL_AFTER_USER_PASSWORD_RESET,
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
            'RKW\\RkwRegistration\\Tools\\Service',
            \RKW\RkwRegistration\Tools\Service::SIGNAL_AFTER_ADMIN_SERVICE_GRANT,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleAdminServiceGrantEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Service',
            \RKW\RkwRegistration\Tools\Service::SIGNAL_AFTER_ADMIN_SERVICE_DENIAL,
            'RKW\\RkwRegistration\\Service\\RkwMailService',
            'handleAdminServiceDenialEvent'
        );

        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwRegistration']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::INFO => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => 'typo3temp/var/logs/tx_rkwregistration.log'
                )
            ),
        );


        //=================================================================
        // register update wizard
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\RKW\RkwRegistration\Updates\PluginUpdate::class] = \RKW\RkwRegistration\Updates\PluginUpdate::class;



    },
    $_EXTKEY
);
