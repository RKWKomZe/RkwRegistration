<?php
$tempCols = [

	'tx_rkwregistration_mobile' => [
		'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_mobile',
		'exclude' => 0,
		'config'=>[
			'type'=>'input',
            'size' => 20,
            'max' => '256',
            'eval' => 'trim'
		],
	],

    'tx_rkwregistration_gender' => [
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_gender',
        'exclude' => 0,
        'config'=>[
			'type' => 'select',
            'renderType' => 'selectSingle',
			'minitems' => 0,
			'maxitems' => 1,
			'default' => 99,
			'items' => [
				['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_gender.I.0', '0'],
				['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_gender.I.1', '1'],
                ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_gender.I.99', '99'],

            ],
        ],
    ],

	'title' => [
		'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.title',
		'exclude' => 1,
		'config' => [
            'type'=>'input',
            'size' => 20,
            'max' => '256',
            'eval' => 'trim',
		],
	],


	'tx_rkwregistration_title' => [
		'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_title',
		'exclude' => 0,
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'foreign_table' => 'tx_rkwregistration_domain_model_title',
			'foreign_table_where' => 'AND tx_rkwregistration_domain_model_title.hidden = 0 AND tx_rkwregistration_domain_model_title.deleted = 0 ORDER BY name ASC',
			'minitems' => 0,
			'maxitems' => 1,
			'items' => [
				['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_title.I.neutral', 0],
			],
            'default' => 0
		],
	],

    'tx_rkwregistration_twitter_url' => [
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_twitter_url',
        'exclude' => 0,
        'config'=>[
            'type'=>'input',
            'renderType' => 'inputLink',
            'size' => 30,
            'max' => '256',
            'eval' => 'trim',
            'softref' => 'typolink'
        ],
    ],

    'tx_rkwregistration_facebook_url' => [
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_facebook_url',
        'exclude' => 0,
        'config'=>[
            'type' => 'input',
            'renderType' => 'inputLink',
            'size' => 30,
            'max' => '256',
            'eval' => 'trim',
            'softref' => 'typolink'
        ],
    ],

	'tx_rkwregistration_xing_url' => [
		'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_xing_url',
		'exclude' => 0,
		'config'=>[
			'type' => 'input',
            'renderType' => 'inputLink',
            'size' => 30,
            'max' => '256',
            'eval' => 'trim',
            'softref' => 'typolink'
		],
	],

    'tx_rkwregistration_register_remote_ip' => [
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_register_remote_ip',
        'exclude' => 0,
        'config'=>[
            'type'=>'input',
            'readOnly' => 1,
            'size' => 30,
            'max' => '256',
            'eval' => 'trim',
        ],
    ],

    'tx_rkwregistration_login_error_count' => [
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_login_error_count',
        'exclude' => 0,
        'config'=>[
            'type'=>'input',
            'size' => 20,
            'max' => '256',
            'eval' => 'trim,int'
        ],
    ],

    'tx_rkwregistration_language_key' => [
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_language_key',
        'exclude' => 0,
        'config'=>[
            'type'=>'input',
            'size' => 20,
            'max' => '256',
            'eval' => 'trim',
        ],
    ],

    'tx_rkwregistration_consent_terms' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.consent_terms',
        'config' => [
            'type' => 'check',
        ],
    ],

    'tx_rkwregistration_consent_marketing' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.consent_marketing',
        'config' => [
            'type' => 'check',
        ],
    ],

    'tx_rkwregistration_data_protection_status' => [
        'config'=>[
            'type' => 'passthrough',
        ],
    ],
    'tx_rkwregistration_consent' => [
		'exclude' => 1,
		'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.tx_rkwregistration_consent',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_rkwregistration_domain_model_consent',
			'foreign_field' => 'frontend_user',
			'foreign_match_fields' => [
				'parent' => 0
			],
			'maxitems'      => 9999,
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
		],
	],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',$tempCols);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','--div--;LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontenduser.socialmedia', '', '');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','tx_rkwregistration_twitter_url, tx_rkwregistration_facebook_url, tx_rkwregistration_xing_url', '', 'after:tx_rkwregistration_twitter_id');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','tx_rkwregistration_title','','after:title');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','tx_rkwregistration_gender','','before:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','tx_rkwregistration_mobile','','after:telephone');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','tx_rkwregistration_login_error_count','','after:disable');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users',', tx_rkwregistration_register_remote_ip, tx_rkwregistration_language_key','','after:lockToDomain');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users',', tx_rkwregistration_consent_terms, tx_rkwregistration_consent_marketing, tx_rkwregistration_consent','','after:image');

