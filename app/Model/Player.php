<?php

App::uses('AppModel', 'Model');

class Player extends AppModel {

    public $useTable = 'players';

    public function getInfoByMobile($mobile) {

        $get_info = $this->find('first', array(
            'conditions' => array(
                'phone' => $mobile,
            ),
        ));

        return $get_info;
    }

    public function isRegisterPackage($mobile) {

        $check = $this->find('first', array(
            'conditions' => array(
                'phone' => $mobile,
                '$or' => array(
                    array(
                        'package_day.status' => 1,
                    ),
                    array(
                        'package_week.status' => 1,
                    ),
                    array(
                        'package_month.status' => 1,
                    ),
                ),
            ),
        ));

        return !empty($check) ? $check : false;
    }

    public function beforeSave($options = array()) {
        parent::beforeSave($options);

        if (!empty($this->data[$this->alias]['password'])) {

            $this->data[$this->alias]['password'] = Security::hash($this->data[$this->alias]['password'], null, true);
        }
    }

    //TungPT
    public function getPlayerInfo($phone_num) {
        $playerInfo = $this->find('first', array(
            'conditions' => array(
                'phone' => $phone_num,
            ),
        ));

        if (empty($playerInfo)) {
            return;
        }

        $res = array(
            'package' => array(),
        );
        if (empty($playerInfo['Player']['num_questions'])) {
            $res['num_questions'] = '';
        } else {
            $count = 0;
            foreach ($playerInfo['Player']['num_questions'] as $q => $cq) {
                $count += 1;
            }
            $res['num_questions'] = $count;
        }
        if ($playerInfo['Player']['package_day']['status'] == 1) {
            array_push($res['package'], $playerInfo['Player']['package_day']);
        }
        if ($playerInfo['Player']['package_week']['status'] == 1) {
            array_push($res['package'], $playerInfo['Player']['package_week']);
        }
        return $res;
    }

    public function checkPlayer($phone_num) {
        $playerInfo = $this->find('first', array(
            'conditions' => array(
                'phone' => $phone_num,
            ),
        ));

        if (empty($playerInfo)) {
            return true;
        }

        if ($playerInfo['Player']['package_day']['status'] == 0 && $playerInfo['Player']['package_week']['status'] == 0) {
            return true;
        }
        return false;
    }

}
