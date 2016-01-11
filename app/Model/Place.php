<?php

App::uses('AppModel', 'Model');

class Place extends AppModel {

        public $useTable = 'places';

        public function findListName() {
                return $this->find('list', ['fields' => ['name']]);
        }

}
