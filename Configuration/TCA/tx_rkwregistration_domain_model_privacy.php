<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwregistration_domain_model_privacy', 'EXT:rkw_registration/Resources/Private/Language/locallang_csh_tx_rkwregistration_domain_model_privacy.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwregistration_domain_model_privacy');
$GLOBALS['TCA']['tx_rkwregistration_domain_model_privacy'] = array(
	'ctrl' => array(
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
	),
	'interface' => array(
		'showRecordFieldList' => 'crdate, foreign_table, foreign_uid, ip_address, user_agent, extension_name, plugin_name, controller_name, action_name, informed_consent_reason, server_host, server_uri,server_referer_url, child',
	),
	'types' => array(
		'1' => array('showitem' => 'crdate, foreign_table, foreign_uid, ip_address, user_agent, extension_name, plugin_name, controller_name, action_name, informed_consent_reason, server_host, server_uri,server_referer_url, child'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'frontend_user' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		'registration_user_sha1' => array(
            'config' => array(
                'type' => 'passthrough',
            ),
		),
		'parent' => array(
			'config' => array(
				'type' => 'passthrough',
				'foreign_table' => 'tx_rkwregistration_domain_model_privacy',
				'foreign_field' => 'uid',
			)
		),
		'child' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.child',
			'config' => array(
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
			)
		),
		'crdate' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.crdate',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime, required',
				'checkbox' => 0,
				'default' => 0,
				'readOnly' => 1,
			),
		),

		'foreign_table' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.foreign_table',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'trim, required',
				'readOnly' => 1,
			),
		),
		'foreign_uid' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.foreign_uid',
			'config' => array(
				'type' => 'input',
				'size' => 5,
				'max' => 10,
				'eval' => 'trim, int, required',
				'readOnly' => 1,
			),
		),
		'ip_address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.ip_address',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			),
		),
		'user_agent' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.user_agent',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'readOnly' => 1,
			),
		),
		'extension_name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.extension_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			),
		),
		'plugin_name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.plugin_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			),
		),
		'controller_name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.controller_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			),
		),
		'action_name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.action_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'eval' => 'required',
				'readOnly' => 1,
			),
		),
		'comment' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.comment',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'readOnly' => 1,
			),
		),
		'server_host' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.server_host',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
				'readOnly' => 1,
			),
		),
		'server_uri' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.server_uri',
			'config' => array(
				'type' => 'text',
				'cols' => 80,
				'rows' => 1,
				'readOnly' => 1,
			),
		),
		'server_referer_url' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_privacy.server_referer_url',
			'config' => array(
				'type' => 'text',
				'cols' => 80,
				'rows' => 1,
				'readOnly' => 1,
			),
		),
	),
);
