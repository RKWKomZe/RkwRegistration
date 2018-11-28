<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempCols = array(

    'tx_rkwregistration_is_service' => array(
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_is_service',
        'exclude' => 0,
        'config'=>array(
            'type' => 'check',
            'default' => 0,
            'items' => array(
                '1' => array(
                    '0' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_is_service.I.enabled'
                )
            )
        )
    ),

	'tx_rkwregistration_service_opening_date' => array(
		'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_opening_date',
		'exclude' => 0,
		'config' => array (
			'type' => 'input',
			'size' => '13',
			'max' => '20',
			'eval' => 'date',
			'default' => '0'
		)

	),
	'tx_rkwregistration_service_closing_date' => array(
		'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_closing_date',
		'exclude' => 0,
		'config'=>array(
			'type' => 'input',
			'size' => '13',
			'max' => '20',
			'eval' => 'date',
			'default' => '0'
		)
	),
    'tx_rkwregistration_service_mandatory_fields' => array(
        'label'=>'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_mandatory_fields',
        'exclude' => 0,
        'config'=>array(
            'type' => 'input',
            'size' => '50',
            'max' => '256',
            'eval' => 'trim',
        )
    ),

    'tx_rkwregistration_service_admins' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_admins',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectSingle',
            'foreign_table' => 'be_users',
            'MM' => 'tx_rkwregistration_fegroups_beusers_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 9999,
            'multiple' => 0,
        ),
    ),

    'tx_rkwregistration_service_pid' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_frontendusergroup.tx_rkwregistration_service_pid',
        'config' => array(
            'type' => 'input',
            'size' => '30',
            'max' => '256',
            'eval' => 'trim',
            'wizards' => array(
                '_PADDING' => 2,
                'link' => array(
                    'type' => 'popup',
                    'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                    'icon' => 'link_popup.gif',
                    'module' => array(
                        'name' => 'wizard_element_browser',
                        'urlParameters' => array(
                            'mode' => 'wizard',
                        )
                    ),
                    'JSopenParams' => 'height=400,width=550,status=0,menubar=0,scrollbars=1',
                    'params' => Array(
                        // List of tabs to hide in link window. Allowed values are:
                        // file, mail, page, spec, folder, url
                        'blindLinkOptions' => 'mail,file,spec,folder,url',

                        // allowed extensions for file
                        //'allowedExtensions' => 'mp3,ogg',
                    ),
                ),
            ),
            'softref' => 'typolink'
        ),
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups',$tempCols);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'tx_rkwregistration_is_service, tx_rkwregistration_service_opening_date, tx_rkwregistration_service_closing_date, tx_rkwregistration_service_mandatory_fields, tx_rkwregistration_service_pid, tx_rkwregistration_service_admins');
