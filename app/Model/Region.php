<?php

App::uses('AppModel', 'Model');

class Region extends AppModel {

        public $useTable = 'regions';

        public function findListName() {
                return $this->find('list', ['fields' => ['name']]);
        }

        public function findNameById($id) {
                $result = $this->find('first', ['fields' => ['name'], 'conditions' => ['id' => $id]]);
                return isset($result['Region']['name']) ? $result['Region']['name'] : '';
        }

        public function searchByName($region) {
                $return = $optionsRegion = [];
                $optionsRegion['conditions']['name']['$regex'] = new MongoRegex("/" . mb_strtolower($region) . "/i");
                $result = $this->find('list', $optionsRegion);
                foreach ($result AS $key => $item) {
                        $return[] = new MongoId($key);
                        unset($item);
                }
                return $return;
        }

}
