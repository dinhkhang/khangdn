<?php

App::uses('AppModel', 'Model');

class Counter extends AppModel {

    public $useTable = 'counters';

    public function getNextSequence($name) {

        $query = [
            'conditions' => ['id' => $name],
        ];

        $result = $this->find('first', $query);
        $seq = !empty($result['Counter']['seq']) ?
                $result['Counter']['seq'] : 0;

        $arr_counter = [
            'id' => $name,
            'seq' => $seq + 1,
        ];
        $this->save($arr_counter);

        return $seq;
    }

}
