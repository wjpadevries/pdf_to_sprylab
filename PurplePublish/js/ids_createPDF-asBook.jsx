#include "woodwing/json2.jsxinc";
//#inlude "Scripts/Scripts\ Panel/Smart\ Connection\ Book\ Support/json2.jsxinc";

// ---------------------------------------------------------
// Constants
// ---------------------------------------------------------
const SCRIPTVERSION = '0.2';
const CONSOLE = "cons";
const INFO = "info";
const WARNING = "warn";
const ERROR = "error";

const LOG_FILE = 1;
const LOG_CONSOLE = 2;
const DEBUGLEVEL = LOG_FILE;

const THIS_FILE_NAME = "ids_createPDF-asBook.jsx";

$.gc();

// ---------------------------------------------------------
// Get parameters
// ---------------------------------------------------------

var pServer     = app.scriptArgs.get("server");
var pTicket     = app.scriptArgs.get("ticket");
var pLayout     = app.scriptArgs.get("layout");
var logfile     = app.scriptArgs.get("logfile"); // variable needs to be called 'logfile'
var IDSJOB      = app.scriptArgs.get("IDSJOB");
var REPORTFILE    = app.scriptArgs.get("reportfile");



app.scriptPreferences.version=8.0;
app.serverSettings.imagePreview = true;
initlog(logfile);
logSystemInfo();

wwlog( CONSOLE, '----------------' );
wwlog( CONSOLE, 'Script Version:' + SCRIPTVERSION);
wwlog( CONSOLE, 'Script Name:'    + THIS_FILE_NAME);



wwlog( CONSOLE, '----------------' );
wwlog( CONSOLE , 'before login: activeServer = [' + app.entSession.activeServer  + '] activeUser = [' + app.entSession.activeUser + ']' );

// To not have a conflict with MCE previews and logged in accounts
// we always re-login (with a performance price)
if( app.entSession.activeTicket ) {
    wwlog( CONSOLE , 'Found activeTicket, so logout active session.');
    // also unlock all possible documents
    wwlog(CONSOLE, 'Aborting possible open layouts ');
    if ( app.documents.length > 0)
    {
        for ( var i=0;i<app.documents.length;i++)
        {
            var doc = app.documents.item(i);
            doc.close();
        }
    }
    app.entSession.logout();
}

wwlog( CONSOLE, 'Login to [' + pServer + '] server with ticket [' + pTicket + ']' );
app.entSession.forkLogin( '', pTicket, pServer );
wwlog( CONSOLE , 'after login: activeServer = [' + app.entSession.activeServer  + '] activeUser = [' + app.entSession.activeUser + ']' );

// -------------------------------
// generic login sequence done,
// now let's do our job
// -------------------------------

//wwlog( CONSOLE, 'IDSJOB:' + IDSJOB ); // show the raw json
IDSJOB = JSON.parse( IDSJOB);
wwlog( CONSOLE, '-- start custom script --');
wwlog( CONSOLE, 'user:' + IDSJOB.user);
wwlog( CONSOLE, 'PDFprofile:' + IDSJOB.pdf_profile);
wwlog( CONSOLE, 'coverImage:' + IDSJOB.coverImage);
wwlog( CONSOLE, 'Book File:' + IDSJOB.bookFile);

wwlog( CONSOLE, 'REPORTFILE:' + REPORTFILE);


// some vars to use later on
var myDoc;
var myErr;
var coverImageCreated = false;






//
// loop trough the layouts and open them
// create a preview of the first page
//
var docs = new Array();
var d = 0;
var layouts = IDSJOB.layouts;
for (var layoutID in layouts) {

    wwlog( CONSOLE, 'LayoutID:' + layoutID );
    wwlog( CONSOLE, 'Opening layout [' + layoutID + ']');

    myDoc = app.openObject(layoutID, true, true ); // false is open read-only

    if (!myDoc) {
        wwlog( CONSOLE, 'Could not open layout [' + pLayout + '] ');
        continue;
    }

    wwlog( CONSOLE , 'Openend Layout name:' + myDoc.name)
    reportLog( 'Openend Layout name:' + myDoc.name);

    // we need to have a nice cover image for purple
    if ( !coverImageCreated ){
        wwlog( CONSOLE, 'Creating CoverImage from page1 to ' + IDSJOB.coverImage);
        var myPageName = myDoc.pages.item(0).name;
        wwlog( CONSOLE, 'myPageName:' + myPageName );
        app.pngExportPreferences.pngExportRange = PNGExportRangeEnum.EXPORT_RANGE;
        app.pngExportPreferences.pageString = myPageName;
        app.pngExportPreferences.pngQuality = PNGQualityEnum.LOW;
        app.pngExportPreferences.pngColorSpace = PNGColorSpaceEnum.RGB;
        app.pngExportPreferences.exportResolution = 72;
        myDoc.exportFile(ExportFormat.PNG_FORMAT, File(IDSJOB.coverImage));
        coverImageCreated = true;
    }

    // add the open layouts to our docs to be added to book
    docs[d] = myDoc;
    d++;
}

// we now have all layouts open
// open a new book
// Make book file
var myBook;
var file = new File( IDSJOB.bookFile );

if( file.exists )
{
    // remove the file
    wwlog( CONSOLE, 'Removing existing file');
    file.remove();
}

// add the book to IDS
myBook = app.books.add ( IDSJOB.bookFile );


wwlog( CONSOLE, 'Assign Layouts to book');
for(var i = 0; i < docs.length; i++) {
    var doc = docs[i];

    var fullName = doc.fullName;
    if(doc.saved)
    {
        // BZ# 18064. Ignore documents that fail to be added to the book.
        try
        {
            var fullName = doc.fullName;
            wwlog( CONSOLE, 'Layout Readonly:' + doc.readOnly );
            wwlog( CONSOLE, 'Assign Layout:' + fullName );
            myBook.bookContents.add( fullName );
        }
        catch( e )
        {
        }
    }

}

// spreads or single pages? false = single pages
app.interactivePDFExportPreferences.exportReaderSpreads = false;

// we have our book complete, now export
//
wwlog( CONSOLE, 'Exporting Layouts to book-pdf:' + IDSJOB.outputFile);
myBook.exportFile(ExportFormat.INTERACTIVE_PDF, IDSJOB.outputFile);

//close book without save the book.
wwlog( CONSOLE, 'Closing book');
myBook.close(SaveOptions.NO);

//close all layouts
wwlog( CONSOLE, 'Closing all layouts');
for(var i = 0; i < docs.length; i++) {
    var doc = docs[i];
    doc.close();
}


wwlog( CONSOLE, '----------------' );
// logout after a job, since server will not find new issues without relogin
wwlog( CONSOLE , 'Logout [' + pServer + ']' );
app.entSession.logout();

// Mark IDS job as failed in case something went wrong above.
if( typeof myErr != 'undefined' ) {
    wwlog( ERROR, "Error: " + myErr.message + " Source: " + THIS_FILE_NAME + "#" + myErr.line );
    throw( myErr );
}

wwlog( CONSOLE , 'Job Completed' );
wwlog( CONSOLE , '----------------' );





// ---------------------------------------------------------
// report functions functions
// ---------------------------------------------------------
function reportLog( reportline )
{

    //wwlog(CONSOLE, 'writing line to report:' + reportline);
    if( typeof(REPORTFILE) != "undefined" ) {
        try {
            var oLogFile = new File( REPORTFILE );
            if( oLogFile.open( "a" ) ) {
                oLogFile.writeln( reportline );
                oLogFile.close();
            }
        }
        catch( err ) { // could not write report file
            app.consoleerr( "Error: " + err.message + " Source: " + THIS_FILE_NAME + "#" + err.line );
        }
    }
}

// ---------------------------------------------------------
// Log functions
// ---------------------------------------------------------

function getDateShort()
{
    var today   = new Date();
    var year    = today.getFullYear().toString();
    var month   = "0" + (today.getMonth()+1).toString();
    var day     = "0" + today.getDate().toString();

    var h = "0" + today.getHours();
    var m = "0" + today.getMinutes();
    var s = "0" + today.getSeconds();
    var ms = "00" + today.getMilliseconds();

    return year.substr(-4) + '-' + month.substr(-2) + '-' + day.substr(-2) + ' ' +
        h.substr(-2) + ':' + m.substr(-2) + ':' + s.substr(-2) + '.' + ms.substr(-3);
}

function initlog( logfile )
{
    if( typeof( logfile ) != "undefined" ) {
        try {
            var oLogFile = new File( logfile );
            oLogFile.remove();
        }
        catch( err ) {
            app.consoleerr( "Error: " + err.message + " Source: " + THIS_FILE_NAME + "#" + err.line );
        }
    }

}

function wwlogtofile( strLogText )
{
    if( typeof(logfile) != "undefined" ) {
        try {
            var oLogFile = new File( logfile );
            if( oLogFile.open( "a" ) ) {
                oLogFile.writeln( "[" + getDateShort() + "] " + strLogText );
                oLogFile.close();
            }

            app.wwlog( THIS_FILE_NAME, LogLevelOptions.INFO, strLogText );
        }
        catch( err ) { // could not write loglines..., not so serious
            app.consoleerr( "Error: " + err.message + " Source: " + THIS_FILE_NAME + "#" + err.line );
        }
    }
}

function wwlog( logmode, strLogText )
{

    strLogText = ' [' + THIS_FILE_NAME +'] ' + strLogText;
    //strLogText = ' [IdsAutomation] ' + strLogText;
    if( logmode == CONSOLE || logmode == ERROR || DEBUGLEVEL >= LOG_CONSOLE ) {
        try {
            if ( logmode != 'ERROR' ) {
                app.consoleout(strLogText);
            } else {
                app.consoleerr(strLogText);
            }
        }
        catch( err ) { // for debugging with InDesign Client
            $.writeln( '[' + logmode + ']' + strLogText );
        }
    }
    if( DEBUGLEVEL >= LOG_FILE ) {
        wwlogtofile ( '[' + logmode + '] ' + strLogText );
    }
}

function logSystemInfo()
{
    if( typeof(logfile) != "undefined" ) {
        wwlog( INFO, 'InDesign Server version=[v' + app.version + ']' );
        var oProducts = app.products;

        // walk through all installed products
        for( var i=0; i<oProducts.length; i++ ) {
            with( oProducts.item(i) ) { // expose props: name, version and activationState
                var sState = "";
                switch( activationState ) {
                    case ActivationStateOptions.none:
                        sState = "none";
                        break;
                    case ActivationStateOptions.demo:
                        sState = "demo";
                        break;
                    case ActivationStateOptions.serial:
                        sState = "serial";
                        break;
                    case ActivationStateOptions.limitedSerial:
                        sState = "limited serial";
                        break;
                    case ActivationStateOptions.server:
                        sState = "server";
                        break;
                    case ActivationStateOptions.limitedServer:
                        sState = "limited server";
                        break;
                }
                wwlog( INFO, 'Installed plugin: [' + name + '] version=[' + version + '] state=[' + sState + ']' );
            }
        }
    }
}

function sleep( milliseconds )
{
    var start = new Date().getTime();
    for( var i = 0; i < 1e7; i++ ) {
        if( (new Date().getTime() - start) > milliseconds ){
            break;
        }
    }
}

