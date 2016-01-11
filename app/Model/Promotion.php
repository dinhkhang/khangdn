<?php

class Promotion extends AppModel {

        public $useTable = 'promotions';
//        public $actsAs = array('ContentProviderPerm');

        public function findListName() {
                return $this->find('list', ['fields' => ['name']]);
        }

        public $customSchema = array(
            'id' => '',
            'location' => array(
                '_id' => '',
                'country_code' => '',
                'region' => '',
                'object_type' => '',
            ),
            'loc' => array(
                'type' => '',
                'coordinates' => '',
            ),
            'name' => '',
            'source' => '',
            'start_date' => '',
            'end_date' => '',
            'short_description' => '',
            'description' => '',
            'categories' => '',
            'collections' => '',
            'object_icon' => '',
            'files' => array(
                'logo' => '',
            ),
            'file_uris' => array(
                'logo' => '',
            ),
            
            'status' => '',
            'order' => '',
            'lang_code' => '',
            'user' => '',
            'created' => '',
            'modified' => '',
        );

}
