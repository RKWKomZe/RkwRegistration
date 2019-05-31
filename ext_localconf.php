<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'RKW.' . $_EXTKEY,
	'Rkwregistration',
	array(
		'Registration' => 'index, welcome, welcomeMessage, xdlLoginShow, loginShow, loginShowExternal, loginTwitter, login, xdlLogin, xdlLogout, logout, logoutExternal, loginAnonymous, loginHintAnonymous, registerShow, register, optin, passwordForgot, passwordForgotShow, createTwitterUser, editUser, updateUser, deleteUserShow, deleteUser, editPassword, updatePassword',
        'Service' => 'list, show, create, delete, optIn',
	),
	// non-cacheable actions
	array(
        'Registration' => 'index, welcome, welcomeMessage, xdlLoginShow, loginShow, loginShowExternal, loginTwitter, login, xdlLogin, xdlLogout, logout, logoutExternal, loginAnonymous, loginHintAnonymous, registerShow, register, optin, passwordForgot, passwordForgotShow, createTwitterUser, editUser, updateUser, deleteUserShow, deleteUser, editPassword, updatePassword',
        'Service' => 'list, show, create, delete, optIn',
	)
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'RKW.' . $_EXTKEY,
    'RkwregistrationAjax',
    array(
        'Ajax' => 'loginInfoInit, loginInfo'
    ),
    // non-cacheable actions
    array(
        'Ajax' => 'loginInfo'
    )
);

// Command controller registrieren
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'RKW\\RkwRegistration\\Controller\\CleanupCommandController';

// register hook for backend delete
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][$_EXTKEY] = 'RKW\\RkwRegistration\\Hooks\\DatahandlerHook';

// set logger
$GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwRegistration']['writerConfiguration'] = array(

    // configuration for WARNING severity, including all
    // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
    \TYPO3\CMS\Core\Log\LogLevel::INFO => array(
        // add a FileWriter
        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
            // configuration for the writer
            'logFile' => 'typo3temp/logs/tx_rkwregistration.log'
        )
    ),
);

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
