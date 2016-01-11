<?php

class Score extends AppModel {

    public function insert($arr_score) {

        $model = new AppModel();
        $model->useTable = 'score_' . date('Y');
        $model->save($arr_score);
        return $model->getLastInsertID();
    }

    public function insert_his($arr_score) {

        $model = new AppModel();
        $model->useTable = 'score_' . date('Y_m_d');
        $model->save($arr_score);
        return $model->getLastInsertID();
    }

    public function checkExistScoreWeek($phone) {
        $time = getdate();
        $year = $time['year'];
        $month = $time['mon'];
        $day = $time['mday'];

        $model = new AppModel();
        $model->useTable = 'score_' . $year;

        $curWeek = $this->getWeek($year, $month, $day);

        $option = array(
            'conditions' => array(
                'phone' => $phone,
                'week.index' => $curWeek,
            ),
        );

        $score_data = $model->find('first', $option);

        return $score_data;
    }

    public function checkExistScoreMon($phone) {
        $time = getdate();
        $year = $time['year'];

        $model = new AppModel();
        $model->useTable = 'score_' . $year;

        $month = $time['mon'];

        $option = array(
            'conditions' => array(
                'phone' => $phone,
                'month.index' => $month, 
            ),
        );

        $score_data = $model->find('first', $option);

        return $score_data;
    }

    public function getWeek($year, $mon, $day) {
        $date_string = $year . "-" . $mon . "-" . $day;
        $date = new DateTime($date_string);
        return ((int) $date->format("W"));
    }

}
