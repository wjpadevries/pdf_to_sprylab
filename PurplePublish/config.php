<?php 
/* 

	settings for publishing to sprylab-PurpleManager
	
 */	

//define ( 'PURPLE_SERVER_URL', "https://purplemanager.com" );
define ( 'PURPLE_SERVER_URL', "https://staging.purplemanager.com" );

// user and password to be used to connect
define ( 'PURPLE_USER', '<your-user>' );
define ( 'PURPLE_PSWD', '<your-passwd>' );

// if you only want to test PDF creation, then set the SENDTOSPRYLAB to false
// for production , set this to true
define ( 'SENDTOSPRYLAB' , true );

// do you want to remove the created files?
// for production , set this to true
define ( 'REMOVEPUBLISHEDFILES', false );


// we need a tempfolder to publish to
define ( 'PDF_TEMPFOLDER' , WEBEDITDIR ); // we need to have an tempdir that can be reached by IDS


// the PDF profile to use for creating the PDF's
define ( 'PDF_PROFILE' , 'PurplePublishPDF'); // case sensitive !!

// Enterprise Publication mapping to PurplePublication mapping
// if no mapping, EnterprisePublication = PurplePublication

// Only the layouts that are  listed  in the VALIDSTATUS will be send
// if VALIDSTATUS is empty, all status are considered valid
// AUTO_SETPREVIEW: if true, the issue will automatically be set as preview
// AUTO_SETPUBLISH: if true, the issue will automatically be published
// PDF_PROPERTIES: if empty, the default settings will be loaded from PDFprofiles/PDFProperties_Generic.php
//				   to overrule, specify filename with other settings, the file will be searched in PDFprofiles folder
define ( 'PURPLE_BRANDMAPPING' , serialize( array( 'WW News' => array( 'PUBLICATION' => 'Woodwing Test First Publication',
                                                                    'VALIDSTATUS' => array('Layouts','readyForPDF'),
                                                                    'AUTO_SETPREVIEW' => true,
                                                                    'AUTO_SETPUBLISH' => false,
                                                                    'PDF_PROPERTIES' => 'PDFProperties_WWNews.php'
                                                                  ),
                                                'Purple News' => array( 'PUBLICATION' => 'Purple News',
                                                                    'VALIDSTATUS' => array(),
                                                                    'AUTO_SETPREVIEW' => true,
                                                                    'AUTO_SETPUBLISH' => false,
                                                                    'PDF_PROPERTIES' => ''
                                                                  ),
        )  ) );



// -------------------------------------------------------------
// Lines below: Do not change unless you know what you are doing
// -------------------------------------------------------------
define( 'ENT_ISSUE_HISTORY_FIELD'   , 'C_PURPLEHISTORY');
define( 'ENT_ISSUE_HISTORY_LABEL'   , 'Publish history');
define( 'ENT_ISSUE_NR_FIELD'        , 'C_PURPLEISSUEVERSION');
define( 'ENT_ISSUE_NR_LABEL'        , 'Issue Version');

