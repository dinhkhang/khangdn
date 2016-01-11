<?php

class ServiceProvider extends AppModel {

        public $useTable = 'service_providers';

        public function findListNameCode() {
                return $this->find('list', ['fields' => ['code', 'name']]);
        }

}
