<?php
/**
 * Created by PhpStorm.
 * User: winfrieddevries
 * Date: 31-10-17
 * Time: 12:17
 */

function CheckFolderAccess( $folder ) {
    $ret = true;

    // Now check if the path to the $folder can be used
    if ( !is_dir( $folder ) && $ret ) {
        print "<br>ERROR<br>The specified folder can not be found!<br>";
        print "Please check if this folder exists [" . $folder . '] and has got correct access rights';
        $ret = false;
    }

    if ( $ret ) {
        // last check, see if we can write to the folder
        $fh = fopen( $folder . '/test-write-file.txt', 'wb' );
        if ( !$fh === false ) {
            fclose( $fh );
            unlink( $folder . '/test-write-file.txt' );
        } else {
            print "<br>ERROR<br>The location [$folder] is not writeable for the Enterprise Server<br>";
            print "Please add sufficient rights";
            $ret = false;
        }
    }
    return $ret;
}

function clearFolder( $folder ) {
    $files = glob( $folder . "*" );
    foreach ( $files as $file ) {
        if ( is_file( $file ) ) {
            unlink( $file );
        }
    }
}


function cleanFilename( $filename)
{
    return str_replace(' ','', $filename) ;

}