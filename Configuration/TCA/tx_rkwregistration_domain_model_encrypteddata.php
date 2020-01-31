<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_encrypteddata',
		'label' => 'foreign_table',
		'label_alt' => 'foreign_field, frontend_user',
		'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => 1,
		'delete' => 'deleted',
		'searchFields' => 'frontend_user, foreign_uid, foreign_table, foreign_field, encrypted_value',
		'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_encrypteddata.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'frontend_user, foreign_uid, foreign_table, foreign_field, encrypted_value',
	],
	'types' => [
		'1' => ['showitem' => 'frontend_user, foreign_uid, foreign_table, foreign_field, encrypted_value'],
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
        'search_key' => [
            'config'=>[
                'type' => 'passthrough',
            ],
        ],
        'foreign_uid' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'foreign_table' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'foreign_class' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'encrypted_data' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
	],
];
