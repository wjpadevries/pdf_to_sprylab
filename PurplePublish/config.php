<?php 
/* 

	settings for publishing to sprylab-PurpleManager
	
 */	

//define ( 'PURPLE_SERVER_URL', "https://purplemanager.com/purple-manager-backend" );
define ( 'PURPLE_SERVER_URL', "https://staging.purplemanager.com/purple-manager-backend" );

// user and password to be used to connect
define ( 'PURPLE_USER', 'wvr+purple@woodwing.com' );
define ( 'PURPLE_PSWD', 'aapNootMies!1' );

// if you only want to test PDF creation, then set the SENDTOSPRYLAB to false
// for production , set this to true
define ( 'SENDTOSPRYLAB' , false);

// do you want to remove the created files?
// for production , set this to true
define ( 'REMOVEPUBLISHEDFILES',false);


// we need a tempfolder to publish to
define ( 'PDF_TEMPFOLDER' , '/Temp/pdf/' ); // use the enterprise tempfolder = TEMPDIRECTORY

// the PDF profile to use for creating the PDF's
define ( 'PDF_PROFILE' , 'PurplePublishPDF'); // case sensitive !!


// Enterprise Publication mapping to PurplePublication mapping
// if no mapping, EnterprisePublication = PurplePublication

// Only the layouts that are  listed  in the VALIDSTATUS will be send
// if VALIDSTATUS is empty, all status are considered valid
define ( 'PURPLE_BRANDMAPPING' , serialize( array( 'WW News' => array( 'PUBLICATION' => 'Woodwing Test First Publication',
                                                                    'VALIDSTATUS' => array('Layouts','readyForPDF'),
                                                                    'AUTO_SETPREVIEW' => true,
                                                                    'AUTO_SETPUBLISH' => false,
                                                                  ),
                                                'Purple News' => array( 'PUBLICATION' => 'Puple News',
                                                                    'VALIDSTATUS' => array(),
                                                                    'AUTO_SETPREVIEW' => true,
                                                                    'AUTO_SETPUBLISH' => false,
                                                                  ),
        )  ) );



// -------------------------------------------------------------
// Lines below: Do not change unless you know what you are doing
// -------------------------------------------------------------
define( 'ENT_ISSUE_HISTORY_FIELD'   , 'C_PURPLEHISTORY');
define( 'ENT_ISSUE_HISTORY_LABEL'   , 'Publish history');
define( 'ENT_ISSUE_NR_FIELD'        , 'C_PURPLEISSUEVERSION');
define( 'ENT_ISSUE_NR_LABEL'        , 'Issue Version');

