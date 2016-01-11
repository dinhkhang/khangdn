<?php

App::uses('AppModel', 'Model');

class Charge extends AppModel {

    public $pattern = 'charge_%s';

    public function getInfoByTransId($trans_id) {

        return $this->find('first', array(
                    'conditions' => array(
                        'trans_id' => $trans_id,
                    ),
        ));
    }

    public function insert($arr_charge) {

        $model = new AppModel();
        $model->useTable = 'charge_' . date('Y_m_d');
        $model->save($arr_charge);
        return $model->getLastInsertID();
    }

    public function init($date = null) {

        if (empty($date)) {

            $date = date('Y-m-d H:i:s');
        }
        $this->useTable = sprintf($this->pattern, date('Y_m_d', strtotime($date)));
    }

}
