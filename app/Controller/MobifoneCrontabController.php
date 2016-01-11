<?php

App::uses('Mo', 'Model');
App::uses('Mt', 'Model');
App::uses('Charge', 'Model');
App::uses('AppQuizController', 'Controller');

class MobifoneCrontabController extends AppQuizController {

    public $uses = array(
        'Player',
        'QuestionGroup',
        'QuestionCategory',
        'Configuration',
        'ChargeCrontabLog',
        'Distributor',
        'DistributionChannel',
        'Mo',
        'Mt',
        'Charge',
    );
    public $components = array(
        'MobifoneCommon',
        'TrackingLogCommon',
    );
    public $auto_trace = 0;
    public $arr_action = array();
    public $mt_pattern = array();
    public $log_file_name = null;
    public $distributor_sharing = array();
    public $distribution_channel_sharing = array();
    public $is_first_renew = 0;

    protected function reset(&$player) {

        $this->logAnyFile(__('RESET: start ...'), $this->log_file_name);

        // thực hiện reset lại trạng thái charge
        if ($player['Player']['package_day']['status'] == 1) {

            $this->logAnyFile(__('RESET: package_day.status_charge, player due to phone=%s', $player['Player']['phone']), $this->log_file_name);
            $player['Player']['package_day']['status_charge'] = 0;
            $player['Player']['package_day']['retry'] = 0;
        }

        if ($player['Player']['package_week']['status'] == 1) {

            $this->logAnyFile(__('RESET: package_week.status_charge, player due to phone=%s', $player['Player']['phone']), $this->log_file_name);
            $player['Player']['package_week']['status_charge'] = 0;
            $player['Player']['package_week']['retry'] = 0;
        }

        if ($player['Player']['package_month']['status'] == 1) {

            $this->logAnyFile(__('RESET: package_month.status_charge, player due to phone=%s', $player['Player']['phone']), $this->log_file_name);
            $player['Player']['package_month']['status_charge'] = 0;
            $player['Player']['package_month']['retry'] = 0;
        }

        // thực hiện reset lại điểm
        $player['Player']['num_questions'] = [];
        $player['Player']['question_group'] = null;
        $player['Player']['count_group_aday'] = 0;
        $player['Player']['num_questions_pending'] = null;
        $player['Player']['score_day'] = array(
            'score' => 0,
            'time' => null,
        );

        $is_new_week = (date("w") == 1);
        $is_new_month = (date("d") == 1);

        // nếu là bắt đầu 1 tuần, thực hiện reset điểm tuần
        if ($is_new_week) {

            $this->logAnyFile(__('RESET: score_week, player due to phone=%s', $player['Player']['phone']), $this->log_file_name);
            $player['Player']['score_week'] = array(
                'score' => 0,
                'time' => null,
            );
        }

        // nếu là bắt đầu 1 tháng, thực hiện reset điểm tháng
        if ($is_new_month) {

            $this->logAnyFile(__('RESET: score_month, player due to phone=%s', $player['Player']['phone']), $this->log_file_name);
            $player['Player']['score_month'] = array(
                'score' => 0,
                'time' => null,
            );
        }

        $this->logAnyFile(__('Reset: end!'), $this->log_file_name);
    }

    /**
     * dailySendMT
     * Thực hiện gửi MT hàng ngày
     */
    public function dailySendMT() {

        set_time_limit(0);

        $this->initResponseText();
        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $this->logAnyFile('START dailySendMT...', $log_file_name);

        $time_current = date('Y-m-d H:i:s');

        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
        $configuration = $this->Configuration->find('first');
        $mt_confs = $configuration['Configuration']['mt'];
        $M16_Choi = $M10_GuideAnswer = '';

        foreach ($mt_confs as $item) {

            if ($item['code'] == 'M16_Choi') {

                $M16_Choi = $item['msg'];
            }

            if ($item['code'] == 'M10_GuideAnswer') {

                $M10_GuideAnswer = $item['msg'];
            }
        }

        $mt_pattern = str_replace('[HUONG_DAN]', $M10_GuideAnswer, $M16_Choi);
        $this->mt_pattern = $mt_pattern;

        $page = 1;
        $limit = 20;
        while (true) {

            $query = [
                'conditions' => [
                    '$or' => array(
                        array(
                            'package_day.status' => 1,
                            'package_day.status_charge' => 1,
                        ),
                        array(
                            'package_week.status' => 1,
                            'package_week.status_charge' => 1,
                        ),
                        array(
                            'package_month.status' => 1,
                            'package_month.status_charge' => 1,
                        ),
                    ),
                    'channel_play' => 'SMS',
                ],
                'limit' => $limit,
                'page' => $page,
                'order' => array(
                    '_id' => 'ASC',
                ),
            ];

            $arr_player = $this->Player->find('all', $query);
            $count_player = count($arr_player);
            if (empty($count_player)) {

                $this->logAnyFile(__("dailySendMT completed, page=%s", $page), $log_file_name);
                exit();
            }

            foreach ($arr_player as $player) {

                // nếu là gói G1, không phải ngày đăng ký đầu tiên (trước chu kỳ charge)
                // đồng thời chưa được cấp phát câu hỏi ngày theo gói G1 tại ngày hiện tại
                if (
                        $player['Player']['package_day']['status'] == 1 &&
                        $player['Player']['package_day']['status_charge'] == 1 &&
//                        !$this->isChargedAtCurrentTime($player, 'G1', $time_current) &&
                        (empty($player['Player']['package_day']['time_send_question']) ||
                        date('Ymd', $player['Player']['package_day']['time_send_question']->sec) < date('Ymd', strtotime($time_current)))
                ) {

                    $this->logAnyFile(__('dailySendMT begin allocateSendDaily G1 for player due to phone=%s', $player['Player']['phone']), $log_file_name);
                    $this->allocateSendDaily($player, 'G1', $log_file_name);
                }

                // nếu là gói G7, không phải ngày đăng ký đầu tiên (trước chu kỳ charge)
                // đồng thời chưa được cấp phát câu hỏi ngày theo gói G7 tại ngày hiện tại
                if (
                        $player['Player']['package_week']['status'] == 1 &&
                        $player['Player']['package_week']['status_charge'] == 1 &&
//                        !$this->isChargedAtCurrentTime($player, 'G7', $time_current) &&
                        (empty($player['Player']['package_week']['time_send_question']) ||
                        date('Ymd', $player['Player']['package_week']['time_send_question']->sec) < date('Ymd', strtotime($time_current)))
                ) {

                    $this->logAnyFile(__('dailySendMT begin allocateSendDaily G7 for player due to phone=%s', $player['Player']['phone']), $log_file_name);

                    // đọc lại thông tin player đã gia hạn theo daily
                    $player = $this->Player->getInfoByMobile($player['Player']['phone']);
                    $this->allocateSendDaily($player, 'G7', $log_file_name);
                }
            }

            $page++;
        }
    }

    protected function allocateSendDaily($player, $package, $log_file_name) {

        // cấp phát bộ câu hỏi
        $limit = Configure::read('sysconfig.Packages.' . $package . '_question_group');
        $player_data = $this->QuestionGroup->allocate($player, $package, $limit, $log_file_name);

        // nếu câu hỏi hiện tại của player đang rỗng, thì thực hiện gửi về mt
        // đồng thời lấy 1 bộ câu hỏi để chơi
        if (empty($player['Player']['question_group'])) {

            $num_questions = !empty($player_data['num_questions']) ?
                    $player_data['num_questions'] : array();
            if (empty($num_questions)) {

                $this->logAnyFile(__('allocateSendDaily Can not send mt to player due to phone=%s, Because num_questions is empty', $player['Player']['phone']), $log_file_name);
                return false;
            }

            $question_group_pop = array_pop($num_questions);
            $question_group_id = $question_group_pop['group_id'];
            $question_group_package = $question_group_pop['package'];

            $player_data['num_questions'] = $num_questions;

            $question_group = $this->QuestionGroup->find('first', array(
                'conditions' => array(
                    '_id' => $question_group_id,
                ),
            ));
            if (empty($question_group)) {

                $this->logAnyFile(__('allocateSendDaily Can not send mt to player due to phone=%s, Because QuestionGroup with id=%s does not exist', $player['Player']['phone'], $question_group_id), $log_file_name);
                return false;
            }
            if (empty($question_group['QuestionGroup']['questions'])) {

                $this->logAnyFile(__('allocateSendDaily Can not send mt to player due to phone=%s, Because QuestionGroup with id=%s have no questions', $player['Player']['phone'], $question_group_id), $log_file_name);
                return false;
            }

            $question_current = $question_group['QuestionGroup']['questions'][0];
            $cate_code = $question_group['QuestionGroup']['cate_code'];
            $cate = $this->QuestionCategory->find('first', array(
                'conditions' => array(
                    'code' => $cate_code,
                ),
            ));

            $player_data['question_group'] = array(
                'group_id' => $question_group_id,
                'package' => $question_group_package,
                'time_start' => new MongoDate(),
                'question_current' => $question_current,
                'cate' => array(
                    'code' => $cate_code,
                    'name' => !empty($cate['QuestionCategory']) ?
                            $cate['QuestionCategory']['name'] : '',
                ),
            );

            // thực hiện gửi MT
            $this->logAnyFile(__('allocateSendDaily begin sendQuestionViaMt to player due to phone=%s', $player['Player']['phone']), $log_file_name);
            $this->sendQuestionViaMt($player, $question_current, $log_file_name);
        }

        if (!$this->Player->save($player_data)) {

            $this->logAnyFile(__('allocateSendDaily Can not send mt to player due to phone=%s, Because can not save into Player', $player['Player']['phone']), $log_file_name);
            return false;
        }
    }

    protected function sendQuestionViaMt($player, $question_current, $log_file_name) {

        $mobile = $player['Player']['phone'];
        $mt_pattern = 'mt_%s_%s_%s';
        $date = date('Y-m-d');
        $mt_track = array(
            'model_pattern' => $mt_pattern,
            'date' => $date,
        );

        $sms_text = str_replace('[CAU_HOI]', $question_current['content_unsigned'], $this->mt_pattern);
        $sms_content = str_replace('[CAU_HOI_SO]', '1', $sms_text);
        $params = array(
            'to' => $mobile,
            'text' => $sms_content,
        );

        $mo_pattern = 'mo_%s';
        $Mo = new Mo(array(
            'table' => sprintf($mo_pattern, date('Y_m_d')),
        ));
        $mo = $Mo->getInfoByAction($this->arr_action['GIA_HAN'], $mobile);

        // thực hiện log vào database
        $mt_his = array(
            'mo' => !empty($mo['Mo']['id']) ?
                    new MongoId($mo['Mo']['id']) : null,
            'service' => null,
            'phone' => $mobile,
            'content' => $sms_content,
            'payload' => $params,
            'action' => $this->arr_action['GIA_HAN'],
            'status' => 2,
            'message' => '',
            'message_variables' => null,
            'os_name' => $this->os_name,
            'os_version' => $this->os_version,
        );

        $service_url = Configure::read('sysconfig.SmsSender.service_url');

        try {

            App::uses('HttpSocket', 'Network/Http');
            $HttpSocket = new HttpSocket();

            $send_sms = $HttpSocket->get($service_url, $params);
            if (!$send_sms->isOk()) {

                $mt_his['status'] = 0;
                $mt_his['message'] = $send_sms->body;
                $mt_his['message_variables'] = $send_sms->raw;

                $this->TrackingLogCommon->daily($mt_his, $mt_track);

                $this->logAnyFile(__('Send sms to %s was failed, sms content: %s', $mobile, $sms_text), $log_file_name);
                return false;
            }

            $result = trim($send_sms->body);
            if ($result != 0) {

                $mt_his['status'] = 0;
                $mt_his['message'] = $send_sms->body;
                $mt_his['message_variables'] = $send_sms->raw;

                $this->TrackingLogCommon->daily($mt_his, $mt_track);

                $this->logAnyFile(__('Send sms to %s was unsucessful, sms content: %s', $mobile, $sms_text), $log_file_name);
                return false;
            }

            // ghi log mt trả về cho mobile
            $mt_his['status'] = 1;
            $this->TrackingLogCommon->daily($mt_his, $mt_track);
            return true;
        } catch (Exception $ex) {

            $mt_his['status'] = 0;
            $mt_his['message'] = $ex->getMessage();
            $mt_his['message_variables'] = $ex->getTraceAsString();

            $this->TrackingLogCommon->daily($mt_his, $mt_track);

            $this->logAnyFile($ex->getMessage(), $log_file_name);
            $this->logAnyFile($ex->getTrace(), $log_file_name);
            return false;
        }
    }

    protected function setSharing(&$mo_data, $player) {

        $distribution_channel_code = !empty($player['Player']['distribution_channel_code']) ?
                $player['Player']['distribution_channel_code'] : '';
        $distributor_code = !empty($player['Player']['distributor_code']) ?
                $player['Player']['distributor_code'] : '';
        $distribution_channel_sharing = $distributor_sharing = '';
        $mo_data['distributor_code'] = $distributor_code;
        $mo_data['distribution_channel_code'] = $distribution_channel_code;
        if (!empty($distribution_channel_code)) {

            if (!isset($this->distribution_channel_sharing[$distribution_channel_code])) {

                $distribution_channel = $this->DistributionChannel->find('first', array(
                    'conditions' => array(
                        'code' => $distribution_channel_code,
                    ),
                ));

                $this->distribution_channel_sharing[$distribution_channel_code] = !empty($distribution_channel) ?
                        $distribution_channel['DistributionChannel']['sharing'] : '';
            }
            $distribution_channel_sharing = $this->distribution_channel_sharing[$distribution_channel_code];
        }
        if (!empty($distributor_code)) {

            if (!isset($this->distributor_sharing[$distributor_code])) {

                $distributor = $this->Distributor->find('first', array(
                    'conditions' => array(
                        'code' => $distributor_code,
                    ),
                ));
                $this->distributor_sharing[$distributor_code] = !empty($distributor) ?
                        $distributor['Distributor']['sharing'] : '';
            }
            $distributor_sharing = $this->distributor_sharing[$distributor_code];
        }
        $mo_data['distribution_channel_sharing'] = $distribution_channel_sharing;
        $mo_data['distributor_sharing'] = $distributor_sharing;
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

    public function renew() {

        $this->autoRender = false;

        set_time_limit(0);
        $this->log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
        $action = $this->arr_action['GIA_HAN'];

        $this->logAnyFile('Renew: start...', $this->log_file_name);
        echo date('Y-m-d H:i:s') . 'Renew: start...' . PHP_EOL;

        $this->Mo->init();
        $this->Mt->init();
        $this->Charge->init();

        // thêm điều kiện chỉ charge cước những thuê bao được giả lập charge_emu
        $charge_emu = $this->request->query('charge_emu');
        // thêm điều kiện chỉ charge theo 1 nhóm số thuê bao
        $phone = $this->request->query('phone');
        if (!empty($phone) && !is_array($phone)) {

            $phone = array($phone);
        }

        $page = 1;
        $limit = 20;

        while (true) {

            // thực hiện lấy ra các thuê bao có trạng thái là kích hoạt, 
            // và có thời điểm time_charge - thời điểm mang đi charge cước <= thời gian hiện tại
            $options = array(
                'conditions' => array(
                    '$or' => array(
                        array(
                            'package_day.status' => 1,
                            'package_day.time_charge' => array(
                                '$lte' => new MongoDate(),
                            ),
                        ),
                        array(
                            'package_week.status' => 1,
                            'package_week.time_charge' => array(
                                '$lte' => new MongoDate(),
                            ),
                        ),
                        array(
                            'package_month.status' => 1,
                            'package_month.time_charge' => array(
                                '$lte' => new MongoDate(),
                            ),
                        ),
                    ),
                ),
                'limit' => $limit,
                'page' => $page,
                'order' => array(
                    '_id' => 'ASC',
                ),
            );

            // thực hiện thêm điều kiện lọc theo có điều kiện charge_emu
            if (!empty($charge_emu)) {

                $options['conditions']['charge_emu'] = array(
                    '$type' => 3,
                );
            }

            // thực hiện thêm điều kiện lọc theo phone
            if (!empty($phone)) {

                $options['conditions']['phone'] = array(
                    '$in' => $phone,
                );
            }

            $arr_player = $this->Player->find('all', $options);

            $count_player = count($arr_player);
            if (empty($count_player)) {

                $this->logAnyFile(__("Renew completed, page=%s", $page), $this->log_file_name);
                echo date('Y-m-d H:i:s') . 'Renew: end.' . PHP_EOL;
                exit();
            }

            if (!is_array($arr_player)) {

                $this->logAnyFile(__("arr_player is invalid array"), $this->log_file_name);
                echo date('Y-m-d H:i:s') . 'Renew: end.' . PHP_EOL;
                exit();
            }

            foreach ($arr_player as $player) {

                // nếu đây là thời điểm gia hạn đầu tiên trong ngày
                $this->is_first_renew = 0;
                if (
                        empty($player['Player']['time_renew']) ||
                        date('Ymd', $player['Player']['time_renew']->sec) < date('Ymd')
                ) {

                    $this->is_first_renew = 1;
                    $this->reset($player);
                }

                $player['Player']['time_renew'] = new MongoDate();
                $player['Player']['time_charge'] = new MongoDate();

                if ($player['Player']['package_day']['status'] == 1) {

                    $package = 'G1';
                    $amount = Configure::read('sysconfig.ChargingVMS.' . $package . '_price');
                    $this->diameterCharge($player, $package, $action, $amount);
                } elseif ($player['Player']['package_week']['status'] == 1) {

                    $package = 'G7';
                    $amount = Configure::read('sysconfig.ChargingVMS.' . $package . '_price');
                    $this->diameterCharge($player, $package, $action, $amount);
                } elseif ($player['Player']['package_month']['status'] == 1) {

                    $package = 'G30';
                    $amount = Configure::read('sysconfig.ChargingVMS.' . $package . '_price');
                    $this->diameterCharge($player, $package, $action, $amount);
                }

                // thực hiện cấp phát câu hỏi
                // nếu Player được charge thành công và
                // có channel_play = WAP và có time_send_question < ngày hiện tại
                $package_alias = $this->getPackageAlias($package);
                if (
                        $player['Player']['channel_play'] == 'WAP' &&
                        $player['Player'][$package_alias]['status_charge'] == 1 &&
                        (
                        empty($player['Player'][$package_alias]['time_send_question']) ||
                        date('Ymd', $player['Player'][$package_alias]['time_send_question']->sec) < date('Ymd')
                        )
                ) {

                    $question_group_limit = Configure::read('sysconfig.Packages.' . $package . '_question_group');
                    $allocate_player = $this->QuestionGroup->allocate($player, $package, $question_group_limit, $this->log_file_name);
                    $save_data = Hash::merge($player['Player'], $allocate_player);
                } else {

                    $save_data = $player['Player'];
                }

                $this->Player->save($save_data);
            }

            $page++;
        }

        $this->logAnyFile('Renew: end.', $this->log_file_name);
        echo date('Y-m-d H:i:s') . 'Renew: end.' . PHP_EOL;
    }

    /**
     * dailyAllocateQuestion
     * Thực hiện cấp phát câu hỏi hàng ngày cho thuê bao, chỉ cấp phát đối với thuê bao đang có channel_play = WAP
     */
    public function dailyAllocateQuestion() {

        $this->autoRender = false;

        set_time_limit(0);
        $this->log_file_name = __CLASS__ . '_' . __FUNCTION__;

        $this->logAnyFile('dailyAllocateQuestion: start...', $this->log_file_name);
        echo date('Y-m-d H:i:s') . 'dailyAllocateQuestion: start... \n';

        $page = 1;
        $limit = 20;

        while (true) {

            // thực hiện lấy ra các thuê bao có trạng thái là kích hoạt, 
            // và có thời điểm time_charge - thời điểm mang đi charge cước <= thời gian hiện tại
            $options = array(
                'conditions' => array(
                    'channel_play' => 'WAP',
                    '$or' => array(
                        array(
                            'package_day.status_charge' => 1,
                            'package_day.status' => 1,
                            // gói package có thời gian hiệu lực chưa hết hạn, <= thời điểm hiện tại
                            'package_day.time_effective' => array(
                                '$lte' => new MongoDate(),
                            ),
                        ),
                        array(
                            'package_week.status_charge' => 1,
                            'package_week.status' => 1,
                            // gói package có thời gian hiệu lực chưa hết hạn, <= thời điểm hiện tại
                            'package_week.time_effective' => array(
                                '$lte' => new MongoDate(),
                            ),
                        ),
                        array(
                            'package_month.status_charge' => 1,
                            'package_month.status' => 1,
                            // gói package có thời gian hiệu lực chưa hết hạn, <= thời điểm hiện tại
                            'package_month.time_effective' => array(
                                '$lte' => new MongoDate(),
                            ),
                        ),
                    ),
                ),
                'limit' => $limit,
                'page' => $page,
                'order' => array(
                    '_id' => 'ASC',
                ),
            );

            $arr_player = $this->Player->find('all', $options);

            $count_player = count($arr_player);
            if (empty($count_player)) {

                $this->logAnyFile(__("dailyAllocateQuestion completed, page=%s", $page), $this->log_file_name);
                echo date('Y-m-d H:i:s') . 'dailyAllocateQuestion: end. \n';
                exit();
            }

            if (!is_array($arr_player)) {

                $this->logAnyFile(__("arr_player is invalid array"), $this->log_file_name);
                echo date('Y-m-d H:i:s') . 'Renew: end.' . PHP_EOL;
                exit();
            }

            foreach ($arr_player as $player) {


                if ($player['Player']['package_day']['status'] == 1) {

                    $package = 'G1';
                } elseif ($player['Player']['package_week']['status'] == 1) {

                    $package = 'G7';
                } elseif ($player['Player']['package_month']['status'] == 1) {

                    $package = 'G30';
                }

                // thực hiện cấp phát câu hỏi
                $question_group_limit = Configure::read('sysconfig.Packages.' . $package . '_question_group');
                $allocate_player = $this->QuestionGroup->allocate($player, $package, $question_group_limit, $this->log_file_name);
                $this->Player->save($allocate_player);
            }

            $page++;
        }

        $this->logAnyFile('dailyAllocateQuestion: end.', $this->log_file_name);
        echo date('Y-m-d H:i:s') . 'dailyAllocateQuestion: end. \n';
    }

    protected function diameterCharge(&$player, $package, $action, $amount) {

        $phone = $player['Player']['phone'];
        $channel = $player['Player']['channel'];
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
        $short_code = '9144';
        $package_alias = $this->getPackageAlias($package);
        $charge_emu = !empty($player['Player']['charge_emu']) ? $player['Player']['charge_emu'] : array();

        if (isset($charge_emu[$package]['amount'])) {

            $amount = $charge_emu[$package]['amount'];
        }

        $arr_charge = array(
            'phone' => $phone,
            'amount' => $amount,
            'service_code' => $service_code,
            'trans_id' => '',
            'channel' => $channel,
            'package' => $package,
            'action' => $action,
            'status' => 2,
            'details' => array(
                'is_first_daily_renew' => $this->is_first_renew,
            ),
        );

        $arr_mo = array(
            'phone' => $phone,
            'short_code' => $short_code,
            'package' => $package,
            'package_day' => $player['Player']['package_day']['status'],
            'package_week' => $player['Player']['package_week']['status'],
            'package_month' => $player['Player']['package_month']['status'],
            'channel' => $channel,
            'amount' => $amount,
            'content' => '',
            'action' => $action,
            'status' => 2,
            'details' => array(
                'is_first_daily_renew' => $this->is_first_renew,
            ),
        );

        $this->setSharing($arr_charge, $player);
        $this->setSharing($arr_mo, $player);

        // lưu lại kết qủa charge
        $charge_res = $this->MobifoneCommon->charge($phone, $amount, array(
            'log_file_name' => $this->log_file_name,
        ));

        $arr_charge['status'] = $arr_mo['status'] = $charge_res['status'];
        $arr_charge['details'] = Hash::merge($arr_charge['details'], $charge_res);
        $arr_mo['details'] = Hash::merge($arr_mo['details'], $charge_res);

        if ($charge_res['status'] == 1) {

            $this->logAnyFile(__('SUCCESS: %s was charged for %s package (%s)', $phone, $package, $amount), $this->log_file_name);

            $player['Player'][$package_alias]['status_charge'] = 1;
            $player['Player'][$package_alias]['time_last_renew'] = new MongoDate();
            $player['Player']['time_last_charge'] = new MongoDate();
            $player['Player']['time_last_renew'] = new MongoDate();
            if (empty($player['Player']['time_first_renew'])) {

                $player['Player']['time_first_renew'] = new MongoDate();
                $arr_charge['details']['is_first_renew'] = 1;
                $arr_mo['details']['is_first_renew'] = 1;
            }

            $player['Player'][$package_alias]['time_effective'] = $this->MobifoneCommon->getTimeEffective($package, time(), $charge_emu);
            $player['Player'][$package_alias]['time_charge'] = $this->MobifoneCommon->getTimeCharge($package, time(), $charge_emu);
        } else {

            $this->logAnyFile(__('FAIL: %s was not charged for %s package (%s)', $phone, $package, $amount), $this->log_file_name);
            $player['Player'][$package_alias]['status_charge'] = 3;
            if (!$this->is_first_renew) {

                $player['Player'][$package_alias]['retry'] = (int) $player['Player'][$package_alias]['retry'] + 1;
            }
        }
        $player['Player'][$package_alias]['modified'] = new MongoDate();

        $this->Mo->create();
        $this->Mo->save($arr_charge);

        $this->Charge->create();
        $this->Charge->save($arr_charge);
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
    }

}
