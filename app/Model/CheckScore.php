<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
App::uses('AppModel', 'Model');

class CheckScore extends AppModel {

    public function getWeek($year, $mon, $day) {
        $date_string = $year . "-" . $mon . "-" . $day;
        $date = new DateTime($date_string);
        return ((int) $date->format("W"));
    }

    public function getListPointByDay($year, $mon_week, $num_count, $phone_num) {
        $model = new AppModel();
        if (empty($year) || $year == 0) {
            $model->useTable = 'score_day_2015';
        } else {
            $model->useTable = 'score_day_' . $year;
        }

        $option['order'] = array(
            'day.date' => 'DESC',
        );
        $option['conditions']['phone'] = $phone_num;

        if ($mon_week == 0) {
            $start_end_day = $this->getStartAndEndDate($year, $num_count);
            $option['conditions']['day.date'] = array(
                '$gte' => new MongoDate(strtotime($start_end_day[0])),
                '$lte' => new MongoDate(strtotime($start_end_day[1])),
            );
        } elseif ($mon_week == 1) {
            $start_end_day_mon = $this->getStartAndEndDateMonth($year, $num_count);
            $option['conditions']['day.date'] = array(
                '$gte' => new MongoDate(strtotime($start_end_day_mon[0])),
                '$lte' => new MongoDate(strtotime($start_end_day_mon[1])),
            );
        } else {
            return;
        }
        $res = array(
            'data' => array(
                'listPointByDay' => array(
                ),
            ),
        );

        $stt = 1;
        $listplay = $model->find('all', $option);
        if (empty($listplay)) {
            return;
        }

        $total_score = 0;
        foreach ($listplay as $p => $np) {
            if (!empty($np)) {
                $res['data']['listPointByDay'][$p] = array(
                    'stt' => $stt,
                    'day' => date('d/m/Y', $np['AppModel']['day']['date']->sec),
                    'score' => $np['AppModel']['day']['score'],
                );
                $stt += 1;
                $total_score = $total_score + $np['AppModel']['day']['score'];
            }
        }
        $res['data']['total_score'] = $total_score;

        return $res;
    }

    public function getYear($currentYear) {
        $startYear = Configure::read('sysconfig.Rating.startYear');
        $res = array(
        );
        for ($i = $startYear; $i <= $currentYear; $i++) {
            $res[$i] = $i;
        }
        return $res;
    }

    public function getMonthWeek() {
        $mon_week = Configure::read('sysconfig.Rating.mon_week');
        return $mon_week;
    }

    public function getNumOfMW($mon_week) {
        $num = array();
        if ($mon_week == 0) {
            $total = 52;
        } elseif ($mon_week == 1) {
            $total = 12;
        }

        for ($i = 1; $i <= $total; $i++) {
            $num[$i] = $i;
        }
        return $num;
    }

    function getStartAndEndDate($year, $week) {
        $return[0] = date('Y-m-d', strtotime($year . 'W' . $week));
        $return[1] = date('Y-m-d', strtotime('+6 days', strtotime($return[0])));

        $return[0] = $return[0] . ' 00:00:00';
        $return[1] = $return[1] . ' 23:59:59';
        return $return;
    }

    function getStartAndEndDateMonth($year, $month) {
        $now_day = $year . "-" . $month;
        $last_day = date("t", strtotime($now_day));

        $string_day[0] = $year . '-' . $month . '-1 00:00:00';
        $string_day[1] = $year . '-' . $month . '-' . $last_day . ' 23:59:59';

        return $string_day;
    }

}
