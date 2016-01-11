<?php

class PlaceActivity extends AppModel {

        public $useTable = 'place_activities';
//        public $actsAs = array('ContentProviderPerm');

        public function findListName() {
                return $this->find('list', ['fields' => ['name']]);
        }

}
