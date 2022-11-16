<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent',
		'label' => 'extension_name',
		'label_alt' => 'action_name,informed_consent_reason',
		'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => 1,
		'delete' => 'deleted',
		'searchFields' => 'crdate, foreign_table, foreign_uid, ip_address, user_agent, extension_name, plugin_name, controller_name, action_name, comment, server_host, server_uri, server_referer_url, consent_privacy, consent_terms, consent_marketing',
		'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_consent.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'crdate, foreign_table, foreign_uid, ip_address, user_agent, extension_name, plugin_name, controller_name, action_name, comment, server_host, server_uri, server_referer_url, consent_privacy, consent_terms, consent_marketing, child',
	],
	'types' => [
		'1' => ['showitem' => 'crdate, foreign_table, foreign_uid, ip_address, user_agent, extension_name, plugin_name, controller_name, action_name, comment, server_host, server_uri, server_referer_url, consent_privacy, consent_terms, consent_marketing, child'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

		'frontend_user' => [
			'config' => [
				'type' => 'passthrough',
                'foreign_table' => 'fe_users',
			],
		],
		'opt_in' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwregistration_domain_model_optin',
            ],
		],
		'parent' => [
			'config' => [
				'type' => 'passthrough',
				'foreign_table' => 'tx_rkwregistration_domain_model_consent',
				'foreign_field' => 'uid',
			],
		],
		'child' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.child',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_rkwregistration_domain_model_consent',
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
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.crdate',
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
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.foreign_table',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'readOnly' => 1,
			],
		],
		'foreign_uid' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.foreign_uid',
			'config' => [
				'type' => 'input',
				'size' => 5,
				'max' => 10,
				'eval' => 'trim',
				'readOnly' => 1,
			],
		],
		'ip_address' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.ip_address',
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
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.user_agent',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'readOnly' => 1,
			],
		],
		'extension_name' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.extension_name',
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
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.plugin_name',
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
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.controller_name',
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
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.action_name',
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
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.comment',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'readOnly' => 1,
			],
		],
		'server_host' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.server_host',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'readOnly' => 1,
			],
		],
		'server_uri' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.server_uri',
			'config' => [
				'type' => 'text',
				'cols' => 80,
				'rows' => 1,
				'readOnly' => 1,
			],
		],
		'server_referer_url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.server_referer_url',
			'config' => [
				'type' => 'text',
				'cols' => 80,
				'rows' => 1,
				'readOnly' => 1,
			],
		],
        'consent_privacy' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.consent_privacy',
            'config' => [
                'type' => 'check',
            ],
        ],
        'consent_terms' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.consent_terms',
            'config' => [
                'type' => 'check',
            ],
        ],
        'consent_marketing' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_consent.consent_marketing',
            'config' => [
                'type' => 'check',
            ],
        ],

	],
];
