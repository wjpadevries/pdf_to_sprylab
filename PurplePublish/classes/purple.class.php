<?php 

class purple 
{
	private $_serverURL = null;
	private $_sessionID = null;
	private $_xrkey = null;
	private $_configuration = null;
	private $_debug = false;
	
	
	public function __construct( $serverURL )
	{
		$this->_serverURL = $serverURL;
		$this->_xrkey = uniqid();
		$this->_debug = false;
	}
	
	public function debugOn()
    {
        $this->_debug = true;
    }

    public function debugOff()
    {
        $this->_debug = false;
    }
	
	public function configuration( )
	{
		$restCall = $this->_serverURL . '/configuration';
		$params = array( );
		$headers = array();;
		$response = $this->request( 'GET', $restCall, $params, $headers );
		
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
			// handle the response
			//print "configuration response:" . print_r($response,1);
			$this->_configuration = $response;
		}
		return $this->_configuration;
	}
	
	
	public function logon( $user, $passwd )
	{
		$restCall = $this->_serverURL . '/auth/login';
		$params = array( 'email' => $user, 'password' => $passwd );
		$headers = array();;
		$response = $this->request( 'POST', $restCall, $params, $headers );
		
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
			// handle the response
			// we should have a json structure with sessionID
			//print "Logon response:" . print_r($response,1);
			$this->_sessionID = $response['sessionID'];
		}
		return true;
	}
	
	public function logout()
	{
		if (! $this->isLoggedIn() ) { return false ; }
		$restCall = $this->_serverURL . '/auth/logout';
		$params = array();
		$headers = array();;
		$response = $this->request( 'POST', $restCall, $params, $headers );
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
			
			print "logout response:" . print_r($response,1);
		}	
		$this->_sessionID = null;
		$this->_xrkey = null;
		return true;
	}
	
	public function publicationList()
	{
		if (! $this->isLoggedIn() ) { return false ; }
		$restCall = $this->_serverURL . '/publication/list';
		$params = array();
		$headers = array();;
		$response = $this->request( 'GET', $restCall, $params, $headers );
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
		    //print "publicationList response:" . print_r($response,1);
		    return $response;
		}    
	}
	
	public function issuesList( $publicationID)
	{
		if (! $this->isLoggedIn() ) { return false ; }
		$restCall = $this->_serverURL . '/publication/listissues';
		$params = array('publicationId' => $publicationID );
		$headers = array();;
		$response = $this->request( 'GET', $restCall, $params, $headers );
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
		    //print "issuesList response:" . print_r($response,1);
		    return $response;
		} 
		
	
	}
	
	public function createIssue( $publicationID, $name, $description, $published, $file)
	{
		if (! $this->isLoggedIn() ) { return false ; }
		
		$restCall = $this->_serverURL . '/issue';
		
		$params = array('publicationId' => $publicationID,
					    'name' 			=> $name,
					    'description' 	=> $description,
					    'published' 	=> $published, 
					    'file' 			=> $file,
					    'issueId'       => -1 ,
					    'id'       => -1 );
		$headers = array();;
		$response = $this->request( 'POSTFORM', $restCall, $params, $headers );
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
		    //print "createIssue response:" . print_r($response,1);
		    return $response;
		} 
	}
	
	public function createIssueVersion( $issueID, $description = null)
	{
		if (! $this->isLoggedIn() ) { return false ; }
		
		$restCall = $this->_serverURL . '/version';
		
		$params = array(
						'issueId'       => $issueID ,
					   );
		$headers = array();;
		$response = $this->request( 'POST', $restCall, $params, $headers );
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
		    //print "createIssueVersion response:" . print_r($response,1);
		    return $response;
		} 
	}
	
	
	public function issueVersionList( $issueID)
	{
		if (! $this->isLoggedIn() ) { return false ; }
		
		$restCall = $this->_serverURL . '/issue/listversions';
		
		$params = array(
						'issueId'       => $issueID ,
					   );
		$headers = array();;
		$response = $this->request( 'GET', $restCall, $params, $headers );
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
		    //print "issueVersionList response:" . print_r($response,1);
		    return $response;
		} 
	}


	// test if the last IssueVersion is active or previewed
	public function isLastIssueVersionActive( $issueVersionList)
    {
        //print "issueVersionList:" . print_r( $issueVersionList,1);

        // need to sort the list to make sure heighest number is always last
        //usort($issueVersionList, 'sortIssueList');
        //print "issueVersionList:" . print_r( $issueVersionList,1);

        $isActive = false;
        $lastVersion = reset($issueVersionList);
        //print "lastversion:" . print_r( $lastVersion,1);
        //print "active:" . (int)$lastVersion['active'] ;
        if ( $lastVersion['active'] == 1)
        {
            print "yep";
        }


        if (  (int)$lastVersion['active'] == 1  ||
              (int)$lastVersion['preview'] == 1 ||
              (int)$lastVersion['comingSoon'] == 1)
        {
            //print "IssueVersion is active<br>";
            $isActive = true;
        }
        else{
            //print "IssueVersion is NOT active<br>";
        }

        return $isActive;
    }

    private function sortIssueList($a,$b)
    {
        if ($a['number'] == $b['number']){ return 0; }
        if ($a['number'] > $b['number']){
            return -1;
        }
        else
        { return +1;}
    }

	public function issueVersionPublish( $issueVersionID )
    {
        if (! $this->isLoggedIn() ) { return false ; }

        $restCall = $this->_serverURL . '/version/activate';

        $params = array('versionId' => $issueVersionID );
        $headers = array();;
        $response = $this->request( 'POST', $restCall, $params, $headers );
        if ( $this->responseHasError( $response ))
        {
            $this->showError($response);
            return false;
        }else{
            //print "issueVersionPublish response:" . print_r($response,1);
            return $response;
        }
    }

    public function issueVersionUnPublish( $issueVersionID )
    {
        if (! $this->isLoggedIn() ) { return false ; }

        $restCall = $this->_serverURL . '/version/deactivate';

        $params = array('versionId' => $issueVersionID );
        $headers = array();;
        $response = $this->request( 'POST', $restCall, $params, $headers );
        if ( $this->responseHasError( $response ))
        {
            $this->showError($response);
            return false;
        }else{
            //print "issueVersionPublish response:" . print_r($response,1);
            return $response;
        }
    }

    public function issueVersionPreview( $issueVersionID )
    {
        if (! $this->isLoggedIn() ) { return false ; }

        $restCall = $this->_serverURL . '/version/preview';

        $params = array('versionId' => $issueVersionID );
        $headers = array();;
        $response = $this->request( 'POST', $restCall, $params, $headers );
        if ( $this->responseHasError( $response ))
        {
            $this->showError($response);
            return false;
        }else{
            //print "issueVersionPublish response:" . print_r($response,1);
            return $response;
        }
    }

    public function issueVersionUnPreview( $issueVersionID )
    {
        if (! $this->isLoggedIn() ) { return false ; }

        $restCall = $this->_serverURL . '/version/notpreview';

        $params = array('versionId' => $issueVersionID );
        $headers = array();;
        $response = $this->request( 'POST', $restCall, $params, $headers );
        if ( $this->responseHasError( $response ))
        {
            $this->showError($response);
            return false;
        }else{
            //print "issueVersionPublish response:" . print_r($response,1);
            return $response;
        }
    }

    public function issueVersionComingSoon( $issueVersionID )
    {
        if (! $this->isLoggedIn() ) { return false ; }

        $restCall = $this->_serverURL . '/version/comingsoon';

        $params = array('versionId' => $issueVersionID );
        $headers = array();;
        $response = $this->request( 'POST', $restCall, $params, $headers );
        if ( $this->responseHasError( $response ))
        {
            $this->showError($response);
            return false;
        }else{
            //print "issueVersionPublish response:" . print_r($response,1);
            return $response;
        }
    }

    public function issueVersionUnComingSoon( $issueVersionID )
    {
        if (! $this->isLoggedIn() ) { return false ; }

        $restCall = $this->_serverURL . '/version/notcomingsoon';

        $params = array('versionId' => $issueVersionID );
        $headers = array();;
        $response = $this->request( 'POST', $restCall, $params, $headers );
        if ( $this->responseHasError( $response ))
        {
            $this->showError($response);
            return false;
        }else{
            //print "issueVersionPublish response:" . print_r($response,1);
            return $response;
        }
    }
	
	
	
	public function uploadVersionData( $issueVersionID, $file)
	{
		if (! $this->isLoggedIn() ) { return false ; }
		
		$restCall = $this->_serverURL . '/package/uploadTransform';

		$params = array('versionId' => $issueVersionID,
					    'type' 			=> 'content_bundle',
					    'file' 			=> $file );
		$headers = array();;
		$response = $this->request( 'POSTFORM', $restCall, $params, $headers );
		if ( $this->responseHasError( $response ))
		{
			$this->showError($response);
			return false;
		}else{
		    //print "uploadVersionData response:" . print_r($response,1);
		    return $response;
		} 
	}
	
	
	private function isLoggedIn()
	{
		if ( is_null ($this->_sessionID )){ 
		  print "<br>Not logged-in<br>";
		  return false; 
		}
		return true;
	}
	
	private function responseHasError( $response )
	{
		if ( isset( $response['ERRORCODE'])){
			return true;
		}
		return false;
	}
	
	private function showError( $response )
	{
		print "ErrorCode:" . $response['ERRORCODE'] . " message:" . $response['ERRORMESSAGE'] . EOL;
	
	}


	// show data structures
    public function showIssueVersions( $issueVersions )
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







	/**
	 * Perform Rest call
	 *
	 * @param string    $method
	 * @param string    $path
	 * @param array[]   $params
	 * @param array[]   $headers
	 * @return mixed
	 * @throws Exception
	 */
	private function request($method = "GET", $url, $params = array(), $headers = array(), $body = null) 
	{
		global $EOL;
		$debug=false;

        if ( $this->_debug == true )
        {
            $debug=true;
        }
		
		// add the xrkey to the paramas
		$params['xr'] = $this->_xrkey;
		$params['sessionID'] = $this->_sessionID ;
		$headers[] =  "xr: " . $this->_xrkey ;
		$url = $url . '?xr=' . $this->_xrkey;
		

		try {
            // prepare the URL
            if (in_array($method, array('GET', 'DELETE')) && !empty($params)) {
                $url .= '&' . http_build_query($params);
            }

            // get a curl instance
            $ch = curl_init($url);

            if (false === $ch) {
                throw new Exception('Failed to init curl');
            }


            // handle POSTFORM which should be a multipart/form-data post
            if ($method == 'POSTFORM') {
                if (isset($params['file'])) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $fileArray = explode(DIRECTORY_SEPARATOR, $params['file']);
                    $filename = end($fileArray);
                    //unset($params['file']);
                    $params['file'] = new CurlFile($params['file'], finfo_file($finfo, $params['file']), $filename);

                }
                //print "params:" .print_r($params,1) . "<br>";


                //$boundary = '--myboundary-xxx';
                //$body = $this->multipart_build_query($boundary, $params );
                //print "body:" . $body . '<br>';
                //$headers[] = "Content-Type: multipart/form-data"; // ; boundary=$boundary

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } elseif ($method == 'POST') {
                // set URL to do a POST request
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            } elseif ($method == 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            } elseif ($method == 'PUTBODY') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            } elseif ($method == 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            }

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            // debug
            if ($debug) {
                //curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen('php://temp', 'rw+'));
                //curl_setopt($ch, CURLOPT_FILETIME, true);
            }


            // set headers
            //print "headers:" . print_r($headers,1) .  "<br>";
            if ($headers) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            // execute request
            $raw = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (!$raw && $status != 200) {
                //throw new Exception(
                print(printf("<br>Client API Error [%s] : %s (%d)<br>", $url, curl_error($ch), curl_errno($ch))) . EOL;
                print "status empty,  http status: $status" . EOL;
                //);
            }

            //echo "<hr>raw result:$raw<br>";

            if ($debug) {
                $requestContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                echo "<br>request Content Type was:" . $requestContentType . "<br>";
                echo "<hr>Verbose information:\n", !rewind($verbose), stream_get_contents($verbose), "\n\n";
            }


            $result = array();
            if ($debug) {
                echo "status:$status<br>";
                echo "<hr>raw result:$raw<br>";
            }
            // handle redirect.
			if ( $status == 302 )
			{
				echo "status:$status<br>";
				echo "<hr>raw result:$raw<br>";
			}elseif ( $status != 200 )
			{
				$result['ERRORCODE'] = $status;
				$result['ERRORMESSAGE'] = $raw;
			}
			else
			{
				$result = json_decode($raw, true);
				if( is_null ($result) ) {
				    // if we can not create json, we return the raw code as-is
					$result = $raw;
				}
			}

			// close connection
			curl_close($ch);

		} catch (\Exception $e) {
			//@todo do something with the error
			throw $e;
		}
		
		return $result;

	}


	private function multipart_build_query1($boundary , $fields )
	{
  		$retval = '';
  		foreach($fields as $key => $value){
    		$retval .= "--$boundary\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n$value\r\n";
  		}
  		$retval .= "--$boundary--";
  		return $retval;
	}
	
	private function multipart_build_query($boundary, $fields)
	{
		$data = '';
		$eol = "\r\n";
		
		$files = array();
		if ( isset( $fields['file'])){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$fileArray = explode(DIRECTORY_SEPARATOR, $fields['file']);
			$filename = end($fileArray);
			$fields['Filedata'] = new CurlFile($fields['file'], finfo_file($finfo, $fields['file']), 'logo.png');
		
			//$files['logo'] =  $fields['file'];
			//unset( $fields['file'] );
		}
		//print "<br>Files:" . print_r( $files,1);

		$delimiter = '-------------' . $boundary;

		foreach ($fields as $name => $content) {
			$data .= "--" . $delimiter . $eol
				. 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
				. $content . $eol;
		}

		$data .= "--" . $delimiter . "--".$eol;


		return $data;
	}
	
	
}