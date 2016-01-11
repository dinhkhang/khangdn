<?php

App::uses('Mo', 'Model');
App::uses('Mt', 'Model');
App::uses('Charge', 'Model');
App::uses('AppQuizController', 'Controller');

class PlayerDailyCrontabsController extends AppQuizController {

    public $uses = array(
        'Player',
        'QuestionGroup',
        'QuestionCategory',
        'Configuration',
        'ChargeCrontabLog',
        'Distributor',
        'DistributionChannel',
    );
    public $components = array(
        'Diameter',
        'TrackingLogCommon',
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
    );
    public $arr_action = array();
    public $mt_pattern = array();

    protected function reset($arr_player, $log_file_name, $time_current) {

        $this->logAnyFile(__('RESET: start ...'), $log_file_name);
        $reset_data = array();
        foreach ($arr_player as $key => $player) {

            $reset_data[$key] = array(
                'id' => $player['Player']['id'],
            );

            if ($player['Player']['package_day']['status'] == 1) {

                // nếu gói G1 chưa được charge ở thời điểm hiện tại - thời điểm trước khi charge theo chu kỳ
                // thì thực hiện reset lại trạng thái package_day.status_charge
                if (!$this->isChargedAtCurrentTime($player, 'G1', $time_current)) {

                    $this->logAnyFile(__('RESET: package_day.status_charge, player due to phone=%s', $player['Player']['phone']), $log_file_name);
                    $reset_data[$key]['package_day.status_charge'] = 0;
                } else {

                    $this->logAnyFile(__('DONT RESET: package_day.status_charge, player due to phone=%s', $player['Player']['phone']), $log_file_name);
                }
            }

            if ($player['Player']['package_week']['status'] == 1) {

                $time_week_charge = date('Y-m-d', $player['Player']['package_week']['time_first_charge']->sec);

                // nếu gói G7 chưa được charge ở thời điểm hiện tại - thời điểm trước khi charge theo chu kỳ
                // thì thực hiện reset lại trạng thái package_day.status_charge
                if (
                        $this->isWeeklyCharge($time_week_charge, 7, $time_current) &&
                        !$this->isChargedAtCurrentTime($player, 'G7', $time_current)
                ) {

                    $this->logAnyFile(__('RESET: package_week.status_charge, player due to phone=%s', $player['Player']['phone']), $log_file_name);
                    $reset_data[$key]['package_week.status_charge'] = 0;
                } else {

                    $this->logAnyFile(__('DONT RESET: package_week.status_charge, player due to phone=%s', $player['Player']['phone']), $log_file_name);
                }
            }

            // thực hiện reset điểm
            $time_last_action = $player['Player']['time_last_action'];
            if (date('Ymd', $time_last_action->sec) < date('Ymd', strtotime($time_current))) {

                $this->logAnyFile(__('RESET: score_day, player due to phone=%s', $player['Player']['phone']), $log_file_name);
                $reset_data[$key]['time_last_action'] = new MongoDate(strtotime($time_current));
                $reset_data[$key]['num_questions'] = [];
                $reset_data[$key]['question_group'] = null;
                $reset_data[$key]['count_group_aday'] = 0;
                $reset_data[$key]['num_questions_pending'] = null;
                $reset_data[$key]['score_day.score'] = 0;
                $reset_data[$key]['score_day.time'] = 0;

                $is_new_week = (date("w") == 1);
                $is_new_month = (date("d") == 1);

                if ($is_new_week) {

                    $this->logAnyFile(__('RESET: score_week, player due to phone=%s', $player['Player']['phone']), $log_file_name);
                    $reset_data[$key]['score_week.score'] = 0;
                    $reset_data[$key]['score_week.time'] = 0;
                }
                if ($is_new_month) {

                    $this->logAnyFile(__('RESET: score_month, player due to phone=%s', $player['Player']['phone']), $log_file_name);
                    $reset_data[$key]['score_month.score'] = 0;
                    $reset_data[$key]['score_month.time'] = 0;
                }
            }
        }

        if (!$this->Player->saveAll($reset_data)) {

            $this->logAnyFile(__('Can not reset charge status for these players:'), $log_file_name);
            $this->logAnyFile($reset_data, $log_file_name);
            return false;
        }

        $this->logAnyFile(__('Reset charge status successful!'), $log_file_name);
        return true;
    }

    /**
     * diameterCharge
     * Thực hiện charge hàng ngày
     */
    public function diameterCharge() {

        set_time_limit(0);

        $this->initResponseText();
        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $this->logAnyFile('START DiameterCharge...', $log_file_name);

        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
        $amount_g1 = Configure::read('sysconfig.ChargingVMS.G1_price');
        $amount_g7 = Configure::read('sysconfig.ChargingVMS.G7_price');

        $time_current = date('Y-m-d H:i:s');
//        $time_current = date('Y-m-d H:i:s', strtotime('+7 days'));

        $charge_pattern = 'charge_%s';
        $Charge = new Charge(array(
            'table' => sprintf($charge_pattern, date('Y_m_d', strtotime($time_current))),
        ));

        $mo_pattern = 'mo_%s';
        $Mo = new Charge(array(
            'table' => sprintf($mo_pattern, date('Y_m_d', strtotime($time_current))),
        ));

        // kiểm tra xem đây có phải là lần charge đầu tiên
        $is_first = $this->ChargeCrontabLog->isFirst($time_current);

        $page = 1;
        $limit = 20;
        while (true) {

            // nếu là lần charge đầu tiên trong ngày thì thực hiện lấy ra tất cả các player đã đăng ký
            if ($is_first) {

                $query = [
                    'conditions' => [
                        '$or' => array(
                            array(
                                'package_day.status' => 1,
                            ),
                            array(
                                'package_week.status' => 1,
                            ),
                            array(
                                'package_month.status' => 1,
                            ),
                        ),
                    ],
                    'limit' => $limit,
                    'page' => $page,
                    'order' => array(
                        '_id' => 'ASC',
                    ),
                ];
            }
            // nếu là lần charge thứ 2, lấy ra các player đã đăng ký và đồng thời có trạng thái charge chưa thành công
            else {

                $query = [
                    'conditions' => [
                        '$or' => array(
                            array(
                                'package_day.status' => 1, 'package_day.status_charge' => 0,
                            ),
                            array(
                                'package_week.status' => 1, 'package_week.status_charge' => 0,
                            ),
                            array(
                                'package_month.status' => 1, 'package_month.status_charge' => 0,
                            ),
                        ),
                    ],
                    'limit' => $limit,
                    'page' => $page,
                    'order' => array(
                        '_id' => 'ASC',
                    ),
                ];
            }

            $arr_player = $this->Player->find('all', $query);

//            $phones = Hash::extract($arr_player, '{n}.Player.phone');
//            debug($phones);

            $count_player = count($arr_player);
            if (empty($count_player)) {

                $this->logAnyFile(__("DiameterCharge completed, page=%s", $page), $log_file_name);

                // thực hiện log thời điểm charge hiện tại
                $this->ChargeCrontabLog->logTracking($time_current);
                exit();
            }

            // nếu là lần charge đầu tiên, thì thực hiện reset lại trạng thái charge
            if ($is_first) {

                $this->reset($arr_player, $log_file_name, $time_current);
            }

            foreach ($arr_player as $player) {

                $options = array(
                    'time_current' => $time_current,
                    'Mo' => $Mo,
                    'Charge' => $Charge,
                    'log_file_name' => $log_file_name,
                    'package' => '',
                    'amount' => 0,
                );

                // nếu là lần charge đầu tiên trong ngày
                if ($is_first) {

                    $package_day_conditions = ($player['Player']['package_day']['status'] == 1);
                    $package_week_conditions = ($player['Player']['package_week']['status'] == 1);
                }
                // nếu là lần charge thứ 2 trong ngày
                else {

                    $package_day_conditions = ($player['Player']['package_day']['status'] == 1 && $player['Player']['package_day']['status_charge'] == 0);
                    $package_week_conditions = ($player['Player']['package_week']['status'] == 1 && $player['Player']['package_week']['status_charge'] == 0);
                }

                $this->logAnyFile(__('ROUTER: player due to phone=%s begin route charge', $player['Player']['phone']), $log_file_name);

                // thực hiện charge tiền theo gói ngày
                if ($package_day_conditions) {

                    $options['package'] = 'G1';
                    $options['amount'] = $amount_g1;
                    $this->dailyCharge($player, $options);
                }

                // thực hiện charge tiền theo tuần
                if ($package_week_conditions) {

                    $options['package'] = 'G7';
                    $options['amount'] = $amount_g7;

                    // đọc lại thông tin player đã gia hạn theo daily
                    $player = $this->Player->getInfoByMobile($player['Player']['phone']);

                    // thực hiện cấp phát câu hỏi hàng ngày theo gói G7
                    $this->weeklyAllocate($player, $options);

                    // thực hiện charge gói G7, nếu đúng chu kỳ charge
                    $this->weeklyCharge($player, $options);
                }
            }

            $page++;
        }

        // thực hiện log thời điểm charge hiện tại
        $this->ChargeCrontabLog->logTracking($time_current);

        $this->logAnyFile('END DiameterCharge', $log_file_name);
        echo 'END DiameterCharge';
    }

    /**
     * dailyCharge
     * Thực hiện charge theo gói ngày G1
     * 
     * @param array $player
     * @param array $options
     */
    protected function dailyCharge($player, $options) {

        $package = $options['package'];
        //$amount = $options['amount']; //ungnv set giá = 0
        $amount = 0;
        $time_current = $options['time_current'];
        $log_file_name = $options['log_file_name'];
        $Charge = $options['Charge'];
        $Mo = $options['Mo'];
        $limit = Configure::read('sysconfig.Packages.' . $package . '_question_group');

        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');

        $phone = $player['Player']['phone'];
        $arr_player_update = [
            'id' => $player['Player']['id'],
        ];

        // kiểm tra xem player đã được charge đối với thời điểm hiện tại hay chưa?
        // xuất phát từ trường hợp: player hủy gói $package và thực hiện đk lại gói $package này trước thời điểm charge theo chu kỳ
        if ($this->isChargedAtCurrentTime($player, $package, $time_current)) {

            $this->logAnyFile(__('WARNING: %s have been charged for %s package (%s)', $phone, $package, $amount), $log_file_name);
            return false;
        }

        $arr_charge = array(
            'phone' => $phone,
            'amount' => 0,
            'service_code' => $service_code,
            'trans_id' => '',
            'channel' => $player['Player']['channel'],
            'package' => $package,
            'action' => $this->arr_action['GIA_HAN'],
            'cp' => '',
            'cp_sharing' => 0,
            'status' => 2,
            'details' => array(),
        );

        $arr_mo = array(
            'phone' => $phone,
            'short_code' => '',
            'package' => $package,
            'package_day' => $player['Player']['package_day']['status'],
            'package_week' => $player['Player']['package_week']['status'],
            'package_month' => $player['Player']['package_month']['status'],
            'channel' => $player['Player']['channel'],
            'amount' => $amount,
            'content' => '',
            'action' => $this->arr_action['GIA_HAN'],
            'status' => 2,
            'details' => array(
                'package' => $package,
            ),
        );

        $this->setSharing($arr_charge, $player);
        $this->setSharing($arr_mo, $player);

        $this->logAnyFile(__('BEGIN: %s is being charge for %s package (%s)', $phone, $package, $amount), $log_file_name);

        $arr_charge['package'] = $package;
        $arr_charge['amount'] = $amount;

        // lưu lại kết qủa charge
        $is_charge = $this->Diameter->charge($phone, $amount, $arr_charge, array(
            'log_file_name' => $log_file_name,
        ));

        // đổ lại thông tin package_day, tránh mất fields khi lưu trường field 2 cấp
        $arr_player_update['package_day'] = $player['Player']['package_day'];

        // nếu charge tiền thành công
        if ($is_charge === true) {

            $this->logAnyFile(__('SUCCESS: %s was charged for %s package (%s)', $phone, $package, $amount), $log_file_name);

            $arr_mo['status'] = 1;

            //Nếu trên kênh WAP thì + 3 gói câu hỏi cho Player luôn, kênh SMS thì để khi nào gửi Câu hỏi hàng ngày sẽ + 3 gói
            if ($player['Player']['channel_play'] == 'WAP') {

                $this->logAnyFile(__('ALLOCATE: %s was allocated question group for %s package (%s)', $phone, $package, $amount), $log_file_name);

                $allocate_player = $this->QuestionGroup->allocate($player, $package, $limit, $log_file_name);
                $arr_player_update = Hash::merge($arr_player_update, $allocate_player);
            } else {

                $this->logAnyFile(__('DONT ALLOCATE: %s was allocated question group for %s package (%s), because channel_play != WAP', $phone, $package, $amount), $log_file_name);
            }

            $arr_player_update['package_day']['status_charge'] = 1;
            $arr_player_update['package_day']['time_effective'] = new MongoDate(strtotime($time_current));
        }
        // nếu không charge tiền thành công
        else {

            $this->logAnyFile(__('FAIL: %s was not charged for %s package (%s)', $phone, $package, $amount), $log_file_name);

            $arr_mo['status'] = 0;
            $arr_player_update['package_day']['status_charge'] = 0;
        }

        $this->Player->save($arr_player_update);

        $Mo->create();
        $Mo->save($arr_mo);

        $Charge->create();
        $Charge->save($arr_charge);
    }

    protected function weeklyAllocate($player, $options) {

        $package = $options['package'];
        $amount = $options['amount'];
        $time_current = $options['time_current'];
        $log_file_name = $options['log_file_name'];
        $limit = Configure::read('sysconfig.Packages.' . $package . '_question_group');

        $phone = $player['Player']['phone'];
        $arr_player_update = [
            'id' => $player['Player']['id'],
        ];

        // kiểm tra xem player đã được charge đối với thời điểm hiện tại hay chưa?
        // xuất phát từ trường hợp: player hủy gói $package và thực hiện đk lại gói $package này trước thời điểm charge theo chu kỳ
        if ($this->isChargedAtCurrentTime($player, $package, $time_current)) {

            $this->logAnyFile(__('WeeklyAllocate: %s have been charged for %s package (%s)', $phone, $package, $amount), $log_file_name);
            return false;
        }

        if (empty($player['Player']['package_week']['time_effective'])) {

            $this->logAnyFile(__('WeeklyAllocate: player due to phone=%s have a empty package_week.time_effective', $player['Player']['phone']), $log_file_name);
            return false;
        }

        $time_effective = $player['Player']['package_week']['time_effective'];

        // nếu thời gian sử dụng > thời gian hiện tại thì cấp phát câu hỏi theo ngày
        if (date('Ymd', $time_effective->sec) > date('Ymd', strtotime($time_current))) {

            //Nếu trên kênh WAP thì + 3 gói câu hỏi cho Player luôn, kênh SMS thì để khi nào gửi Câu hỏi hàng ngày sẽ + 3 gói
            if ($player['Player']['channel_play'] == 'WAP') {

                $this->logAnyFile(__('WeeklyAllocate: %s was allocated question group for %s package (%s)', $phone, $package, $amount), $log_file_name);

                $allocate_player = $this->QuestionGroup->allocate($player, $package, $limit, $log_file_name);
                $arr_player_update = Hash::merge($arr_player_update, $allocate_player);
            } else {

                $this->logAnyFile(__('WeeklyAllocate: %s was not allocated question group for %s package (%s), because channel_play != WAP', $phone, $package, $amount), $log_file_name);
            }

            $this->Player->save($arr_player_update);
        }
        // nếu thời gian sử dụng <= thời gian hiện tại, thì ngừng cấp phát câu hỏi theo ngày
        // cấp phát câu hỏi này sẽ dựa vào trạng thái của việc charge
        else {

            $this->logAnyFile(__('WeeklyAllocate: %s was not allocated question group for %s package (%s), because this time for Charge', $phone, $package, $amount), $log_file_name);
        }
    }

    /**
     * weeklyCharge
     * Thực hiện charge theo gói tuần G7
     * 
     * @param array $player
     * @param array $options
     * 
     * @return boolean
     */
    protected function weeklyCharge($player, $options) {

        $time_week_charge = date('Y-m-d', $player['Player']['package_week']['time_first_charge']->sec);
        $package = $options['package'];
        //$amount = $options['amount']; ungnv set giá = 0
        $amount = 0;
        $time_current = $options['time_current'];
        $log_file_name = $options['log_file_name'];
        $Charge = $options['Charge'];
        $Mo = $options['Mo'];
        $limit = Configure::read('sysconfig.Packages.' . $package . '_question_group');
        $phone = $player['Player']['phone'];
        $arr_player_update = [
            'id' => $player['Player']['id'],
        ];

        // nếu không phải ngày gia hạn weekly, thì chưa thực hiện trừ tiền
        if (!$this->isWeeklyCharge($time_week_charge, 7, $time_current)) {

            $this->logAnyFile(__('WARNING: %s have no need to charged for %s package (%s)', $phone, $package, $amount), $log_file_name);
            return false;
        }

        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');

        // kiểm tra xem player đã được charge đối với thời điểm hiện tại hay chưa?
        // xuất phát từ trường hợp: player hủy gói $package và thực hiện đk lại gói $package này trước thời điểm charge theo chu kỳ
        if ($this->isChargedAtCurrentTime($player, $package, $time_current)) {

            $this->logAnyFile(__('WARNING: %s have been charged for %s package (%s)', $phone, $package, $amount), $log_file_name);
            return false;
        }

        $arr_charge = array(
            'phone' => $phone,
            'amount' => 0,
            'service_code' => $service_code,
            'trans_id' => '',
            'channel' => $player['Player']['channel'],
            'package' => $package,
            'action' => $this->arr_action['GIA_HAN'],
            'cp' => '',
            'cp_sharing' => 0,
            'status' => 2,
            'details' => array(),
        );

        $arr_mo = array(
            'phone' => $phone,
            'short_code' => '',
            'package' => $package,
            'package_day' => $player['Player']['package_day']['status'],
            'package_week' => $player['Player']['package_week']['status'],
            'package_month' => $player['Player']['package_month']['status'],
            'channel' => $player['Player']['channel'],
            'amount' => $amount,
            'content' => '',
            'action' => $this->arr_action['GIA_HAN'],
            'status' => 2,
            'details' => array(
                'package' => $package,
            ),
        );

        $this->setSharing($arr_charge, $player);
        $this->setSharing($arr_mo, $player);

        $this->logAnyFile(__('BEGIN: %s is being charge for %s package (%s)', $phone, $package, $amount), $log_file_name);

        $arr_charge['package'] = $package;
        $arr_charge['amount'] = $amount;

        // lưu lại kết qủa charge
        $is_charge = $this->Diameter->charge($phone, $amount, $arr_charge, array(
            'log_file_name' => $log_file_name,
        ));

        $arr_player_update['package_week'] = $player['Player']['package_week'];

        // nếu charge tiền thành công
        if ($is_charge === true) {

            $this->logAnyFile(__('SUCCESS: %s was charged for %s package (%s)', $phone, $package, $amount), $log_file_name);

            $arr_mo['status'] = 1;

            //Nếu trên kênh WAP thì + 3 gói câu hỏi cho Player luôn, kênh SMS thì để khi nào gửi Câu hỏi hàng ngày sẽ + 3 gói
            if ($player['Player']['channel_play'] == 'WAP') {

                $this->logAnyFile(__('ALLOCATE: %s was allocated question group for %s package (%s)', $phone, $package, $amount), $log_file_name);

                $allocate_player = $this->QuestionGroup->allocate($player, $package, $limit, $log_file_name);
                $arr_player_update = Hash::merge($arr_player_update, $allocate_player);
            } else {

                $this->logAnyFile(__('DONT ALLOCATE: %s was allocated question group for %s package (%s), because channel_play != WAP', $phone, $package, $amount), $log_file_name);
            }

            $arr_player_update['package_week']['status_charge'] = 1;
            $arr_player_update['package_week']['time_effective'] = new MongoDate(strtotime('+7 days', strtotime($time_current)));
        }
        // nếu không charge tiền thành công
        else {

            $this->logAnyFile(__('FAIL: %s was not charged for %s package (%s)', $phone, $package, $amount), $log_file_name);

            $arr_mo['status'] = 0;
            $arr_player_update['package_week']['status_charge'] = 0;
        }

        $this->Player->save($arr_player_update);

        $Mo->create();
        $Mo->save($arr_mo);

        $Charge->create();
        $Charge->save($arr_charge);
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

    /**
     * isWeeklyCharge
     * Kiểm tra ngày charge có phải là ngày charge theo tuần đối với player
     * 
     * @param string $time_charge
     * @param int $period_day
     * @param string $time_current
     * @return boolean
     */
    protected function isWeeklyCharge($time_charge, $period_day = 7, $time_current = null) {

        if (empty($time_current)) {

            $time_current = date('Y-m-d');
        }

        $date1 = new DateTime(date('Y-m-d', strtotime($time_charge)));
        $date2 = new DateTime(date('Y-m-d', strtotime($time_current)));
        $interval = $date1->diff($date2);

        // số ngày chênh lệch
        $days = $interval->days;

        // nếu số ngày chênh lệch đúng theo chu kỳ
        if ($days % $period_day == 0) {

            return true;
        }

        return false;
    }

    /**
     * isChargedAtCurrentTime
     * kiểm tra xem player đã được charge đối với thời điểm hiện tại hay chưa?
     * xuất phát từ trường hợp: player hủy gói $package và thực hiện đk lại gói $package này trước thời điểm charge theo chu kỳ
     * 
     * @param array $player
     * @param string $package
     * @param string $time_current
     * 
     * @return boolean
     */
    protected function isChargedAtCurrentTime($player, $package, $time_current) {

        $package_alias = $this->getPackageAlias($package);
        if (
                $player['Player'][$package_alias]['status_charge'] == 1 &&
                !empty($player['Player'][$package_alias]['time_register']) &&
                date('Ymd', $player['Player'][$package_alias]['time_register']->sec) == date('Ymd', strtotime($time_current))
        ) {

            return true;
        }

        return false;
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

            $distribution_channel = $this->DistributionChannel->find('first', array(
                'conditions' => array(
                    'code' => $distribution_channel_code,
                ),
            ));
            $distribution_channel_sharing = !empty($distribution_channel) ?
                    $distribution_channel['DistributionChannel']['sharing'] : '';
        }
        if (!empty($distributor_code)) {

            $distributor = $this->Distributor->find('first', array(
                'conditions' => array(
                    'code' => $distributor_code,
                ),
            ));
            $distributor_sharing = !empty($distributor) ?
                    $distributor['Distributor']['sharing'] : '';
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
        }

        return $package_alias;
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
    }

}
