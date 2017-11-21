<?php

require_once __DIR__ . '/../lib/fpdf/fpdf.php';
require_once __DIR__ . '/../lib/fpdi/fpdi.php';

/**
 * This class override is needed to get the correct pagenumbers being displayed
 * in Acrobat. The extend is on FPDI, but that one is already an extend of FPDF
 * from which we copied the _putcatalog function so we can add the /PageLabels.
 */
class pqFPDI extends FPDI
{
	public $pageNumbers = array();

	function _putcatalog() {
		$this->_out( '/Type /Catalog' );
		$this->_out( '/Pages 1 0 R' );

		// this part is added compared to standard _putcatalog in FPDF
		if ( count( $this->pageNumbers ) > 0 ) {
			$this->_out( '/PageLabels << ' );
			$this->_out( '/Nums [' );
			$c = 0;
			foreach ( $this->pageNumbers as $pagenum ) {
				$this->_out( " $c <</S/D/St $pagenum >>" );
				$c++;
			}
			$this->_out( ' ]' );
			$this->_out( '>>' );
		}

		// add addition
		if ( $this->ZoomMode === 'fullpage' ) {
			$this->_out( '/OpenAction [3 0 R /Fit]' );
		} elseif ( $this->ZoomMode === 'fullwidth' ) {
			$this->_out( '/OpenAction [3 0 R /FitH null]' );
		} elseif ( $this->ZoomMode === 'real' ) {
			$this->_out( '/OpenAction [3 0 R /XYZ null null 1]' );
		} elseif ( !is_string( $this->ZoomMode ) ) {
			$this->_out( '/OpenAction [3 0 R /XYZ null null ' . sprintf( '%.2F', $this->ZoomMode / 100 ) . ']' );
		}

		if ( $this->LayoutMode === 'single' ) {
			$this->_out( '/PageLayout /SinglePage' );
		} elseif ( $this->LayoutMode === 'continuous' ) {
			$this->_out( '/PageLayout /OneColumn' );
		} elseif ( $this->LayoutMode === 'two' ) {
			$this->_out( '/PageLayout /TwoColumnLeft' );
		}
	}
}

class PDFutils
{
	public function getPdfOfLayout( $ticket, $objId, $info ) {
		require_once __DIR__ . '/../../config.php';
		require_once BASEDIR . '/config/configserver.php';
		require_once BASEDIR . '/server/utils/LogHandler.class.php';
		require_once BASEDIR . '/server/bizclasses/BizSession.class.php';
		require_once BASEDIR . '/server/bizclasses/BizObject.class.php';
		require_once __DIR__ . '/config.php';

		BizSession::checkTicket( $ticket );
		$username = BizSession::getShortUserName();

		LogHandler::Log( __METHOD__, 'DEBUG', 'getting PDF pages from ObjId: ' . $objId );

		require_once BASEDIR . '/server/bizclasses/BizTransferServer.class.php';

		$transferSvr = new BizTransferServer();
		$requestInfo = array( 'Pages' ); // only ask what we need
		$object = BizObject::getObject( $objId, $username, false, 'output', $requestInfo, false, false, array( 'Workflow' ) );

		$pdfPages = array();
		if ( $object && $object->Pages ) {
			foreach ( $object->Pages as $page ) {
				$pdfname = $object->MetaData->BasicMetaData->Name;
				LogHandler::Log( __METHOD__, 'DEBUG', 'Name:' . $pdfname );
				LogHandler::Log( __METHOD__, 'DEBUG', 'PageNum:' . $page->PageNumber );
				LogHandler::Log( __METHOD__, 'DEBUG', 'PageOrder' . $page->PageOrder );

				if ( $page->Files ) {
					foreach ( $page->Files as $file ) {
						if ( $file->Rendition === 'output' ) {
							// @todo Handle PDF using $file->FilePath
							$pdfFileName = PDF_TEMPFOLDER . $pdfname . '_' . $page->Edition->Name . '_' . $page->PageOrder . '_' . $page->PageNumber . '.pdf';
							LogHandler::Log( __METHOD__, 'DEBUG', 'Saving PDF to : ' . $pdfFileName );
							// write content to file
							$fh = fopen( $pdfFileName, 'w' );

							$attachment = $file;
							fwrite( $fh, $transferSvr->getContent( $attachment ) );
							fclose( $fh );

							if ( file_exists( $pdfFileName ) ) {

								$key = sprintf( "%'.04d", $page->PageNumber ) . '_' . $page->Edition->Name;
								LogHandler::Log( __METHOD__, 'DEBUG', "Adding page on key [$key]" );
								$pdfPages[$key] = array( 'layoutName' => $pdfname, // just add layoutname to each page.
									'layoutID' => $objId,
									'pageNumber' => $page->PageNumber,
									'pdfFile' => $pdfFileName,
									'height' => $page->Height,
									'width' => $page->Width,
									'edition' => $page->Edition->Name );
							} else {
								LogHandler::Log( __METHOD__, 'DEBUG', 'Error getting filedata' );
							}
							$transferSvr->deleteFile( $file->FilePath );
						}
					}
				}
			}
		}

		return $pdfPages;
	}

	public function combinePdfPages( $pdfName, $pdfPages ) {

		// Now combine the PDF's to one large PDF
		// one PDF per edition
		$startpagenr = 0;
		$endpagenr = 0;

		//create a PDF instance
		$pdfLib = new pqFPDI( 'P', 'mm' );


		foreach ( $pdfPages as $order => $pdf ) {
			LogHandler::Log( __METHOD__, 'DEBUG', 'order:' . $order . '  PDF:' . $pdf['pdfFile'] );

			if ( $startpagenr === 0 ) {
				$startpagenr = $pdf['pageNumber'];
			}

			$endpagenr = $pdf['pageNumber'];

			$pdfLib->AliasNbPages();
			$pdfLib->pageNumbers[] = $pdf['pageNumber'];

			// set the sourcefile
			$pageCount = $pdfLib->setSourceFile( $pdf['pdfFile'] );
			for ( $pageNo = 1; $pageNo <= $pageCount; $pageNo++ ) {
				$tplIdx = $pdfLib->importPage( $pageNo );
				$s = $pdfLib->getTemplateSize( $tplIdx );
				$pdfLib->AddPage( $s['w'] > $s['h'] ? 'L' : 'P', array( $s['w'], $s['h'] ) );
				$pdfLib->useTemplate( $tplIdx );
				LogHandler::Log( __METHOD__, 'DEBUG', 'Page added:' . $pageNo );

				//draw page number on the page
				//$pdfLib->SetFont( 'helvetica', '', 18 );
				//$pdfLib->SetXY( 2, 6 ); //mm
				//$pdfLib->Write( 0, 'P:' . $pdf['pageNumber'] );
			}
		}

		//$pagenumprefix = 'p' . $startpagenr . '-' . $endpagenr . '_';


		LogHandler::Log( __METHOD__, 'DEBUG', 'Writting combined PDF to:' . $pdfName );
		$pdfLib->Output( $pdfName, 'F' );
		return $pdfName;
	}

	public function cleanPDFpages( $pdfPages ) {
		LogHandler::Log( __METHOD__, 'DEBUG', 'removing PDF pages' );
		foreach ( $pdfPages as $order => $pdf ) {
			if ( file_exists( $pdf['pdfFile'] ) ) {
				LogHandler::Log( __METHOD__, 'DEBUG', 'removing :' . $pdf['pdfFile'] );
				unlink( $pdf['pdfFile'] );
			}
		}
	}

	public function getContentType( $filename ) {
		// get file extention
		$ext = pathinfo( $filename, PATHINFO_EXTENSION );
		LogHandler::Log( __METHOD__, 'DEBUG', 'Ext is : ' . $ext );
		$contentType = '';
		switch ( $ext ) {
			case 'jpg' :
				$contentType = 'image/jpeg';
				break;
			case 'pdf' :
				$contentType = 'application/pdf';
				break;
			case 'zip' :
				$contentType = 'application/octet-stream';
				break;
		}

		LogHandler::Log( __METHOD__, 'DEBUG', 'FileType is : ' . $contentType );
		return $contentType;
	}
}
