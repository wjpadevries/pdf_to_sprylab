<?php


 require_once __DIR__ .'/fpdf/fpdf.php';
 require_once __DIR__ .'/fpdi/fpdi.php';
 
 
 class WWPDF extends FPDF
 {
 
   var $javascript;
    var $n_js;

    function IncludeJS($script) {
        $this->javascript=$script;
    }

    function _putjavascript() {
        $this->_newobj();
        $this->n_js=$this->n;
        $this->_out('<<');
        $this->_out('/Names [(EmbeddedJS) '.($this->n+1).' 0 R ]');
        $this->_out('>>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<<');
        $this->_out('/S /JavaScript');
        $this->_out('/JS '.$this->_textstring($this->javascript));
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putresources() {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
    }

    function _putcatalog() {
        parent::_putcatalog();
        if (isset($this->javascript)) {
            $this->_out('/Names <</JavaScript '.($this->n_js).' 0 R>>');
        }
    }
 
   //Page header
   function Header()
   {
     
     //Arial bold 15
     $this->SetFont('Arial','B',15);
     //Move to the right
     //$this->Cell(10);
     //Title
     $this->SetXY(3,3);
     $this->SetFillColor(128,128,128);
     $this->Cell(292,15,'Publication Overview',1,0,'C',1);
     //Logo
     //$this->Image('../images/woodwing95.gif',189,11,10,10);
     $this->SetXY(10,25);
     $this->SetFont('Arial','I',7);
     //$this->Cell(190,5  , 'DISCLAIMER: This is a sample that shows how objects can be printed from Woodwing Enterprise and ContentStation. These scripts are provided as-is',1,0,'C');
    
     //Line break
     $this->Ln(20);
   }

  //Page footer
  function Footer()
  {
    //Position at 1.5 cm from bottom
    $this->SetY(-20);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
   
   //Page number
    
    $time = localtime();
    $timestr =  "printed at " . sprintf("%02d-%02d-%d  %02d:%02d:%02d", $time[3]  , $time[4]+1, $time[5]+1900 , $time[2] , $time[1] , $time[0]) ;
    $this->SetFont('Arial','I',7);
   // $this->Cell(0,5  , 'DISCLAIMER: This is a sample that shows how objects can be printed from Woodwing Enterprise and ContentStation. These scripts are provided as-is',1,0,'C');
    $this->SetFont('Arial','I',8);
    $this->SetXY(10,-12);
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb} ' . $timestr,0,0,'C');
  }
  
  
  function AutoPrint($dialog=false)
  {
    //Launch the print dialog or start printing immediately on the standard printer
    $param=($dialog ? 'true' : 'false');
    $script="print($param);";
    $this->IncludeJS($script);
  }

  function AutoPrintToPrinter($server, $printer, $dialog=false)
  {
    //Print on a shared printer (requires at least Acrobat 6)
    $script = "var pp = getPrintParams();";
    if($dialog)
        $script .= "pp.interactive = pp.constants.interactionLevel.full;";
    else
        $script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
    $script .= "pp.printerName = '\\\\\\\\".$server."\\\\".$printer."';";
    $script .= "print(pp);";
    $this->IncludeJS($script);
  }
  
  
  
}

