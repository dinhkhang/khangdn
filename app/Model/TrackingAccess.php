<?php

class TrackingAccess extends AppModel {

    public $pattern = 'tracking_access_%s';

    public function insert($data) {

        $this->useTable = "tracking_access_" . date('Y_m_d');
        $this->save($data);
    }

    function getLastQuery() {

        $dbo = $this->getDatasource();
        $logs = $dbo->getLog();
        $lastLog = end($logs['log']);
        return $lastLog['query'];
    }

    public function init($date = null) {

        if (empty($date)) {

            $date = date('Y-m-d H:i:s');
        }
        $this->useTable = sprintf($this->pattern, date('Y_m_d', strtotime($date)));
    }

}
