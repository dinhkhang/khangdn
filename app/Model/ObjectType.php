<?php

App::uses('AppModel', 'Model');

class ObjectType extends AppModel {

    public $useTable = 'object_types';

    public function getCodeById($id) {

        $get_code = $this->find('first', array(
            'conditions' => array(
                'id' => new MongoId($id),
            ),
        ));

        return !empty($get_code) ? $get_code[$this->alias]['code'] : null;
    }

    public function getInfoByCode($code) {

        $get_info = $this->find('first', array(
            'conditions' => array(
                'code' => array(
                    '$regex' => new MongoRegex("/^" . mb_strtolower($code) . "$/i"),
                ),
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
            ),
        ));

        return !empty($get_info) ? $get_info[$this->alias] : array();
    }

}
