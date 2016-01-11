<?php

App::uses('AppModel', 'Model');

class Mo extends AppModel {

    public $pattern = 'mo_%s';

    public function getInfoByTransId($trans_id) {

        return $this->find('first', array(
                    'conditions' => array(
                        'details.trans_id' => $trans_id,
                    ),
        ));
    }

    public function insert($arr_mo) {

        $model = new AppModel();
        $model->useTable = 'mo_' . date('Y_m_d');
        $model->save($arr_mo);
        return $model->getLastInsertID();
    }

    public function getInfoByAction($action, $phone) {

        return $this->find('first', array(
                    'conditions' => array(
                        'action' => $action,
                        'phone' => $phone,
                    ),
        ));
    }

    public function init($date = null) {

        if (empty($date)) {

            $date = date('Y-m-d H:i:s');
        }
        $this->useTable = sprintf($this->pattern, date('Y_m_d', strtotime($date)));
    }

}
