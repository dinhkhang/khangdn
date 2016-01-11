<?php

class VisitorHistoryLocation extends AppModel {

    public $useTable = 'visitor_history_locations';
    public $customSchema = array(
        'id' => '',
        'visitor' => '',
        'loc' => '',
        'created' => '',
        'modified' => '',
    );

}
