<?php

App::uses('Component', 'Controller');
App::uses('TrackingAccess', 'Model');
Configure::load('mobifone_ip');

class TrackingAccessCommonComponent extends Component {

    public $controller = null;
    public $channel = null;
    public $auto_trace = 1;

    const DEFAULT_SERVICE_CODE = 'HALOVIETNAM';
    const DEFAULT_ACTION = 'OTHER';
    const CHANNEL_WAP = 'WAP';
    const CHANNEL_WEB = 'WEB';
    const CHANNEL_APP = 'APP';
    const TOKEN_SECRET = null;

    public function initialize(\Controller $controller) {
        parent::initialize($controller);

        $this->controller = $controller;
        if (!isset($this->controller->TrackingAccess)) {

            $this->controller->loadModel('TrackingAccess');
        }
    }

    public function trace($save_data = array(), $options = array()) {

        if (empty($options['date'])) {

            $date = date('d-m-Y H:i:s');
        } else {

            $date = $options['date'];
        }

        if (!empty($options['payload'])) {

            $payload = $options['payload'];
        } else {

            $payload = $this->controller->request->query;
        }

        if (empty($options['screen_code'])) {

            $screen_code = strtolower($this->controller->name . '_' . $this->controller->action);
        } else {

            $screen_code = $options['screen_code'];
        }

        // thực hiện xác định xem request có thuộc ip của mobifone không?
        $client_ip = $this->controller->request->clientIp();
        if ($this->checkIPRange($client_ip)) {

            $is_detect_ip = 1;
            // thực hiện detect xem có ok hay không?
            $raw_headers = $this->getallheaders();
            $headers = array_change_key_case($raw_headers, CASE_LOWER);
            if (!empty($headers['msisdn'])) {

                $detect_status = 1;
            } else {

                $detect_status = 0;
            }
        } else {

            $is_detect_ip = 0;
            $detect_status = null;
        }

        $default_save_data = array(
            'host' => $this->controller->request->host(),
            'client_ip' => $client_ip,
            'is_detect_ip' => $is_detect_ip,
            'detect_status' => $detect_status,
            'screen_code' => $screen_code,
            'payload' => $payload,
            'path' => $this->controller->request->here(),
            'referer' => $this->controller->request->referer(),
            'user_agent' => $this->controller->request->header('User-Agent'),
            'os_name' => !empty($options['os_name']) ? $options['os_name'] : $this->controller->os_name,
            'os_version' => !empty($options['os_version']) ? $options['os_version'] : $this->controller->os_version,
            'visitor' => !empty($options['visitor']) ? $options['visitor'] : null,
            'visitor_username' => !empty($options['visitor_username']) ? $options['visitor_username'] : $this->controller->username,
            'mobile' => !empty($options['mobile']) ? $options['mobile'] : null,
            'distributor_code' => !empty($options['distributor_code']) ? $options['distributor_code'] : null,
            'distribution_channel_code' => !empty($options['distribution_channel_code']) ? $options['distribution_channel_code'] : null,
            'channel' => !empty($options['channel']) ? $options['channel'] : null,
            'action' => !empty($options['action']) ? $options['action'] : self::DEFAULT_ACTION,
            'service_code' => !empty($options['service_code']) ? $options['service_code'] : self::DEFAULT_SERVICE_CODE,
        );

        $save_data = Hash::merge($default_save_data, $save_data);

        $this->controller->TrackingAccess->init($date);

        // nếu thực hiện lưu đồng bộ
        if (!empty($options['async']) && $options['async'] === false) {

            $this->controller->TrackingAccess->create();

            return $this->controller->TrackingAccess->save($save_data);
        }
        // nếu thực hiện lưu không đồng bộ
        else {

            if (empty($save_data['created'])) {

                $save_data['created'] = new MongoDate();
            }
            if (empty($save_data['modified'])) {

                $save_data['modified'] = new MongoDate();
            }

            $mongo = $this->controller->TrackingAccess->getDataSource();
            $mongoCollectionObject = $mongo->getMongoCollection($this->controller->TrackingAccess);

            return $mongoCollectionObject->insert($save_data, array('w' => 0));
        }
    }

    public function setWapOpts(&$options) {

        $options['channel'] = self::CHANNEL_WAP;

        // lấy ra thông tin về nhận diện thuê bao
        $raw_headers = $this->getallheaders();
        $headers = array_change_key_case($raw_headers, CASE_LOWER);
        $msisdn = isset($headers['msisdn']) ? $headers['msisdn'] : null;
        $options['mobile'] = $msisdn;

        // lấy ra thông tin về visitor đăng nhập
        $user = CakeSession::read('Auth.User');
        $options['visitor'] = !empty($user['id']) ? new MongoId($user['id']) : null;
        $options['visitor_username'] = !empty($user['username']) ? $user['username'] : null;

        if (!empty($options['visitor_username'])) {

            $options['mobile'] = $options['visitor_username'];
        }
    }

    public function setApiOpts(&$options) {

        $options['channel'] = self::CHANNEL_APP;

        // lấy ra thông tin về nhận diện thuê bao
        $raw_headers = $this->getallheaders();
        $headers = array_change_key_case($raw_headers, CASE_LOWER);
        $msisdn = isset($headers['msisdn']) ? $headers['msisdn'] : null;
        $options['mobile'] = $msisdn;

        $token = $this->controller->request->header('token');
        if (!empty($token)) {

            App::import('Vendor', 'JWT', array('file' => 'JWT' . DS . 'Authentication' . DS . 'JWT.php'));
            try {

                $token_decode = JWT::decode($token, self::TOKEN_SECRET);
                $options['visitor'] = !empty($token_decode->visitor->id) ? new MongoId($token_decode->visitor->id) : null;
                $options['visitor_username'] = !empty($token_decode->visitor->username) ? $token_decode->visitor->username : null;
                if (!empty($options['visitor_username'])) {

                    $options['mobile'] = $options['visitor_username'];
                }
            } catch (Exception $ex) {
                
            }
        }
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

    protected function cidrMatch($ip, $range) {

        list ($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
        return ($ip & $mask) == $subnet;
    }

    public function checkIPRange($ip) {

        $mobifone_ip = Configure::read('mobifone_ip');
        foreach ($mobifone_ip as $range) {

            if ($this->cidrMatch($ip, $range)) {

                return true;
            }
        }

        return false;
    }

}
