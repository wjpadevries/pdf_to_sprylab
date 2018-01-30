<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <title>Publish to Sprylab</title>
    <link rel="stylesheet" type="text/css" href="css/purple.css">
</head>
<body>

<?php
define ('SCRIPT_VERSION' , 'v0.1');
define ('EOL' , "<br>\n");

// include enterprise config
require_once '../../config.php';

//include specific config
require_once 'config.php';
require_once 'generic.php';
require_once __DIR__ . '/classes/enterprise.class.php';

require_once __DIR__ . '/classes/progressBar.class.php';
$pg = New progressBar;


LogHandler::Log( 'purplePublish', 'DEBUG', 'starting script (version:' . SCRIPT_VERSION . ')');

// --------------------------------------
// get the parameters from the URL
// --------------------------------------
$ticket = isset( $_GET['ticket'] ) ? $_GET ['ticket'] : '';
$inPub = isset( $_GET['brand'] ) ? intval( $_GET['brand'] ) : 0;
$inIssue = isset( $_GET['issue'] ) ? intval( $_GET['issue'] ) : 0;
$inEdi = isset( $_GET['edition'] ) ? intval( $_GET['edition'] ) : 0;
$inCato = isset( $_GET['category'] ) ? intval( $_GET['category'] ) : -2;
$inState = isset( $_GET['status'] ) ? intval( $_GET['status'] ) : -2;  // state -1 is 'All', state #1 is 'personal state'



// --------------------------------------
// validations
// --------------------------------------

// run check for temp folder?
if ( !CheckFolderAccess( PDF_TEMPFOLDER ) ) {
    exit;
};

// check if the ticket is valid
try {
    require_once( BASEDIR . '/server/bizclasses/BizSession.class.php' );
    $user = BizSession::checkTicket( $ticket );
} catch ( BizException $e ) {
    // if we reach this point, we will not continue.
    LogHandler::Log( 'purplePublish', 'DEBUG', 'ERROR:Not a valid ticket' );
    print "ERROR:Not a valid ticket<br>";
    exit;
}

// ----------------------------------------------
// get human readable names for enterprise ID's
// ----------------------------------------------
$pdfJob = array();
$pdfJob['report'] = array(); // used to store strings that will be shown as report on the end of the process
$pdfJob['user'] = DBTicket::checkTicket( $ticket );
require_once BASEDIR . '/server/dbclasses/DBPublication.class.php';
$pdfJob['brand'] = DBPublication::getPublicationName( $inPub );

require_once BASEDIR . '/server/dbclasses/DBIssue.class.php';
$pdfJob['issue'] = DBIssue::getIssueName( $inIssue );

if ( $inEdi != -1 ) {
	require_once BASEDIR . '/server/dbclasses/DBEdition.class.php';
	$edition = DBEdition::getEdition( $inEdi );
	$pdfJob['edition'] = $edition->Name;
} else {
	$pdfJob['edition'] = '-';
}

// also add commandline info to pdfInfo
$pdfJob['username']   = $user;
$pdfJob['brandID']    = $inPub;
$pdfJob['issueID']    = $inIssue;
$pdfJob['editionID']  = $inEdi;
$pdfJob['categoryID'] = $inCato;
$pdfJob['stateID']    = $inState;
$pdfJob['tempfolder'] = PDF_TEMPFOLDER;
$pdfJob['pdf_profile'] = PDF_PROFILE;

// add more info to the structure so we have all we need in IDS
$outputFile = $pdfJob['brand'] . '-' . $pdfJob['issue'] ;
if ( $pdfJob['edition'] != '-'){
    $outputFile .= '-' . $pdfJob['edition'];
}

// these are the files that will be created and uploaded to PurpleManager
$pdfJob['outputFile'] = PDF_TEMPFOLDER . '/' . $outputFile . '.pdf';
$pdfJob['coverImage'] = PDF_TEMPFOLDER . '/' . $outputFile . '.png';
$pdfJob['bookFile']   = PDF_TEMPFOLDER . '/' . $outputFile . '.indb';

// -----------------------
// clean up before we run
// -----------------------
LogHandler::Log( 'PurplePublish', 'DEBUG',"Cleanup our future output files");
if ( file_exists($pdfJob['outputFile'])){unlink( $pdfJob['outputFile']);}
if ( file_exists($pdfJob['coverImage'])){unlink( $pdfJob['coverImage']);}
if ( file_exists($pdfJob['bookFile']))  {unlink( $pdfJob['bookFile']);}



// --------------------------------------
// log some info
// --------------------------------------
LogHandler::Log( 'PurplePublish', 'DEBUG', '------------------------' );
LogHandler::Log( 'PurplePublish', 'DEBUG', 'parameters from commandline');
LogHandler::Log( 'PurplePublish', 'DEBUG', 'brand:' . $inPub );
LogHandler::Log( 'PurplePublish', 'DEBUG', 'issue:' . $inIssue );
LogHandler::Log( 'PurplePublish', 'DEBUG', 'edition:' . $inEdi );
LogHandler::Log( 'PurplePublish', 'DEBUG', 'category:' . $inCato );
LogHandler::Log( 'PurplePublish', 'DEBUG', 'state:' . $inState );
LogHandler::Log( 'PurplePublish', 'DEBUG', 'pdfJob:' . print_r( $pdfJob,1));

addToReport('');

// --------------------------------------
// start working
// --------------------------------------
// load the enterprise issue data
$enterprise = new EnterpriseUtils($ticket);
if (! $enterprise->getIssue($inIssue)){
    print "Invalid Enterprise Issue Specified, unable to continue". EOL;
    exit;
};


print "<h1>Create Issue PDF and upload to Sprylab</h1>" . EOL;
$pg->insertDiv();

// Total processes for top-level progress-bar
$pg->totalCount = 4; // should be number of steps to take before task is finished
$pg->actualCount = 0;


$pg->detail = "Task: Collecting Page Information";
$pg->actualCount=0;
$pg->pbUpdate();


// ---------------------
//  Create the PDF
// ---------------------
if ( ! file_exists($pdfJob['outputFile'])) {

    LogHandler::Log( 'PurplePublish', 'DEBUG',"Ouputfile not found , creating PDF now");

    // collect all layouts for this issue
    // get the pagesInfo as simplyfied structure
    LogHandler::Log( 'PurplePublish', 'DEBUG', 'Calling getPagesAsArray' );
    $pagesArray = getPagesAsArray(  $inPub, $inIssue, $inEdi  );

    $statusList = mapEnterpriseStatus( $pdfJob['brand'] );
    LogHandler::Log( 'PurplePublish', 'DEBUG', 'StatusList for this brand:' . print_r($statusList,1));
    addToReport('Pages in status [' . implode(',', $statusList).'] are selected');

    // convert the getPages output to something we can use in InDesignServer
    $IDSinstructionList = createIDSinstructionList($pdfJob, $pagesArray , $statusList);
    
    if ( $IDSinstructionList === false ){
    	// no layouts found so we quit
    	addToReport( "No layouts found that match specified status");
    	$pg->pbFinished();
 		showReport();
    	exit;
    }
    $pdfJob['layouts'] = $IDSinstructionList; // add to generic job

    $pg->detail = "Task: creating pdf pages via InDesign Server";
    $pg->actualCount++;
    $pg->pbUpdate();
    IDSCreatePDF($pdfJob);


    $pg->detail = "Combine pages to one PDF";
    $pg->actualCount++;
    $pg->pbUpdate();

    /* addToReport( "Combine pages to one PDF :" . $pdfJob['outputFile']  );

    if (! combinePDF($pdfJob)) {
        print "<div class='error'>ERROR: Failed to create PDF</div>" . EOL;
        LogHandler::Log( 'PurplePublish', 'DEBUG',"ERROR: while creating outputfile");

    }*/
    LogHandler::Log( 'PurplePublish', 'DEBUG',"outputfile created");
    print "<h3>PDF succesfully created</h3>" . EOL;
}

// ---------------
// send to sprylab
// ---------------
if ( SENDTOSPRYLAB &&
     file_exists($pdfJob['outputFile']) ) {
     
    //read the PDFproperties
    $pdfJob['PDFproperties'] = mapPDFProperties( $pdfJob['brand'] );
     
    addToReport('Sending PDF-file ['. $pdfJob['outputFile'].'] to SpryLab');
    LogHandler::Log( 'PurplePublish', 'DEBUG',"Ouputfile found , sending to Purple");
    // call publishTo functionality
    // in this case 'purple'
    //print "Calling PurplePublishing" . EOL;
    $pg->detail = "Task: Send PDF to Purble-Publish Channel";
    $pg->actualCount++;
    $pg->pbUpdate();

    $purpleIssueVersionID = publishToPurple($pdfJob);
    if ($purpleIssueVersionID) {
        print "<h3>Issue succesfully published</h3>" . EOL;

    }
    print "<img src='images/purple-logo.png'><a target='_blank' href='" . PURPLE_SERVER_URL . "/#issueDetail;id=" . $pdfJob['purple_issueid'] . "'>Click here to see this issue on PurpleManager</a>";

}




$pg->pbFinished();
$enterprise->setIssueCustomPropertyValue( ENT_ISSUE_HISTORY_FIELD, JSON_ENCODE($pdfJob['report']) );
$enterprise->setIssueCustomPropertyValue( ENT_ISSUE_NR_FIELD, $pdfJob['purple_issueversion_number'] . '/' . $pdfJob['purple_issueversion_id']  );
$enterprise->saveIssue();



showReport();
LogHandler::Log( 'PurplePublish', 'DEBUG','all done');
print "<hr>";


// ---------
// clean up
// ---------
if ( REMOVEPUBLISHEDFILES ) {
    LogHandler::Log( 'PurplePublish', 'DEBUG',"Removing files");
    if ( file_exists($pdfJob['outputFile'])){unlink( $pdfJob['outputFile']);}
    if ( file_exists($pdfJob['coverImage'])){unlink( $pdfJob['coverImage']);}
    if ( file_exists($pdfJob['bookFile']))  {unlink( $pdfJob['bookFile']);}
}


// --------------------------------------
// functions only below this line
// --------------------------------------



// ----------------------------------
// Call functions to get Pages info
// ----------------------------------

function getPagesAsArray(  $inPub, $inIssue, $inEdi  )
{
    global $ticket;

    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "Collecting pages for Brand:$inPub, Issue:$inIssue edition:$inEdi" );

    $issueId = $inIssue;
    $editionId = $inEdi;

    // Get pages info...
    $pageObjects = array();
    $layoutIds = array();
    try {
        require_once BASEDIR . '/server/services/wfl/WflGetPagesInfoService.class.php';
        $request = new WflGetPagesInfoRequest();
        $request->Ticket = $ticket;
        $request->Issue = new Issue();
        $request->Issue->Id = $issueId;
        //$request->Issue->Name = '';
        
        $request->Issue->OverrulePublication = false;
        $request->IDs = null;
        // #001 only specify edition when available
        if ( $editionId != -1 ) {
            $request->Edition = new Edition();
            $request->Edition->Id = $editionId;
           // $request->Edition->Name = '';
        }
        // #001 end
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "Calling getPagesInfo: " . print_r( $request, 1 ) );

        $service = new WflGetPagesInfoService();
        $response = $service->execute( $request );

        //LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "Returned getPagesInfo: " . print_r( $response, 1 ) );


        // create an array with layoutId as key to store info, as we need to be able to process this sequentially later
		$layoutIds = array();
        foreach ( $response->EditionsPages[0]->PageObjects as $pageObj ) {
            LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "Handling layoutID:" . $pageObj->ParentLayoutId );
            $layoutIds[] = $pageObj->ParentLayoutId;
            //LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "pageObj:" . print_r( $pageObj, 1 ) );
            //if ( in_array( $pageObj->ParentLayoutId, $layoutIds ) ) {
            // store info from this page so we can use it later
            $page = array();
            $page['pagePosition'] = $pageObj->IssuePagePosition;
            $page['pageOrder'] = $pageObj->PageOrder;
            $page['pageNumber'] = $pageObj->PageNumber;
            $page['OutputRenditionAvailable'] = $pageObj->OutputRenditionAvailable;

            LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "page:" . print_r( $page, 1 ) );

            
    		$pageObjects[$pageObj->ParentLayoutId]['pages'][$pageObj->PageNumber] = $page;
            
            $ppn = $pageObj->ppn;
            $pageObjects[$pageObj->ParentLayoutId]['pagenrmapping'][$ppn->RealPageNumber] = $pageObj->PageNumber;
            
        }



        // now add data from the LayoutObjects to the pageObjects
        foreach ( $response->LayoutObjects as $layoutObject ) {
            //LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "layoutObject:" . print_r( $layoutObject, 1 ) );
            if ( array_key_exists( $layoutObject->Id, $pageObjects ) &&
                 in_array( $layoutObject->Id, $layoutIds )
            ) {
                $page = $pageObjects[$layoutObject->Id];
                $page['objID'] = $layoutObject->Id;
                $page['name'] = $layoutObject->Name;
                $page['stateName'] = $layoutObject->State->Name;
                $page['stateColor'] = $layoutObject->State->Color;
                $page['version'] = $layoutObject->Version;
                $page['modified'] = $layoutObject->Modified;
                $pageObjects[$layoutObject->Id] = $page;
            }
        }
    } catch ( BizException $e ) {
        echo 'ERROR1: ' . $e->getMessage() . '<br/>';
    }


    // make the array only one level deep...Yes, duplicating data
    
    $retArray = array();
    $retArray['source'] = $pageObjects;
    $retArray['ordered'] = array();
    $retArray['double'] = array();
    

    foreach ( $pageObjects as $pageObject ) {
        // global info is at this level
        // pages info is at  $pageObject['pages'];
        foreach ( $pageObject['pages'] as $page ) {
            $tempArray = array();
            //merge the pageObject and page arrays
            $tempArray = array_merge( $pageObject, $page );

            // decide if this pageNumber is already used or not
            if ( !array_key_exists( $page['pageNumber'], $retArray['ordered'] ) ) {
                $retArray['ordered'][$page['pageNumber']] = $tempArray;
            } else {
                $retArray['double'][] = $tempArray;
            }

        }
    }
    
    return $retArray;
}


// convert the $pagesArray to a structure we can easily use for IDS
// it will be :
// <layoutID>: [ pagenr: pagename ....]

function createIDSinstructionList($pdfJob, $pagesArray , $stateList = null)
{
	LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "createIDSinstructionList");
    //LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "stateList:" . print_r($stateList,1));
	
	$idsInstructions = array();
	
	$outputPath = $pdfJob['tempfolder'] . cleanFilename( $pdfJob['brand'] . '-' . $pdfJob['issue'] );
	$layoutCount = count($pagesArray['source']);
	$usedLayoutCount = 0;
	foreach( $pagesArray['source'] as $layouts )
	{
        // take care only to include layouts in the specified status.
        $addLayout = true;
        if ( ! is_null($stateList)){
            $layoutState = $layouts['stateName'];
            if ( ! in_array($layoutState,$stateList)){
                $addLayout = false;
                LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "Layoutstate [$layoutState] does not match with stateList");
            }
        }

		$layoutPages = array();
		foreach( $layouts['pages'] as $page )
		{
			$layoutPages[$page['pagePosition']] = $outputPath . '-' . $page['pagePosition'] . '.pdf';
		}
		
		if ( $addLayout){
            LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "adding layout to result list");
            $idsInstructions[ $layouts['objID'] ] = $layoutPages;
            $usedLayoutCount++;
        }

	}
	if ( $usedLayoutCount == 0)
	{
		LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "return:false, no layouts in correct state found");
		return false;
	}
	
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "returning:$usedLayoutCount of $layoutCount layouts");
	return $idsInstructions;
}



function IDSCreatePDF  ( $IDSjob )
{
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "enter: IDSCreatePDF");
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "IDSjob:" . print_r($IDSjob,1));
    require_once BASEDIR . '/server/dataclasses/InDesignServerJob.class.php';
    require_once BASEDIR . '/server/bizclasses/BizInDesignServerJob.class.php';
    require_once BASEDIR . '/server/bizclasses/BizFileStoreXmpFileInfo.class.php';

    $errorMsg = '';
    $scriptContent = file_get_contents( __DIR__ . '/js/ids_createPDF-asBook.jsx'); // ids_createPDF-asBook or ids_createPDF.jsx

    $layoutID = key($IDSjob['layouts']);
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG',  "Layout Object ID: $layoutID");

    // let Indesign write a report to this file specified
    // this must be in the path as seen from IDS
    $idsReport = WEBEDITDIRIDSERV.'report.txt';
    $localIdsReport = WEBEDITDIR.'report.txt';
    clearstatcache( true, $localIdsReport );
    if( file_exists($localIdsReport) ) {
        if( !unlink( $localIdsReport ) ) { // clear previous runs
            LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG','Could not remove test file of previous runs:'.$localIdsReport);
            LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG','Please remove this file manually (since PHP does not require write access to this InDesign Server workspace).');
        }
    }

    $serverVersion = BizFileStoreXmpFileInfo::getInDesignDocumentVersion($layoutID);
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG',  "ServerVersion: " . print_r( $serverVersion,1));
    $job = new InDesignServerJob();
    $job->JobScript = $scriptContent;
    $job->JobParams = array(
        'server' => INDESIGNSERV_APPSERVER,  // servername to use as set in wwsettings
        'layout' => $layoutID,
        'logfile' => WEBEDITDIRIDSERV . 'layout-' . $layoutID . '.log', // default = log to InDesign Server console, specify writable file in here
        'delay' => defined('IDSA_WAIT_BETWEEN_OPEN_AND_SAVE') ? IDSA_WAIT_BETWEEN_OPEN_AND_SAVE : 0,
        'IDSJOB' => json_encode($IDSjob),
        'reportfile' => $idsReport,
    );
    $job->JobType = 'CREATEPDF';
    $job->ObjectId = $layoutID; // send the first layoutID
    $job->JobPrio = 1;
    $job->Context = 'PurplePublish';
    $job->Foreground = true; // BG
    $job->Initiator = $IDSjob['username'];
    $job->MinServerVersion = $serverVersion;
    $job->MaxServerVersion = $serverVersion;

    /* $jobId = BizInDesignServerJobs::createJob($job);
    if ($jobId) {
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG',  'IDS jobID submitted as [' . $jobId . ']');
    } else {
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG',  'No IDS job created');
    }
    */
    // for FG job:
    $result =  BizInDesignServerJobs::createAndRunJob( $scriptContent, $job->JobParams,$job->JobType, $job->ObjectId,
                                            $server = null, $job->MinServerVersion,  $job->MaxServerVersion, $job->Context );

    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG',  'result:' . print_r($result,1));
    // check for the report file, we now need to look at the local file

    if (! file_exists($localIdsReport) ){
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG',  'No report found from IDS, please check IDS console for errors' );
    }else{
        $report = file_get_contents($localIdsReport);
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG',  'report:' . $report );
        addToReport( $report);
    }

    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "exit: IDSCreatePDF");
}



function combinePDF( $pdfJob ){
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "enter: combinePDF");
    $pdfPages = array(); // will contain the structure for combinePDF

    foreach ( $pdfJob['layouts'] as $layoutID => $layout ) {
        foreach ($layout as $pageNumber => $layoutPDF) {
            if (file_exists($layoutPDF)) {

                $key = sprintf("%'.04d", $pageNumber) . '_' . $pdfJob['edition'];
                LogHandler::Log(__METHOD__, 'DEBUG', "Adding page on key [$key]");
                $pdfPages[$key] = array('layoutName' => basename($layoutPDF), // just add layoutname to each page.
                    'layoutID' => $layoutID,
                    'pageNumber' => $pageNumber,
                    'pdfFile' => $layoutPDF,
                    'height' => 297, // page size height
                    'width' => 210, // page siz width
                    'edition' => $pdfJob['edition']
                );
            } else {
                LogHandler::Log(__METHOD__, 'DEBUG', 'Error getting filedata');
            }
        }
    }
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG','pdfPages:' . print_r($pdfPages,1));
    require_once __DIR__ . '/classes/PDFutils.class.php';
    $PDFutils = new PDFutils();
    // return the filename to download
    $combinedPDF = $PDFutils->combinePdfPages( $pdfJob['outputFile'] , $pdfPages);

    // clean up the seperate pages
    $PDFutils->cleanPDFpages( $pdfPages );

    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "exit: combinePDF");
    if ( ! file_exists( $combinedPDF)){
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', 'CombinedPDF file not found [' .$combinedPDF.']');
        return false;
    }
    return true;
}


function publishToPurple( &$pdfJob ){
    print "<h2>Pushing PDF to Sprylab</h2>" . EOL;
    // create pb for purple
    $purplePG = New progressBar;
    $purplePG->setProgressBarID(1);
    $purplePG->insertDiv();
    // Total processes
    $purplePG->totalCount = 5; // should be number of steps to take before task is finished
    $purplePG->actualCount = 0;

    require_once __DIR__ . '/classes/purple.class.php';
    $purple = new purple( PURPLE_SERVER_URL );
    $purplePG->detail = "Login to Purple";
    $purplePG->actualCount++;
    $purplePG->pbUpdate();
    addToReport('Login to ['.PURPLE_SERVER_URL.'] with user ['.PURPLE_USER.']');
    $purple->logon( PURPLE_USER, PURPLE_PSWD );

    $purplePG->detail = "Locating correct publication";
    $purplePG->actualCount++;
    $purplePG->pbUpdate();
    sleep(1);
    $publications = $purple->publicationList();
    $pubID = mapEnterpriseBrand( $pdfJob['brand'], $publications);


    if ( $pubID === false){
        print "ERROR: PubID not matched";
        return false;
    }

    addToReport('Publish to ['.$pubID .']');
    $pdfJob['purple_publicationid'] = $pubID;

    // check if our issue already exists.
    // if not, create
    $purplePG->detail = "Checking Issue";
    $purplePG->actualCount++;
    $purplePG->pbUpdate();
    sleep(1);
    $issues = $purple->issuesList($pubID );

    $issueID = findOrCreateIssue( $purple, $pubID, $pdfJob['issue'],  $issues, $pdfJob['coverImage']);
    if ( $issueID === false){
        print "ERROR: issueID not matched";
        return false;
    }
    addToReport('Issue to ['.$issueID .']');

    $purplePG->detail = "Checking IssueVersionList";
    $purplePG->actualCount++;
    $purplePG->pbUpdate();
    $issueVersionList = $purple->issueVersionList( $issueID );
    $pdfJob['purple_issueid'] = $issueID;

    //$purple->showIssueVersions($issueVersionList); // nice for debugging

    // check if the last version is already published,
    // if so we create a new version,
    // otherwise we update the existing one
    if ($purple->isLastIssueVersionActive( $issueVersionList )){
        addToReport('Creating new issueversion');
        //print "Create new IssueVersion" . EOL;
        $issueVersionID = $purple->createIssueVersion($issueID);
        // reload list
        $issueVersionList = $purple->issueVersionList( $issueID );
    }

    $issueVersionID = reset($issueVersionList )['id'];
    $pdfJob['purple_issueversion_number'] = reset($issueVersionList )['number'];
    $pdfJob['purple_issueversion_id'] = reset($issueVersionList )['id'];


    addToReport("Uploading to issueVersion[$issueVersionID]");
    //print "<hr>Attempt to upload pdf to issueVersion[$issueVersionID]" . EOL;

    $purplePG->detail = "Uploading content";
    $purplePG->actualCount++;
    $purplePG->pbUpdate();
    $pdfUpload = $purple->uploadVersionData( $issueVersionID, $pdfJob['outputFile'], $pdfJob['PDFproperties'] );
    if ( $pdfUpload === false){
        print "ERROR: pdfUpload failed";
        return false;
    }


    if ( autoSetPreview( $pdfJob['brand'] ))
    {
        //$purple->debugOn();
        //print EOL ." make the issueVersion previewable [$issueVersionID]" . EOL;
        $purple->issueVersionPreview( $issueVersionID );
        addToReport("set version to preview");
        //$purple->debugOff();
    }

    if ( autoSetPublish( $pdfJob['brand'] )) {
        //$purple->debugOn();
        //print EOL . " Set the issueVersion Published [$issueVersionID]" . EOL;
        $purple->issueVersionPublish($issueVersionID);
        addToReport("set version to Publish");
        //$purple->debugOff();
    }

    $purplePG->pbFinished();
    $purplePG->hide();
    return $issueVersionID;

}


function addToReport( $line )
{
    global $pdfJob;
    $pdfJob['report'][] = $line;
}

function showReport()
{
    global $pdfJob;
    print "<h3>Publish Report</h3>";
    foreach ( $pdfJob['report'] as $line ){
        $line = str_replace("\n","<br>", $line);
        $line = str_replace("\r","<br>", $line);
        print $line . EOL;
    }

}


/*
// take the enterprise brand,
// use the mapping from the config
// see if we can find a matching puplePublication
 */
function mapEnterpriseBrand( $brand, $purplePublications)
{
    $brandMapping = unserialize( PURPLE_BRANDMAPPING);
    $purplePubId = false;
    //LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "BrandMApping:" . print_r($brandMapping,1));
    if ( array_key_exists( $brand,$brandMapping)){

        $purplePublication = $brandMapping[$brand]['PUBLICATION'];
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "Found mapping [". $brand ."] to [$purplePublication]");
        foreach( $purplePublications as $pub){
            if ( $pub['name'] == $purplePublication){
                $purplePubId = $pub['id'];
            }
        }
    }
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "return with purplePubId:$purplePubId");
    return $purplePubId;
}

/*
// take the enterprise brand,
// return the list of valid status
 */
function mapEnterpriseStatus( $brand )
{
    $brandMapping = unserialize( PURPLE_BRANDMAPPING);
    $statusList = array();
    //LogHandler::Log( '-mapEnterpriseStatus-', 'DEBUG', "BrandMApping:" . print_r($brandMapping,1));
    if ( array_key_exists( $brand,$brandMapping)){
        if ( array_key_exists( 'VALIDSTATUS',$brandMapping[$brand])) {
            $statusList = $brandMapping[$brand]['VALIDSTATUS'];
        }
    }
    //LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "return with statusList:" . print_r($statusList,1));
    return $statusList;
}


/*
// take the enterprise brand,
// return the PDFproperties as array
 */
function mapPDFProperties( $brand )
{
    $brandMapping = unserialize( PURPLE_BRANDMAPPING);
    $PDFPropertiesFile = 'PDFProperties_Generic.php';
    $PDFproperties = array();
    //LogHandler::Log( '-mapPDFProperties-', 'DEBUG', "BrandMapping:" . print_r($brandMapping,1));
    if ( array_key_exists( $brand,$brandMapping)){
        if ( array_key_exists( 'PDF_PROPERTIES',$brandMapping[$brand]) &&
        	 $brandMapping[$brand]['PDF_PROPERTIES'] != '' ) {
            $PDFPropertiesFile = $brandMapping[$brand]['PDF_PROPERTIES'];
        }
    }
    LogHandler::Log( '-mapPDFProperties-', 'DEBUG', "Using PDFProperties File:" . $PDFPropertiesFile);
    // see if the file exists
    if ( file_exists( __DIR__ . '/PDFprofiles/' . $PDFPropertiesFile )){
    	// load the file
    	LogHandler::Log( '-mapPDFProperties-', 'DEBUG', "Loading PDF properties");
    	include __DIR__ . '/PDFprofiles/' . $PDFPropertiesFile;
    	
    }
    LogHandler::Log( '-mapPDFProperties-', 'DEBUG', "PDFproperties:" . print_r($PDFproperties,1));
    return $PDFproperties;
}


function autoSetPreview( $brand )
{
    $autoPreview = false;
    $brandMapping = getBrandInfo( $brand);
    if ( $brandMapping )
    {
       if ( array_key_exists( 'AUTO_SETPREVIEW',$brandMapping)) {
        $autoPreview = $brandMapping['AUTO_SETPREVIEW'];
        }
    }
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "return with autoPreview:" . print_r($autoPreview,1));
    return $autoPreview;
}

function autoSetPublish( $brand )
{
    $autoPublish = false;
    $brandMapping = getBrandInfo( $brand);
    if ( $brandMapping)
    {
        if ( array_key_exists( 'AUTO_SETPUBLISH',$brandMapping)) {
            $autoPublish = $brandMapping['AUTO_SETPUBLISH'];
        }
    }
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "return with autoPublish:" . print_r($autoPublish,1));
    return $autoPublish;
}

function getBrandInfo( $brand)
{
    $brandMapping = unserialize( PURPLE_BRANDMAPPING);
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "BrandMApping:" . print_r($brandMapping,1));
    if ( array_key_exists( $brand,$brandMapping)){
        return $brandMapping[$brand];
    }
    return false;
}


function findOrCreateIssue( $purple, $pubID, $entIssue, $purpleIssues, $coverImage = null)
{
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "findOrCreateIssue [$entIssue]");
    //LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "purpleIssues:" . print_r($purpleIssues,1));
    $purpleIssueId = false;
    foreach( $purpleIssues as $iss){
        if ( $iss['name'] == $entIssue){
            $purpleIssueId = $iss['id'];
        }
    }
    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "purpleIssueID after scan of existing issues: $purpleIssueId");
    if ( $purpleIssueId === false){
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "No Matching Issues Found ");
        // check the coverImage
        if ( is_null($coverImage)){
            $coverImage = __DIR__ . '/images/woodwing.png';
        }
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG','using coverImage ['. $coverImage.']');
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "need to create issue [$entIssue]");
        $purpleIssueId = $purple->createIssue( $pubID, $name = $entIssue,
            $description = $entIssue,
            $published = date('Y-m-d'),
            $file = $coverImage);
        LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "created purpleIssueID: $purpleIssueId");
    }

    LogHandler::Log( '-PP-getPagesAsArray-', 'DEBUG', "Using purpleIssueId: $purpleIssueId");

    return $purpleIssueId;
}