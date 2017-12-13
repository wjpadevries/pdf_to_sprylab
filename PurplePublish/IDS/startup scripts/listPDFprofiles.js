// at startup, list the available PDF profiles

if (app.name == "Adobe InDesign Server") 
{


       for (i=app.pdfExportPresets.length-1;i>-1;i--) {
         	var myPdfExportPreset=app.pdfExportPresets[i] ;
	        app.consoleout( "Available (pdfExportPreset) :'" + myPdfExportPreset.name + "' " + myPdfExportPreset.fullName) ;
        } ;
        
}        