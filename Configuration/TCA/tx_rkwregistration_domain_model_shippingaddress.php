<?php
return [
    'ctrl' => [
        'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress',
        'label' => 'last_name',
        'label_alt' => 'first_name, company, address, zip, city',
        'label_alt_force' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'frontend_user, first_name,last_name,address,zip,city,email,frontend_user,pages',
        'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_shippingaddress.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden, frontend_user, title, gender, company, first_name, last_name, address, zip, city',
    ],
    'types' => [
        '1' => ['showitem' => 'hidden,--palette--;;1, frontend_user, title, gender, company, first_name, last_name, address, zip, city'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [


        'hidden' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'frontend_user' => [
            'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.frontend_user',
            'exclude' => 0,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY username ASC',
                'minitems' => 1,
                'maxitems' => 1,
                'items' => [
                    ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.frontend_user.pleaseSelect', 0],
                ],
            ],
        ],

        'gender' => [
            'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.gender',
            'exclude' => 0,
            'config'=>[
                'type' => 'select',
                'renderType' => 'selectSingle',
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 99,
                'items' => [
                    ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.gender.I.man', '0'],
                    ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.gender.I.woman', '1'],
                    ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.gender.I.neutral', '99'],
                ],
            ],
        ],
        'title' => [
            'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.title',
            'exclude' => 0,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_rkwregistration_domain_model_title',
                'foreign_table_where' => 'AND tx_rkwregistration_domain_model_title.hidden = 0 AND tx_rkwregistration_domain_model_title.deleted = 0 AND tx_rkwregistration_domain_model_title.is_title_after = 0 ORDER BY name ASC',
                'minitems' => 0,
                'maxitems' => 1,
                'items' => [
                    ['LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.title.pleaseSelect', 0],
                ],
            ],
        ],
        'first_name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.first_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'last_name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.last_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'company' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.company',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'address' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.address',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'zip' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.zip',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int'
            ],
        ],
        'city' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_shippingaddress.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
    ],
];
