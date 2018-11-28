<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwregistration_domain_model_service', 'EXT:rkw_registration/Resources/Private/Language/locallang_csh_tx_rkwregistration_domain_model_service.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwregistration_domain_model_service');
$GLOBALS['TCA']['tx_rkwregistration_domain_model_service'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service',
		'label' => 'user',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => TRUE,
		'hideTable' => 1,

		'searchFields' => 'user ,usergroup, enabled_by_admin',
		'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_service.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'user, usergroup, enabled_by_admin, token_yes, token_no, valid_until, service_sha1',
	),
	'types' => array(
		'1' => array('showitem' => 'user, usergroup, enabled_by_admin, token_yes, token_no, valid_until, service_sha1'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'user' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.user',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'size' => 4,
				'eval' => 'int',
				'foreign_class' => 'Tx_Extbase_Domain_Model_FrontendUser',
				'foreign_table' => 'fe_users',
				'foreign_table_where' => 'AND fe_users.disable = 0',
			)
		),

		'usergroup' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.usergroup',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'size' => 4,
				'eval' => 'int',
				'foreign_class' => 'Tx_Extbase_Domain_Model_FrontendUserGroup',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'AND fe_groups.hidden = 0',
			)
		),

		'enabled_by_admin' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.enabled_by_admin',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
				'default' => time()
			),
		),

		'token_yes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.token_yes',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			),
		),

		'token_no' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.token_no',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			),
		),

		'valid_until' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.valid_until',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
				'default' => time()
			),
		),

		'service_sha1' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_service.service_sha1',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			),
		),

	),
);
