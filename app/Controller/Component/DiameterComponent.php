<?php

App::uses('Component', 'Controller');
App::uses('HttpSocket', 'Network/Http');

class DiameterComponent extends Component {

    const OK = 'CPS-0000';
    const NOK_NO_MORE_CREDIT_AVAILABLE = 'CPS-1001';

    /**
     * charge
     * Thực hiện charge cước với telco
     * 
     * @param string $mobile
     * @param int $amount
     * @param reference &$charge_data
     * @param array $options
     * 
     * @return boolean
     */
    public function charge($mobile, $amount, &$charge_data, $options = array()) {

        $charge_pattern = Configure::read('sysconfig.ChargingVMS.diameter');
        $charge_url = sprintf($charge_pattern, $mobile, $amount);

        // thực hiện hard code để test
//        $charge_url = sprintf($charge_pattern, 'CPS-0000');
//        $charge_url = sprintf($charge_pattern, 'CPS-1001');

        $charge_data['details']['charge_url'] = $charge_url;

        $HttpSocket = new HttpSocket(array(
            'ssl_verify_peer' => false,
            'ssl_verify_host' => false,
            'ssl_allow_self_signed' => false,
            'ssl_cafile' => false,
        ));

        if (empty($options['log_file_name'])) {

            $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        } else {

            $log_file_name = $options['log_file_name'];
        }

        try {

            $results = $HttpSocket->get($charge_url, array(), array(
                'redirect' => true,
            ));

            $clean_result = $this->cleanResult($results);
            if ($clean_result == self::OK) {

                $charge_data['status'] = 1;
                $charge_data['details']['message'] = $results->body;
                $charge_data['details']['message_variables'] = $results->raw;

                return true;
            } else {

                $charge_data['status'] = 0;
                $charge_data['details']['message'] = $results->body;
                $charge_data['details']['message_variables'] = $results->raw;

                $this->logAnyFile(__('Response was not OK, charge_url=%s', $charge_url), $log_file_name);
                $this->logAnyFile(__('Raw response: %s', $results->body), $log_file_name);

                return $clean_result;
            }
        } catch (Exception $ex) {

            $this->logAnyFile($ex->getMessage(), $log_file_name);
            $this->logAnyFile($ex->getTraceAsString(), $log_file_name);

            $charge_data['status'] = 0;
            $charge_data['details']['message'] = $ex->getMessage();
            $charge_data['details']['message_variables'] = $ex->getTraceAsString();
        }
    }

    protected function cleanResult($raw) {

        $pretty = trim($raw);
        $clean = str_replace('\n', '', $pretty);

        return $clean;
    }

    /**
     * logAnyFile
     * 
     * @param mixed $content
     * @param string $file_name
     */
    protected function logAnyFile($content, $file_name) {

        CakeLog::config($file_name, array(
            'engine' => 'File',
            'types' => array($file_name),
            'file' => $file_name,
        ));

        $this->log($content, $file_name);
    }

}
