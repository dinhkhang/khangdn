<?php

App::uses('AppModel', 'Model');

class ChargeCrontabLog extends AppModel {

    public $useTable = 'charge_crontab_logs';

    public function isFirst($datetime) {

        $date = date('Ymd', strtotime($datetime));
        $check = $this->find('first', array(
            'conditions' => array(
                'date' => $date,
            ),
        ));

        if (empty($check)) {

            return true;
        }

        return false;
    }

    public function logTracking($datetime) {

        $date = date('Ymd', strtotime($datetime));
        $save_data = array(
            'date' => $date,
        );
        $this->create();
        return $this->save($save_data);
    }

}
