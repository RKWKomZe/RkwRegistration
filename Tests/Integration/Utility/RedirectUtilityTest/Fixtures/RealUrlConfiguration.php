<?php
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] = array(

    //===============================================
    // Configuration for ALL domains (fallback)
    '_DEFAULT' => array(

        //===============================================
        // Setup
        'init' => array(
            'enableCHashCache' 			=> 1,
            'appendMissingSlash' 		=> 'ifNotFile,redirect',
            'adminJumpToBackend'        => 1,
            'enableUrlDecodeCache' 		=> 1,
            'enableUrlEncodeCache' 		=> 1,
            'emptyUrlReturnValue'       => '/'
        ),

        //===============================================
        // Redirects
        'redirects' => array(

        ),

        //===============================================
        // Basic variables which are prepended
        'preVars' => array(

            // No-Cache
            array(
                'GETvar' => 'no_cache',
                'valueMap' => array(
                    'nc' => 1,
                ),
                'noMatch' => 'bypass',
            ),

            // type
            array(
                'GETvar' => 'type',
                'valueMap' => array(
                    'pagetype-print' 			=> 	'98',
                    'pagetype-xml'				=>	'150',
                    'pagetype-content-only' 	=> 	'160',
                    'pagetype-plaintext'		=>	'170',
                    'pagetype-csv'				=>	'180',

                ),
                'noMatch' => 'bypass',
            ),


            // Language
            array(
                'GETvar' => 'L',
                'valueMap' => array(
                    'de' => '0',
                    'en' => '1',
                    'fr' => '2',
                    'ru' => '3',
                    'it' => '4'
                ),
                'noMatch' => 'bypass',
            ),
        ),


        //===============================================
        // Params for path-processing
        'pagePath' => array(
            'spaceCharacter' 	=> '-',
            'languageGetVar' 	=> 'L',
            'expireDays' 		=> 3,
            'rootpage_id' 		=> 1,
        ),


        //===============================================
        // Variables without keyword based grouping
        'fixedPostVarSets' => array(

        ),


        //===============================================
        // Variables with keyword based grouping
        'postVarSets' => array(

            '_DEFAULT' => array(


                'tx-rkw-basics' => array (
                    array(
                        'GETvar' => 'tx_rkwbasics_rkwmediasources[controller]' ,
                        'valueMap' => array(
                            'media' => 'MediaSources',
                        ),
                    ),
                    array(
                        'GETvar' => 'tx_rkwbasics_rkwmediasources[action]' ,
                    ),
                ),
            ),
        ),

        //==================================================
        // map some type to file name: rss.xml = &typo=100
        /*
        'fileName' => array(
            'defaultToHTMLsuffixOnPrev' => 0,
            'acceptHTMLsuffix'			=> 0,

            'index' => array(
                'rss.xml' => array(
                    'keyValues' => array(
                        'type' => 100,
                    ),
                ),

                'rss091.xml' => array(
                    'keyValues' => array(
                        'type' => 101,
                    ),
                ),

                'rdf.xml' => array(
                    'keyValues' => array(
                        'type' => 102,
                    ),
                ),

                'atom.xml' => array(
                    'keyValues' => array(
                        'type' => 103,
                    ),
                )
            )
        )
        */
    )
);
