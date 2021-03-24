<?php
defined('TYPO3_MODE') || die('Access denied.');

//=================================================================
// Register Plugin
//=================================================================
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'Register',
    'RKW Registration: Registrierung'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'Welcome',
    'RKW Registration: Willkommen'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'AuthInternal',
    'RKW Registration: Authentifizierung'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'AuthExternal',
    'RKW Registration: Authentifizierung (extern)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'LogoutInternal',
    'RKW Registration: Logout'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'LogoutExternal',
    'RKW Registration: Logout (extern)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'Password',
    'RKW Registration: Passwort'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'FrontendUserEdit',
    'RKW Registration: FrontendUser (editieren)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'FrontendUserDelete',
    'RKW Registration: FrontendUser (löschen)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'Service',
    'RKW Registration: Service'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'ServiceOptIn',
    'RKW Registration: Service (OptIn)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'RkwregistrationAjax',
    'RKW Registration Ajax'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'RkwregistrationInfo',
    'RKW Registration Info'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RKW.RkwRegistration',
    'GoBack',
    'RKW Registration: Zurück (Redirect-URL)'
);

//=================================================================
// Add Flexform
//=================================================================
$extKey = 'rkw_registration';
$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extKey));
$pluginName = strtolower('Rkwregistration');
$pluginSignature = $extensionName.'_'.$pluginName;

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:'.$extKey . '/Configuration/FlexForms/Registration.xml'
);
