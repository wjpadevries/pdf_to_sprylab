# PurplePublish from ContentStation v11.x 	

Adding the publish action for PurplePublish to the Publication Overview in ContentStation 11 (html) is different then in CS9(flash)

in CS9 the menus actions could be added from the wwsettings.xml 

in CS11 some javascript is required

This readme concentrates on how to add the custom menu for publishing to PurplePublish.


## Installation

1. From the PurplePublish plugin folder, copy the CS11/PO_MenuActions.js to the Enterprise server:
``<enterprise>/contentstation/integrations``

2. Edit the ``config.js`` for CS11 which can be found on the server in '``<enterprise>/contentstation/``

In the config.js, there should be a section 'plugins' 
and in that section that should be a sub-section called 'publicationOverview'

		// Add custom actions to context menu in search results.
  		// See 'plugins.md' and 'contentstation-sdk.md' available in sdk folder for more information.
  		plugins: {
    		contentStation: [
      			'integrations/ObjectContextMenuActions.js',
   			],
    		digitalEditor: [
      			// 'sdk/samples/digital-editor-sdk-sample.js'
    		],
    		printEditor: [
      			// 'sdk/samples/print-editor-text-sdk-sample.js'
      			// 'sdk/samples/print-editor-ui-sdk-sample.js'
    		],
    		publicationOverview: [
      			'integrations/PO_MenuActions.js'
    		]
 	 	},
 
 3. In the publicationOverview section add the reference to the ``integrations/PO_MenuActions.js`` as can be seen in the code above.
 






### Generic information about Custom Publication Overview Menus

See the woodwing helpcenter article for more explanation on how to add custom menus to the PO.

[Publication-Overview-Add-Custom-Actions](https://helpcenter.woodwing.com/hc/en-us/articles/360000273523-Publication-Overview-Add-Custom-Actions)
