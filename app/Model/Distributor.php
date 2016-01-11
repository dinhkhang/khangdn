<?php

App::uses('AppModel', 'Model');

class Distributor extends AppModel {

    public $useTable = 'distributors';

    public function findListNameCode() {
        return $this->find('list', ['fields' => ['code', 'name']]);
    }

    public function get_sharing_by_code($code = '') {
        $option = array(
            'conditions' => array(
                'code' => $code,
            )
        );
        $record = $this->find('first', $option);

        if (!empty($record)) {
            $detail = $record[$this->alias];
            return $detail['sharing'];
        } else {
            return null;
        }
    }

    public function findByCode($code) {

        return $this->find('first', array(
                    'conditions' => array(
                        'code' => $code,
                    ),
        ));
    }

}
