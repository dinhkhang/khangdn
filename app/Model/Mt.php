<?php

App::uses('AppModel', 'Model');

class Mt extends AppModel {

    public $pattern = 'mt_%s';

    public function insert($arr_mt) {

        $model = new AppModel();
        $model->useTable = 'mt_' . date('Y_m_d');
        $model->save($arr_mt);
        return $model->getLastInsertID();
    }

    public function init($date = null) {

        if (empty($date)) {

            $date = date('Y-m-d H:i:s');
        }
        $this->useTable = sprintf($this->pattern, date('Y_m_d', strtotime($date)));
    }

}
