# PurplePublish

This PurplePublish plugin will publish the PDF pages of the selected Issue in ContentStation-PublicationOverview to the corrosponding Issue in Sprylab,PurlePublish



### Add PurplePublish button to ContentStation

open wwsettings.xml

look for section 'PublicationOverviewActions'

add : 

``<PublicationOverviewAction tooltip="PurplePublish"  icon="{SERVER_URL}config/plugins/PurplePublish/images/purple-logo.png" url="{SERVER_URL}config/plugins/PurplePublish/purplePublish.php?ticket={SESSION_ID}&amp;brand={BRAND_ID}&amp;issue={ISSUE_ID}&amp;edition={EDITION_ID}&amp;category={CATEGORY_ID}&amp;status={STATUS_ID}" displayMode="external"/>``





Flow

1) Issue select:
most easy to do this is to start from publication overview
       
       
2) Create PDF with correct profile, using IDS
for each layout in the issue, kick IDS to create PDF of each page to certain output folder.

3) Collect the PDF pages and combine to one combined PDF

4) Check or create issue on Purple

5) upload combined PDF to purple

       
       
       
# ToDo
       
       - keep track of publish history in issue metadata
       - which layouts, which pages
       - make tool to view publish history