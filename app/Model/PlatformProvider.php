<?php

class PlatformProvider extends AppModel {

        public $useTable = 'platform_providers';

        public function findListNameCode() {
                return $this->find('list', ['fields' => ['code', 'name']]);
        }

}
