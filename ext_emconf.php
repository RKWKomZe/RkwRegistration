<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "rkw_registration"
 *
 * Auto generated by Extension Builder 2014-06-12
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
	'title' => 'RKW Registration',
	'description' => '',
	'category' => 'plugin',
	'author' => 'Maximilian Fäßler, Steffen Kroggel',
	'author_email' => 'faesslerweb@web.de, developer@steffenkroggel.de',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '8.7.21',
	'constraints' => [
		'depends' => [
            'typo3' => '7.6.0-8.7.99',
            'rkw_basics' => '8.7.30-8.7.99',
            'rkw_mailer' => '8.7.1-8.7.99'
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];