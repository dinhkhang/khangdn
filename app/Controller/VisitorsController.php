<?php

App::uses('AppQuizController', 'Controller');

class VisitorsController extends AppQuizController {

    public $uses = array(
        'Visitor',
        'Player',
        'Distributor',
        'DistributionChannel',
        'Mo',
    );
    public $debug_mode = 0;
    public $log_file_name = null;
    public $components = array(
        'Session',
        'Paginator',
        'Auth' => array(
            'loginAction' => array(
                'controller' => 'Visitors',
                'action' => 'login',
            ),
            'loginRedirect' => array('controller' => 'Gamequiz', 'action' => 'index'),
            'logoutRedirect' => array('controller' => 'Gamequiz', 'action' => 'index'),
            'authorize' => array('Controller'),
            'authenticate' => array(
                'Form' => array(
                    'userModel' => 'Visitor',
                    'fields' => array('username' => 'username', 'password' => 'password')
                )
            )
        ),
//        'DebugKit.Toolbar',
        'Diameter',
    );
    public $helpers = array('Common');

    public function login() {

        $this->layout = 'login';
        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $this->log_file_name = $log_file_name;

        if ($this->debug_mode > 1) {

            $this->logAnyFile('Post data: ', $log_file_name);
            $this->logAnyFile($this->request->data, $log_file_name);
        }

        $this->set('model_name', $this->modelClass);
        $time_lock_reset_password = Configure::read('sysconfig.Visitors.time_lock_reset_password');
        $this->set('time_lock_reset_password', $time_lock_reset_password);

        if ($this->request->is('post') || $this->request->is('put')) {

            if (isset($this->request->data[$this->modelClass]['username'])) {

                $this->request->data[$this->modelClass]['username'] = $this->standardizeMobile($this->request->data[$this->modelClass]['username']);
            }

            if ($this->validateRequestData() && $this->Auth->login()) {

                $visitor = $this->Session->read('Auth.User');
                if (empty($visitor)) {

                    $error_system_title = Configure::read('sysconfig.Visitors.error_system_title');
                    $this->set('login_failed_title', $error_system_title);

                    $error_system_content = Configure::read('sysconfig.Visitors.error_system_content');
                    $this->set('login_failed_content', $error_system_content);

                    return false;
                }

                $mobile = $visitor['mobile'];

                // thực hiện lưu vào bảng Mo
                $mo_data = array(
                    'phone' => $mobile,
                    'distributor_code' => '',
                    'distribution_channel_code' => '',
                    'distributor_sharing' => '',
                    'distribution_channel_sharing' => '',
                    'short_code' => '',
                    'package' => '',
                    'package_day' => '',
                    'package_week' => '',
                    'package_month' => '',
                    'channel' => 'WAP',
                    'amount' => '',
                    'content' => '',
                    'action' => 'DANG_NHAP',
                    'status' => 1,
                );
                $this->Mo->init();
                $this->Mo->create();
                $this->Mo->save($mo_data);

                // thực hiện lưu vào tracking_access với action = DANG_NHAP
                $opts_trace = array();
                $save_data = array(
                    'mobile' => $mobile,
                    'visitor' => new MongoId($visitor[$this->modelClass]['id']),
                    'visitor_username' => $visitor[$this->modelClass]['username'],
                    'service_code' => 'HALOVIETNAM',
                    'action' => 'DANG_NHAP',
                    'channel' => 'WAP',
                );
                $this->TrackingAccessCommon->trace($save_data, $opts_trace);

                // xác định xem thuê bao đã đk gói cước chưa?
                $player = $this->Player->getInfoByMobile($mobile);
                if (!empty($player)) {

                    $this->Player->save(array(
                        'id' => $player['Player']['id'],
                        'time_last_action' => new MongoDate(),
                    ));
                }

                // nếu chưa đk
                if (empty($player) || !$this->isRegisteredPackage($player)) {


                    $this->Session->write('Auth.Player', null);

                    return $this->redirect(array(
                                'controller' => 'Packages',
                                'action' => 'view',
                    ));
                } else {

                    $this->Session->write('Auth.Player', $player['Player']);
                }

                return $this->redirectHome();
            } else {
                
            }
        } else {

            // thực hiện detect mobile
            $mobile = $this->detectMobile();

            // nếu đã nhận diện được
            if (!empty($mobile)) {

                // lấy về thông tin của visitor
                $visitor = $this->Visitor->getInfoByMobile($mobile);

                // nếu không tồn tại visitor
                if (empty($visitor)) {

                    $unregister_title = Configure::read('sysconfig.Visitors.not_yet_register_title');
                    $this->set('login_failed_title', $unregister_title);

                    $unregister_content = Configure::read('sysconfig.Visitors.not_yet_register_content');
                    $this->set('login_failed_content', $unregister_content);

                    $this->Session->delete('Auth.User');
                    return false;
                }

                // thực hiện ghi session
                $this->Session->write('Auth.User', $visitor['Visitor']);

                // xác định xem thuê bao đã đk gói cước chưa?
                $player = $this->Player->getInfoByMobile($mobile);
                if (!empty($player)) {

                    $this->Player->save(array(
                        'id' => $player['Player']['id'],
                        'time_last_action' => new MongoDate(),
                    ));
                }

                // nếu visitor chưa đăng ký package, thì hiện lên cảnh báo và chuyển hướng tới màn hình đăng ký package
                if (empty($player) || !$this->isRegisteredPackage($player)) {

                    $this->Session->write('Auth.Player', null);

                    return $this->redirect(array(
                                'controller' => 'Packages',
                                'action' => 'view',
                    ));
                }
                // nhận diện được và đã đk thì chuyển hướng sang trang khác
                else {

                    $this->Session->write('Auth.Player', $player['Player']);
                }

                return $this->redirectHome();
            }

            if ($this->Auth->login()) {

                return $this->redirectHome();
            }
        }
    }

    /**
     * isRegisteredPackage
     * xác định xem thuê bao đã đăng ký gói package nào khác chưa?
     * 
     * @param array $player
     * @return bool
     */
    protected function isRegisteredPackage($player) {

        if (!empty($player['Player']['package_day']['status']) && $player['Player']['package_day']['status'] == 1) {

            return 'G1';
        }

        if (!empty($player['Player']['package_week']['status']) && $player['Player']['package_week']['status'] == 1) {

            return 'G7';
        }

        if (!empty($player['Player']['package_month']['status']) && $player['Player']['package_month']['status'] == 1) {

            return 'G30';
        }

        return false;
    }

    public function logout() {

        return $this->redirect($this->Auth->logout());
    }

    protected function redirectHome() {

        return $this->redirect(array('action' => 'index', 'controller' => 'Gamequiz'));
    }

    protected function validateRequestData() {

        $mobile = trim($this->request->data($this->modelClass . '.username'));
        $password = $this->request->data($this->modelClass . '.password');
        if (!strlen($mobile)) {

            $this->set('login_failed_title', Configure::read('sysconfig.Visitors.empty_mobile_title'));
            $this->set('login_failed_content', Configure::read('sysconfig.Visitors.empty_mobile_content'));
            return false;
        }
        if (!strlen($password)) {

            $this->set('login_failed_title', Configure::read('sysconfig.Visitors.empty_password_title'));
            $this->set('login_failed_content', Configure::read('sysconfig.Visitors.empty_password_content'));
            return false;
        }
        if (!$this->validateMobile($mobile)) {

            $this->set('login_failed_title', Configure::read('sysconfig.Visitors.wrong_mobile_title'));
            $this->set('login_failed_content', Configure::read('sysconfig.Visitors.wrong_mobile_content'));
            return false;
        }

        $visitor = $this->Visitor->getInfoByMobile($mobile);
        if ($this->debug_mode > 1) {

            $this->logAnyFile('Visitor data: ', $this->log_file_name);
            $this->logAnyFile($visitor, $this->log_file_name);
        }
        if (empty($visitor)) {

            $unregister_title = Configure::read('sysconfig.Visitors.not_yet_register_title');
            $this->set('login_failed_title', $unregister_title);

            $unregister_content = Configure::read('sysconfig.Visitors.not_yet_register_content');
            $this->set('login_failed_content', $unregister_content);

            return false;
        }

        // kiểm tra tính hợp lệ của password
        if ($visitor['Visitor']['password'] != Security::hash($password, null, true)) {

            $unregister_title = Configure::read('sysconfig.Visitors.wrong_password_title');
            $this->set('login_failed_title', $unregister_title);

            $unregister_content = Configure::read('sysconfig.Visitors.wrong_password_content');
            $this->set('login_failed_content', $unregister_content);

            if ($this->debug_mode > 1) {

                $this->logAnyFile('visitor password was not mapping to input password', $this->log_file_name);
                $this->logAnyFile(__('visitor password=%s', $visitor['Visitor']['password']), $this->log_file_name);
                $this->logAnyFile(__('input password=%s', Security::hash($password, null, true)), $this->log_file_name);
            }

            return false;
        }
        return true;
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
        $this->VisitorBlacklistCommon->allow();
    }

}
