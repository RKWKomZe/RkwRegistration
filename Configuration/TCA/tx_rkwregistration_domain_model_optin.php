<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_optin',
		'label' => 'valid_until',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,
        'delete' => 'deleted',
        'enablecolumns' => [
            'starttime' => 'starttime',
            'endtime' => 'endtime'
        ],
		'searchFields' => 'frontend_user_uid, token_user, token_yes, token_no, category, valid_until, enabled_by_admin, data',
		'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_optin.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'frontend_user_uid, frontend_user_update, token_user, token_yes, token_no, admin_token_yes, admin_token_no, category, approved, admin_approved, data',
	],
	'types' => [
		'1' => ['showitem' => 'frontend_user_uid, frontend_user_update, token_user, token_yes, token_no, admin_token_yes, admin_token_no, category, approved, admin_approved, data, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

        'starttime' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'endtime' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
		'frontend_user_uid' => [
			'config' => [
				'type' => 'passthrough',
			],
		],
        'frontend_user_update' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
		'token_user' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'token_yes' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'token_no' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'admin_token_yes' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'admin_token_no' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'approved' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'admin_approved' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'category' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'foreign_table' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'foreign_uid' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'data' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
	],
];
