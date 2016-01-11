<?php

App::uses('Component', 'Controller');
App::uses('HttpSocket', 'Network/Http');

class MobifoneCommonComponent extends Component {

    const OK = 'CPS-0000';
    const NOK_NO_MORE_CREDIT_AVAILABLE = 'CPS-1001';
    const SENT_OK = '0|OK';

    /**
     * charge
     * Thực hiện charge cước với telco
     * 
     * @param string $mobile
     * @param int $amount
     * @param array $options
     * 
     * @return array $res = array(
      'charge_url' => $charge_url,
      'pretty_message' => null,
      'message' => null,
      'message_variables' => null,
      'status' => 0,
      );
     */
    public function charge($mobile, $amount, $options = array()) {

        $charge_pattern = Configure::read('sysconfig.ChargingVMS.diameter');
        $charge_url = sprintf($charge_pattern, $mobile, $amount);

        $res = array(
            'charge_url' => $charge_url,
            'pretty_message' => null,
            'message' => null,
            'message_variables' => null,
            'status' => 0,
        );

        if (empty($options['log_file_name'])) {

            $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        } else {

            $log_file_name = $options['log_file_name'];
        }

        $HttpSocket = new HttpSocket(array(
            'ssl_verify_peer' => false,
            'ssl_verify_host' => false,
            'ssl_allow_self_signed' => false,
            'ssl_cafile' => false,
        ));

        try {

            $results = $HttpSocket->get($charge_url, array(), array(
                'redirect' => true,
            ));

            $pretty_result = trim($results->body);
            $res['pretty_message'] = $pretty_result;
            if ($pretty_result == self::OK) {

                $res['status'] = 1;
                $res['message'] = $results->body;
                $res['message_variables'] = $results->raw;
            } else {

                $res['status'] = 0;
                $res['message'] = $results->body;
                $res['message_variables'] = $results->raw;

                $this->logAnyFile(__('Response was not OK, charge_url=%s', $charge_url), $log_file_name);
                $this->logAnyFile(__('Raw response: %s', $results->body), $log_file_name);
            }
        } catch (Exception $ex) {

            $res['status'] = 0;
            $res['pretty_message'] = 'Exception';
            $res['message'] = $ex->getMessage();
            $res['message_variables'] = $ex->getTraceAsString();

            $this->logAnyFile(__('Charge %s exception:', $charge_url), $log_file_name);
            $this->logAnyFile($ex->getMessage(), $log_file_name);
            $this->logAnyFile($ex->getTraceAsString(), $log_file_name);
        }

        return $res;
    }

    public function sendSms($mobile, $text, $options = array()) {

        $service_url = Configure::read('sysconfig.SmsSender.service_url');
        $params = array(
            'to' => $mobile,
            'text' => $text,
        );

        $res = array(
            'status' => 1,
            'pretty_message' => '',
            'message' => '',
            'message_variables' => '',
            'sms_url' => '',
        );

        if (empty($options['log_file_name'])) {

            $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        } else {

            $log_file_name = $options['log_file_name'];
        }

        try {

            App::uses('HttpSocket', 'Network/Http');
            $HttpSocket = new HttpSocket();

            $send_sms = $HttpSocket->get($service_url, $params);
            $res['sms_url'] = $service_url . '?' . http_build_query($params);
            if (!$send_sms->isOk()) {

                $res['status'] = 0;
                $res['pretty_message'] = trim($send_sms->body);
                $res['message'] = $send_sms->body;
                $res['message_variables'] = $send_sms->raw;

                $this->logAnyFile(__('Send sms to %s was failed, sms content: %s', $mobile, $text), $log_file_name);
                return $res;
            }

            $result = trim($send_sms->body);
            if ($result != self::SENT_OK) {

                $res['status'] = 0;

                $this->logAnyFile(__('Send sms to %s was unsucessful, sms content: %s', $mobile, $text), $log_file_name);
            } else {

                $res['status'] = 1;
            }

            $res['pretty_message'] = trim($send_sms->body);
            $res['message'] = $send_sms->body;
            $res['message_variables'] = $send_sms->raw;

            return $res;
        } catch (Exception $ex) {

            $res['status'] = 0;
            $res['pretty_message'] = 'Exception';
            $res['message'] = $ex->getMessage();
            $res['message_variables'] = $ex->getTraceAsString();

            $this->logAnyFile('Charge exception:', $log_file_name);
            $this->logAnyFile($ex->getMessage(), $log_file_name);
            $this->logAnyFile($ex->getTraceAsString(), $log_file_name);

            return $res;
        }
    }

    /**
     * getEndOfMongoDate
     * Lấy ra thời điểm 23:59:59 của 1 ngày dưới dạng MongoDate
     * 
     * @param int $time
     * @return \MongoDate
     */ public function getEndOfMongoDate($time = null) {

        if (empty($time)) {

            $time = time();
        }

        $date = date('Y-m-d', $time) . ' 23:59:59';
        return new MongoDate(strtotime($date));
    }

    /**
     * getBeginOfMongoDate
     * Lấy ra thời điểm 00:00:00 của 1 ngày dưới dạng MongoDate
     * 
     * @param int $time
     * @return \MongoDate
     */ public function getBeginOfMongoDate($time = null) {

        if (empty($time)) {

            $time = time();
        }

        $date = date('Y-m-d', $time) . ' 00:00:00';
        return new MongoDate(strtotime($date));
    }

    public function getTimeEffective($package, $time_current = null, $charge_emu = array()) {

        if (empty($time_current)) {

            $time_current = time();
        }
        if (!empty($charge_emu[$package]['time_effective'])) {

            $time_effective = $this->getEndOfMongoDate(strtotime($charge_emu[$package]['time_effective'], $time_current));
        } else {

            if ($package == 'G1') {

                $time_effective = $this->getEndOfMongoDate($time_current);
            } elseif ($package == 'G7') {
                $time_effective = $this->getEndOfMongoDate(strtotime('+6 days', $time_current));
            } elseif ($package == 'G30') {

                $time_effective = $this->getEndOfMongoDate(strtotime('+29 days', $time_current));
            }
        }

        return $time_effective;
    }

    public function getTimeCharge($package, $time_current = null, $charge_emu = array()) {

        if (empty($time_current)) {

            $time_current = time();
        }
        if (!empty($charge_emu[$package]['time_charge'])) {

            $time_charge = $this->getBeginOfMongoDate(strtotime($charge_emu[$package]['time_charge'], $time_current));
        } else {

            if ($package == 'G1') {

                $time_charge = $this->getBeginOfMongoDate(strtotime('+1 day', $time_current));
            } elseif ($package == 'G7') {
                $time_charge = $this->getBeginOfMongoDate(strtotime('+7 days', $time_current));
            } elseif ($package == 'G30') {

                $time_charge = $this->getBeginOfMongoDate(strtotime('+30 days', $time_current));
            }
        }

        return $time_charge;
    }

    /**
     * logAnyFile
     * 
     * @param mixed $content
     * @param string $file_name
     */
    protected function logAnyFile($content, $file_name) {

        CakeLog::config($file_name, array('engine' => 'File',
            'types' => array($file_name),
            'file' => $file_name,
        ));

        $this->log($content, $file_name);
    }

}
