<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy',
		'label' => 'extension_name',
		'label_alt' => 'action_name,informed_consent_reason',
		'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => TRUE,
		'hideTable' => 1,
		'delete' => 'deleted',
		'searchFields' => 'crdate,foreign_table,foreign_uid,ip_address,user_agent,extension_name,plugin_name,controller_name,action_name,informed_consent_reason,server_host,server_uri,server_referer_url,child',
		'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_privacy.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'crdate, foreign_table, foreign_uid, ip_address, user_agent, extension_name, plugin_name, controller_name, action_name, informed_consent_reason, server_host, server_uri,server_referer_url, child',
	],
	'types' => [
		'1' => ['showitem' => 'crdate, foreign_table, foreign_uid, ip_address, user_agent, extension_name, plugin_name, controller_name, action_name, informed_consent_reason, server_host, server_uri,server_referer_url, child'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

		'frontend_user' => [
			'config' => [
				'type' => 'passthrough',
			],
		],
		'registration_user_sha1' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'parent' => [
			'config' => [
				'type' => 'passthrough',
				'foreign_table' => 'tx_rkwregistration_domain_model_privacy',
				'foreign_field' => 'uid',
			],
		],
		'child' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.child',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_rkwregistration_domain_model_privacy',
				'foreign_field' => 'parent',
				'maxitems'      => 1,
				'appearance' => [
					'collapseAll' => 1,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1,
					'enabledControls' => [
						'new' => FALSE,
					],
				],
				'readOnly' => 1,
			],
		],
		'crdate' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.crdate',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime, required',
				'checkbox' => 0,
				'default' => 0,
				'readOnly' => 1,
			],
		],

		'foreign_table' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.foreign_table',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 30,
				'eval' => 'datetime',
				'readOnly' => 1,
			],
		],
		'foreign_uid' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.foreign_uid',
			'config' => [
				'type' => 'input',
				'size' => 5,
				'max' => 10,
				'eval' => 'trim, int, required',
				'readOnly' => 1,
			],
		],
		'ip_address' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.ip_address',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			],
		],
		'user_agent' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.user_agent',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'readOnly' => 1,
			],
		],
		'extension_name' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.extension_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			],
		],
		'plugin_name' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.plugin_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			],
		],
		'controller_name' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.controller_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			],
		],
		'action_name' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.action_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			],
		],
		'comment' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.comment',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'readOnly' => 1,
			],
		],
		'server_host' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.server_host',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'readOnly' => 1,
			],
		],
		'server_uri' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.server_uri',
			'config' => [
				'type' => 'text',
				'cols' => 80,
				'rows' => 1,
				'readOnly' => 1,
			],
		],
		'server_referer_url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.server_referer_url',
			'config' => [
				'type' => 'text',
				'cols' => 80,
				'rows' => 1,
				'readOnly' => 1,
			],
		],
	],
];
