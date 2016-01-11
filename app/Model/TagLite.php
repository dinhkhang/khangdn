<?php

class TagLite extends AppModel {

        public $useDbConfig = 'lite';
        public $useTable = 'tags';
        public $primaryKey = 'name';

        public function beforeSave($options = array()) {
                parent::beforeSave($options);
                if($this->find('count', array('conditions' => array('name' => $this->data['TagLite']['name'])))) {
                        return false;
                }
        }
}
