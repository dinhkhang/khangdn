<?php

App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');

class Configuration extends AppModel {

    public $useTable = 'configurations';

    public function getConfigByShortCode($short_code) {

        $config = $this->find('first', array());

        return empty($config) ? null : $config['Configuration'];
    }

    public function getConfigByCode($code) {

        $config = $this->find('first', array(
            'conditions' => array(
                'code' => $code,
            ),
        ));

        return empty($config) ? null : $config['Configuration'];
    }

    public function getSmsByCmd($short_code, $cmd) {

        $config = $this->getConfigByShortCode($short_code);
        if (!empty($config['sms'])) {
            foreach ($config['sms'] as $sms) {
                if ($sms['code'] == $cmd) {
                    return $sms;
                }
            }
        }
        return null;
    }

    public function getSmsByCmdFromConfig($config, $cmd) {

        if (!empty($config['sms'])) {
            foreach ($config['sms'] as $sms) {
                if ($sms['code'] == $cmd) {
                    return $sms;
                }
            }
        }
        return null;
    }

    public function getArrMt($short_code) {

        $config = $this->getConfigByShortCode($short_code);

        if (!empty($config['mt'])) {
            return $config['mt'];
        }
        return null;
    }

    public function getArrMtFromConfig($config) {

        if (!empty($config['mt'])) {
            return $config['mt'];
        }
        return null;
    }

    public function getMtByCode($short_code, $code) {

        $config = $this->getConfigByShortCode($short_code);
        if (!empty($config['mt'])) {
            foreach ($config['mt'] as $mt) {
                if ($mt['code'] == $code) {
                    return $mt["msg"];
                }
            }
        }
        return "";
    }

    public function getMtByCodeFromArrMT($arr_mt, $code) {
        foreach ($arr_mt as $mt) {
            if ($mt['code'] == $code) {
                return $mt["msg"];
            }
        }
        return "";
    }

    //unv

    public function is_denied_sendmt($arr_mt, $code)
    {
        $off_schedule = array();
        foreach ($arr_mt as $mt) {
            if (isset($mt['off_schedule']) && $mt['code'] == $code) {
                $off_schedule = $mt["off_schedule"];
            }
        }

        if( count($off_schedule) > 0 ){
            foreach ($off_schedule as $item) {
                if($this->is_valid_off_time($item['start'],$item['end'])){
                    return true;
                }
            }
        }
        return false;
    }

    private function is_valid_off_time($start,$end){
        $current = time();
        if( $current >= strtotime($start) && $current <= strtotime($end) ){
            return true;
        }else return false;
    }
    //eunv

}
