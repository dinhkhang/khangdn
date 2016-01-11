<?php

class TagClientVersion extends AppModel {

        public $useTable = 'tag_client_versions';

        public $customSchema = array(
            'id' => '',
            'version' => '',
            'count' => '',
        );
}
