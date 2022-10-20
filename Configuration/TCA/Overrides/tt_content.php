<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(

    function () {

        //=================================================================
        // Register Plugins
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'Register',
            'RKW Registration: Registrierung'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RkwRegistration',
            'AuthInternal',
            'RKW Registration: Login'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'LogoutInternal',
            'RKW Registration: Logout'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'Password',
            'RKW Registration: Passwort'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'FrontendUserWelcome',
            'RKW Registration: Willkommen'
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
            'FrontendUserGroup',
            'RKW Registration: FrontendUserGroup'
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
            'Info',
            'RKW Registration Info'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'GoBack',
            'RKW Registration: Zurück (Redirect-URL)'
        );
    }
);
