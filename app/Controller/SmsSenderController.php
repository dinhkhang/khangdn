<?php

App::uses('AppQuizController', 'Controller');

class SmsSenderController extends AppQuizController {

    public $uses = array(
        'Visitor',
        'Player',
        'Mo',
        'Mt',
        'Configuration',
    );
    public $components = array(
        'TrackingLogCommon',
        'MobifoneCommon',
        'VisitorBlacklistCommon',
    );
    public $debug_mode = 3;

    public function reqResetPassword() {

        $this->setInit();

        if (!$this->request->is('post')) {

            $this->resError('#sms002');
        }

        $raw_mobile = trim($this->request->data('mobile'));
        if (!strlen($raw_mobile)) {

            $options = array(
                'data' => array(
                    'client_message' => Configure::read('sysconfig.Visitors.wrong_mobile_content'),
                    'client_title' => Configure::read('sysconfig.Visitors.wrong_mobile_title'),
                ),
            );
            $this->resError('#sms003');
        }

        // kiểm tra tính hợp lệ mobile, chuyển mobile từ đầu số 0 sang đầu số 84
        // kiểm tra xem là số điện thoại của mobi
        $mobile = $this->validateMobile($raw_mobile);
        if ($mobile === false) {

            $options = array(
                'message_args' => $raw_mobile,
                'data' => array(
                    'client_message' => Configure::read('sysconfig.Visitors.wrong_mobile_content'),
                    'client_title' => Configure::read('sysconfig.Visitors.wrong_mobile_title'),
                ),
            );
            $this->resError('#sms004', $options);
        }
        $pretty_mobile = $this->prettyMobile($mobile);

        // thực hiện chặn gửi sms nếu thuê bao thuộc vào blacklist
        $this->limitBlacklist($mobile);

        $sms_content = Configure::read('sysconfig.SmsSender.reset_password_content');
        $sms_action = Configure::read('sysconfig.SmsSender.reset_password_action');
        $sms_status = Configure::read('sysconfig.SmsSender.status');

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;

        // lấy về thông tin của visitor
        $visitor = $this->Visitor->getInfoByMobile($mobile);

        // nếu không tồn tại visitor, thì thực hiện tạo ra visitor mới
        if (empty($visitor)) {

            $this->Visitor->create();
            $this->Visitor->save(array(
                'username' => $mobile,
                'mobile' => $mobile,
                'status' => 2,
            ));

            $visitor_id = $this->Visitor->getLastInsertID();
            $visitor = $this->Visitor->find('first', array(
                'conditions' => array(
                    'id' => $visitor_id,
                ),
            ));
        }

        $visitor_id = $visitor['Visitor']['id'];
        $new_password = $this->generatePassword();
        $sms_text = __($sms_content, $new_password, $pretty_mobile);

        // thực hiện xem player đã được tạo hay chưa?
        $player = $this->Player->getInfoByMobile($mobile);
        if (empty($player)) {

            $arr_player = [
                'visitor' => new MongoId($visitor_id),
                'session_id' => $this->Session->id(),
                'phone' => $mobile,
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
        }
        // thực hiện update lại time_last_action
        else {

            $this->Player->save(array(
                'id' => $player['Player']['id'],
                'time_last_action' => new MongoDate(),
            ));
        }

        $this->Mo->init();
        $this->Mt->init();

        $mo_data = array(
            'phone' => $mobile,
            'short_code' => '',
            'package' => '',
            'question' => null,
            'package_day' => '',
            'package_week' => '',
            'package_month' => '',
            'channel' => 'WAP',
            'amount' => '',
            'content' => '',
            'action' => $sms_action,
            'status' => 2,
            'details' => array(
                // thực hiện lưu thêm các thông tin về request
                'host' => $this->request->host(),
                'client_ip' => $this->request->clientIp(),
                'path' => $this->request->here(),
                'referrer' => $this->request->referer(),
                'user_agent' => $this->request->header('User-Agent'),
            ),
        );

        // thực hiện log vào database
        $mt_his = array(
            'mo_id' => null,
            'service' => null,
            'phone' => $mobile,
            'content' => $sms_text,
            'action' => $sms_action,
            'status' => $sms_status,
            'os_name' => $this->os_name,
            'os_version' => $this->os_version,
            'status' => 2,
            'channel' => 'APP',
        );

        $send_sms = $this->MobifoneCommon->sendSms($mobile, $sms_text, array(
            'log_file_name' => $log_file_name,
        ));

        $mo_data['status'] = $mt_his['status'] = $send_sms['status'];
        $mt_his['details'] = $send_sms;

        $this->Mo->save($mo_data);
        $mt_his['mo_id'] = new MongoId($this->Mo->getLastInsertID());
        $this->Mt->save($mt_his);

        if (!$mt_his['status']) {

            $this->resError('#sms010', array(
                'message_args' => array(
                    $mobile,
                    $sms_text,
                ),
                'data' => array(
                    'client_message' => Configure::read('sysconfig.Visitors.error_system_content'),
                    'client_title' => Configure::read('sysconfig.Visitors.error_system_title'),
                ),
            ));
        }

        // thực hiện tạo lại password cho visitor 
        // - chú ý việc tạo password này chỉ cần điền vào đúng số mobifone là gen lại
        if (!$this->Visitor->save(array(
                    'id' => $visitor_id,
                    'password' => $new_password,
                    'session_id' => $this->Session->id(),
                ))) {

            $options = array(
                'message_args' => $visitor_id,
                'data' => array(
                    'client_message' => Configure::read('sysconfig.Visitors.error_system_content'),
                    'client_title' => Configure::read('sysconfig.Visitors.error_system_title'),
                ),
            );
            $this->resError('#sms008', $options);
        }

        $res = array(
            'status' => 'success',
            'data' => null,
        );

        $this->resSuccess($res);
    }

    public function generatePassword() {

        $password = $this->generateRandomLetters(Configure::read('sysconfig.SmsSender.password_length'));
        return $password;
    }

    /**
     * limitBlacklist
     * Thực hiện giới hạn đối với thuê bao trong blacklist
     * 
     * @param string $mobile
     * 
     * @return boolean
     */
    protected function limitBlacklist($mobile) {

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $is_blacklist = $this->VisitorBlacklistCommon->isLimit($mobile);
        if (!$is_blacklist) {

            return false;
        }

        $this->logAnyFile(__('The mobile="%s" is limited', $mobile), $log_file_name);
        $options = array(
            'data' => array(
                'client_message' => Configure::read('sysconfig.Visitors.blacklist_content'),
                'client_title' => Configure::read('sysconfig.Visitors.blacklist_title'),
            ),
        );
        $this->resError('#sms014', $options);
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow(array('reqResetPassword'));
    }

}
