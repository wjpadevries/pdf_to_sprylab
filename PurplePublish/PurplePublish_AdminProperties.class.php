<?php
/****************************************************************************
   Copyright 2017 WoodWing Software BV

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
****************************************************************************/

// include enterprise config
require_once __DIR__ . '/config.php';
require_once BASEDIR . '/server/interfaces/plugins/connectors/AdminProperties_EnterpriseConnector.class.php';

class PurplePublish_AdminProperties extends AdminProperties_EnterpriseConnector
{
	/**
	 * On Server Plug-in initialization, this function is called by core server for each 
	 * supported admin entity. It allows the server plug-in to define all additional 
	 * custom admin properties for a given admin entity. After that, specified properties 
	 * will be created at DB (by the server). In other terms, the DB model for the admin
	 * entity gets extended with custom properties provided by this function. The returned
	 * collection should include hidden properties (not shown at dialogs) but should exclude
	 * the special dialog widget separators (shown at dialogs).
	 *
	 * Note: When making changes to the collection of properties, run the Server Plug-ins 
	 * page to reflect them to DB model!
	 *
	 * @param string $entity Admin object type: Publication, PubChannel or Issue
	 * @return DialogWidget[] List of property definitions to create in database.
	 */
	public function collectDialogWidgets( $entity )
	{
        return $this->doCollectDialogWidgets( $entity, 'update_dbmodel' );
	}

	/**
	 * Before a dialog is build, the core server calls this function to collect all possible
	 * widgets for a given context. No matter if some properties need to be hidden while others
	 * needs to be shown, this is the moment to return all widgets for a given context, 
	 * admin entity and action. In fact, these are the properties to travel along with an admin
	 * entity. This could be less properties than returned through the collectDialogWidgets() 
	 * function, for example when it is needed to extend Issue- or Publication Channel entities
	 * -only- for a certain Publication Channel Type or Publish System. But, do NOT return
	 * widgets that aren't returned through collectDialogWidgets() because they can not be stored
	 * in the DB which blocks them from traveling along and would lead into errors. The returned
	 * collection should include hidden properties (not shown at dialogs) but should exclude
	 * the special dialog widget separators (shown at dialogs).
	 *
	 * This function was added since 9.0.0. For backward compatibility reasons, it returns NULL
	 * which tells the core to call the collectDialogWidgets() instead. Obviously it is better
	 * to return properties, depending on the given context, which leads to much more efficient
	 * storage since only a subset of properties is stored in the DB per entity instance.
	 *
	 * @since 9.0.0
	 * @param AdminProperties_Context $context Publication, Issue, etc for which the properties are maintained
	 * @param string $entity Admin object type: Publication, PubChannel or Issue
	 * @param string $action User operation: Create or Update.
	 * @return DialogWidget[]|null List of property definitions. Return NULL to use collectDialogWidgets() instead.
	 */
	public function collectDialogWidgetsForContext( AdminProperties_Context $context, $entity, $action ) 
	{
        $context = $context; $action = $action; // keep analyzer happy
        return $this->doCollectDialogWidgets( $entity, 'extend_entity' );
	}

	/**
	 * When custom admin properties are about to get displayed at the maintenance pages, 
	 * this function is called by the core server. It allows the Server Plug-in to initialize
	 * or adjust properties ($showWidgets) before shown to admin user. Properties can be added,
	 * removed or re-ordered. Be careful: don't fail when expected props are suddenly not present.
	 * The collection of properties could be less than returned by collectDialogWidgetsForContext()
	 * in case some properties needs to be round-tripped with the admin entity, but should not
	 * be shown in the dialog. Those hidden properties could be used for internal usage of 
	 * the plug-in, just to track data that needs to be hidden from admin users. Another reason
	 * to hide properties is that during the Create action there is maybe nothing to fill-in yet,
	 * while for the Update action there is.
	 *
	 * @param AdminProperties_Context $context Publication, Issue, etc for which the properties are maintained
	 * @param string $entity Admin object type: Publication, PubChannel or Issue
	 * @param string $action User operation: Create or Update.
	 * @param DialogWidget[] $allWidgets Complete list all properties. Key = property name, Value = DialogWidget object.
	 * @param DialogWidget[] $showWidgets Properties that should be shown to admin user in current order. Key = sequential index, Value = DialogWidget object.
	 */
	public function buildDialogWidgets( AdminProperties_Context $context, $entity, $action, $allWidgets, &$showWidgets ) 
	{
        $action = $action; $allWidgets = $allWidgets; $context = $context; // keep code analyzer happy

        // This way you can grab contextual data:
        //$pubObj = $context->getPublication();
        //$channelObj = $context->getPubChannel();
        //$issueObj = $context->getIssue();

        // Add our custom props depending on the given admin entity.
        // Let's simply add our custom props at the end of all properties.
        $showWidgets += $this->doCollectDialogWidgets( $entity, 'draw_dialog' );
	}

	public function getPrio() { return self::PRIO_DEFAULT; }



    private function doCollectDialogWidgets( $entity, $mode )
    {
        $widgets = array();
        switch( $entity ) {

            case 'Issue':
                // Draw the SGP ID field.

                if( $mode == 'draw_dialog' ) { // Show separator on dialogs, but do not add it to the DB model.
                    $widgets['C_CUSTADMPROPDEMO_SEP1'] = new DialogWidget(
                        new PropertyInfo( 'C_CUSTADMPROPDEMO_SEP1', 'Sprylab - PurplePublish', null, 'separator' ),
                        new PropertyUsage( 'C_CUSTADMPROPDEMO_SEP1', true, false, false, false ) );
                }
                $widgets['C_PURPLEHISTORY'] = new DialogWidget(
                    new PropertyInfo( ENT_ISSUE_HISTORY_FIELD, ENT_ISSUE_HISTORY_LABEL, null, 'multiline'),
                    new PropertyUsage(ENT_ISSUE_HISTORY_FIELD, true));
                $widgets['C_PURPLEISSUEVERSION'] = new DialogWidget(
                    new PropertyInfo( ENT_ISSUE_NR_FIELD, ENT_ISSUE_NR_LABEL, null, 'string'),
                    new PropertyUsage(ENT_ISSUE_NR_FIELD, true));
                break;
        }
        return $widgets;

    }


}
