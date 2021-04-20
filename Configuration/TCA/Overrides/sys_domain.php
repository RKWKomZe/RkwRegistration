<?php
$tempCols = [

    'tx_rkwregistration_fallback' => [
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tx_rkwregistration_fallback',
        'exclude' => 0,
        'config'=>[
            'type' => 'check',
            'default' => 0,
            'items' => [
                '1' => [
                    '0' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tx_rkwregistration_fallback.I.enabled'
                ],
            ],
        ],
    ],

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
        'displayCond' => 'FIELD:tx_rkwregistration_fallback:=:0',
	],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_domain', $tempCols);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_domain',
    '--div--;LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_sysdomain.tabs.myRkw,tx_rkwregistration_fallback,tx_rkwregistration_related_sys_domain',
    '',
    'after:sys_domain');

