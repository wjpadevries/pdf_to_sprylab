<?php



// this will insert the div components
// that will be used to show the progress
class ProgressBar 
{
   public $pbID = 0;
   public $totalCount = 0;
   public $actualCount = 0;
   public $detail = '';
   public $information = '';
   public $header = '';

   function setProgressBarID( $id = 0 )
   {
     $this->pbID = $id;
   }


	public   function insertDiv()
	{
		print "
		<div id=\"progressBar_". $this->pbID . "\">
		<!-- Header holder -->
		<div id=\"header_"       . $this->pbID . "\" style=\"width\">" .$this->header . "</div>
		
		<!-- Progress bar holder -->
		<div id=\"progress_"   . $this->pbID . "\" style=\"width:500px;border:1px solid #ccc;background-color:#ccc;\">&nbsp;</div>
		<!-- Progress information -->
		<div id=\"information_". $this->pbID . "\" style=\"width\"></div>
		<br>
		<!-- Detail bar holder -->
		<div id=\"detail_".     $this->pbID . "\" style=\"width\"></div>
		<!-- Error information -->
		<div id=\"errormsg_"   . $this->pbID . "\" style=\"width\"></div>
		</div>";


		try {while (ob_get_level() > 0) ob_end_flush();} catch( Exception $e ) {}
	}

    // in case we want to insert ourselves. 
    public   function insertDivAsString()
	{
		$div = "<div id=\"progressBar_". $this->pbID . "\">
		<!-- Progress bar holder -->
		<div id=\"progress_". $this->pbID . "\" style=\"width:500px;border:1px solid #ccc;background-color:#ccc;\">&nbsp;</div>
		<!-- Progress information -->
		
		<div id=\"information_". $this->pbID . "\" style=\"width\"></div>
		<br>
		<!-- Detail bar holder -->
		<div id=\"detail_". $this->pbID . "\" style=\"width\"></div>
		<!-- Error information -->
		<div id=\"errormsg_". $this->pbID . "\" style=\"width\"></div>
		</div>";
		


		//try {while (ob_get_level() > 0) ob_end_flush();} catch( Exception $e ) {}
		return $div;
	}


	public  function pbUpdate()
	{
	  $percent = 0;
	  //echo 'a:' . $this->actualCount;
	  //echo 't:' . $this->totalCount;
  
	  if ( $this->totalCount > 0 )
	  {
		$percent = intval($this->actualCount/$this->totalCount * 100)."%";
	  }
  
      $infoStr =  $this->actualCount.' tasks of ' . $this->totalCount . ' performed.';
      if ($this->information != '')
      {
         $infoStr = $this->information;
      }
	  echo '<script language="javascript">
	  	document.getElementById("header_'      . $this->pbID . '").innerHTML="' . $this->header .'";
	
	    document.getElementById("progress_'    . $this->pbID . '").innerHTML="<div style=\"width:500px;border:1px solid #ccc;background-color:#ccc;\">&nbsp;</div>";
		document.getElementById("progress_'    . $this->pbID . '").innerHTML="<div style=\"width:'.$percent.';background-color:#2f2;\">&nbsp;</div>";
		document.getElementById("detail_'      . $this->pbID . '").innerHTML="' . $this->detail .'";
		document.getElementById("information_' . $this->pbID . '").innerHTML="' . $infoStr . '";
   
		</script>';
	
	  // This is for the buffer achieve the minimum size in order to flush data  
	  echo str_repeat(' ',1024*64);

	
	  // Send output to browser immediately
	  flush();  


	}


    public function addToDetail( $string )
    {
       $this->detail = $this->detail . $string;
       $this->pbUpdate();
    
    }
    
    public function show()
    {
       echo '<script language="javascript">
       document.getElementById("progressBar_'    . $this->pbID .'").innerHTML="
       <!-- Progress bar holder -->
		<div id="progress_'. $this->pbID . '" style="width:500px;border:1px solid #ccc;background-color:#ccc;">&nbsp;</div>
		<!-- Progress information -->
		<div id="information_'. $this->pbID . '" style="width"></div>
		<!-- Detail bar holder -->
		<br>
		<div id="detail_'. $this->pbID . '" style="width"></div>
		<!-- Error information -->
		<div id="errormsg_'. $this->pbID . '" style="width"></div>";
       </script>';
    }
    
    
    public function hide()
    {
       echo '<script language="javascript">
       document.getElementById("progressBar_'    . $this->pbID .'").innerHTML="";
       </script>';
    }

    public function reset()
    {
       $this->totalCount  = 0;
       $this->actualCount = 0;
       $this->detail = '';
       $this->pbUpdate();
    }
    
	public function pbFinished()
	{
	    $this->actualCount = $this->totalCount;
        self::pbUpdate();
	   echo '<script language="javascript">
	   document.getElementById("information_'. $this->pbID . '").innerHTML="Process completed";
	   document.getElementById("detail_'. $this->pbID . '").innerHTML="&nbsp;";
	   </script>';
	}

}