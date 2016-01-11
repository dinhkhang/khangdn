<?php

App::uses('AppQuizController', 'Controller');
App::uses('Mo', 'Model');
App::uses('Charge', 'Model');
App::uses('Score', 'Model');
App::uses('Mt', 'Model');

class PackagesController extends AppQuizController {

    public $uses = array(
        'Player',
        'Counter',
        'QuestionGroup',
        'Visitor',
        'MoDk',
        'Distributor',
        'DistributionChannel',
    );
    public $components = array(
        'Diameter',
        'MobifoneCommon',
    );
    public $arr_action = array();
    public $distributor_code = '';
    public $distribution_channel_code = '';
    public $distributor_sharing = '';
    public $distribution_channel_sharing = '';

    public function view() {

        $visitor = $this->Session->read('Auth.User');
        // nếu player chưa đăng nhập, thực hiện điều hướng sang màn hình đăng nhập wifi
        if (empty($visitor)) {

            return $this->redirect(array(
                        'controller' => 'Visitors',
                        'action' => 'login',
            ));
        }

        $mobile = $visitor['mobile'];
        $player = $this->Player->getInfoByMobile($mobile);
        $package_buy_display = 0;
        $package_day_status = $package_week_status = 0;

        if (!empty($player)) {

            $package_day_status = !empty($player['Player']['package_day']['status']) ?
                    $player['Player']['package_day']['status'] : 0;
            $package_week_status = !empty($player['Player']['package_week']['status']) ?
                    $player['Player']['package_week']['status'] : 0;


            if ($package_day_status == 1 || $package_week_status == 1) {

                $package_buy_display = 1;
            }
        }

        if ($package_day_status == 0 && $package_week_status == 0) {

            $this->_flash(Configure::read('sysconfig.Packages.unregister_title'));
            $this->_flash(Configure::read('sysconfig.Packages.unregister_content'));
        }

        $this->set('package_day_status', $package_day_status);
        $this->set('package_week_status', $package_week_status);
        $this->set('package_buy_display', $package_buy_display);
        $this->set('player', $player);
    }

    public function register($type = null) {

        // lưu lại trang điều hướng cần register redirect
        $this->Session->write('register_redirect', $this->referer(array(
                    'action' => 'view',
        )));

        if ($type != 'G1' && $type != 'G7') {

            return $this->redirect($this->referer(array(
                                'action' => 'view',
            )));
        }

        $arr_visitor_session = $this->Session->read('Auth.User');
        if (empty($arr_visitor_session)) {

            return $this->redirect(array(
                        'controller' => 'Visitors',
                        'action' => 'login',
            ));
        }

        $phone = $arr_visitor_session['mobile'];

        // thực hiện cô lập hệ thống
        $blackbox = Configure::read('sysconfig.App.blackbox');
        if (!empty($blackbox)) {

            // nếu số thuê bao không thuộc blackbox
            if (!in_array($phone, $blackbox)) {

                $this->logAnyFile(__('End: because phone "%s" is out blackbox', $phone), __CLASS__ . '_' . __FUNCTION__);
                die();
            }
        }

        $player = $this->Player->getInfoByMobile($phone);
        $package_alias = null;
        if ($type == 'G1') {

            $package_alias = 'package_day';
        } elseif ($type == 'G7') {

            $package_alias = 'package_week';
        }

        // nếu player đã đăng ký gói G7 hoặc G1
        if (
                !empty($player) &&
                !empty($package_alias) &&
                !empty($player['Player'][$package_alias]['status']) &&
                $player['Player'][$package_alias]['status'] == 1
        ) {

            return $this->redirectRegisteredPackage($player);
        }

        // thực hiện kiểm tra xem player đã đăng ký gói package nào khác không?
        // đảm bảo player chỉ có thể đăng ký 1 gói package tại 1 thời điểm
        if ($this->isRegisteredPackage($player)) {

            return $this->redirectRegisteredPackage($player);
        }

        // nếu player chưa tồn tại
        if (empty($player)) {

            $arr_player = [
                'visitor' => new MongoId($arr_visitor_session['id']),
                'session_id' => $this->Session->id(),
                'phone' => $phone,
                'package_day' => [
                    "status" => 0,
                    'status_charge' => 0,
                    'retry' => 0,
                    'time_send_question' => null,
                    'time_register' => null,
                    'time_first_charge' => null,
                    'time_effective' => null,
                    'time_charge' => null,
                    'time_deactive' => null,
                    'time_last_renew' => null,
                    'time_last_telco_deactive' => null,
                    'modified' => new MongoDate(),
                    'created' => new MongoDate(),
                ],
                'package_week' => [
                    "status" => 0,
                    'status_charge' => 0,
                    'retry' => 0,
                    'time_send_question' => null,
                    'time_register' => null,
                    'time_first_charge' => null,
                    'time_effective' => null,
                    'time_charge' => null,
                    'time_deactive' => null,
                    'time_last_renew' => null,
                    'time_last_telco_deactive' => null,
                    'modified' => new MongoDate(),
                    'created' => new MongoDate(),
                ],
                'package_month' => [
                    "status" => 0,
                    'status_charge' => 0,
                    'retry' => 0,
                    'time_send_question' => null,
                    'time_register' => null,
                    'time_first_charge' => null,
                    'time_effective' => null,
                    'time_charge' => null,
                    'time_deactive' => null,
                    'time_last_renew' => null,
                    'time_last_telco_deactive' => null,
                    'modified' => new MongoDate(),
                    'created' => new MongoDate(),
                ],
                'score_total' => array(
                    'score' => 0,
                    'time' => 0,
                ),
                'score_day' => array(
                    'score' => 0,
                    'time' => 0,
                ),
                'score_week' => array(
                    'score' => 0,
                    'time' => 0,
                ),
                'score_month' => array(
                    'score' => 0,
                    'time' => 0,
                ),
                'channel' => 'WAP',
                'channel_play' => 'WAP',
                'status' => 0,
                'status_play' => 0,
                'time_register' => null,
                'time_register_first' => null,
                'time_last_action' => new MongoDate(), // Thời điểm gần nhất thuê bao tác động lên hệ thống 
                'time_charge' => null, // Thời điểm thuê bao được mang đi charge cước hợp lệ
                'time_last_charge' => null, // thời điểm thuê bao được charge cước thành công gần nhất
                'time_last_renew' => null, // thời điểm thuê bao được charge cước gia hạn thành công gần nhất
                'time_deactive' => null, // thời điểm thuê bao hủy gần thành công gần nhất nói chung
                'time_last_self_deactive' => null, // thời điểm thuê bao tự hủy thành công gần nhất
                'time_last_system_deactive' => null, // thời điểm thuê bao bị hệ thống hủy thành công gần nhất
                'time_last_cms_deactive' => null, // thời điểm thuê bao bị hủy qua cms thành công gần nhất
                'time_last_buy_question' => null, // thời điểm thuê bao mua câu hỏi thành công gần nhất
                'time_last_telco_deactive' => null, // thời điểm thuê bao bị nhà mạng hủy thành công gần nhất
                'time_renew' => null, // đánh dấu thời điểm mang đi renew
                'comment' => '',
                'count_group_aday' => 0,
                'num_questions' => [],
                'num_questions_pending' => [],
                'answered_groups' => [],
                'question_group' => null,
                'distributor_code' => '',
                'distribution_channel_code' => '',
            ];

            $this->Player->create();
            $this->Player->save($arr_player);
            $player = $this->Player->find('first', array(
                'conditions' => array(
                    'id' => $this->Player->getLastInsertID(),
                ),
            ));
            $this->Session->write('Auth.Player', $player['Player']);
        }
        // nếu player đã tồn tại và chưa đăng ký bất kỳ package nào
        else {

            $arr_player = array(
                'id' => $player['Player']['id'],
                'time_last_action' => new MongoDate(),
            );
            $this->Player->save($arr_player);
            $this->Session->write('Auth.Player', $player['Player']);
        }

        // hiệu chỉnh lại việc hiện thị giá bên trang dangky.mobi
        // khi thuê bao đăng ký lần đầu tiên thì giá = 0
        if (empty($player['Player']['time_register_first'])) {

            $price = 0;
            $information = Configure::read('sysconfig.ChargingVMS.' . $type . '_free1day_information');
        } else {

            $price = Configure::read('sysconfig.ChargingVMS.' . $type . '_price');
            $information = Configure::read('sysconfig.ChargingVMS.' . $type . '_information');
        }
        $information = $this->unicodeToMobiNCRDecimal($information);

        $seq_tbl_key = Configure::read('sysconfig.SEQ_TBL_KEY.seq_tbl_charge');
        $seq_tbl_charge = $this->Counter->getNextSequence($seq_tbl_key);
        $trans_id = $seq_tbl_charge;

//        $url_return = Configure::read('sysconfig.ChargingVMS.url_return');
        $url_return = Router::url(array(
                    'action' => 'registerCallback',
                        ), true);

        $key = Configure::read('sysconfig.ChargingVMS.key');
        $sp_id = Configure::read('sysconfig.ChargingVMS.sp_id');
        $pkg = $type;
        $link = base64_encode($this->aes128_ecb_encrypt("$key", "$trans_id&$pkg&$price&$url_return&$information", ""));

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $this->logAnyFile(__('link: %s', $link), $log_file_name);

        $url_charge = Configure::read('sysconfig.ChargingVMS.url_charge') . "?sp_id=$sp_id&link=$link";
        $this->logAnyFile(__('url_charge: %s', $url_charge), $log_file_name);

        // hard code để test được ở local
//        $url_charge_pattern = Configure::read('sysconfig.ChargingVMS.url_charge');
//        $url_charge = sprintf($url_charge_pattern, $trans_id, $phone, 1);
        // thuc hien luu charge
        $options = array(
            'price' => $price,
            'trans_id' => $trans_id,
            'redirect_url' => $url_charge,
            'package' => $type,
            'redirect_params' => array(
                'url_return' => $url_return,
                'key' => $key,
                'sp_id' => $sp_id,
                'link' => $link,
            ),
        );

        if (!$this->logTracking($player, $options)) {

            return $this->redirect($this->referer(array(
                                'action' => 'view',
            )));
        }

        return $this->redirect($url_charge);
    }

    public function about() {

        $visitor = $this->Session->read('Auth.User');
        if (empty($visitor)) {

            return $this->redirect(array(
                        'controller' => 'Visitors',
                        'action' => 'login',
            ));
        }

        $mobile = $visitor['mobile'];
//        $player = $this->Player->getInfoByMobile($mobile);
        $player = $this->Player->isRegisterPackage($mobile);

        $register_package = array();
        $unregister_package = array();
        $time_effective = array();
        $max_package = 2;
        $package_buy_display = 0;
        $package_day_status = $package_week_status = 0;
        if (!empty($player)) {

            if (!empty($player['Player']['package_day']['status'])) {

                $register_package[] = 'G1';
                if (!empty($player['Player']['package_day']['time_effective'])) {

                    $time_effective['G1'] = date('d/m/Y', $player['Player']['package_day']['time_effective']->sec);
                } else {

                    $time_effective['G1'] = __('unknown');
                }
                $package_day_status = 1;
            } else {

                $unregister_package[] = 'G1';
            }

            if (!empty($player['Player']['package_week']['status'])) {

                $register_package[] = 'G7';
                if (!empty($player['Player']['package_week']['time_effective'])) {

                    $time_effective['G7'] = date('d/m/Y', $player['Player']['package_week']['time_effective']->sec);
                } else {

                    $time_effective['G7'] = __('unknown');
                }
                $package_week_status = 1;
            } else {

                $unregister_package[] = 'G7';
            }

            if (
                    empty($player['Player']['question_group']) &&
                    empty($player['Player']['num_questions']) &&
                    count($register_package) >= $max_package
            ) {

                $empty_question_daily_title = Configure::read('sysconfig.Packages.empty1_question_daily_title');
                $empty_question_daily_content = Configure::read('sysconfig.Packages.empty1_question_daily_content');
                $this->set('empty_question_daily_title', sprintf($empty_question_daily_title, implode(', ', $register_package)));
                $this->set('empty_question_daily_content', $empty_question_daily_content);
            } elseif (
                    empty($player['Player']['question_group']) &&
                    empty($player['Player']['num_questions']) &&
                    count($register_package) < $max_package
            ) {

                $empty_question_daily_title = Configure::read('sysconfig.Packages.empty_question_daily_title');
                $empty_question_daily_content = Configure::read('sysconfig.Packages.empty_question_daily_content');
                $this->set('empty_question_daily_title', sprintf($empty_question_daily_title, implode(', ', $register_package)));
                $this->set('empty_question_daily_content', sprintf($empty_question_daily_content, implode(', ', $unregister_package)));
            }

            if (count($register_package) > 0) {

                $package_buy_display = 1;
            }

            $this->setUsageQuestion($player);
        }
        // nếu player chưa đăng ký gói package nào thì thực hiện điều hướng về trang mua gói package
        else {

//            $this->_flash(Configure::read('sysconfig.Packages.unregister_title'));
//            $this->_flash(Configure::read('sysconfig.Packages.unregister_content'));
            return $this->redirect(array(
                        'action' => 'view',
            ));
        }

        $this->set('package_day_status', $package_day_status);
        $this->set('package_week_status', $package_week_status);
        $this->set('package_buy_display', $package_buy_display);
        $this->set('register_package', $register_package);
        $this->set('time_effective', $time_effective);
        $this->set('player', $player);
    }

    protected function setUsageQuestion($player) {

        $usage_question_pattern = Configure::read('sysconfig.Packages.usage_question');
        if (
                empty($player['Player']['num_questions']) &&
                empty($player['Player']['question_group'])
        ) {

            return;
        }

        $total = !empty($player['Player']['count_group_aday']) ?
                $player['Player']['count_group_aday'] * 5 : 0;

        $retain = count($player['Player']['num_questions']) * 5;
        if (!empty($player['Player']['question_group']['question_current'])) {

            $retain += 5 - ($player['Player']['question_group']['question_current']['index'] + 1);
        }
        $used = $total - $retain;
        $usage_question = sprintf($usage_question_pattern, $used, $retain);
        $this->set('usage_question', $usage_question);
    }

    /**
     * redirectRegisteredPackage
     * Thực hiện điều hướng khi player đã đăng ký package
     * 
     * @param array $player
     * @param string $type
     */
    protected function redirectRegisteredPackage($player) {

        $unregister_package = array();
        $register_package = array();
        // kiểm tra xem player đã đăng ký các gói package nào
        if (empty($player['Player']['package_day']['status'])) {

            $unregister_package[] = 'G1';
        } else {

            $register_package[] = 'G1';
        }
        if (empty($player['Player']['package_week']['status'])) {

            $unregister_package[] = 'G7';
        } else {

            $register_package[] = 'G7';
        }

        // nếu chưa hết câu hỏi trong ngày
        if (!empty($player['Player']['num_questions'])) {

            return $this->redirect(array(
                        'controller' => 'Gamequiz',
                        'action' => 'index',
            ));
        }

        // nếu đã hết câu hỏi ngày
        if (!empty($unregister_package)) {

            $empty_question_daily_title = Configure::read('sysconfig.Packages.empty_question_daily_title');
            $empty_question_daily_content = Configure::read('sysconfig.Packages.empty_question_daily_content');
        } else {

            $empty_question_daily_title = Configure::read('sysconfig.Packages.empty1_question_daily_title');
            $empty_question_daily_content = Configure::read('sysconfig.Packages.empty1_question_daily_content');
        }

        $this->Session->write('Notification.charge_failed_title', __($empty_question_daily_title, implode(', ', $register_package)));
        $this->Session->write('Notification.charge_failed_content', __($empty_question_daily_content, implode(', ', $unregister_package)));

        return $this->redirect(array(
                    'action' => 'view',
        ));
    }

    /**
     * logTracking
     * Thực hiện lưu Mo và Charge
     * 
     * @param array $player
     * @param array $options
     * 
     * @return boolean
     */
    protected function logTracking($player, $options = array()) {

        // thực hiện set thông số liên quan tới kênh truyền thông
        $this->setSharing();

        $year = date('Y');
        $month = date('m');
        $day = date('d');

        $mo_pattern = 'mo_%s_%s_%s';
        $charge_patern = 'charge_%s_%s_%s';
        $Mo = new Mo(array(
            'table' => sprintf($mo_pattern, $year, $month, $day),
        ));
        $Charge = new Charge(array(
            'table' => sprintf($charge_patern, $year, $month, $day),
        ));
        $this->arr_action = Configure::read('sysconfig.Players.ACTION');

        $mo_data = array(
            'phone' => $player['Player']['phone'],
            'short_code' => '',
            'package' => $options['package'],
            'question' => null,
            'package_day' => $player['Player']['package_day']['status'],
            'package_week' => $player['Player']['package_week']['status'],
            'package_month' => $player['Player']['package_month']['status'],
            'channel' => 'WAP',
            'amount' => $options['price'],
            'content' => '',
            'action' => $this->arr_action['DANG_KY'],
            'status' => 2,
            'details' => array(
                'trans_id' => $options['trans_id'],
                'package' => $options['package'],
                'redirect_url' => $options['redirect_url'],
                // thực hiện lưu thêm các thông tin về request
                'host' => $this->request->host(),
                'client_ip' => $this->request->clientIp(),
                'path' => $this->request->here(),
                'referrer' => $this->request->referer(),
                'user_agent' => $this->request->header('User-Agent'),
            ),
            'distributor_code' => $this->distributor_code,
            'distribution_channel_code' => $this->distribution_channel_code,
            'distributor_sharing' => $this->distributor_sharing,
            'distribution_channel_sharing' => $this->distribution_channel_sharing,
        );

        if (!empty($options['is_distributor_link'])) {

            $mo_data['is_distributor_link'] = 1;
            $mo_data['agency_code'] = $options['agency_code'];
        } else {

            $mo_data['is_distributor_link'] = 0;
            $mo_data['agency_code'] = '';
        }

        // thực hiện lưu thêm thao số khi truyền sang redirect
        if (!empty($options['redirect_params'])) {

            $mo_data['details'] = Hash::merge($mo_data['details'], $options['redirect_params']);
        }

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $Mo->create();
        if (!$Mo->save($mo_data)) {

            $this->logAnyFile(__('Can not save Mo, error detail: '), $log_file_name);
            $this->logAnyFile($options, $log_file_name);

            return false;
        }
        //unv
        $mo_id = $Mo->getLastInsertID();

        $arr_modk = $mo_data;
        $arr_modk['mo_id'] = new MongoId($mo_id);
        $this->generate_distributor_data($arr_modk, $mo_data);
        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
        $arr_modk['package'] = $this->arr_action['DANG_KY'];

        $this->MoDk->save($arr_modk);
        //eunv

        $Charge->create();
        $charge_data = array(
            'phone' => $player['Player']['phone'],
            'amount' => $options['price'],
            'service_code' => '',
            'trans_id' => $options['trans_id'],
            'channel' => 'WAP',
            'package' => $options['package'],
            'action' => $this->arr_action['DANG_KY'],
            'status' => 2,
            'is_distributor_link' => $mo_data['is_distributor_link'],
            'agency_code' => $mo_data['agency_code'],
            'details' => array(
                'redirect_url' => $options['redirect_url'],
                'mo_id' => new MongoId($Mo->getLastInsertID()),
            ),
            'distributor_code' => $this->distributor_code,
            'distribution_channel_code' => $this->distribution_channel_code,
            'distributor_sharing' => $this->distributor_sharing,
            'distribution_channel_sharing' => $this->distribution_channel_sharing,
        );
        $charge_data['details'] = Hash::merge($charge_data['details'], $mo_data['details']);

        if (!$Charge->save($charge_data)) {

            $this->logAnyFile(__('Can not save Charge, error detail: '), $log_file_name);
            $this->logAnyFile($options, $log_file_name);

            return false;
        }

        return true;
    }

    /**
     * registerCallback
     * Hàm callback dùng để mobi gọi lại
     * 
     * @return type
     */
    public function registerCallback() {

        // thực hiện đọc ra register redirect cần điều hướng về
        if ($this->Session->check('register_redirect')) {

            $register_redirect = $this->Session->read('register_redirect');
            $this->Session->delete('register_redirect');
        } else {

            $register_redirect = Router::url(array(
                        'action' => 'view',
                            ), true);
        }

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $raw_link = $this->request->query('link');
        $link = str_replace(" ", "+", $raw_link);

        $key = Configure::read('sysconfig.ChargingVMS.key');
        $decode_link = $this->aes128_ecb_decrypt("$key", base64_decode($link), "");

        // thực hiện hard code để test ở local
//        $decode_link = $raw_link;

        $extract_link = explode('&', $decode_link);
        if (count($extract_link) < 3) {

            $this->logAnyFile(__('decode_link is invalid format'), $log_file_name);
            $this->logAnyFile(__('raw_link: %s', $raw_link), $log_file_name);
            $this->logAnyFile(__('decode_link: %s', $decode_link), $log_file_name);

            return $this->redirect($register_redirect);
        }

        $trans_id = (int) $extract_link[0];
        $msisdn = $extract_link[1];
        $status = (int) $extract_link[2];

        // lấy ra các giá trị cần thiết dành cho chia sẻ Distributor
        $this->setSharing();

        // lấy ra thông tin player
        $player = $this->Player->getInfoByMobile($msisdn);
        if (empty($player)) {

            $this->logAnyFile(__('Player due to phone=%s does not exist'), $log_file_name);
            $this->Session->delete('Auth.User');
            return $this->redirect($register_redirect);
        }

        $year = date('Y');
        $month = date('m');
        $day = date('d');

        $mo_pattern = 'mo_%s_%s_%s';
        $charge_patern = 'charge_%s_%s_%s';
        $Mo = new Mo(array(
            'table' => sprintf($mo_pattern, $year, $month, $day),
        ));
        $Charge = new Charge(array(
            'table' => sprintf($charge_patern, $year, $month, $day),
        ));

        $get_mo = $Mo->getInfoByTransId($trans_id);
        if (empty($get_mo)) {

            $this->Session->write('Notification.charge_failed_title', Configure::read('sysconfig.Packages.error_system_title'));
            $this->Session->write('Notification.charge_failed_content', Configure::read('sysconfig.Packages.error_system_content'));

            $this->logAnyFile(__('Mo due to trans_id=%s does not exist', $trans_id), $log_file_name);
            return $this->redirect($register_redirect);
        }

        if (!empty($get_mo)) {

            $mo_details = !empty($get_mo['Mo']['details']) ? $get_mo['Mo']['details'] : array();
            $mo_details['endcoded_link'] = $raw_link;
            $mo_details['decoded_link'] = $decode_link;
            $mo_data = array(
                'id' => $get_mo['Mo']['id'],
                'status' => $status,
                'phone' => $msisdn,
                'details' => $mo_details,
                'distributor_code' => $this->distributor_code,
                'distribution_channel_code' => $this->distribution_channel_code,
                'distributor_sharing' => $this->distributor_sharing,
                'distribution_channel_sharing' => $this->distribution_channel_sharing,
            );
            $Mo->save($mo_data);
        }

        // lấy ra thông tin mo_dk, thực hiện set thông tin kênh truyền thông vào distributor
        $mo_id = $get_mo['Mo']['id'];
        $get_mo_dk = $this->MoDk->findByMoId($mo_id);
        if (!empty($get_mo_dk)) {

            $mo_dk_data = array(
                'status' => $status,
                'phone' => $msisdn,
                'details' => $mo_details,
                'distributor_code' => $this->distributor_code,
                'distribution_channel_code' => $this->distribution_channel_code,
                'distributor_sharing' => $this->distributor_sharing,
                'distribution_channel_sharing' => $this->distribution_channel_sharing,
                'id' => $get_mo_dk['MoDk']['id'],
            );
            $this->MoDk->save($mo_dk_data);
        }

        $get_charge = $Charge->getInfoByTransId($trans_id);
        if (empty($get_charge)) {

            $this->Session->write('Notification.charge_failed_title', Configure::read('sysconfig.Packages.error_system_title'));
            $this->Session->write('Notification.charge_failed_content', Configure::read('sysconfig.Packages.error_system_content'));

            $this->logAnyFile(__('Charge due to trans_id=%s does not exist', $trans_id), $log_file_name);
            return $this->redirect($register_redirect);
        }

        $charge_details = !empty($get_charge['Charge']['details']) ? $get_charge['Charge']['details'] : array();
        $charge_details['endcoded_link'] = $raw_link;
        $charge_details['decoded_link'] = $decode_link;
        $charge_data = array(
            'id' => $get_charge['Charge']['id'],
            'phone' => $msisdn,
            'details' => $charge_details,
            'distributor_code' => $this->distributor_code,
            'distribution_channel_code' => $this->distribution_channel_code,
            'distributor_sharing' => $this->distributor_sharing,
            'distribution_channel_sharing' => $this->distribution_channel_sharing,
        );

        // nếu trạng thái redirect trả về từ mobifone không thành công
        if ($status == 0) {

            $charge_data['status'] = 3;
            $Charge->save($charge_data);

            $this->Session->write('Notification.charge_failed_title', Configure::read('sysconfig.Packages.cancel_register_title'));
            $this->Session->write('Notification.charge_failed_content', Configure::read('sysconfig.Packages.cancel_register_content'));

            $this->logAnyFile(__('Callback due to trans_id=%s return failed from mobifone', $trans_id), $log_file_name);
            return $this->redirect($register_redirect);
        }

        $type = !empty($get_charge) ? $get_charge['Charge']['package'] : '';
        $charge_amount = $get_charge['Charge']['amount'];

        $charge_options = array(
            'log_file_name' => $log_file_name,
            'trans_id' => $trans_id,
        );

        // kiểm tra nếu player đăng ký đầu tiên
        if ($status == 1 && empty($player['Player']['time_register_first'])) {

            $charge_data['status'] = 1;
            $charge_data['amount'] = 0;
            $charge_data['details']['time_register_first'] = $player['Player']['time_register_first'];
            $charge_data['details']['is_register_first'] = 1;
            $Charge->save($charge_data);

            // thực hiện khuyến mãi cho lần ĐK đầu tiên
            $this->rewardFirstRegister($player, $type);
        }

        // thực hiện cấp phát câu hỏi cho lần ĐK từ thứ 2 trở đi
        else if ($status == 1) {

            $this->Player->save(array(
                'id' => $player['Player']['id'],
                'time_charge' => new MongoDate(),
            ));

            $player['time_charge'] = new MongoDate();

            // thực hiện charge cước
            $is_charge = $this->diameterCharge($Charge, $msisdn, $charge_amount, $charge_data, $charge_options);

            // nếu không charge thành công
            if (!$is_charge) {

                $this->Session->write('Notification.charge_failed_title', Configure::read('sysconfig.Packages.error_system_title'));
                $this->Session->write('Notification.charge_failed_content', Configure::read('sysconfig.Packages.error_system_content'));
                return $this->redirect($register_redirect);
            }

            $this->rewardRegister($player, $type);
        }

        // nếu là link truyền thông thì thực hiện gửi mt về
        if (!empty($get_mo['Mo']['is_distributor_link'])) {

            $options = array(
                'player' => $player,
                'mo_id' => $mo_id,
                'log_file_name' => $log_file_name,
            );
            $this->sendRegisterSms($options);
        }

        // chuyển hướng tới màn hình câu hỏi
        return $this->redirect(array(
                    'controller' => 'Players',
                    'action' => 'play',
        ));
    }

    /**
     * diameterCharge
     * Thực hiện charge cước
     * 
     * @param Model $Charge
     * @param string $msisdn
     * @param int $charge_amount
     * @param array $charge_data
     * @param array $options
     * 
     * @return boolean
     */
    protected function diameterCharge($Charge, $msisdn, $charge_amount, $charge_data, $options = array()) {

        $trans_id = $options['trans_id'];
        $log_file_name = $options['log_file_name'];
        // thực hiện charge
        $diameter_charge = $this->MobifoneCommon->charge($msisdn, $charge_amount, array(
            'log_file_name' => $log_file_name,
        ));

        $charge_data_details = !empty($charge_data['details']) ?
                $charge_data['details'] : array();
        $charge_data['details'] = Hash::merge($charge_data_details, $diameter_charge);
        $charge_data['status'] = $diameter_charge['status'];
        $Charge->save($charge_data);

        if ($diameter_charge['status'] == 1) {

            return true;
        }

        $this->logAnyFile(__('Charge due to trans_id=%s return failed from mobifone', $trans_id), $log_file_name);
        $this->logAnyFile(__('Charge data: '), $log_file_name);
        $this->logAnyFile($charge_data, $log_file_name);

        if ($diameter_charge['pretty_message'] == 'CPS-1001') {

            $this->Session->write('Notification.charge_failed_title', Configure::read('sysconfig.Packages.NOK_NO_MORE_CREDIT_AVAILABLE_TITLE'));
            $this->Session->write('Notification.charge_failed_content', Configure::read('sysconfig.Packages.NOK_NO_MORE_CREDIT_AVAILABLE_CONTENT'));
        } else {

            $this->Session->write('Notification.charge_failed_title', Configure::read('sysconfig.Packages.error_system_title'));
            $this->Session->write('Notification.charge_failed_content', Configure::read('sysconfig.Packages.error_system_content'));
        }

        return false;
    }

    /**
     * rewardFirstRegister
     * Thực hiện khuyến mãi và cấp phát câu hỏi cho lần đk đầu tiên
     * 
     * @param array $player
     * @param string $type
     */
    protected function rewardFirstRegister($player, $type) {

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        // lấy ra thông số giả lập dành cho charge
        $charge_emu = !empty($player['Player']['charge_emu']) ? $player['Player']['charge_emu'] : array();
        $package_alias = $this->getPackageAlias($type);

        $player_data = array(
            'id' => $player['Player']['id'],
            'time_register_first' => new MongoDate(),
            'time_register' => new MongoDate(),
            'status' => 1,
            'distributor_code' => $this->distributor_code,
            'distribution_channel_code' => $this->distribution_channel_code,
        );
        // thời điểm tương tác cuối cùng với hệ thống
        $player_data['time_last_action'] = new MongoDate();
        // thời điểm charge cước thành công
//        $player_data['time_last_charge'] = new MongoDate();
        // đọc lại thông tin liên quan tới package từ player
        $player_data[$package_alias] = $player['Player'][$package_alias];

        // check và thực hiện reset dữ liệu của player nếu sang 1 ngày mới
        $reset_player_data = $this->checkNewDayAndResetPlayer($player['Player']);
        $player_data = Hash::merge($player_data, $reset_player_data);

        // thực hiện cấp phát câu hỏi
        $question_group_limit = Configure::read('sysconfig.Packages.reward_first_register.question_group');
        $player_data_allocate = $this->QuestionGroup->allocate($player, $type, $question_group_limit, $log_file_name);
        $player_data = Hash::merge($player_data, $player_data_allocate);

        $player_data[$package_alias]['status'] = 1;
        $player_data[$package_alias]['status_charge'] = 1;
        $player_data[$package_alias]['time_register'] = new MongoDate();
        $player_data[$package_alias]['time_first_charge'] = $this->MobifoneCommon->getBeginOfMongoDate(strtotime('+1 day'));
        $player_data[$package_alias]['modified'] = new MongoDate();

        // luôn set thời điểm mang đi charge cước là ngày hôm sau đối với mọi loại package
        $player_data[$package_alias]['time_charge'] = $this->MobifoneCommon->getBeginOfMongoDate(strtotime('+1 day'));
        $player_data[$package_alias]['time_effective'] = $this->MobifoneCommon->getTimeEffective($type, time(), $charge_emu);

        $reward_score = Configure::read('sysconfig.Packages.reward_first_register.score');

        $player_data['score_total'] = $player['Player']['score_total'];
        $player_data['score_day'] = $player['Player']['score_day'];
        $player_data['score_week'] = $player['Player']['score_week'];
        $player_data['score_month'] = $player['Player']['score_month'];

        $player_data['score_total']['score'] = (int) $player_data['score_total']['score'] + $reward_score;
        $player_data['score_day']['score'] = (int) $player_data['score_day']['score'] + $reward_score;
        $player_data['score_week']['score'] = (int) $player_data['score_week']['score'] + $reward_score;
        $player_data['score_month']['score'] = (int) $player_data['score_month']['score'] + $reward_score;

        // thực hiện update lại kênh channel
        $player_data['channel'] = 'WAP';
        $player_data['channel_play'] = 'WAP';

        $this->Player->save($player_data);

        // thực hiện cộng điểm
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $score_pattern = 'score_%s_%s_%s';
        $Score = new Score(array(
            'table' => sprintf($score_pattern, $year, $month, $day),
        ));

        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
        $score_total = $player_data['score_total'];
        $score_data = array(
            'phone' => $player['Player']['phone'],
            'score_total' => $score_total,
            'score' => $reward_score,
            'action' => $this->arr_action['DANG_KY'],
            'service_code' => $service_code,
            'channel' => 'WAP',
            'status' => 0,
            'question' => null,
            'details' => array(
                'player_time_register_first' => $player_data['time_register_first'],
                'package' => $type,
            ),
        );

        $Score->create();
        $Score->save($score_data);
    }

    /**
     * rewardRegister
     * Thực hiện cấp phát bộ câu hỏi trong ngày
     * 
     * @param array $player
     * @param string $type
     */
    protected function rewardRegister($player, $type) {

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $package_alias = $this->getPackageAlias($type);
        $charge_emu = !empty($player['Player']['charge_emu']) ? $player['Player']['charge_emu'] : array();

        $player_data = array(
            'id' => $player['Player']['id'],
            'time_register' => new MongoDate(),
            'status' => 1,
            'distributor_code' => $this->distributor_code,
            'distribution_channel_code' => $this->distribution_channel_code,
            // lưu lại thời điểm charge cước thành công
            'time_last_charge' => new MongoDate(),
            'time_last_action' => new MongoDate(),
        );
        $player_data[$package_alias] = $player['Player'][$package_alias];

        // check và thực hiện reset dữ liệu của player nếu sang 1 ngày mới
        $reset_player_data = $this->checkNewDayAndResetPlayer($player['Player']);
        $player_data = Hash::merge($player_data, $reset_player_data);

        // thực hiện cấp phát câu hỏi
        $question_group_limit = Configure::read('sysconfig.Packages.reward_first_register.question_group');
        $player_data_allocate = $this->QuestionGroup->allocate($player, $type, $question_group_limit, $log_file_name);
        $player_data = Hash::merge($player_data, $player_data_allocate);

        $player_data[$package_alias]['status'] = 1;
        $player_data[$package_alias]['status_charge'] = 1;
        $player_data[$package_alias]['time_register'] = new MongoDate();
        $player_data[$package_alias]['time_first_charge'] = $this->MobifoneCommon->getTimeCharge($type, time());
        $player_data[$package_alias]['modified'] = new MongoDate();

        // thực hiện set thời điểm charge cước sẽ diễn ra theo chu kỳ phụ thuộc vào package
        $player_data[$package_alias]['time_charge'] = $this->MobifoneCommon->getTimeCharge($type, time());
        $player_data[$package_alias]['time_effective'] = $this->MobifoneCommon->getTimeEffective($type, time(), $charge_emu);

        // thực hiện update lại kênh channel
        $player_data['channel'] = 'WAP';
        $player_data['channel_play'] = 'WAP';

        // restore lại câu hỏi đang pending 
        $this->restoreQuestionPending($player, $player_data, $package_alias, $type);

        $this->Player->save($player_data);
    }

    protected function restoreQuestionPending($player, &$player_data, $package_alias, $package) {

        if (empty($player['Player']['num_questions_pending'][$package_alias]['groups'])) {

            return true;
        }

        $num_questions = !empty($player['Player']['num_questions']) ?
                $player['Player']['num_questions'] : array();
        $num_questions_pending = $player['Player']['num_questions_pending'][$package_alias]['groups'];
        foreach ($num_questions_pending as $pending) {

            if ($pending['package'] == $package) {

                $num_questions[] = $pending;
            }
        }
        $player_data['num_questions'] = $num_questions;
    }

    /**
     * buy
     * Thực hiện mua gói câu hỏi
     * 
     * @param string $type
     * 
     * @return type
     */
    public function buy($type = null) {

        if ($type != 'MUA') {

            return $this->redirect($this->referer(array(
                                'action' => 'view',
            )));
        }

        $visitor = $this->Session->read('Auth.User');
        if (empty($visitor)) {

            return $this->redirect(array(
                        'controller' => 'Visitors',
                        'action' => 'login',
            ));
        }

        $mobile = $visitor['mobile'];
        $player = $this->Player->getInfoByMobile($mobile);
        if (empty($player)) {

            return $this->redirect(array(
                        'controller' => 'Visitors',
                        'action' => 'login',
            ));
        }

        $buy_amount = Configure::read('sysconfig.ChargingVMS.MUA_price');
        $log_file_name = __CLASS__ . '_' . __FUNCTION__;

        $year = date('Y');
        $month = date('m');
        $day = date('d');

        $mo_pattern = 'mo_%s_%s_%s';
        $Mo = new Mo(array(
            'table' => sprintf($mo_pattern, $year, $month, $day),
        ));
        $mo_data = array(
            'phone' => $mobile,
            'short_code' => '',
            'package' => 'MUA',
            'question' => null,
            'package_day' => $player['Player']['package_day']['status'],
            'package_week' => $player['Player']['package_week']['status'],
            'package_month' => $player['Player']['package_month']['status'],
            'channel' => 'WAP',
            'amount' => $buy_amount,
            'content' => '',
            'action' => 'MUA',
            'status' => 2,
            'details' => array(),
            //unv
            'distributor_code' => "",
            'distribution_channel_code' => "",
            'distributor_sharing' => "",
            'distribution_channel_sharing' => ""
                //eunv
        );
        // lấy ra thông tin chia sẻ của các kênh truyền thông distributor
        $this->generate_distributor_data($mo_data, $player['Player']);

        $charge_patern = 'charge_%s_%s_%s';
        $Charge = new Charge(array(
            'table' => sprintf($charge_patern, $year, $month, $day),
        ));
        $charge_data = array(
            'phone' => $mobile,
            'amount' => $buy_amount,
            'service_code' => '',
            'trans_id' => '',
            'channel' => 'WAP',
            'package' => 'MUA',
            'action' => 'MUA',
            'status' => 2, // trạng thái chưa có response charge trả về
            'details' => array(),
            //unv
            'distributor_code' => "",
            'distribution_channel_code' => "",
            'distributor_sharing' => "",
            'distribution_channel_sharing' => ""
                //eunv
        );

        // lưu lại thời điểm mang đi charge cước
        $player_data = array(
            'id' => $player['Player']['id'],
            'time_charge' => new MongoDate(),
            'time_last_action' => new MongoDate(),
        );

        // thực hiện charge cước
        $diameter_charge = $this->MobifoneCommon->charge($mobile, $buy_amount, array(
            'log_file_name' => $log_file_name,
        ));
        $charge_data['status'] = $diameter_charge['status'];
        $charge_data['details'] = $diameter_charge;

        $mo_data['status'] = $diameter_charge['status'];
        $mo_data['details'] = $diameter_charge;

        // nếu không charge thành công
        if ($diameter_charge['status'] != 1) {

            // nếu không đủ tiền
            if ($diameter_charge['pretty_message'] == 'CPS-1001') {

                $this->Session->write('Notification.charge_failed_title', Configure::read('sysconfig.Packages.NOK_NO_MORE_CREDIT_AVAILABLE_TITLE'));
                $this->Session->write('Notification.charge_failed_content', Configure::read('sysconfig.Packages.NOK_NO_MORE_CREDIT_AVAILABLE_CONTENT'));
            }
            // lỗi khác
            else {

                $this->Session->write('Notification.charge_failed_title', Configure::read('sysconfig.Packages.error_system_title'));
                $this->Session->write('Notification.charge_failed_content', Configure::read('sysconfig.Packages.error_system_content'));
            }
        }
        // nếu charge cước thành công, lưu lại thời điểm charge cước thành công
        else {

            $player_data['time_last_charge'] = new MongoDate();
            $player_data['time_last_buy_question'] = new MongoDate();
        }
        // lưu lại thời điểm charge và charge cước thành công nếu có
        $this->Player->save($player_data);

        // lấy ra thông tin chia sẻ của các kênh truyền thông distributor
        $this->generate_distributor_data($charge_data, $player['Player']);

        $Charge->create();
        $Charge->save($charge_data);

        $Mo->create();
        $Mo->save($mo_data);

        // nếu charge cước thành công thì cấp phát câu hỏi
        if ($charge_data['status'] == 1) {

            // thực hiện cấp phát câu hỏi
            $question_group_limit = Configure::read('sysconfig.Packages.buy_question_group');
            $player_data = $this->QuestionGroup->allocate($player, 'MUA', $question_group_limit, $log_file_name);
            $player_data['time_last_action'] = new MongoDate();

            // check và thực hiện reset dữ liệu của player nếu sang 1 ngày mới
            $reset_player_data = $this->checkNewDayAndResetPlayer($player['Player']);
            $player_data = Hash::merge($player_data, $reset_player_data);

            $this->Player->save($player_data);

            return $this->redirect(array(
                        'controller' => 'Gamequiz',
                        'action' => 'index',
            ));
        }

        return $this->redirect($this->referer(array(
                            'action' => 'view',
        )));
    }

    protected function getPackageAlias($type) {

        $package_alias = null;
        if ($type == 'G1') {

            $package_alias = 'package_day';
        } elseif ($type == 'G7') {

            $package_alias = 'package_week';
        } elseif ($type == 'G30') {

            $package_alias = 'package_month';
        }

        return $package_alias;
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
        if ($this->action != 'view' && $this->action != 'about') {

            $this->Session->delete('Notification.charge_failed_title');
            $this->Session->delete('Notification.charge_failed_content');
        }
    }

    //unv
    public function generate_distributor_data(&$arr_data, $player) {

        if (isset($player['distributor_code']) && $player['distributor_code'] != '') {

            $arr_data['distributor_code'] = $player['distributor_code'];
            $arr_data['distributor_sharing'] = $this->Distributor->get_sharing_by_code($player['distributor_code']);
        }

        if (isset($player['distribution_channel_code']) && $player['distribution_channel_code'] != '') {

            $arr_data['distribution_channel_code'] = $player['distribution_channel_code'];
            $arr_data['distribution_channel_sharing'] = $this->DistributionChannel->get_sharing_by_code($player['distribution_channel_code']);
        }
    }

    public function setSharing() {

        $agency_code = $this->Session->read('agency_code');
        $distribution_channel_code = $distributor_code = '';
        if (!empty($agency_code)) {

            $distributor_channel = $this->DistributionChannel->findByAgencyCode($agency_code);

            if (!empty($distributor_channel)) {

                $distribution_channel_code = $distributor_channel['DistributionChannel']['code'];
                $distributor_code = $distributor_channel['DistributionChannel']['distributor_code'];

                $this->distributor_code = $distributor_code;
                $this->distribution_channel_code = $distribution_channel_code;
                $this->distribution_channel_sharing = !empty($distributor_channel['DistributionChannel']['sharing']) ?
                        $distributor_channel['DistributionChannel']['sharing'] : '';
            }
        }

        if (!empty($this->distributor_code)) {

            $distributor = $this->Distributor->findByCode($this->distributor_code);
            $this->distributor_sharing = !empty($distributor['Distributor']['sharing']) ?
                    $distributor['Distributor']['sharing'] : '';
        }
    }

    public function registerForDistributor() {

        $package = $this->request->query('package');
        if ($package != 'G1' && $package != 'G7') {

            return $this->redirect(array(
                        'action' => 'index',
                        'controller' => 'Gamequiz',
            ));
        }

        $phone = $this->detectMobile();
        if (empty($phone)) {

            return $this->redirect(array(
                        'controller' => 'Gamequiz',
                        'action' => 'index',
            ));
        }

        // thực hiện cô lập hệ thống
        $blackbox = Configure::read('sysconfig.App.blackbox');
        if (!empty($blackbox)) {

            // nếu số thuê bao không thuộc blackbox
            if (!in_array($phone, $blackbox)) {

                $this->logAnyFile(__('End: because phone "%s" is out blackbox', $phone), __CLASS__ . '_' . __FUNCTION__);
                die();
            }
        }

        // lấy về thông tin của visitor
        $visitor = $this->Visitor->getInfoByMobile($phone);
        if (empty($visitor)) {

            $this->Visitor->create();
            $this->Visitor->save(array(
                'username' => $phone,
                'mobile' => $phone,
                'status' => 2,
            ));
            $visitor_id = $this->Visitor->getLastInsertID();
        } else {

            $visitor_id = $visitor['Visitor']['id'];
        }

        $player = $this->Player->getInfoByMobile($phone);
        $package_alias = null;
        if ($package == 'G1') {

            $package_alias = 'package_day';
        } elseif ($package == 'G7') {

            $package_alias = 'package_week';
        }
        $type = $package;

        // nếu player đã đăng ký gói G7 hoặc G1
        if (
                !empty($player) &&
                !empty($package_alias) &&
                !empty($player['Player'][$package_alias]['status']) &&
                $player['Player'][$package_alias]['status'] == 1
        ) {

            return $this->redirectRegisteredPackage($player);
        }

        // thực hiện kiểm tra xem player đã đăng ký gói package nào khác không?
        // đảm bảo player chỉ có thể đăng ký 1 gói package tại 1 thời điểm
        if ($this->isRegisteredPackage($player)) {

            return $this->redirectRegisteredPackage($player);
        }

        // nếu player chưa tồn tại
        if (empty($player)) {

            $arr_player = [
                'visitor' => new MongoId($visitor_id),
                'session_id' => $this->Session->id(),
                'phone' => $phone,
                'package_day' => [
                    "status" => 0,
                    'status_charge' => 0,
                    'retry' => 0,
                    'time_send_question' => null,
                    'time_register' => null,
                    'time_first_charge' => null,
                    'time_effective' => null,
                    'time_charge' => null,
                    'time_deactive' => null,
                    'time_last_renew' => null,
                    'time_last_telco_deactive' => null,
                    'modified' => new MongoDate(),
                    'created' => new MongoDate(),
                ],
                'package_week' => [
                    "status" => 0,
                    'status_charge' => 0,
                    'retry' => 0,
                    'time_send_question' => null,
                    'time_register' => null,
                    'time_first_charge' => null,
                    'time_effective' => null,
                    'time_charge' => null,
                    'time_deactive' => null,
                    'time_last_renew' => null,
                    'time_last_telco_deactive' => null,
                    'modified' => new MongoDate(),
                    'created' => new MongoDate(),
                ],
                'package_month' => [
                    "status" => 0,
                    'status_charge' => 0,
                    'retry' => 0,
                    'time_send_question' => null,
                    'time_register' => null,
                    'time_first_charge' => null,
                    'time_effective' => null,
                    'time_charge' => null,
                    'time_deactive' => null,
                    'time_last_renew' => null,
                    'time_last_telco_deactive' => null,
                    'modified' => new MongoDate(),
                    'created' => new MongoDate(),
                ],
                'score_total' => array(
                    'score' => 0,
                    'time' => 0,
                ),
                'score_day' => array(
                    'score' => 0,
                    'time' => 0,
                ),
                'score_week' => array(
                    'score' => 0,
                    'time' => 0,
                ),
                'score_month' => array(
                    'score' => 0,
                    'time' => 0,
                ),
                'channel' => 'WAP',
                'channel_play' => 'WAP',
                'status' => 0,
                'status_play' => 0,
                'time_register' => null,
                'time_register_first' => null,
                'time_last_action' => new MongoDate(),
                'time_charge' => null, // Thời điểm thuê bao được mang đi charge cước hợp lệ
                'time_last_charge' => null, // thời điểm thuê bao được charge cước thành công gần nhất
                'time_last_renew' => null, // thời điểm thuê bao được charge cước gia hạn thành công gần nhất
                'time_deactive' => null, // thời điểm thuê bao hủy gần thành công gần nhất nói chung
                'time_last_self_deactive' => null, // thời điểm thuê bao tự hủy thành công gần nhất
                'time_last_system_deactive' => null, // thời điểm thuê bao bị hệ thống hủy thành công gần nhất
                'time_last_cms_deactive' => null, // thời điểm thuê bao bị hủy qua cms thành công gần nhất
                'time_last_buy_question' => null, // thời điểm thuê bao mua câu hỏi thành công gần nhất
                'time_last_telco_deactive' => null, // thời điểm thuê bao bị nhà mạng hủy thành công gần nhất
                'time_renew' => null, // thời điểm thuê bao bị nhà mạng hủy thành công gần nhất
                'comment' => '',
                'count_group_aday' => 0,
                'num_questions' => [],
                'num_questions_pending' => [],
                'answered_groups' => [],
                'question_group' => null,
                'distributor_code' => '',
                'distribution_channel_code' => '',
            ];

            $this->Player->create();
            $this->Player->save($arr_player);
            $player = $this->Player->find('first', array(
                'conditions' => array(
                    'id' => $this->Player->getLastInsertID(),
                ),
            ));
            $this->Session->write('Auth.Player', $player['Player']);
        }
        // nếu player đã tồn tại và chưa đăng ký bất kỳ package nào
        else {

            $arr_player = array(
                'id' => $player['Player']['id'],
                'time_last_action' => new MongoDate(),
            );
            $this->Player->save($arr_player);
            $this->Session->write('Auth.Player', $player['Player']);
        }

        // hiệu chỉnh lại việc hiện thị giá bên trang dangky.mobi
        // khi thuê bao đăng ký lần đầu tiên thì giá = 0
        if (empty($player['Player']['time_register_first'])) {

            $price = 0;
            $information = Configure::read('sysconfig.ChargingVMS.' . $type . '_free1day_information');
        } else {

            $price = Configure::read('sysconfig.ChargingVMS.' . $type . '_price');
            $information = Configure::read('sysconfig.ChargingVMS.' . $type . '_information');
        }
        $information = $this->unicodeToMobiNCRDecimal($information);

        $seq_tbl_key = Configure::read('sysconfig.SEQ_TBL_KEY.seq_tbl_charge');
        $seq_tbl_charge = $this->Counter->getNextSequence($seq_tbl_key);
        $trans_id = $seq_tbl_charge;

//        $url_return = Configure::read('sysconfig.ChargingVMS.url_return');
        $url_return = Router::url(array(
                    'action' => 'registerCallback',
                        ), true);

        $key = Configure::read('sysconfig.ChargingVMS.key');
        $sp_id = Configure::read('sysconfig.ChargingVMS.sp_id');
        $pkg = $type;
        $link = base64_encode($this->aes128_ecb_encrypt("$key", "$trans_id&$pkg&$price&$url_return&$information", ""));

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $this->logAnyFile(__('link: %s', $link), $log_file_name);

        $url_charge = Configure::read('sysconfig.ChargingVMS.url_charge') . "?sp_id=$sp_id&link=$link";
        $this->logAnyFile(__('url_charge: %s', $url_charge), $log_file_name);

        // hard code để test được ở local
//        $url_charge_pattern = Configure::read('sysconfig.ChargingVMS.url_charge');
//        $url_charge = sprintf($url_charge_pattern, $trans_id, $phone, 1);
        // thuc hien luu charge
        $options = array(
            'price' => $price,
            'trans_id' => $trans_id,
            'redirect_url' => $url_charge,
            'package' => $type,
            'is_distributor_link' => 1,
            'agency_code' => $this->Session->read('agency_code'),
        );

        if (!$this->logTracking($player, $options)) {

            return $this->redirect($this->referer(array(
                                'action' => 'view',
            )));
        }

        return $this->redirect($url_charge);
    }

    protected function extractMtFromConfig($mt, $code) {

        if (empty($mt)) {

            return false;
        }

        foreach ($mt as $v) {

            if ($v['code'] == $code) {

                return $v;
            }
        }

        return false;
    }

    protected function isSendMt($off_schedule) {

        if (empty($off_schedule) || !is_array($off_schedule)) {

            return true;
        }
        $time = time();
        foreach ($off_schedule as $v) {

            $start = strtotime($v['start']);
            $end = strtotime($v['end']);
            if ($time >= $start && $time <= $end) {

                return false;
            }
        }
        return true;
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

            return $player['Player']['package_day'];
        }

        if (!empty($player['Player']['package_week']['status']) && $player['Player']['package_week']['status'] == 1) {

            return $player['Player']['package_week'];
        }

        if (!empty($player['Player']['package_month']['status']) && $player['Player']['package_month']['status'] == 1) {

            return $player['Player']['package_month'];
        }

        return false;
    }

    /**
     * sendRegisterSms
     * Thực hiện gửi tin nhắn đăng ký qua sms
     * 
     * @param array $options
     */
    protected function sendRegisterSms($options = array()) {

        $player = $options['player'];
        $year = !empty($options['year']) ? $options['year'] : date('Y');
        $month = !empty($options['month']) ? $options['month'] : date('m');
        $day = !empty($options['day']) ? $options['day'] : date('d');

        $mo_id = $options['mo_id'];
        $log_file_name = !empty($options['log_file_name']) ?
                $options['log_file_name'] : __CLASS__ . '_' . __FUNCTION__;

        $mt_pattern = 'mt_%s_%s_%s';
        $mobile = $player['Player']['phone'];

        $Mt = new Mt(array(
            'table' => sprintf($mt_pattern, $year, $month, $day),
        ));

        $this->loadModel('Configuration');
        $config = $this->Configuration->find('first');
        $mt_config = $config['Configuration']['mt'];

        // lấy ra mt dựa vào lần đăng ký đầu tiên hay lần đăng ký thứ 2
        if (empty($player['Player']['time_register_first'])) {

            $mt_code = 'M06_RegisterForDistributorSuccessFirst';
        } else {

            $mt_code = 'M07_RegisterForDistributorSuccessSecond';
        }

        // lấy ra cấu hình dành cho mt dựa vào code
        $mt = $this->extractMtFromConfig($mt_config, $mt_code);
        $mt_off_schedule = $mt['off_schedule'];

        // thực hiện kiểm tra xem có phải thời điểm off không gửi mt không?
        $is_send_mt = $this->isSendMt($mt_off_schedule);
        if ($is_send_mt) {

            $sms_text = $mt['msg'];
            $sms_status = 2;
            $mt_his = array(
                'mo_id' => new MongoId($mo_id),
                'service' => null,
                'phone' => $mobile,
                'content' => $sms_text,
                'action' => $this->arr_action['DANG_KY'],
                'status' => $sms_status,
                'os_name' => $this->os_name,
                'os_version' => $this->os_version,
                'channel' => 'WAP',
            );

            $send_sms = $this->MobifoneCommon->sendSms($mobile, $sms_text, array(
                'log_file_name' => $log_file_name,
            ));

            $mt_his['status'] = $send_sms['status'];
            $mt_his['details'] = $send_sms;
            $Mt->save($mt_his);
        }
    }

    //eunv
}
