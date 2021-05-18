<?php
$tempCols = [

	'tx_rkwregistration_related_sys_domain' => [
		'exclude' => 1,
		'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tx_rkwregistration_related_sys_domain',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'foreign_table' => 'sys_domain',
            'foreign_table_where' => 'AND sys_domain.hidden = 0 ORDER BY domainName ASC',
            'maxitems' => 1,
            'multiple' => 0,
            'items' => [
                ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tx_rkwregistration_related_sys_domain.I.neutral', 0],
            ],
        ],
        // @toDo: How to set this value via TsConfig?
        //'displayCond' => 'FIELD:pid:=:3577',
	],

    'tx_rkwregistration_page_login' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tx_rkwregistration_page_login',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'pages',
            'maxitems' => 1,
            'minitems' => 0,
            'size' => 1,
            'default' => 0,
            'suggestOptions' => [
                'default' => [
                    'additionalSearchFields' => 'nav_title, alias, url',
                    'addWhere' => 'AND pages.doktype = 1'
                ]
            ]
        ]
    ],

    'tx_rkwregistration_page_logout' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tx_rkwregistration_page_logout',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'pages',
            'maxitems' => 1,
            'minitems' => 0,
            'size' => 1,
            'default' => 0,
            'suggestOptions' => [
                'default' => [
                    'additionalSearchFields' => 'nav_title, alias, url',
                    'addWhere' => 'AND pages.doktype = 1'
                ]
            ]
        ]
    ],

    'tx_rkwregistration_page_login_guest' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tx_rkwregistration_page_login_guest',
        'config' => [
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'pages',
            'maxitems' => 1,
            'minitems' => 0,
            'size' => 1,
            'default' => 0,
            'suggestOptions' => [
                'default' => [
                    'additionalSearchFields' => 'nav_title, alias, url',
                    'addWhere' => 'AND pages.doktype = 1'
                ]
            ]
        ]
    ],

];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_domain', $tempCols);

// Feld einer neuen Palette hinzufügen
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_domain',
    'registeredUsers',
    'tx_rkwregistration_page_login, tx_rkwregistration_page_logout'
);

// Feld einer neuen Palette hinzufügen
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_domain',
    'guestUsers',
    'tx_rkwregistration_page_login_guest'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_domain',
    '
    --div--;LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tabs.myRkw,
    tx_rkwregistration_related_sys_domain,
    --palette--;LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.palette.registeredUsers;registeredUsers,
    --palette--;LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.palette.guestUsers;anonymousUsers,
    ',
    '',
    'after:sys_domain');

