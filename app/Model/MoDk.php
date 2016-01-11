<?php

App::uses('AppModel', 'Model');

class MoDk extends AppModel {

    public function insert($arr_mo_dks) {

        $model = new AppModel();
        $model->useTable = 'mo_dks';
        $model->save($arr_mo_dks);
        return $model->getLastInsertID();
    }

    public function findByMoId($mo_id) {

        return $this->find('first', array(
                    'conditions' => array(
                        'mo_id' => new MongoId($mo_id),
                    ),
        ));
    }

}
