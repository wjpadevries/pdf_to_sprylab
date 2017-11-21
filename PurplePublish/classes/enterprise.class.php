<?php 

class enterpriseUtils
{
    private $issue = null;
    private $_ticket = null;

    public function __construct( $ticket = null)
    {
        if($ticket){
            $this->_ticket = $ticket;
        }
    }


    /**
     * Load issue properties for the issue identified by the Issue Id
     *
     * @param int   $id     Issue Id
     * @return int | bool
     */
    public function getIssue( $issueID)
    {
        LogHandler::Log( 'enterprise.class', 'DEBUG', sprintf("Enter: %s", __METHOD__));
        require_once BASEDIR.'/server/interfaces/services/adm/AdmGetIssuesRequest.class.php';
        require_once BASEDIR.'/server/services/adm/AdmGetIssuesService.class.php';

        // we need a publication id to get the issue object
        require_once BASEDIR.'/server/dbclasses/DBIssue.class.php';
        $issueRow = DBIssue::getIssue( $issueID );
        LogHandler::Log( 'enterprise.class', 'DEBUG','row:' . print_r($issueRow,1));
        if ( !$issueRow )
        {
            LogHandler::Log( 'enterprise.class', 'DEBUG','IssueRow not found,return false');
            return false;
        }


        $this->brandId = (int) $issueRow['publication'];
        $this->channelId = (int) $issueRow['channelid'];

        // now get the issue
        $service = new AdmGetIssuesService();
        $request = new AdmGetIssuesRequest($this->_ticket, array(), $this->brandId, $this->channelId, array($issueID));
        $response = $service->execute($request);

        if ($response->Issues) {
            // since we're only asking for a single issue this is the only result and we are save to just
            // return the first array object
            $this->issue = $response->Issues[0];
            return (int) $this->issue->Id;
        }

        return false;
    }



    /**
     * Modify issue in Enterprise
     *
     * @return bool|int
     */
    public function saveIssue() {
        LogHandler::Log( 'enterprise.class  ', 'DEBUG', sprintf("Enter: %s", __METHOD__));

        require_once BASEDIR.'/server/services/adm/AdmModifyIssuesService.class.php';
        require_once BASEDIR.'/server/interfaces/services/adm/AdmModifyIssuesRequest.class.php';

        $service = new AdmModifyIssuesService();
        $request = new AdmModifyIssuesRequest($this->_ticket, array(), $this->brandId, $this->channelId, array($this->issue));
        $response = $service->execute($request);

        if (isset($response->Issues[0])) {
            $this->issue = $response->Issues[0];
            LogHandler::Log( 'enterprise.class  ', 'DEBUG','Return: issueID:' . $this->issue->Id );
            return (int) $this->issue->Id;
        }

        return false;
    }

    /**
     * Update or Create a custom metadata property with the given value
     *
     * @param string $property
     * @param string $value
     */
    public function setIssueCustomPropertyValue($property, $value) {
        LogHandler::Log( 'enterprise.class  ', 'DEBUG',sprintf("Enter: %s", __METHOD__));

        // first check if we have a custom property with the name already
        if ($this->issue->ExtraMetaData) {
            /** @var \AdmExtraMetaData $extraMetadata */
            foreach ($this->issue->ExtraMetaData as $extraMetadata) {
                if ($extraMetadata->Property == $property) {
                    $extraMetadata->Values = [$value];
                    return;
                }
            }
        }

        // if not found, add it
        $this->issue->ExtraMetaData[] = new \AdmExtraMetaData($property, [$value]);
    }

    public function getIssueCustomPropertyValue($property) {
        LogHandler::Log( 'enterprise.class  ', 'DEBUG',sprintf("Enter: %s", __METHOD__));

        // first check if we have a custom property with the name already
        if ($this->issue->ExtraMetaData) {
            /** @var \AdmExtraMetaData $extraMetadata */
            foreach ($this->issue->ExtraMetaData as $extraMetadata) {
                if ($extraMetadata->Property == $property) {
                    return $extraMetadata->Values;
                }
            }
        }

        return false;
    }


}
