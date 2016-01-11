<?php

class ScoreDay extends AppModel {

    public function insert($arr_score) {

        $model = new AppModel();
        $model->useTable = 'score_day_' . date('Y');
        $model->save($arr_score);
        return $model->getLastInsertID();
    }

    public function checkExistScoreDay($phone) { 

        $model = new AppModel();
        $model->useTable = 'score_day_' . date('Y');

        $startTime = date('Y-m-d') . ' 00:00:00';
        $endTime = date('Y-m-d') . ' 23:59:59';
        $option = [
            'conditions' => [
                'phone' => $phone,
                'created' => array(
                    '$gte' => new MongoDate(strtotime($startTime)),
                    '$lte' => new MongoDate(strtotime($endTime)),
                ),
            ]
        ]; 

        $score_day = $model->find('first', $option);
//        $this->logAnyFile($option, __CLASS__ . '_' . __FUNCTION__);
//        $this->logAnyFile($score_day, __CLASS__ . '_' . __FUNCTION__);
//        $this->logAnyFile("PLAYER:" . $score_day['AppModel']['id'], __CLASS__ . '_' . __FUNCTION__);
        return $score_day;
    }

}
