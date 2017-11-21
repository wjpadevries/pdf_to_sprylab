<?php

define('LOGPATH', dirname(__FILE__) . '/log/');
define('LOGNAME', basename(__FILE__) );

define('LOG_ALL',true); // if true, everything will be logged, 
                        // if false, only IP's listed will be logged
                        //   or if defined, only specified userID will be logged

// define IP-addresses to log
define('LOG_IP', serialize( array('localhost',
                                 )
                           ) );   

// define userID's to log                           
define('LOG_USERID' , serialize( array(-4,)));                                


// see : http://php.net/manual/en/timezones.asia.php
ini_set('date.timezone', 'Asia/Kuala_Lumpur');





// =======================================================
error_reporting(E_ALL);
ini_set('display_errors','On');
ini_set ('error_log', LOGPATH . 'php.log');
set_error_handler( 'ErrorHandler' );

function ErrorHandler( $errno, $errmsg, $file, $line, $debug )
{
   MyLog("ERROR in PHP: Errno:$errno  errMsg[$errmsg] $file:$line");
}   





function getRealIpAddr()
{
    $ip = '::1';
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    elseif (!empty($_SERVER['REMOTE_ADDR']))
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    
    
    if ( $ip == '::1' ) { $ip = 'localhost';}
    return $ip;
}



/**
	 * Places dangerous characters with "-" characters. Dangerous characters are the ones that 
	 * might error at several file systems while creating files or folders. This function does
	 * NOT check the platform, since the Server and Filestore can run at different platforms!
	 * So it replaces all unsafe characters, no matter the OS flavor. 
	 * Another advantage of doing this, is that it keeps filestores interchangable.
	 * IMPORTANT: The given file name should NOT include the file path!
	 *
	 * @param string $fileName Base name of file. Path excluded!
	 * @return string The file name, without dangerous chars.
	 */
function replaceDangerousChars( $fileName )
{
    MyLog('-replaceDangerousChars');
    MyLog(" input: $fileName ");
	$dangerousChars = "`~!@#$%^*\\|;:'<>/?\"";
	$safeReplacements = str_repeat( '-', strlen($dangerousChars) );
	$fileName = strtr( $fileName, $dangerousChars, $safeReplacements );
	MyLog(" output: $fileName ");
	return $fileName;
}
	
	/**
	 * Encodes the given file path respecting the FILENAME_ENCODING setting.
	 *
	 * @param string $path2encode The file path to encode
	 * @return string The encoded file path
	 */
function encodePath( $path2encode )
{
  MyLog('-encodePath');
  MyLog(" input: $path2encode ");
  
  setlocale(LC_CTYPE, 'nl_NL');
  $newPath = iconv('UTF-8', "ASCII//TRANSLIT", $path2encode);
  $newPath = preg_replace('/[^A-Za-z0-9\-]/', '', $newPath);
  
  MyLog(" output: $newPath ");
  return $newPath;
}

function clearLogFiles($logpath,  $olderThenTime )
{
 $folderName = $logpath;
 MyLog ( "Start scanning $folderName [ $olderThenTime ] <br>");
 $dir_to_delete = array();
 if (file_exists($folderName)) 
 {
    foreach (new DirectoryIterator($folderName) as $fileInfo) {
        
        if ($fileInfo->isDot()) 
        {
          continue;
        }
        
        if (is_dir($fileInfo->getRealPath()))
        {
          clearLogFiles($fileInfo->getRealPath(),  $olderThenTime );
          // if dir is empty, also remove dir
          if ( is_dirEmpty( $fileInfo->getRealPath() ) )
          {
             MyLog ( "Remove directory [" . $fileInfo->getRealPath() . "]<br>");
             $dir_to_delete[] = $fileInfo->getRealPath();
          }
        }
        
        if (! is_dir($fileInfo->getRealPath()))
        {
          MyLog ( "--<br>");
          MyLog ( "check file:" . $fileInfo->getRealPath() . '<br>');
          //MyLog ( "FileTime:" . $fileInfo->getCTime() . '<br>');
          $compareTime = (time() - $fileInfo->getMTime());
          MyLog ( "compare:" . $compareTime . ' against ' . $olderThenTime . '<br>');
          
          if ( $compareTime > $olderThenTime) 
          {
            MyLog ( "Remove it<br>");
            unlink($fileInfo->getRealPath());
          }
          else
          {
            MyLog ( "File is to new to delete<br>");
          }
          
          if (basename($fileInfo->getRealPath() ) == '.DS_Store')
          {
             MyLog ( "Remove .DS_Store<br>");
             unlink($fileInfo->getRealPath());
          } 
        }
    }
  }
  // now remove empty dirs
  foreach ( $dir_to_delete as $dir )
  {
     MyLog ( "Remove Empty directory [" . $dir  . "]<br>");
     rmdir ($dir);
  }
  
  
}


function is_dirEmpty( $folderPath )
{
   $files_in_directory = scandir($folderPath);
   $items_count = count($files_in_directory);
   if ($items_count <= 2)
   {
    $empty = true;
   }
   else 
   {
    $empty = false;
   }
   return $empty;
}

function getLogPath()
{
   $logfolder = LOGPATH;
   $date = date('Ymd');
   
    if ( ! file_exists( $logfolder) )
    { 
       error_log (basename(__FILE__) . ' -> ERROR: Logfolder [' . $logfolder . '] does not exists, please create',0);
    }
    
   $logfolder = $logfolder . $date ;
   if ( ! file_exists( $logfolder) )
   {
     mkdir($logfolder,0777);
     chmod($logfolder,0777);
   } 
      
      
   // add IPAdres
   $ip = getRealIpAddr();
   $logfolder = $logfolder . '/' . $ip;
   if ( ! file_exists( $logfolder) )
   {
     mkdir($logfolder,0777);
     chmod($logfolder,0777);
   }    

   return $logfolder .'/';
}

function getLogTimeStamp()
{
  list($ms, $sec) = explode(" ", microtime()); // get seconds with ms part
  $msFmt = sprintf( '%03d', round( $ms*1000, 0 ));
  return date('Y-m-d H-i-s (T)',$sec).'.'.$msFmt;
}

function mustLog()
{
   global $loggedInUser;
   $do_log = false;
  // error_log('LOG_ALL:' . LOG_ALL );
   $ip = getRealIpAddr();
   
   if ( LOG_ALL === false)
   {
    
     $logip = unserialize(LOG_IP);
    // error_log('logip:' . print_r($logip,1));
    // error_log('ip:' . print_r($ip,1));
      
     if (in_array($ip,$logip) )
     {
       $do_log = true;
     }  
   
     // check for userID logging
     $userID = 0;
     if ( isset($loggedInUser->user_id) )
     {
        $userID = $loggedInUser->user_id;
     }
     $logusers = unserialize(LOG_USERID);
     //error_log('userID:'   . $userID);
     //error_log('logusers:' . print_r($logusers,1));
     if ( in_array($userID,$logusers))
     {
       $do_log = true;
     }
   
   
   }
   else
   {
     $do_log = true;
   } 
   //error_log( 'do_log:' . $do_log );
   return $do_log;
}


function MyLogS( $logline )
{
   MyLog( $logline, true );
}

function MyLog( $logline , $toBrowser = false)
{ 
   global $loggedInUser, $currentCommand, $logTimeStamp, $LOGNAME, $logfilename;
   
   if ( isset($logfilename))
   {
     $LOGNAME = $logfilename;
   }
   else
   {
     $LOGNAME = LOGNAME;
   }
   
   if ( mustLog() === true )
   {
      
      $userID = 0;
      if ( isset($loggedInUser->user_id) )
      {
        $userID = $loggedInUser->user_id;
      }
      $ip = getRealIpAddr();

      $datetime = getLogTimeStamp() . "[$ip] [$userID]";
      //'[' . date("d-M-Y H:i:s") . "] [$ip] [$userID]";
      
      $logfolder = getLogPath();
      $logname = $LOGNAME;
      
      
                                        
      if ( $currentCommand != '' &&
           $logTimeStamp   != '')
      {
         $logfile = $logfolder . '/' .$logTimeStamp . '-' . $currentCommand .  '.log';
      }
      else
      {                                  
        $logfile = $logfolder . '/' . $logname . '.log';
      }
      
      $logh = fopen($logfile, 'a');
      if ( $logh !== false)
      {
         fwrite( $logh, $datetime .  $logline . "\n");
         fclose( $logh );
         chmod ( $logfile, 0777 );
      }
      else
      {
          error_log ( basename(__FILE__) . ' -> ERROR: writing to logfile [$logfile]' );
      }
    
      if ( $toBrowser )
      {
        print $logline . "<br>\n"; 
        try {while (ob_get_level() > 0) ob_end_flush();} catch( Exception $e ) {}
      }     
    }
 } 


function MyLogRequest( $command, $request)
{
   if ( mustLog() === true )
   {
     $logfolder = getLogPath();
     $filename = $logfolder . getLogTimeStamp() . '-' . $command . '.req.txt';
     file_put_contents( $filename, print_r($request,1) );
     chmod( $filename, 0777 );
   }
}

function MyLogResponse( $command, $response)
{
   if ( mustLog() === true )
   {
     $logfolder = getLogPath();
     $filename = $logfolder . getLogTimeStamp() . '-' . $command . '.resp.txt';
     file_put_contents( $filename,  print_r(json_decode($response),1) );
     chmod( $filename, 0777 );
   }
}
