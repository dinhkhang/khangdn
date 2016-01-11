<?php

App::uses('Component', 'Controller');

class VisitorBlacklistCommonComponent extends Component {

    public $controller = null;
    public $blacklists = array();
    public $allowedActions = array();

    const REDIRECT_PATH = '/VisitorBlacklists/index'; // trang chuyển hướng trên wap, khi visitor bị blacklist

    public function initialize(\Controller $controller) {
        parent::initialize($controller);

        $this->controller = $controller;

        if (!isset($this->controller->VisitorBlacklist)) {

            $this->controller->loadModel('VisitorBlacklist');
        }

        // lấy ra tất cả các số điện thoại trong blacklist
        $blacklists = $this->controller->VisitorBlacklist->readCacheAll();
        $this->blacklists = $blacklists;
    }

    /**
     * isLimit
     * Xác định xem số điện thoại có nằm trong blacklists hay không?
     * 
     * @param string $mobile
     * 
     * @return boolean
     */
    public function isLimit($mobile) {

        if (empty($this->blacklists)) {

            return false;
        }

        if (in_array($mobile, $this->blacklists)) {

            return true;
        }

        return false;
    }

    /**
     * isRedirectUrl
     * Kiểm tra url chuyển hướng hiện tại có đúng là self:: REDIRECT_PATH
     * nếu đúng thì ngắt chuyển hướng trang tránh chuyển hướng lặp vô tận
     * 
     * @param \Controller $controller
     * @return boolean
     */
    protected function isRedirectUrl(\Controller $controller) {

        $path = '/' . $controller->name . '/' . $controller->action;
        if ($path == self:: REDIRECT_PATH) {

            return true;
        }

        return false;
    }

    public function allow($action = null) {

        $args = func_get_args();
        if (empty($args) || $action === null) {
            $this->allowedActions = $this->controller->methods;
            return;
        }
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }
        $this->allowedActions = array_merge($this->allowedActions, $args);
    }

    public function startup(\Controller $controller) {
        parent::startup($controller);

        // nếu là action thuộc controller của VisitorBlacklist
        if ($this->isRedirectUrl($controller)) {

            return true;
        }

        // nếu action hiện tại thuộc vào allowedActions thì luôn cho phép
        if (in_array($controller->action, $this->allowedActions)) {

            return true;
        }

        $redirect_url = Router::url(self::REDIRECT_PATH, true);

        // lấy ra số điện thoại
        $detect_mobile = $this->detectMobile();
        // nếu nhận diện được thuê baom và số thuê bao nằm trong blacklists, thì thực hiện chuyển hướng tới trang
        // blacklists
        if (!empty($detect_mobile) && $this->isLimit($detect_mobile)) {

            return $this->controller->redirect($redirect_url);
        }

        // nếu lấy được thông tin về số điện thoại từ session, thì thực hiện chuyển hướng tới trang
        // blacklists
        $visitor_mobile = CakeSession::read('Auth.User.mobile');
        if (!empty($visitor_mobile) && $this->isLimit($visitor_mobile)) {

            return $this->controller->redirect($redirect_url);
        }

        return true;
    }

    /**
     * detectMobile
     * Thực hiện detect số mobile
     * 
     * @return string
     */
    protected function detectMobile() {

        $raw_headers = $this->getallheaders();
        if (empty($raw_headers)) {

            return null;
        }
        $headers = array_change_key_case($raw_headers, CASE_LOWER);
        $msisdn = isset($headers['msisdn']) ? $headers['msisdn'] : null;

        return $msisdn;
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

}
