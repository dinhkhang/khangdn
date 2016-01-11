<?php

class Visitor extends AppModel {

    public $useTable = 'visitors';
    public $customSchema = array(
        'id' => '',
        'username' => '',
        'password' => '',
        'first_name' => '',
        'middle_name' => '',
        'last_name' => '',
        'name' => '',
        'gender' => '',
        'time_zone' => '',
        'email' => '',
        'mobile' => '',
        'token' => '',
        'session_id' => '',
        'avatar_url' => '',
        'loc' => array(
            'type' => '',
            'coordinates' => '',
        ),
        'identities' => '',
        'files' => array(
            'avatar' => '',
        ),
        'file_uris' => array(
            'avatar' => '',
        ),
        'bookmarks' => '',
        'favorites' => '',
        'search_history' => '',
        'settings' => '',
        'shipping' => '',
        'status' => '',
        'order' => '',
        'user' => '',
        'created' => '',
        'modified' => '',
    );

    public function getInfoByMobile($mobile) {

        $get_info = $this->find('first', array(
            'conditions' => array(
                'mobile' => $mobile,
            ),
        ));

        return !empty($get_info) ? $get_info : array();
    }

    public function getInfoByUsername($username) {

        $get_info = $this->find('first', array(
            'conditions' => array(
                'username' => $username,
            ),
        ));

        return !empty($get_info) ? $get_info : array();
    }

    public function addNew($mobile) {

        $get_info = $this->find('first', array(
            'conditions' => array(
                'mobile' => $mobile,
            ),
        ));

        if (!empty($get_info)) {

            return $get_info;
        }

        $this->create();
        $this->save(array(
            'username' => $mobile,
            'mobile' => $mobile,
            'status' => 2,
        ));

        return $this->find('first', array(
                    'conditions' => array(
                        'id' => $this->getLastInsertID(),
                    ),
        ));
    }

    public function beforeSave($options = array()) {
        parent::beforeSave($options);

        if (!empty($this->data[$this->alias]['password'])) {

            $this->data[$this->alias]['password'] = Security::hash($this->data[$this->alias]['password'], null, true);
        }
    }

}
