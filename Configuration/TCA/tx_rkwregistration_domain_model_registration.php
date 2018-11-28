<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwregistration_domain_model_registration', 'EXT:rkw_registration/Resources/Private/Language/locallang_csh_tx_rkwregistration_domain_model_registration.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwregistration_domain_model_registration');
$GLOBALS['TCA']['tx_rkwregistration_domain_model_registration'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration',
		'label' => 'valid_until',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => TRUE,
		'hideTable' => 1,

		'searchFields' => 'category, user, usergroup',
		'iconfile' => 'EXT:rkw_registration/Resources/Public/Icons/tx_rkwregistration_domain_model_registration.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'category, user, token_yes, token_no, valid_until, data',
	),
	'types' => array(
		'1' => array('showitem' => 'category, user, token_yes, token_no, valid_until, data'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(


		'category' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.category',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			),
		),

		'user' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.user',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'size' => 4,
				'eval' => 'int',
				'foreign_class' => 'Tx_Extbase_Domain_Model_FrontendUser',
				'foreign_table' => 'fe_users',
				'foreign_table_where' => 'AND disable = 0',

			)
		),

		'user_sha1' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.user_sha1',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			),
		),


		'token_yes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.token_yes',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			),
		),


		'token_no' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.token_no',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			),
		),


		'valid_until' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.valid_until',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
				'default' => time()
			),
		),

        'data' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:rkw_registration/Resources/Private/Language/locallang_db.xlf:tx_rkwregistration_domain_model_registration.data',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),


	),
);
