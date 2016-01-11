<?php

/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
//App::uses('AppHelper', 'View');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class CommonHelper extends AppHelper {

    /**
     * Format date from MongoDate
     * @param MonggoDate $mongoDatetime
     * @return string
     */
    public function parseDate($mongoDatetime) {

        if ($mongoDatetime instanceof MongoDate) {

            return date("d-m-Y", $mongoDatetime->sec);
        }

        return date("d-m-Y", strtotime($mongoDatetime));
    }

    /**
     * Format time from MongoDate
     * @param MongoDate $mongoDatetime
     * @return string
     */
    public function parseTime($mongoDatetime) {

        if ($mongoDatetime instanceof MongoDate) {

            return date("H:i:s", $mongoDatetime->sec);
        }

        return date("H:i:s", strtotime($mongoDatetime));
    }

    /**
     * Format date from MongoDate
     * @param MonggoDate $mongoDatetime
     * @return string
     */
    public function parseDateTime($mongoDatetime) {

        if ($mongoDatetime instanceof MongoDate) {

            return date("d-m-Y H:i:s", $mongoDatetime->sec);
        }

        return date("d-m-Y H:i:s", strtotime($mongoDatetime));
    }

    /**
     * Format time from MongoId
     * @param MongoId $mongoId
     * @return string
     */
    public function parseId($mongoId) {

        if ($mongoId instanceof MongoId) {

            return (string) $mongoId;
        }

        return $mongoId;
    }

    /**
     * getallheaders
     * lấy ra các thông số trong header request, chuyển thành cấu trúc array
     * 
     * @return array
     */
    protected function getallheaders() {

        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public function detectMobile() {

        $raw_headers = $this->getallheaders();
        $headers = array_change_key_case($raw_headers, CASE_LOWER);
        $msisdn = isset($headers['msisdn']) ? $headers['msisdn'] : null;

        return $msisdn;
    }

    /**
     * standardizeMobile
     * chuyển số 0 ở đầu nếu có thành 84
     * 
     * @param string $raw_mobile
     * @return string
     */
    public function standardizeMobile($raw_mobile) {

        $mobile = preg_replace('/^0/', '84', $raw_mobile);
        return $mobile;
    }

    /**
     * prettyMobile
     * làm đẹp số điện thoại, phù hợp với cách đọc của visitor
     * 
     * @param string $raw_mobile
     * @return string
     */
    public function prettyMobile($raw_mobile) {

        $mobile = preg_replace('/^84/', '0', $raw_mobile);
        return $mobile;
    }
    
    public function parseTel($tel, $class = '') {
        $mobile = filter_var($tel, FILTER_SANITIZE_NUMBER_INT);
        if(isset($class) && strlen($class)) {
            return '<a href="tel:'.$mobile.'" class="'.$class.'">'.__('Call Now').'</a>';
        } else {
            return '<a href="tel:'.$mobile.'">'.$tel.'</a>';
        }
    }

}
