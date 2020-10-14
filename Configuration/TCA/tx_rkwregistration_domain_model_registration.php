<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration',
		'label' => 'valid_until',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => TRUE,
		'hideTable' => 1,

		'searchFields' => 'category, user, usergroup',
		'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_registration.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'category, user, token_yes, token_no, valid_until, data',
	],
	'types' => [
		'1' => ['showitem' => 'category, user, token_yes, token_no, valid_until, data'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [


		'category' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.category',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			],
		],

		'user' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.user',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'size' => 4,
				'eval' => 'int',
				'foreign_class' => 'Tx_Extbase_Domain_Model_FrontendUser',
				'foreign_table' => 'fe_users',
				'foreign_table_where' => 'AND disable = 0',

			],
		],

		'user_sha1' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.user_sha1',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			],
		],


		'token_yes' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.token_yes',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			],
		],


		'token_no' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.token_no',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			],
		],


		'valid_until' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.valid_until',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
				'default' => time(),
			],
		],

        'data' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.data',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
	],
];
