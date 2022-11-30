<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(

    function () {

        //=================================================================
        // Register Plugins
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'Auth',
            'RKW Registration: Login'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'Logout',
            'RKW Registration: Logout'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'Password',
            'RKW Registration: Passwort'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'Welcome',
            'RKW Registration: Willkommen'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'UserEdit',
            'RKW Registration: FrontendUser (editieren)'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'UserDelete',
            'RKW Registration: FrontendUser (löschen)'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'Group',
            'RKW Registration: FrontendUserGroup'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRegistration',
            'Info',
            'RKW Registration: Info'
        );
    }
);
