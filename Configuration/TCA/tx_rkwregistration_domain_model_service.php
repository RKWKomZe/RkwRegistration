<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service',
		'label' => 'user',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => TRUE,
		'hideTable' => 1,

		'searchFields' => 'user ,usergroup, enabled_by_admin',
		'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_service.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'user, usergroup, enabled_by_admin, token_yes, token_no, valid_until, service_sha1',
	],
	'types' => [
		'1' => ['showitem' => 'user, usergroup, enabled_by_admin, token_yes, token_no, valid_until, service_sha1'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

		'user' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.user',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'size' => 4,
				'eval' => 'int',
				'foreign_class' => 'Tx_Extbase_Domain_Model_FrontendUser',
				'foreign_table' => 'fe_users',
				'foreign_table_where' => 'AND fe_users.disable = 0',
			],
		],

		'usergroup' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.usergroup',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'size' => 4,
				'eval' => 'int',
				'foreign_class' => 'Tx_Extbase_Domain_Model_FrontendUserGroup',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'AND fe_groups.hidden = 0',
			],
		],

		'enabled_by_admin' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.enabled_by_admin',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
				'default' => time(),
			],
		],

		'token_yes' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.token_yes',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			],
		],

		'token_no' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.token_no',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			],
		],

		'valid_until' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.valid_until',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
				'default' => time(),
			],
		],

		'service_sha1' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.service_sha1',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			],
		],
	],
];
