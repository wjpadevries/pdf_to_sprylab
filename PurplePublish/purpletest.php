<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <title>Test Publish to Sprylab</title>
    <link rel="stylesheet" type="text/css" href="css/purple.css">
</head>
<body>
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/enterprise.class.php';
require_once __DIR__ . '/classes/purple.class.php';


define ( 'EOL', "<br>\n" );
$publications = array();
$issues = array();

$testPDF = __DIR__ . '/testfiles/vtair-airracer.pdf';

echo "<h2>Testing Purple communication</h2>" . EOL;

$purple = new purple( PURPLE_SERVER_URL );

print '<a target="_blank" href="' . PURPLE_SERVER_URL . '">Purple Manager</a>';

echo "<hr>Logon" . EOL;
if ( ! $purple->logon( PURPLE_USER, PURPLE_PSWD ) ){
    echo "Logon failed" . EOL;
}

echo "<hr>configuration" . EOL;
$purple->configuration();

echo "<hr>publicationList" . EOL;
$publications = $purple->publicationList();
showPublications( $publications );




//take the first publication to create the issue in
if ( count($publications) > 0 ){

	$pubID = $publications[0]['id'];
	$issueNr = $publications[0]['issues']+1;

    /*$issueID = $purple->createIssue( $pubID, $name = 'Issue' . $issueNr,
									 $description = 'FirstWWIssue',
									 $published = date('Y-m-d'), 
									 $file = __DIR__ . '/testfiles/woodwing.png');

	echo "IssueID:" . print_r($issueID,1);
    */
	
}



if ( count($publications) > 0 ){
	$pubID = $publications[0]['id'];
	echo "<hr>IssueList (pubID:$pubID)" . EOL;
	$issues = $purple->issuesList($pubID );

	showIssues( $issues ); 
}	

if ( count($issues) > 0 ){

	$issueID = end($issues)['id'];

    //echo "<hr>createIssueVersion" . EOL;
	//$issueVersion = $purple->createIssueVersion( $issueID );
	//print "IssueVersion:" . $issueVersion . EOL;

	echo "<hr>IssueVersionList" . EOL;
	$issueVersionList = $purple->issueVersionList( $issueID );
	print "issueList:" . print_r($issueVersionList,1) . EOL;

	showIssueVersions( $issueVersionList );
	
	if ( count($issueVersionList) > 0 )
	{
		$issueVersion = $issueVersionList[0]['id'];
		print EOL . "Found versionId:$issueVersion" . EOL;
		print "<hr>Attempt to upload pdf" . EOL;
		
		//$pdfUpload = $purple->uploadVersionData( $issueVersion, $testPDF );
		
	}
	
	//
	
	

}

//echo "<hr>Logout" . EOL;
//$purple->logout();






function showPublications( $publications )
{
	print "<hr>Publications" .EOL;
	print "<table><tr><th>Publ.Name</th><th>ID</th><th>Created</th><th>Apps</th><th>#Issues</th></tr>";
	foreach ( $publications as $pub )
	{
		print "<tr>";
		print "<td>" . $pub['name'] . "</td>";
		print "<td>" . $pub['id'] . "</td>";
		print "<td>" . $pub['createdAt'] . "</td>";
		$apps = '';
		foreach ( $pub['apps'] as $app )
		{
			$apps .= $app['name'];
		}
		print "<td>" . $apps . "</td>";
		print "<td>" . $pub['issues'] . "</td>";
		print "</tr>";
	}
	print "</table>";
}


function showIssues( $issues )
{
	print "<hr>Issues" .EOL;
	print "<table><tr><th>Issue.Name</th><th>ID</th><th>Created</th><th>Published</th><th>Active</th></tr>";
	foreach ( $issues as $iss )
	{
		//print print_r($issues,1);
		print "<tr>";
		print "<td>" . $iss['name'] . "</td>";
		print "<td>" . $iss['id'] . "</td>";
		print "<td>" . $iss['createdAt'] . "</td>";
		print "<td>" . $iss['published'] . "</td>";
		print "<td>" . $iss['active'] . "</td>";
		print "</tr>";
	}
	print "</table>";
}

function showIssueVersions( $issueVersions )
{
    print "<hr>IssuesVersions" .EOL;
    print "<table><tr><th>id</th><th>number</th><th>Created</th><th>active</th><th>preview</th><th>comingSoon</th></tr>";
    foreach ( $issueVersions as $issVer )
    {
        //print print_r($issues,1);
        print "<tr>";
        print "<td>" . $issVer['id'] . "</td>";
        print "<td>" . $issVer['number'] . "</td>";
        print "<td>" . $issVer['created'] . "</td>";
        print "<td>" . $issVer['active'] . "</td>";
        print "<td>" . $issVer['preview'] . "</td>";
        print "<td>" . $issVer['comingSoon'] . "</td>";
        print "</tr>";
    }
    print "</table>";
}
