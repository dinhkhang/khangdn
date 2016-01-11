<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
App::uses('AppModel', 'Model');

class GameRating extends AppModel {

    public function getWeek($year, $mon, $day) {
        $date_string = $year + "-" + $mon + "-" + $day;
        $date = new DateTime($date_string);
        return $date->format("W");
    }

    public function modifyData($listData, $mon_week, $limit, $page) {
        $res = array(
            'data' => array(
                'limit' => $limit,
                'arr_topPlayer' => array(),
            ),
        );
        if ((int) $page == 0) {
            $stt = (int) $page * (int) $limit;
        } else {
            $stt = ((int) $page - 1 ) * (int) $limit;
        }

        if ($mon_week == 0) {
            foreach ($listData as $l => $n) {
                $stt += 1;
                $res['data']['arr_topPlayer'][$l] = array(
                    'stt' => $stt,
                    'phone' => $this->replayPhone($n['GameRating']['phone']),
                    'score' => $n['GameRating']['week']['score'],
                    'time' => $this->convertTime($n['GameRating']['week']['time']),
                );
            }
        } elseif ($mon_week == 1) {
            foreach ($listData as $ld => $nd) {
                $stt += 1;
                $res['data']['arr_topPlayer'][$ld] = array(
                    'stt' => $stt,
                    'phone' => $this->replayPhone($nd['GameRating']['phone']),
                    'score' => $nd['GameRating']['month']['score'],
                    'time' => $this->convertTime($nd['GameRating']['month']['time']),
                );
            }
        } else {
            return;
        }
        if (empty($res['data']['arr_topPlayer'])) {
            return;
        }
        return $res;
    }

    public function getYear($currentYear) {
        $startYear = Configure::read('sysconfig.Rating.startYear');
        $res = array();
        for ($i = $startYear; $i <= $currentYear; $i++) {
            $res[$i] = $i;
        }
        return $res;
    }

    public function

    getMonthWeek() {
        $mon_week = Configure::read('sysconfig.Rating.mon_week');
        return $mon_week;
    }

    protected function convertTime($seconds) {

        $hours = floor($seconds / 3600);

        $minute = floor(($seconds - ($hours * 3600 ) ) / 60);

        $second = $seconds - ($hours * 3600 ) - ($minute * 60);

        if ($hours < 10) {
            $hours = '0' . $hours;
        }
        if ($minute < 10) {
            $minute = '0' . $minute;
        }
        if ($second < 10) {
            $second = '0' . $second;
        }

        $tring_time = $hours . ':' . $minute . ':' . $second;

        return $tring_time;
    }

    protected function

    replayPhone($phone) {
        $newPhone = str_replace(substr($phone, strlen($phone) - 3, strlen($phone) - 1), Configure::read('sysconfig.Rating.replacestr'), $phone);
        return $newPhone;
    }

}
