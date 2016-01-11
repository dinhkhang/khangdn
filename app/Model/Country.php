<?php

App::uses('AppModel', 'Model');

class Country extends AppModel {

        public $useTable = 'countries';

        public $validate = [
                'name' => [
                    'rule' => 'isUnique',
                    'message' => 'country_name_duplicate'
                ],
                'code' => [
                    'rule' => 'isUnique',
                    'message' => 'country_code_duplicate'
                ],
        ];

        public function getCountryCodeByCountryId($id) {
                $country = $this->find('first', ['conditions' => ['_id' => $id]]);
                return isset($country['Country']['code']) ? $country['Country']['code'] : NULL;
        }

        /**
         * get list country name + country code which have status equal two
         * @return array
         */
        public function getListCountryCode() {
                return $country = $this->find('list', ['conditions' => ['status' => 2], 'fields' => ['code', 'name']]);
        }

        public function getCountryIdByCountryCode($code) {
                $country = $this->find('first', ['conditions' => ['code' => $code]]);
                return isset($country['Country']['id']) ? $country['Country']['id'] : null;
        }

}
