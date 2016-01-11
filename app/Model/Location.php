<?php

App::uses('AppModel', 'Model');
App::uses('Region', 'Model');

class Location extends AppModel {

        public $useTable = 'locations';

        public function getListLocationId() {
                return $this->find('list', array('fields' => array('id', 'name')));
        }

        public function getCountryRegion($locationId, Country $country, Region $region) {
                $location = $this->find('first', ['conditions' => ['id' => $locationId]]);
                $countryInfo = $country->find('first', ['fields' => ['id', 'name'], 'conditions' => ['code' => $location['Location']['country_code']]]);
                $regionInfo = $region->find('first', ['fields' => ['name'], 'conditions' => ['_id' => new MongoId($location['Location']['region'])]]);
                $return = [
                    'location' => [$location['Location']['id'] => $location['Location']['name']],
                    'country' => isset($countryInfo['Country']['id']) ? [$countryInfo['Country']['id'] => $countryInfo['Country']['name']] : [],
                    'region' => isset($regionInfo['Region']['id']) ? [$regionInfo['Region']['id'] => $regionInfo['Region']['name']] : [],
                ];
                return $return;
        }

        public function _validateLocation(&$data, $model) {
                if (!isset($data[$model]['location'])) {
                        return;
                }
                // check if id of location exists, return
                if ($this->find('first', ['conditions' => ['id' => new MongoId($data[$model]['location'])]])) {
                        $data[$model]['location'] = new MongoId($data[$model]['location']);
                        return;
                }
                // check no input location and has not region name in db
                $region = new Region();
                $regionName = $region->findNameById($data[$model]['location']);
                // find in table location has name of region, return, else duplication region into location, return
                if ($location = $this->find('first', ['conditions' => ['name' => $regionName]])) {
                        $data[$model]['location'] = new MongoId($location['Location']['id']);
                        return;
                } else {
                        $region = $region->find('first', ['conditions' => ['id' => new MongoId($data[$model]['location'])]]);
                        unset($region['Region']['id']);
                        $save['Location'] = $region['Region'];
                        $save['Location']['region'] = $data[$model]['location'];
                        $save['Location']['address'] = '';
                        $this->save($save);
                        $data[$model]['location'] = $this->getLastInsertID();
                }
        }

        public function beforeSave($options = array()) {
                parent::beforeSave($options);

                // thực hiện chuyển về đúng kiểu dữ liệu của trường field 
                if (isset($this->data[$this->alias]['country_id'])) {
                        App::uses('Country', 'Model');
                        $country = new Country();
                        $this->data[$this->alias]['country_code'] = $country->getCountryCodeByCountryId($this->data[$this->alias]['country_id']);
                        unset($this->data[$this->alias]['country_id']);
                }
        }

}
