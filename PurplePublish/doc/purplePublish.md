# PurplePublish

This PurplePublish plugin will publish the pages of the selected Issue to the corresponding Issue in Sprylab, PurlePublish.

### Discription of actions
The process starts from ContentStation publication overview where a new button is added. This button will start the publish process. 

Depending on configuration, all layouts of the issue, or only layouts in a certain state, will be selected for the publish action.

The selected layouts will be send to InDesignServer. IndesignServer will create the PDF pages using the specified profile and save the pages to disk.

Then the PDF pages will be combined to one large PDF that will be send to Sprylab.

## Configuration

Several configuration steps needs to be taken to make this plugin work.

##### prerequisites
- It's assumed that InDesignServer configuration is done and that IDS is working for normal Enterprise jobs.
- it's assumed that basic knowledge of Enterprise, ContentStation, InDesign and SmartConnection is available.

### 1.Install the plugin
Copy the zipfile to ``<enterprise>/config/plugins``

Unzip the zipfile, a folder called PurplePublish should now have been created.

Make sure the folder has got read rights for the webserver process.

##### Enable the plugin in Enterprise
Go to the Enterprise admin interface, and load the server-Plugins page. 
In the list, look for the Purple Publish line.
Click on the red-plug to enable the plugin.

### 2.Create PurpleManager account
Login to th purpleManager production or staging site and create an account:

production: ``https://purplemanager.com/purple-manager-backend``
staging: 
``https://staging.purplemanager.com/purple-manager-backend``


Open the purplePublish/config.php file and take care you set the correct URL (staging/production) and username/password in these defines:

``PURPLE_SERVER_URL`` the URL

``PURPLE_USER`` the username you choose
 
``PURPLE_PSWD`` the password you choose



### 3.Add PurplePublish button to ContentStation v9

Please note: This works currently only with ContentStation air (v9)	

open wwsettings.xml on your local machine/client.

look for section 'PublicationOverviewActions'

add : 

``<PublicationOverviewAction tooltip="PurplePublish"  icon="{SERVER_URL}config/plugins/PurplePublish/images/purple-logo.png" url="{SERVER_URL}config/plugins/PurplePublish/purplePublish.php?ticket={SESSION_ID}&amp;brand={BRAND_ID}&amp;issue={ISSUE_ID}&amp;edition={EDITION_ID}&amp;category={CATEGORY_ID}&amp;status={STATUS_ID}" displayMode="external"/>``

Save the file and restart ContentStation.

Now login to ContentStation, open the Publication-Overview and check if you see the small PurlePublish Icon on the top-bar.



### 4.Create PDF profile

To have control about the PDF being created by InDesignServer it is usefull to create a specific PDF profile.

A PDF profile needs to be created on InDesign and needs to be copied to the machine where InDesignServer is running.

**TIP: For quick testing, you can use one of the default PDF profiles, for example '[High Quality Print]'**

[to be addded later or refer to helpcenter]

[Indesign Secrets: Customizing PDF Presets](https://indesignsecrets.com/customizing-pdf-presets.php)

### 5. Install support scripts for InDesignServer
To allow to use JSON data packages, we need to install a JSON library on  InDesignServer.

In the plugin folder, there is a folder called 'IDS'. 
Copy the 'woodwing' folder to your InDesignServer xxx/scripts folder.

##### List PDF profiles
It can be usefull to install a script that will list the installed PDF profiles on the moment IDS starts. This will help to determine if the required PDF profiles can be found by IDS.

Copy the PurplePublish/IDS/startup scripts/listPDFprofiles.js to your IDS folder InDesignServer xxx/scripts/startup scripts


##Testing

To be able to test the whole flow you need to take the following steps:

1. create a new issue in the brand of your choice (WW News)
2. in the issue, create 2 layouts, give the layouts a couple of pages (layout1: 4 pages, layout2: 5 pages) make sure the pagenumbering is correct.
3. open the publication overview, select the correct brand and issue, this should show your pages of the layouts your created.
4. Assuming there is no status defined to filter which layouts should be send to Sprylab. If there is a status defined, take care to have at least one layout in that status.
5. Press the 'PurblePublish' button and watch the magic happen....


### What should happen is the following:
1. A browser tab should appear with a progress bar, showing the process going on.
2.  Watch the console of IDS, in a short while after pressing the button you should see IDS opening the layouts and writing the PDF's. Keep an eye on possible errors for the first attempts of running this.
3. After IDS is finished, the progress bars will continue, first with combining the PDF pages to one PDF then with uploading to Sprylab.

It will be usefull to switch on debug logging for Enterprise as this will show the steps bing taken.






       
       
       
# ToDo
       
       - keep track of publish history in issue metadata
       - which layouts where published and which pages
       - make tool to view publish history