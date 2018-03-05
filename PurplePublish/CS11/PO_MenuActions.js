(function(){

/*
   This script will help to convert old-style (CS9 and older) ContentStation <PublicationOverviewActions> 
   to CS10 Publiction-Overview actions. These actions can be found under the meny (three dots)
   
    The old defintion for CS9 is located in wwsettings.xml, in the <PublicationOverviewActions> section.
   the lines from that section can be converted to this defintion by making some small changes.
   
   a wwsetting line looks like:
  	<PublicationOverviewAction tooltip="PurplePublish"  icon="{SERVER_URL}config/plugins/PurplePublish/images/purple-logo.png" url="{SERVER_URL}config/plugins/PurplePublish/purplePublish.php?ticket={SESSION_ID}&brand={BRAND_ID}&issue={ISSUE_ID}&edition={EDITION_ID}&category={CATEGORY_ID}&status={STATUS_ID}" displayMode="external"/>
        	    
   replace:  '<PublicationOverviewAction' with '{'
   replace:  ending '/>' with '}'
   replace:  'tooltip=' with 'label:'
   replace: the '=' behind each field with a ':'
   add a comma between each field
   replace &amp; with &	
   
   { label:"printObjectAsHTML", icon: {SERVER_URL}config/plugins/PurplePublish/images/purple-logo.png", url:"{SERVER_URL}config/CS-scripts/printObjectAsHTML.php?ticket={SESSION_ID}&ids={OBJECT_IDS}", objtypes:"Layout" displayMode:"external" }
	

   the actionlist below needs to contain objects with the structure
   { label: '' , icon: '', url:'', displayMode:'', subMenu: ''}
   
   where 
   - label -> the text in the Menu
   - icon  -> the icon that will show in front of the label
   - url   -> the path to the server call. 
   - displayMode -> 'external' open in new window, 
   					'internal' open in same window, 
   					'dialog' open in popup
   					'info'   show simple dialog with close
   					'silent' will not show any result
   					html pages using POST/GET will need to use 'external'
   					html/javascript pages that use ajax can use internal or dialog
   - subMenu	 -> if set, this action will be under the specified submenu
   - userGroups  -> (comma seperated list of matching Enterprise userGroups) if set and if user is member of the listed groups,
   					then the menu will be active.
   
  
   the URL defined in the actionList can contain placeholders
    - {SERVER_URL}	will be replaced with the serverURL of the Enterprise Server  
  	- {SESSION_ID}	will be replaced with the active ticket
  	- {BRAND_ID} 	will be replaced with the filter value of the brand
  	- {ISSUE_ID}	will be replaced with the filter value of the issue
  	- {EDITION_ID}	will be replaced with the filter value of the edition
  	- {CATEGORY_ID}	will be replaced with the filter value of the category
  	- {STATUS_ID}	will be replaced with the filter value of the status
   	
 */
 

 var po_menuList = [
 					  {	 
 					    label:	"PurplePublish" , 
 						icon:   "{SERVER_URL}config/plugins/PurplePublish/images/purple-logo.png", 
  					  	url:	"{SERVER_URL}config/plugins/PurplePublish/purplePublish.php?ticket={SESSION_ID}&brand={BRAND_ID}&issue={ISSUE_ID}&edition={EDITION_ID}&category={CATEGORY_ID}&status={STATUS_ID}", 
  					  	displayMode: "dialog",
  					  	subMenu: "",
  					  	userGroups: "purple", // only when user belongs to the listed UG the menu action will be valid
  					  },
  					  
        	
 					];
 
 
 	// draw a seperator line
  PoUiSdk.createAction({
  		forceSeparator: true
  	
  });
	  		
  
  
  // loop trough the actions and create the menu entries and action events	 
  // 	
  var subMenus = {};
  
  // get the array of userGroups this user belongs to
  // this will be used later to determine if a menu action needs to be active or not
  var ugOfUser = userGroupsOfUser();
  
  po_menuList.forEach ( function( call) 
  {
   	  var showMenu = true;
   	  console.log('-- initialize action [' +  call.label + ']' );
   	  
      if ( typeof (call.userGroups) != 'undefined' &&
				   call.userGroups  != '' )
	  {
			console.log( '  Found UserGroups info for menu  [' + call.userGroups + ']');
			var menuGroups  = call.userGroups.split(',');
			showMenu = false;
			// loop to the active groups
			ugOfUser.forEach( function( menuGrp ) 
							{
								console.log( '  matching [' + menuGrp + ']');
								if ( $.inArray( menuGrp, menuGroups ) != -1 )
								{
									// we found one
									showMenu = true;
								}
							});
			console.log( '  showMenu:' + showMenu );
       }else{
		  console.log( '  no userGroup defined, menu will be added');
	   }			
	 		 
     
       var thisAction = {
       
		label:  call.label,
		icon : replaceVariables ( call.icon ),
		click: function(){
		  var executeURL = replaceVariables ( call.url );	
		  //var executeURL =  call.url ;	
		  switch( call.displayMode ) {
		  	case 'dialog' : 
		  			openInDialog( call.label, executeURL );
		  			break;
		    case 'info' :
		    		showInfo( call.label, executeURL );
		  			break;	
		  	case 'silent'   :	
		  			runSilent( call.label, executeURL );
		  			break;		
		  	case 'external'	:
		  			window.open(executeURL, '_blank');
		  			break;
		  	case 'internal' :
		  			window.open(executeURL, '_self');
		  			break;
		  }
		}
	  };


	  	
	  thisAction.disabled = ! showMenu;
	  // other properties of an action
	  // thisAction.visible = true/false
	 
	  
	  // if the subMenu key is there and not empty
	  if ( typeof (call.subMenu ) != 'undefined' &&
		   call.subMenu != '' )
	  {
		// then add to the action to list on the subMenu key
		if (typeof( subMenus[ call.subMenu ] ) == 'undefined' ) { subMenus[ call.subMenu ] = []; }
		subMenus[ call.subMenu ].push( thisAction ) ; 
	  }
	  else{
		  // no submenu, just create the action	
		  PoUiSdk.createAction( thisAction );
	  }	  	
      
  });
 
 
  
  // create the subMenus (if any) from the subMenus array
  // remember that the array value is a complete onAction function
  jQuery.each ( subMenus, 
  				function ( label, subMenuEntries )
  				{
  					var menuId = PoUiSdk.createAction({
      						label: label
    				});
  				
  					// now loop the subMenuEntries
  					jQuery.each ( subMenuEntries,
  								  function ( index, subMenu )
  								  {
  									PoUiSdk.createSubAction(menuId,	subMenu );
   								  }	 
   								 );
					
	 			}
	 			);
  
 
  				  
  
  // draw a seperator line
  PoUiSdk.createAction({
		forceSeparator: true
  });	
 
 
  // ----------------------------------------
  // functions only below this line
  // ----------------------------------------
  
  /* -replaceVariables-
  
    the URL defined in the actionList can contain placeholders
    - {SERVER_URL}	will be replaced with the serverURL of the Enterprise Server  
  	- {SESSION_ID}	will be replaced with the active ticket
  	- {BRAND_ID} 	will be replaced with the filter value of the brand
  	- {ISSUE_ID}	will be replaced with the filter value of the issue
  	- {EDITION_ID}	will be replaced with the filter value of the edition
  	- {CATEGORY_ID}	will be replaced with the filter value of the category
  	- {STATUS_ID}	will be replaced with the filter value of the status
   */	
  function 	replaceVariables( url )
  {
  	 // PoUiSdk.currentSelectedPage()
  	 // replace {SERVER_URL}
  	 var info = ContentStationSdk.getInfo();
	 var serverURL =  info.ServerInfo.URL.replace('index.php','');
     url = url.replace('{SERVER_URL}',serverURL);
  
	 // replace {SESSION_ID}
  	 url = url.replace('{SESSION_ID}',info.Ticket);
  	 
  	 var filterValues = PoUiSdk.currentFilterSetting();
  	 url = url.replace('{BRAND_ID}',   filterValues.brandId);
  	 url = url.replace('{ISSUE_ID}',   filterValues.issueId);
  	 url = url.replace('{EDITION_ID}', filterValues.editionId);
  	 url = url.replace('{CATEGORY_ID}',filterValues.categoryId);
  	 url = url.replace('{STATUS_ID}',  filterValues.stateId);
    
     return url;
  }
	
	
  function userGroupsOfUser(){
  	 var info = ContentStationSdk.getInfo();
  	 //console.log(JSON.stringify(info.Membership, null, 2));
	 var Membership = [];
	 //convert to simple array!
	 info.Membership.forEach( function ( member )
	 	{
	 		Membership.push( member.Name );
	 	});
	 
	 return Membership;
  }	
	
	
  function openInDialog( label, url ){		
   	 var jqModalContent = $('<iframe frameborder="0" style="margin: 0; padding: 0; height: 500px; width: 100%"></iframe>');
    // Open modal dialog and keep the dialog id
    var dialogId = ContentStationSdk.openModalDialog({
      title: label,
      width: 1000,
      content: jqModalContent.attr('src', url ),
      contentNoPadding: true,
      buttons: [
        // Button defined as secondary with class 'pale'
        // Has no callback defined - will close the dialog.
        {
          label: 'Close',
          class: 'pale'
        },
        // Button defined as normal/primary
        // On click will close the dialog with cached dialog id, and open new tab with iframeSrc url
        {
          label: 'Open in new tab',
          callback: function(){
            PoUiSdk.closeModalDialog(dialogId);
            window.open(url);
          }
        }
      ]
    });	
  }  
 
 
  // show information in a smaller dialog  
  function showInfo( label, url ){		
   	 var jqModalContent = $('<iframe frameborder="0" style="margin: 0; padding: 0; height: 150px; width: 100%"></iframe>');
    // Open modal dialog and keep the dialog id
    var dialogId = ContentStationSdk.openModalDialog({
      title: label,
      width: 600,
      content: jqModalContent.attr('src', url ),
      contentNoPadding: true,
      buttons: [
        // Button defined as secondary with class 'pale'
        // Has no callback defined - will close the dialog.
        {
          label: 'Close',
          class: 'pale'
        },
        
      ]
    });	
  }  
  
  
  // we mis-use the dialog to load content, but close the dialog afterwards
  //
  function runSilent( label, url ){		
   	 var jqModalContent = $('<iframe frameborder="0" style="margin: 0; padding: 0; height: 150px; width: 100%"></iframe>');
    // Open modal dialog and keep the dialog id
    var dialogId = ContentStationSdk.openModalDialog({
      title: label,
      width: 600,
      content: jqModalContent.attr('src', url ),
      
    });	
    PoUiSdk.closeModalDialog(dialogId);
  }  
  
  
 
 })();  