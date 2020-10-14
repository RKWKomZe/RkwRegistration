<?php

$tempCols = [

    'tx_rkwregistration_is_service' => [
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_is_service',
        'exclude' => 0,
        'config'=>[
            'type' => 'check',
            'default' => 0,
            'items' => [
                '1' => [
                    '0' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_is_service.I.enabled'
                ],
            ],
        ],
    ],

	'tx_rkwregistration_service_opening_date' => [
		'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_opening_date',
		'exclude' => 0,
		'config' => [
			'type' => 'input',
            'renderType' => 'inputDateTime',
			'size' => '13',
			'eval' => 'date',
			'default' => '0'
		],

	],
	'tx_rkwregistration_service_closing_date' => [
		'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_closing_date',
		'exclude' => 0,
		'config'=>[
			'type' => 'input',
            'renderType' => 'inputDateTime',
			'size' => '13',
			'eval' => 'date',
			'default' => '0'
		],
	],
    'tx_rkwregistration_service_mandatory_fields' => [
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_mandatory_fields',
        'exclude' => 0,
        'config'=>[
            'type' => 'input',
            'size' => '50',
            'max' => '256',
            'eval' => 'trim',
        ],
    ],

    'tx_rkwregistration_service_admins' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_admins',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'foreign_table' => 'be_users',
            'MM' => 'tx_rkwregistration_fegroups_beusers_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 9999,
            'multiple' => 0,
        ],
    ],

    'tx_rkwregistration_service_pid' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_pid',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
            'size' => '30',
            'max' => '256',
            'eval' => 'trim',
            'softref' => 'typolink'
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups',$tempCols);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'tx_rkwregistration_is_service, tx_rkwregistration_service_opening_date, tx_rkwregistration_service_closing_date, tx_rkwregistration_service_mandatory_fields, tx_rkwregistration_service_pid, tx_rkwregistration_service_admins');
