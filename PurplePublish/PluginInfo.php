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

require_once BASEDIR.'/server/interfaces/plugins/EnterprisePlugin.class.php';

class PurplePublish_EnterprisePlugin extends EnterprisePlugin
{
	public function getPluginInfo()
	{ 
		require_once BASEDIR.'/server/interfaces/plugins/PluginInfoData.class.php';
		$info = new PluginInfoData(); 
		$info->DisplayName = 'Purple Publish';
		$info->Version     = '0.1'; // don't use PRODUCTVERSION
		$info->Description = 'Publish Issue PDF to Sprylab-PurplePublishing';
		$info->Copyright   = COPYRIGHT_WOODWING;
		return $info;
	}
	
	final public function getConnectorInterfaces() 
	{ 
		return array(

// adm services
			// 'AdmAddGroupsToUser_EnterpriseConnector',
			// 'AdmAddTemplateObjects_EnterpriseConnector',
			// 'AdmAddUsersToGroup_EnterpriseConnector',
			// 'AdmCopyIssues_EnterpriseConnector',
			// 'AdmCreateAccessProfiles_EnterpriseConnector',
			// 'AdmCreateAutocompleteTermEntities_EnterpriseConnector',
			// 'AdmCreateAutocompleteTerms_EnterpriseConnector',
			// 'AdmCreateEditions_EnterpriseConnector',
			// 'AdmCreateIssues_EnterpriseConnector',
			// 'AdmCreatePubChannels_EnterpriseConnector',
			// 'AdmCreatePublicationAdminAuthorizations_EnterpriseConnector',
			// 'AdmCreatePublications_EnterpriseConnector',
			// 'AdmCreateRoutings_EnterpriseConnector',
			// 'AdmCreateSections_EnterpriseConnector',
			// 'AdmCreateStatuses_EnterpriseConnector',
			// 'AdmCreateUserGroups_EnterpriseConnector',
			// 'AdmCreateUsers_EnterpriseConnector',
			// 'AdmCreateWorkflowUserGroupAuthorizations_EnterpriseConnector',
			// 'AdmDeleteAccessProfiles_EnterpriseConnector',
			// 'AdmDeleteAutocompleteTermEntities_EnterpriseConnector',
			// 'AdmDeleteAutocompleteTerms_EnterpriseConnector',
			// 'AdmDeleteEditions_EnterpriseConnector',
			// 'AdmDeleteIssues_EnterpriseConnector',
			// 'AdmDeletePubChannels_EnterpriseConnector',
			// 'AdmDeletePublicationAdminAuthorizations_EnterpriseConnector',
			// 'AdmDeletePublications_EnterpriseConnector',
			// 'AdmDeleteRoutings_EnterpriseConnector',
			// 'AdmDeleteSections_EnterpriseConnector',
			// 'AdmDeleteStatuses_EnterpriseConnector',
			// 'AdmDeleteUserGroups_EnterpriseConnector',
			// 'AdmDeleteUsers_EnterpriseConnector',
			// 'AdmDeleteWorkflowUserGroupAuthorizations_EnterpriseConnector',
			// 'AdmGetAccessProfiles_EnterpriseConnector',
			// 'AdmGetAutocompleteTermEntities_EnterpriseConnector',
			// 'AdmGetAutocompleteTerms_EnterpriseConnector',
			// 'AdmGetEditions_EnterpriseConnector',
			// 'AdmGetIssues_EnterpriseConnector',
			// 'AdmGetPubChannels_EnterpriseConnector',
			// 'AdmGetPublicationAdminAuthorizations_EnterpriseConnector',
			// 'AdmGetPublications_EnterpriseConnector',
			// 'AdmGetRoutings_EnterpriseConnector',
			// 'AdmGetSections_EnterpriseConnector',
			// 'AdmGetStatuses_EnterpriseConnector',
			// 'AdmGetTemplateObjects_EnterpriseConnector',
			// 'AdmGetUserGroups_EnterpriseConnector',
			// 'AdmGetUsers_EnterpriseConnector',
			// 'AdmGetWorkflowUserGroupAuthorizations_EnterpriseConnector',
			// 'AdmLogOff_EnterpriseConnector',
			// 'AdmLogOn_EnterpriseConnector',
			// 'AdmModifyAccessProfiles_EnterpriseConnector',
			// 'AdmModifyAutocompleteTermEntities_EnterpriseConnector',
			// 'AdmModifyAutocompleteTerms_EnterpriseConnector',
			// 'AdmModifyEditions_EnterpriseConnector',
			// 'AdmModifyIssues_EnterpriseConnector',
			// 'AdmModifyPubChannels_EnterpriseConnector',
			// 'AdmModifyPublications_EnterpriseConnector',
			// 'AdmModifyRoutings_EnterpriseConnector',
			// 'AdmModifySections_EnterpriseConnector',
			// 'AdmModifyStatuses_EnterpriseConnector',
			// 'AdmModifyUserGroups_EnterpriseConnector',
			// 'AdmModifyUsers_EnterpriseConnector',
			// 'AdmModifyWorkflowUserGroupAuthorizations_EnterpriseConnector',
			// 'AdmRemoveGroupsFromUser_EnterpriseConnector',
			// 'AdmRemoveTemplateObjects_EnterpriseConnector',
			// 'AdmRemoveUsersFromGroup_EnterpriseConnector',

// ads services
			// 'AdsCopyDatasource_EnterpriseConnector',
			// 'AdsCopyQuery_EnterpriseConnector',
			// 'AdsDeleteDatasource_EnterpriseConnector',
			// 'AdsDeletePublication_EnterpriseConnector',
			// 'AdsDeleteQuery_EnterpriseConnector',
			// 'AdsDeleteQueryField_EnterpriseConnector',
			// 'AdsGetDatasource_EnterpriseConnector',
			// 'AdsGetDatasourceInfo_EnterpriseConnector',
			// 'AdsGetDatasourceType_EnterpriseConnector',
			// 'AdsGetDatasourceTypes_EnterpriseConnector',
			// 'AdsGetPublications_EnterpriseConnector',
			// 'AdsGetQueries_EnterpriseConnector',
			// 'AdsGetQuery_EnterpriseConnector',
			// 'AdsGetQueryFields_EnterpriseConnector',
			// 'AdsGetSettings_EnterpriseConnector',
			// 'AdsGetSettingsDetails_EnterpriseConnector',
			// 'AdsNewDatasource_EnterpriseConnector',
			// 'AdsNewQuery_EnterpriseConnector',
			// 'AdsQueryDatasources_EnterpriseConnector',
			// 'AdsSaveDatasource_EnterpriseConnector',
			// 'AdsSavePublication_EnterpriseConnector',
			// 'AdsSaveQuery_EnterpriseConnector',
			// 'AdsSaveQueryField_EnterpriseConnector',
			// 'AdsSaveSetting_EnterpriseConnector',

// dat services
			// 'DatGetDatasource_EnterpriseConnector',
			// 'DatGetRecords_EnterpriseConnector',
			// 'DatGetUpdates_EnterpriseConnector',
			// 'DatHasUpdates_EnterpriseConnector',
			// 'DatOnSave_EnterpriseConnector',
			// 'DatQueryDatasources_EnterpriseConnector',
			// 'DatSetRecords_EnterpriseConnector',

// pln services
			// 'PlnCreateAdverts_EnterpriseConnector',
			// 'PlnCreateLayouts_EnterpriseConnector',
			// 'PlnDeleteAdverts_EnterpriseConnector',
			// 'PlnDeleteLayouts_EnterpriseConnector',
			// 'PlnLogOff_EnterpriseConnector',
			// 'PlnLogOn_EnterpriseConnector',
			// 'PlnModifyAdverts_EnterpriseConnector',
			// 'PlnModifyLayouts_EnterpriseConnector',

// pub services
			// 'PubAbortOperation_EnterpriseConnector',
			// 'PubGetDossierOrder_EnterpriseConnector',
			// 'PubGetDossierURL_EnterpriseConnector',
			// 'PubGetPublishInfo_EnterpriseConnector',
			// 'PubOperationProgress_EnterpriseConnector',
			// 'PubPreviewDossiers_EnterpriseConnector',
			// 'PubPublishDossiers_EnterpriseConnector',
			// 'PubSetPublishInfo_EnterpriseConnector',
			// 'PubUnPublishDossiers_EnterpriseConnector',
			// 'PubUpdateDossierOrder_EnterpriseConnector',
			// 'PubUpdateDossiers_EnterpriseConnector',

// sys services
			// 'SysGetSubApplications_EnterpriseConnector',

// wfl services
			// 'WflAddObjectLabels_EnterpriseConnector',
			// 'WflAutocomplete_EnterpriseConnector',
			// 'WflChangeOnlineStatus_EnterpriseConnector',
			// 'WflChangePassword_EnterpriseConnector',
			// 'WflCheckSpelling_EnterpriseConnector',
			// 'WflCheckSpellingAndSuggest_EnterpriseConnector',
			// 'WflCopyObject_EnterpriseConnector',
			// 'WflCreateArticleWorkspace_EnterpriseConnector',
			// 'WflCreateObjectLabels_EnterpriseConnector',
			// 'WflCreateObjectOperations_EnterpriseConnector',
			// 'WflCreateObjectRelations_EnterpriseConnector',
			// 'WflCreateObjects_EnterpriseConnector',
			// 'WflCreateObjectTargets_EnterpriseConnector',
			// 'WflDeleteArticleWorkspace_EnterpriseConnector',
			// 'WflDeleteObjectLabels_EnterpriseConnector',
			// 'WflDeleteObjectRelations_EnterpriseConnector',
			// 'WflDeleteObjects_EnterpriseConnector',
			// 'WflDeleteObjectTargets_EnterpriseConnector',
			// 'WflGetArticleFromWorkspace_EnterpriseConnector',
			// 'WflGetDialog2_EnterpriseConnector',
			// 'WflGetObjectRelations_EnterpriseConnector',
			// 'WflGetObjects_EnterpriseConnector',
			// 'WflGetPages_EnterpriseConnector',
			// 'WflGetPagesInfo_EnterpriseConnector',
			// 'WflGetServers_EnterpriseConnector',
			// 'WflGetStates_EnterpriseConnector',
			// 'WflGetSuggestions_EnterpriseConnector',
			// 'WflGetVersion_EnterpriseConnector',
			// 'WflInstantiateTemplate_EnterpriseConnector',
			// 'WflListArticleWorkspaces_EnterpriseConnector',
			// 'WflListVersions_EnterpriseConnector',
			// 'WflLockObjects_EnterpriseConnector',
			// 'WflLogOff_EnterpriseConnector',
			// 'WflLogOn_EnterpriseConnector',
			// 'WflMultiSetObjectProperties_EnterpriseConnector',
			// 'WflNamedQuery_EnterpriseConnector',
			// 'WflPreviewArticleAtWorkspace_EnterpriseConnector',
			// 'WflPreviewArticlesAtWorkspace_EnterpriseConnector',
			// 'WflQueryObjects_EnterpriseConnector',
			// 'WflRemoveObjectLabels_EnterpriseConnector',
			// 'WflRestoreObjects_EnterpriseConnector',
			// 'WflRestoreVersion_EnterpriseConnector',
			// 'WflSaveArticleInWorkspace_EnterpriseConnector',
			// 'WflSaveObjects_EnterpriseConnector',
			// 'WflSendMessages_EnterpriseConnector',
			// 'WflSendTo_EnterpriseConnector',
			// 'WflSendToNext_EnterpriseConnector',
			// 'WflSetObjectProperties_EnterpriseConnector',
			// 'WflSuggestions_EnterpriseConnector',
			// 'WflUnlockObjects_EnterpriseConnector',
			// 'WflUpdateObjectLabels_EnterpriseConnector',
			// 'WflUpdateObjectRelations_EnterpriseConnector',
			// 'WflUpdateObjectTargets_EnterpriseConnector',

// business connectors
			'AdminProperties_EnterpriseConnector',
			// 'AutocompleteProvider_EnterpriseConnector',
			// 'AutomatedPrintWorkflow_EnterpriseConnector',
			// 'ConfigFiles_EnterpriseConnector',
			// 'ContentSource_EnterpriseConnector',
			// 'CustomObjectMetaData_EnterpriseConnector',
			// 'DataSource_EnterpriseConnector',
			// 'FileStore_EnterpriseConnector',
			// 'ImageConverter_EnterpriseConnector',
			// 'InDesignServerJob_EnterpriseConnector',
			// 'IssueEvent_EnterpriseConnector',
			// 'MetaData_EnterpriseConnector',
			// 'NameValidation_EnterpriseConnector',
			// 'ObjectEvent_EnterpriseConnector',
			// 'Preview_EnterpriseConnector',
			// 'PubPublishing_EnterpriseConnector',
			// 'Search_EnterpriseConnector',
			// 'ServerJob_EnterpriseConnector',
			// 'Session_EnterpriseConnector',
			// 'Spelling_EnterpriseConnector',
			// 'SuggestionProvider_EnterpriseConnector',
			// 'Version_EnterpriseConnector',
			// 'WebApps_EnterpriseConnector',

		);
	}
}