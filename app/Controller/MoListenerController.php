<?php

App::uses('AppQuizController', 'Controller');

class MoListenerController extends AppQuizController {

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
    }

    public $uses = array(
        'Configuration',
        'Visitor',
        'Player',
        'QuestionGroup',
        'Counter',
        'QuestionCategory',
        'Mo',
        'Mt',
        'Score',
        'ScoreDay',
        'Charge',
        'MoDk',
        'Distributor',
        'DistributionChannel',
    );
    public $components = array(
        'MobifoneCommon',
    );

    public function index() {

        $this->logAnyFile("================ START MoListener", __CLASS__ . '_' . __FUNCTION__);
        $this->initResponseText();

        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
        $username = $this->request->query("username");
        $password = $this->request->query("password");
        $phone = $this->request->query("phone");
        $short_code = $this->request->query("short_code");
        $content = strtoupper(trim($this->request->query("content")));

        // thực hiện cô lập hệ thống
        $blackbox = Configure::read('sysconfig.App.blackbox');
        if (!empty($blackbox)) {

            // nếu số thuê bao không thuộc blackbox
            if (!in_array($phone, $blackbox)) {

                $this->logAnyFile(__('End: because phone "%s" is out blackbox', $phone), __CLASS__ . '_' . __FUNCTION__);
                die();
            }
        }

        $result = $this->alalysisSyntax($short_code, $phone, $content);

        $this->logAnyFile("================ END MoListener with $short_code, $phone, $content result($result)", __CLASS__ . '_' . __FUNCTION__);
        return $result;
    }

    private function alalysisSyntax($short_code, $phone, $content) {

        // thực hiện giới hạn nếu thuê bao thuộc blacklist
        $this->limitBlacklist($short_code, $phone, $content);

        $arr_mo = [
            'phone' => $phone,
            'short_code' => $short_code,
            'package_day' => 0,
            'package_week' => 0,
            'package_month' => 0,
            'channel' => "SMS",
            'amount' => 0,
            'content' => $content,
            'note' => "",
            'action' => $this->arr_action['KHAC'],
            'details' => array(),
            'status' => 1,
            //unv
            'distributor_code' => "",
            'distribution_channel_code' => "",
            'distributor_sharing' => "",
            'distribution_channel_sharing' => ""
                //eunv
        ];

        $result = "1";
        $content = str_replace(" ", "", $content);
        $this->logAnyFile("================ START alalysisSyntax($short_code, $phone, str_replace(content): $content)", __CLASS__ . '_' . __FUNCTION__);
        switch ($content) {
            case 'DKG1':
                $arr_mo['action'] = $this->arr_action['DANG_KY'];
                $arr_mo['package_day'] = 1;
                $arr_mo['package'] = 'G1';
                $result = $this->processingCmdDK($arr_mo);
                break;
            case 'DKG7':
                $arr_mo['action'] = $this->arr_action['DANG_KY'];
                $arr_mo['package_week'] = 1;
                $arr_mo['package'] = 'G7';
                $result = $this->processingCmdDK($arr_mo);
                break;
            case 'HUYG1':
                $arr_mo['action'] = $this->arr_action['HUY'];
                $arr_mo['package_day'] = 1;
                $arr_mo['package'] = 'G1';
                $result = $this->processingCmdHUY($arr_mo);
                break;
            case 'HUYG7':
                $arr_mo['action'] = $this->arr_action['HUY'];
                $arr_mo['package_week'] = 1;
                $arr_mo['package'] = 'G7';
                $result = $this->processingCmdHUY($arr_mo);
                break;
            case 'DKPAN':
                $arr_mo['action'] = $this->arr_action['MUA'];
                $arr_mo['package'] = 'MUA';
                $result = $this->processingCmdMUA($arr_mo);
                break;
            //ungnv(bo sung cu phap theo yeu cau kinh doanh - 25/12/2015)
            case 'DKGAME':
                $arr_mo['action'] = $this->arr_action['MUA'];
                $arr_mo['package'] = 'MUA';
                $result = $this->processingCmdMUA($arr_mo);
                break;
            case 'CHOI':
                $arr_mo['action'] = $this->arr_action['CHOI'];
                $result = $this->processingCmdCHOI($arr_mo);
                break;
            case '1':
            case '2':
                $arr_mo['action'] = $this->arr_action['TRA_LOI'];
                $result = $this->processingCmdTRALOI($arr_mo);
                break;
            case 'TIEP':
                $arr_mo['action'] = $this->arr_action['BO_QUA'];
                $result = $this->processingCmdTIEP($arr_mo);
                break;
            case 'CHUYEN':
                $arr_mo['action'] = $this->arr_action['CHUYEN'];
                $result = $this->processingCmdCHUYEN($arr_mo);
                break;
            case 'HDPAN':
                $arr_mo['action'] = $this->arr_action['HUONG_DAN'];
                $result = $this->processingCmdHD($arr_mo);
                break;
            //ungnv(bo sung cu phap theo yeu cau kinh doanh - 25/12/2015)
            case 'HDGAME':
                $arr_mo['action'] = $this->arr_action['HUONG_DAN'];
                $result = $this->processingCmdHD($arr_mo);
                break;
            case 'KQPAN':
                $arr_mo['action'] = $this->arr_action['XEM_KET_QUA'];
                $result = $this->processingCmdKQ($arr_mo);
                break;
            //ungnv(bo sung cu phap theo yeu cau kinh doanh - 25/12/2015)
            case 'KQGAME':
                $arr_mo['action'] = $this->arr_action['XEM_KET_QUA'];
                $result = $this->processingCmdKQ($arr_mo);
                break;

            default:
                $arr_mo['action'] = $this->arr_action['KHAC'];
                $result = $this->processingCmdDefault($arr_mo);
                break;
        }
        $this->logAnyFile("================ END alalysisSyntax($short_code, $phone, str_replace(content): $content)", __CLASS__ . '_' . __FUNCTION__);

        return $result;
    }

    const KEY_TIEN = "[TIEN]";
    const KEY_TIME_PHUT = "[TIME_P]";
    const KEY_TIME_GIAY = "[TIME_G]";

    private function processingCmdDK($arr_mo) {

        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];
        $content = $arr_mo['content'];
        $this->logAnyFile("================ START processingCmdDK($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        $arr_player_status = Configure::read('sysconfig.Players.STATUS');

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
        $player = $this->Player->getInfoByMobile($phone);

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,
        ];
        $denied_send_mt = false; //unv
        $this->arr_player_status = Configure::read('sysconfig.Players.STATUS');
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
        $amount_g1 = Configure::read('sysconfig.ChargingVMS.G1_price');
        $amount_g7 = Configure::read('sysconfig.ChargingVMS.G7_price');
        $RESULT_CHARGE_OK = Configure::read('sysconfig.ChargingVMS.RESULT_CHARGE_OK');
        $reward_first_register = Configure::read('sysconfig.Packages.reward_first_register');
        $arr_charge = array(
            'phone' => $phone,
            'amount' => 0,
            'service_code' => $service_code,
            'trans_id' => '',
            'channel' => 'SMS',
            'package' => $arr_mo['package'],
            'action' => $arr_mo['action'],
            'note' => '',
            'status' => 0,
            'details' => array(),
            //unv
            'distributor_code' => "",
            'distribution_channel_code' => "",
            'distributor_sharing' => "",
            'distribution_channel_sharing' => ""
                //eunv
        );
        $mt_content = "";
        //chưa sử dụng dịch vụ
        if (empty($player)) {

            $this->logAnyFile("$phone Chua dang ky", __CLASS__ . '_' . __FUNCTION__);

            $reward_score = Configure::read('sysconfig.Packages.reward_first_register.score');
            $arr_player = [
                'visitor' => null,
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
                'channel' => 'SMS',
                'channel_play' => 'SMS',
                'status' => $arr_player_status['REGISTER'],
                'status_play' => 0,
                'time_register' => new MongoDate(),
                'time_register_first' => new MongoDate(),
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
            ];
            $arr_mo['amount'] = 0;
            $arr_mo['status'] = 1;
            $arr_mo['note'] = 'Đăng ký lần đầu';
            $arr_mo['details']['is_register_first'] = 1;

            $arr_charge['amount'] = 0;
            $arr_charge['status'] = 1;
            $arr_charge['note'] = 'Đăng ký lần đầu';
            $arr_charge['details']['is_register_first'] = 1;

            $arr_player['time_register_first'] = new MongoDate();
//            $arr_player['time_charge'] = new MongoDate();
//            $arr_player['time_last_charge'] = new MongoDate();
            $arr_player['score_total']['score'] = $reward_score;
            $arr_player['score_day']['score'] = $reward_score;
            $arr_player['score_week']['score'] = $reward_score;
            $arr_player['score_month']['score'] = $reward_score;

            //INSERT CHARGE     
            if ($arr_mo['package_day'] == 1) {

                $arr_group_all = $this->QuestionGroup->getArrGroupPackageOthers($reward_first_register['question_group'], null, 'G1');
                $arr_group_package = $arr_group_all['arr_group_package'];
                $arr_group_id = $arr_group_all['arr_group_id'];

                $arr_player['package_day'] = [
                    "status" => 1,
                    'status_charge' => 1,
                    'retry' => 0,
                    'time_send_question' => new MongoDate(),
                    'time_register' => new MongoDate(),
                    'time_first_charge' => $this->MobifoneCommon->getBeginOfMongoDate(strtotime('+1 day')),
                    'time_charge' => $this->MobifoneCommon->getBeginOfMongoDate(strtotime('+1 day')),
                    'time_effective' => $this->MobifoneCommon->getTimeEffective('G1'),
                    'time_deactive' => null,
                    'time_last_renew' => null,
                    'time_last_telco_deactive' => null,
                    'modified' => new MongoDate(),
                    'created' => new MongoDate(),
                ];
                $arr_player['num_questions'] = $arr_group_package;
                $arr_player['answered_groups'] = $arr_group_id;
                $arr_player['count_group_aday'] = count($arr_group_id);
                $arr_charge['package'] = "G1";
                $arr_mo['package'] = "G1";
            }

            if ($arr_mo['package_week'] == 1) {

                $arr_group_all = $this->QuestionGroup->getArrGroupPackageOthers($reward_first_register['question_group'], null, 'G7');
                $arr_group_package = $arr_group_all['arr_group_package'];
                $arr_group_id = $arr_group_all['arr_group_id'];

                $arr_player['package_week'] = [
                    "status" => 1,
                    'status_charge' => 1,
                    'retry' => 0,
                    'time_send_question' => new MongoDate(),
                    'time_register' => new MongoDate(),
                    'time_first_charge' => $this->MobifoneCommon->getBeginOfMongoDate(strtotime('+1 day')),
                    'time_charge' => $this->MobifoneCommon->getBeginOfMongoDate(strtotime('+1 day')),
                    'time_effective' => $this->MobifoneCommon->getTimeEffective('G7'),
                    'time_deactive' => null,
                    'time_last_renew' => null,
                    'time_last_telco_deactive' => null,
                    'modified' => new MongoDate(),
                    'created' => new MongoDate(),
                ];
                $arr_player['num_questions'] = $arr_group_package;
                $arr_player['answered_groups'] = $arr_group_id;
                $arr_player['count_group_aday'] = count($arr_group_id);
                $arr_charge['package'] = "G7";
                $arr_mo['package'] = "G7";
            }
            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M06_RegisterSuccessFirst');
            $arr_mt['content'] = $mt_content;
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M06_RegisterSuccessFirst');
            //eunv

            $visitor = $this->Visitor->addNew($phone);
            $arr_player['visitor'] = new MongoId($visitor['Visitor']['id']);
            $this->Player->save($arr_player);

            //unv
            //$this->generate_distributor_data($arr_charge,$player);//bỏ do empty($player) -> ko có dữ liệu distributor
            //eunv
            $this->Charge->insert($arr_charge);
        }
        // Trường hợp đăng ký không phải đăng ký lần đầu tiên
        else {
            $this->logAnyFile("$phone Da HUY-> dang ky lai", __CLASS__ . '_' . __FUNCTION__);

            $player = $player['Player'];
            $charge_emu = !empty($player['charge_emu']) ? $player['charge_emu'] : array();

            $arr_player_update = [
                'id' => $player['id'],
                'package_day' => $player['package_day'],
                'package_week' => $player['package_week'],
                'package_month' => $player['package_month'],
                'channel' => 'SMS',
                'channel_play' => 'SMS',
                'status' => $arr_player_status['REGISTER'],
//                'time_register' => new MongoDate(),
                'time_last_action' => new MongoDate(),
                'count_group_aday' => $player['count_group_aday'],
            ];
            // nếu là đăng ký gói ngày
            if ($arr_mo['package_day'] == 1) {

                $arr_mo['amount'] = $amount_g1;

                // thiết lập package
                $arr_mo['package'] = 'G1';
                $arr_charge['package'] = 'G1';

                // nếu đã đăng ký gói ngày rồi thì trả về mt thông báo
                if ($player['package_day']['status'] == 1) {

                    $this->logAnyFile("$phone Da dang ky G1 roi", __CLASS__ . '_' . __FUNCTION__);
                    $arr_charge['note'] = "Đã đăng ký";

                    $arr_mo['status'] = 0;
                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M03_Registed');
                    $arr_mt['content'] = $mt_content;
                    //unv
                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M03_Registed');
                    //eunv
                }

                //unv đăng ký G1 khi đang dùng G7
                elseif ($player['package_week']['status'] == 1) {

                    $this->logAnyFile("$phone dang ky G1 khi dang dung G7", __CLASS__ . '_' . __FUNCTION__);
                    $arr_charge['note'] = "Đăng ký G1 khi đang dùng G7";

                    // thiết lập package
                    $arr_mo['package'] = 'G7';
                    $arr_charge['package'] = 'G7';

                    $arr_mo['status'] = 0;
                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M22_Registed');
                    $arr_mt['content'] = $mt_content;

                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M22_Registed');
                }
                //eunv
                // nếu chưa đăng ký sử dụng bất kỳ gói package nào
                else {

                    // lưu lại thời điểm trước khi mang đi charge cước
                    $arr_player_update['time_charge'] = new MongoDate();

                    $arr_mo['package'] = "G1";
                    $arr_charge['package'] = "G1";
                    $arr_charge['amount'] = $amount_g1;

                    $charge_result = $this->MobifoneCommon->charge($phone, $amount_g1, array(
                        'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
                    ));
                    $arr_charge['details'] = $charge_result;

                    $this->logAnyFile("CHARGE_OK($RESULT_CHARGE_OK)", __CLASS__ . '_' . __FUNCTION__);
                    $this->logAnyFile("result(" . $charge_result['pretty_message'] . ")", __CLASS__ . '_' . __FUNCTION__);

                    // lấy lại dữ liệu trong package_day
                    $arr_player_update['package_day'] = $player['package_day'];

                    // nếu charge cước thành công
                    if ($charge_result['status'] == 1) {

                        // lưu lại thời điểm đăng ký và thời điểm charge thành công
                        $arr_player_update['time_register'] = new MongoDate();
                        $arr_player_update['time_last_charge'] = new MongoDate();

                        //ĐK lại lần đầu tiên trong ngày mới được cấp bộ câu hỏi
                        if (!empty($player['package_day']['time_register']->sec) && date('Ymd') > date('Ymd', $player['package_day']['time_register']->sec)) {

                            $this->logAnyFile("Đăng ký rồi -> Hủy -> Đăng ký lại G1 lần đầu tiên trong ngày -> cấp 3 gói câu hỏi", __CLASS__ . '_' . __FUNCTION__);
                            $arr_group_all = $this->QuestionGroup->getArrGroupPackageOthers($reward_first_register['question_group'], null, 'G1');
                            $arr_group_package = $arr_group_all['arr_group_package'];
                            $arr_group_id = $arr_group_all['arr_group_id'];

                            $arr_player_update['num_questions'] = array_merge($player['num_questions'], $arr_group_package);
                            $arr_player_update['answered_groups'] = array_merge($player['answered_groups'], $arr_group_id);
                            $arr_player_update['count_group_aday'] = $player['count_group_aday'] + count($arr_group_id);
                        }

                        // cập nhật trạng thái của gói package
                        $arr_player_update['package_day']['status'] = 1;
                        $arr_player_update['package_day']['status_charge'] = 1;
                        $arr_player_update['package_day']['time_send_question'] = new MongoDate();
                        $arr_player_update['package_day']['time_register'] = new MongoDate();
                        $arr_player_update['package_day']['time_charge'] = $this->MobifoneCommon->getTimeCharge('G1', time());
                        $arr_player_update['package_day']['time_first_charge'] = $this->MobifoneCommon->getTimeCharge('G1', time());
                        $arr_player_update['package_day']['time_effective'] = $this->MobifoneCommon->getTimeEffective('G1', time(), $charge_emu);
                        $arr_player_update['package_day']['modified'] = new MongoDate();

                        $arr_charge['status'] = 1;

                        if (!empty($player['package_day']['time_register']->sec) && date('Ymd') == date('Ymd', $player['package_day']['time_register']->sec)) {

                            $this->logAnyFile("Đăng ký rồi -> Hủy -> Đăng ký lại G1 trong ngày -> trả lại các gói câu hỏi Pending...", __CLASS__ . '_' . __FUNCTION__);
                            $arr_player_update['num_questions_pending']['package_day'] = $player['num_questions_pending']['package_day'];
                            if (!empty($player['num_questions_pending']['package_day']['groups'])) {
                                $this->logAnyFile("Chuyển các gói câu hỏi Pending: " . count($player['num_questions_pending']['package_day']['groups']), __CLASS__ . '_' . __FUNCTION__);

                                $arr_player_update['num_questions'] = array_merge($player['num_questions'], $player['num_questions_pending']['package_day']['groups']);
                                $arr_player_update['count_group_aday'] = $player['count_group_aday'] + count($player['num_questions_pending']['package_day']['groups']);
                                $arr_player_update['num_questions_pending']['package_day']['groups'] = null;
                            }
                            if (empty($player['question_group']) && !empty($player['num_questions_pending']['package_day']['question_group'])) {

                                $this->logAnyFile("$phone Chưa có gói nào, lấy gói Pending cho Player", __CLASS__ . '_' . __FUNCTION__);

                                $arr_player_update['question_group'] = $player['num_questions_pending']['package_day']['question_group'];
                                $arr_player_update['num_questions_pending']['package_day']['question_group'] = null;
                            }
                        }

                        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M07_RegisterSuccessSecond');
                        $arr_mt['content'] = $mt_content;
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M07_RegisterSuccessSecond');
                        //eunv

                        $this->logAnyFile("$phone ReCharge G1 result successful: " . $charge_result['pretty_message'], __CLASS__ . '_' . __FUNCTION__);
                    }
                    // nếu charge cước không thành công
                    else {

                        // cập nhật trạng thái của package
                        $arr_player_update['package_day']['status'] = 0;
                        $arr_player_update['package_day']['status_charge'] = 0;
                        $arr_player_update['package_day']['modified'] = new MongoDate();

                        $arr_charge['status'] = 0;
                        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M13_BuyFailue');
                        $mt_content = str_replace(self::KEY_TIEN, Configure::read('sysconfig.Packages.day'), $mt_content);
                        $arr_mt['content'] = $mt_content;
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M13_BuyFailue');
                        //eunv

                        $this->logAnyFile("$phone ReCharge G1 result failue: " . $charge_result['pretty_message'], __CLASS__ . '_' . __FUNCTION__);
                    }
                }
            }

            // nếu đăng ký gói tuần
            if ($arr_mo['package_week'] == 1) {

                // thiết lập package
                $arr_mo['package'] = 'G7';
                $arr_charge['package'] = 'G7';

                $arr_mo['amount'] = $amount_g7;
                // nếu đã đăng ký gói package thì trả về package
                if ($player['package_week']['status'] == 1) {

                    $this->logAnyFile("$phone Da dang ky G7 roi", __CLASS__ . '_' . __FUNCTION__);
                    $arr_charge['note'] = "Đã đăng ký";

                    $arr_mo['status'] = 0;
                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M03_Registed');
                    $arr_mt['content'] = $mt_content;
                    //unv
                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M03_Registed');
                    //eunv
                }
                //unv đăng ký G7 khi đang dùng G1
                elseif ($player['package_day']['status'] == 1) {

                    $this->logAnyFile("$phone dang ky G7 khi dang dung G1", __CLASS__ . '_' . __FUNCTION__);
                    $arr_charge['note'] = "Đăng ký G7 khi đang dùng G1";

                    $arr_mo['status'] = 0;
                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M22_Registed');
                    $arr_mt['content'] = $mt_content;

                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M22_Registed');
                }
                //eunv
                // thực hiện đăng ký 
                else {

                    // lưu lại thời điểm trước khi mang đi charge cước
                    $arr_player_update['time_charge'] = new MongoDate();

                    $arr_charge['package'] = "G7";
                    $arr_charge['amount'] = $amount_g7;

                    $charge_result = $this->MobifoneCommon->charge($phone, $amount_g7, array(
                        'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
                    ));
                    $arr_charge['details'] = $charge_result;

                    $log_content = "CHARGE_OK($RESULT_CHARGE_OK), result(" . $charge_result['pretty_message'] . ")";
                    $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

                    // lấy lại dữ liệu trong package_day
                    $arr_player_update['package_week'] = $player['package_week'];

                    // nếu charge cước thành công
                    if ($charge_result['status'] == 1) {

                        // lưu lại thời điểm đăng ký và thời điểm charge thành công
                        $arr_player_update['time_register'] = new MongoDate();
                        $arr_player_update['time_last_charge'] = new MongoDate();

                        //ĐK lại lần đầu tiên trong ngày mới được cấp bộ câu hỏi
                        if (!empty($player['package_day']['time_register']->sec) && date('Ymd') > date('Ymd', $player['package_week']['time_register']->sec)) {

                            $this->logAnyFile("Đăng ký rồi -> Hủy -> Đăng ký lại G7 lần đầu tiên trong ngày -> cấp 3 gói câu hỏi", __CLASS__ . '_' . __FUNCTION__);
                            $arr_group_all = $this->QuestionGroup->getArrGroupPackageOthers($reward_first_register['question_group'], null, 'G7');
                            $arr_group_package = $arr_group_all['arr_group_package'];
                            $arr_group_id = $arr_group_all['arr_group_id'];

                            $arr_player_update['num_questions'] = array_merge($player['num_questions'], $arr_group_package);
                            $arr_player_update['answered_groups'] = array_merge($player['answered_groups'], $arr_group_id);
                            $arr_player_update['count_group_aday'] = $player['count_group_aday'] + count($arr_group_id);
                        }

                        // cập nhật trạng thái của gói package
                        $arr_player_update['package_week']['status'] = 1;
                        $arr_player_update['package_week']['status_charge'] = 1;
                        $arr_player_update['package_week']['time_send_question'] = new MongoDate();
                        $arr_player_update['package_week']['time_register'] = new MongoDate();
                        $arr_player_update['package_week']['time_charge'] = $this->MobifoneCommon->getTimeCharge('G7', time());
                        $arr_player_update['package_week']['time_first_charge'] = $this->MobifoneCommon->getTimeCharge('G7', time());
                        $arr_player_update['package_week']['time_effective'] = $this->MobifoneCommon->getTimeEffective('G7', time(), $charge_emu);
                        $arr_player_update['package_week']['modified'] = new MongoDate();

                        $arr_charge['status'] = 1;

                        if (!empty($player['package_day']['time_register']->sec) && date('Ymd') == date('Ymd', $player['package_week']['time_register']->sec)) {

                            $this->logAnyFile("Đăng ký rồi -> Hủy -> Đăng ký lại G7 trong ngày -> trả lại các gói câu hỏi Pending...", __CLASS__ . '_' . __FUNCTION__);
                            $arr_player_update['num_questions_pending']['package_week'] = $player['num_questions_pending']['package_week'];

                            if (!empty($player['num_questions_pending']['package_week']['groups'])) {
                                $this->logAnyFile("Chuyển các gói câu hỏi Pending: " . count($player['num_questions_pending']['package_week']['groups']), __CLASS__ . '_' . __FUNCTION__);

                                $arr_player_update['num_questions'] = array_merge($player['num_questions'], $player['num_questions_pending']['package_week']['groups']);
                                $arr_player_update['count_group_aday'] = $player['count_group_aday'] + count($player['num_questions_pending']['package_week']['groups']);
                                $arr_player_update['num_questions_pending']['package_week']['groups'] = null;
                            }
                            if (empty($player['question_group']) && !empty($player['num_questions_pending']['package_week']['question_group'])) {
                                $this->logAnyFile("$phone Chưa có gói nào, lấy gói Pending cho Player", __CLASS__ . '_' . __FUNCTION__);

                                $arr_player_update['question_group'] = $player['num_questions_pending']['package_week']['question_group'];
                                $arr_player_update['num_questions_pending']['package_week']['question_group'] = null;
                            }
                        }

                        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M07_RegisterSuccessSecond');
                        $arr_mt['content'] = $mt_content;
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M07_RegisterSuccessSecond');
                        //eunv

                        $this->logAnyFile("$phone Charge G7 result successful: " . $charge_result['pretty_message'], __CLASS__ . '_' . __FUNCTION__);
                    }
                    // nếu không thực hiện charge cước thành công
                    else {

                        // cập nhật trạng thái của package
                        $arr_player_update['package_day']['status'] = 0;
                        $arr_player_update['package_day']['status_charge'] = 0;
                        $arr_player_update['package_day']['modified'] = new MongoDate();

                        $arr_charge['status'] = 0;
                        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M13_BuyFailue');
                        $mt_content = str_replace(self::KEY_TIEN, Configure::read('sysconfig.Packages.week'), $mt_content);
                        $arr_mt['content'] = $mt_content;
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M13_BuyFailue');
                        //eunv

                        $this->logAnyFile("$phone Charge G7 result failue: " . $charge_result['pretty_message'], __CLASS__ . '_' . __FUNCTION__);
                    }
                }
            }

            $this->Player->save($arr_player_update);
            //unv
            $this->generate_distributor_data($arr_charge, $player);
            //eunv

            $this->Charge->insert($arr_charge);
        }

        if (empty($mt_content)) {
            $mt_content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $mt_content", __CLASS__ . '_' . __FUNCTION__);
        }

        //unv
        if (!empty($player)) {

            $this->generate_distributor_data($arr_mo, $player);
        }
        //eunv

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));

        //unv
        $arr_modk = array();
        $arr_modk['mo_id'] = $arr_mt['mo_id'];
        $arr_modk = array_merge($arr_modk, $arr_mo);
        $arr_modk['package'] = $this->arr_action['DANG_KY'];
        $this->MoDk->insert($arr_modk);

        //eunv
        $arr_mt['content'] = $mt_content;

        //unv
        if ($denied_send_mt == false) {

            $this->logAnyFile('gui', __CLASS__ . '_' . __FUNCTION__);
            $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $this->logAnyFile('ko gui', __CLASS__ . '_' . __FUNCTION__);
            $arr_mt['status'] = 2;
        }
        //eunv

        /* $result = $this->sendmt($phone, $mt_content);
          $arr_mt['note'] = $result; */
        $this->Mt->insert($arr_mt);

        $log_content = "================ END processingCmdDK($short_code, $phone, content: $content)";
        $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    private function processingCmdHUY($arr_mo) {

        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];
        $content = $arr_mo['content'];
        $this->logAnyFile("================ START processingCmdHUY($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        $arr_player_status = Configure::read('sysconfig.Players.STATUS');

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
        $player = $this->Player->getInfoByMobile($phone);

        // thêm lưu vào charge khi hủy
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
        $arr_charge = [
            'phone' => $phone,
            'distributor_code' => !empty($player['Player']['distributor_code']) ?
                    $player['Player']['distributor_code'] : '',
            'distribution_channel_code' => !empty($player['Player']['distribution_channel_code']) ?
                    $player['Player']['distribution_channel_code'] : '',
            'distributor_sharing' => '',
            'distribution_channel_sharing' => '',
            'service_code' => $service_code,
            'trans_id' => '',
            'amount' => $arr_mo['amount'],
            'channel' => $arr_mo['channel'],
            'package' => $arr_mo['package'],
            'action' => $arr_mo['action'],
            'status' => 0,
        ];

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,
        ];
        $denied_send_mt = false; //unv
        $this->arr_player_status = Configure::read('sysconfig.Players.STATUS');
        $mt_content = "";
        // nếu thuê bao không tồn tại hoặc chưa đăng ký thì trả mt về
        if (empty($player) || ($player['Player']['package_day']['status'] == 0 && $player['Player']['package_week']['status'] == 0)) {

            $this->logAnyFile("$phone Chua dang ky", __CLASS__ . '_' . __FUNCTION__);

            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M02_NotRegister');
            $arr_mt['content'] = $mt_content;
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M02_NotRegister');
            //eunv
            // thực hiện set trạng thái của Mo là không thành công
            $arr_mo['status'] = 0;
        }
        // nếu thuê bao đã tồn tại
        else {

            $this->logAnyFile("$phone Da ton tai trong he thong.", __CLASS__ . '_' . __FUNCTION__);

            $player = $player['Player'];

            $arr_player_update = [
                'id' => $player['id'],
                'status' => $arr_player_status['CANCEL'],
                'package_day' => $player['package_day'],
                'package_week' => $player['package_week'],
                'time_deactive' => new MongoDate(), // Thời gian thuê bao hủy cuối cùng
                'time_last_self_deactive' => new MongoDate(), // Thời gian thuê bao tự hủy cuối cùng
                'distributor_code' => '',
                'distribution_channel_code' => '',
            ];

            // nếu hủy gói ngày
            if ($arr_mo['package_day'] == 1) {

                // nếu thuê bao chưa được đăng ký, gửi mt về
                if ($player['package_day']['status'] == 0) {

                    $this->logAnyFile("$phone Chua dang ky G1", __CLASS__ . '_' . __FUNCTION__);

                    $arr_mo['status'] = 0;
                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M02_NotRegister');
                    $arr_mt['content'] = $mt_content;
                    //unv
                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M02_NotRegister');
                    //eunv
                }
                // nếu thuê bao đã đăng ký, thực hiện hủy
                else {

                    // thực hiện các cờ trạng thái thời gian
                    $arr_player_update['package_day']['status'] = 0;
                    $arr_player_update['package_day']['time_deactive'] = new MongoDate();
                    $arr_player_update['package_day']['modified'] = new MongoDate();
                    $arr_player_update['time_deactive'] = new MongoDate();
                    $arr_player_update['time_last_self_deactive'] = new MongoDate();

                    // thực hiện xóa đi thời điểm mang đi gia hạn
                    $arr_player_update['package_day']['time_charge'] = null;

                    $arr_group_package = $this->QuestionGroup->splitArrGroupIdByPackage($player['num_questions'], "G1");
                    $arr_group_id = $arr_group_package['arr_group_id'];
                    $arr_group_new = $arr_group_package['arr_group_new'];

                    $package_week = !empty($player['num_questions_pending']['package_week']) ? $player['num_questions_pending']['package_week'] : null;
                    $arr_player_update['num_questions_pending'] = [
                        'package_day' => null,
                        'package_week' => $package_week,
                    ];
                    $arr_player_update['num_questions_pending']['package_day'] = [
                        'question_group' => null,
                        'groups' => $arr_group_id,
                    ];

                    if (!empty($player['question_group']) && $player['question_group']['package'] == 'G1') {

                        $arr_player_update['num_questions_pending']['package_day']['question_group'] = $player['question_group'];
                        $arr_player_update['question_group'] = null;
                    } else {

                        $arr_player_update['question_group'] = $player['question_group'];
                    }

                    $arr_player_update['count_group_aday'] = $player['count_group_aday'] - count($arr_group_id);
                    $arr_player_update['num_questions'] = $arr_group_new;

                    //HỦY G1 XONG THÌ KIỂM TRA XEM CÒN CÂU HỎI Ở GÓI G7 KHÔNG THÌ TRẢ VỀ
                    if ($player['package_week']['status'] == 1) {
                        $this->logAnyFile("HỦY G1 XONG THÌ KIỂM TRA XEM CÒN CÂU HỎI Ở GÓI G7 KHÔNG THÌ TRẢ VỀ", __CLASS__ . '_' . __FUNCTION__);
                        if (!empty($arr_player_update['question_group'])) {
                            $this->logAnyFile("Còn gói hiện tại", __CLASS__ . '_' . __FUNCTION__);
                            $question_group = $arr_player_update['question_group'];
                            $mt_question = $question_group['question_current']['content_unsigned'];

                            $this->logAnyFile("Số liệu: count_group_aday:" . $arr_player_update['count_group_aday'] . ",num_questions:" . count($arr_player_update['num_questions']) . ",question_current.index:" . $question_group['question_current']['index'], __CLASS__ . '_' . __FUNCTION__);
                        } else if (!empty($player['num_questions_pending']['package_week']['question_group'])) {
                            $arr_player_update['question_group'] = $player['num_questions_pending']['package_week']['question_group'];
                            $arr_player_update['num_questions_pending']['package_week']['question_group'] = null;
                            $mt_question = $arr_player_update['question_group']['question_current']['content_unsigned'];
                            $this->logAnyFile("Còn gói Pending G7 nên trả về luôn", __CLASS__ . '_' . __FUNCTION__);
                        } else if (!empty($arr_group_new)) {
                            $this->logAnyFile("Lấy gói mới", __CLASS__ . '_' . __FUNCTION__);
                            $group_id_new = array_shift($arr_group_new);
//                            $this->logAnyFile($group_id_new, __CLASS__ . '_' . __FUNCTION__);
                            $question_group_db = $this->QuestionGroup->getById($group_id_new['group_id']);
                            $this->logAnyFile($question_group_db, __CLASS__ . '_' . __FUNCTION__);
                            if (empty($question_group_db)) {
                                $question_group_db = $this->QuestionGroup->getOne();
                                if (!empty($question_group_db)) {
                                    $group_id_new['group_id'] = new MongoId($question_group_db['QuestionGroup']['id']);
                                }
                            }
                            if (!empty($question_group_db)) {
                                $question_group_db = $question_group_db['QuestionGroup'];
                                $this->logAnyFile($question_group_db, __CLASS__ . '_' . __FUNCTION__);
                                $cate_db = $this->QuestionCategory->find('first', ['conditions' => ['code' => $question_group_db['cate_code']]]);
                                $this->logAnyFile("cate_db: $cate_db", __CLASS__ . '_' . __FUNCTION__);
                                $question_group = [
                                    'group_id' => $group_id_new['group_id'],
                                    'package' => $group_id_new['package'],
                                    'cate' => ['code' => '', 'name' => '',],
                                    'time_start' => new MongoDate(),
                                    'question_current' => $question_group_db['questions'][0],
                                ];
                                if (!empty($cate_db)) {
                                    $question_group['cate'] = ['code' => $cate_db['QuestionCategory']['code'], 'name' => $cate_db['QuestionCategory']['name']];
                                }
//                                $this->logAnyFile($question_group, __CLASS__ . '_' . __FUNCTION__);
                                $arr_player_update['question_group'] = $question_group;

                                $arr_player_update['num_questions'] = $arr_group_new;

                                $mt_question = $question_group['question_current']['content_unsigned'];
                                $this->logAnyFile("Số liệu: count_group_aday:" . $arr_player_update['count_group_aday'] . ",num_questions:" . count($arr_player_update['num_questions']) . ",question_current.index:" . $question_group['question_current']['index'], __CLASS__ . '_' . __FUNCTION__);

                                $this->logAnyFile("HUY thanh cong G1 --> trả về câu hỏi G7 Số", __CLASS__ . '_' . __FUNCTION__);
                            } else {
                                $arr_player_update['question_group'] = null;
                                $this->logAnyFile("ERROR: Không lấy được câu hỏi trong DB với ID " . $group_id_new['group_id'], __CLASS__ . '_' . __FUNCTION__);
                            }
                        }
                    } else {
                        $this->logAnyFile("$phone HUY thanh cong khoi he thong(Huy ca G1, C7)", __CLASS__ . '_' . __FUNCTION__);
                        $arr_player_update['status'] = $arr_player_status['CANCEL'];
                    }

                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M09_CancelSuccess');
                    $mt_content = str_replace(self::KEY_GOI_CUOC, "G1", $mt_content);

                    $arr_mt['content'] = $mt_content;
                    //unv
                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M09_CancelSuccess');
                    //eunv
                    $this->logAnyFile("$phone HUY thanh cong G1", __CLASS__ . '_' . __FUNCTION__);
                }
            }

            // nếu hủy gói tuần
            if ($arr_mo['package_week'] == 1) {

                // nếu thuê bao chưa đăng ký, thực hiện gửi về mt
                if ($player['package_week']['status'] == 0) {

                    $this->logAnyFile("$phone Chua dang ky G7", __CLASS__ . '_' . __FUNCTION__);

                    $arr_mo['status'] = 0;
                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M02_NotRegister');
                    $arr_mt['content'] = $mt_content;
                    //unv
                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M02_NotRegister');
                    //eunv
                }
                // thực hiện hủy
                else {

                    // thực hiện các cờ trạng thái thời gian
                    $arr_player_update['package_week']['status'] = 0;
                    $arr_player_update['package_week']['time_deactive'] = new MongoDate();
                    $arr_player_update['package_week']['modified'] = new MongoDate();
                    $arr_player_update['time_deactive'] = new MongoDate();
                    $arr_player_update['time_last_self_deactive'] = new MongoDate();

                    // thực hiện xóa đi thời điểm mang đi gia hạn
                    $arr_player_update['package_week']['time_charge'] = null;

                    $arr_group_package = $this->QuestionGroup->splitArrGroupIdByPackage($player['num_questions'], "G7");
                    $arr_group_id = $arr_group_package['arr_group_id'];
                    $arr_group_new = $arr_group_package['arr_group_new'];

                    $package_day = !empty($player['num_questions_pending']['package_day']) ? $player['num_questions_pending']['package_day'] : null;
                    $arr_player_update['num_questions_pending'] = ['package_day' => $player['num_questions_pending']['package_day'], 'package_week' => null,];
                    $arr_player_update['num_questions_pending']['package_week'] = [
                        'question_group' => null,
                        'groups' => $arr_group_id,
                    ];
                    if (!empty($player['question_group']) && $player['question_group']['package'] == 'G7') {
                        $arr_player_update['num_questions_pending']['package_week']['question_group'] = $player['question_group'];
                        $arr_player_update['question_group'] = null;
                    } else {
                        $arr_player_update['question_group'] = $player['question_group'];
                    }
                    $arr_player_update['count_group_aday'] = $player['count_group_aday'] - count($arr_group_id);
                    $arr_player_update['num_questions'] = $arr_group_new;

                    //HỦY G7 XONG THÌ KIỂM TRA XEM CÒN CÂU HỎI Ở GÓI G1 KHÔNG THÌ TRẢ VỀ
                    if ($player['package_day'] ['status'] == 1) {
                        $this->logAnyFile("HỦY G7 XONG THÌ KIỂM TRA XEM CÒN CÂU HỎI Ở GÓI G1 KHÔNG THÌ TRẢ VỀ", __CLASS__ . '_' . __FUNCTION__);
                        if (!empty($arr_player_update['question_group'])) {
                            $this->logAnyFile("Còn gói hiện tại", __CLASS__ . '_' . __FUNCTION__);
                            $question_group = $arr_player_update['question_group'];
                            $mt_question = $question_group['question_current'] ['content_unsigned'];

                            $this->logAnyFile("Số liệu: count_group_aday:" . $arr_player_update['count_group_aday'] . ",num_questions:" . count($arr_player_update['num_questions']) . ",question_current.index:" . $question_group['question_current']['index'], __CLASS__ . '_' . __FUNCTION__);
                        } else if (!empty($player['num_questions_pending']['package_day']['question_group'])) {
                            $arr_player_update['question_group'] = $player['num_questions_pending']['package_day']['question_group'];
                            $arr_player_update['num_questions_pending']['package_day']['question_group'] = null;
                            $mt_question = $arr_player_update['question_group'] ['question_current']['content_unsigned'];
                            $this->logAnyFile("Còn gói Pending G1 nên trả về luôn", __CLASS__ . '_' . __FUNCTION__);
                        } else if (!empty($arr_group_new)) {
                            $this->logAnyFile("Lấy gói mới", __CLASS__ . '_' . __FUNCTION__);
                            $group_id_new = array_shift($arr_group_new);
//                            $this->logAnyFile($group_id_new, __CLASS__ . '_' . __FUNCTION__);
                            $question_group_db = $this->QuestionGroup->getById($group_id_new['group_id']);
                            $this->logAnyFile($question_group_db, __CLASS__ . '_' . __FUNCTION__);
                            if (empty($question_group_db)) {
                                $question_group_db = $this->QuestionGroup->getOne();
                                if (!empty($question_group_db)) {
                                    $group_id_new['group_id'] = new MongoId($question_group_db['QuestionGroup']['id']);
                                }
                            }
                            if (!empty($question_group_db)) {
                                $question_group_db = $question_group_db['QuestionGroup'];
                                $this->logAnyFile($question_group_db, __CLASS__ . '_' . __FUNCTION__);
                                $cate_db = $this->QuestionCategory->find('first', ['conditions' => ['code' => $question_group_db['cate_code']]]);
                                $this->logAnyFile("cate_db: $cate_db", __CLASS__ . '_' . __FUNCTION__);
                                $question_group = [
                                    'group_id' => $group_id_new['group_id'],
                                    'package' => $group_id_new['package'],
                                    'cate' => ['code' => '', 'name' => '',],
                                    'time_start' => new MongoDate(),
                                    'question_current' => $question_group_db['questions'][0],
                                ];
                                if (!empty($cate_db)) {
                                    $question_group['cate'] = ['code' => $cate_db['QuestionCategory']['code'], 'name' => $cate_db['QuestionCategory']['name']];
                                }
//                                $this->logAnyFile($question_group, __CLASS__ . '_' . __FUNCTION__);
                                $arr_player_update['question_group'] = $question_group;

                                $arr_player_update['num_questions'] = $arr_group_new;

                                $mt_question = $question_group['question_current'] ['content_unsigned'];

                                $this->logAnyFile("Số liệu: count_group_aday:" . $arr_player_update['count_group_aday'] . ",num_questions:" . count($arr_player_update['num_questions']) . ",question_current.index:" . $question_group['question_current']['index'], __CLASS__ . '_' . __FUNCTION__);


                                $this->logAnyFile("HUY thanh cong G7 --> trả về câu hỏi G1", __CLASS__ . '_' . __FUNCTION__);
                            } else {
                                $arr_player_update ['question_group'] = null;
                                $this->logAnyFile("ERROR: Không lấy được câu hỏi trong DB với ID " . $group_id_new['group_id'], __CLASS__ . '_' . __FUNCTION__);
                            }
                        }
                    } else {
                        $this->logAnyFile("$phone HUY thanh cong khoi he thong(Huy ca G1, C7)", __CLASS__ . '_' . __FUNCTION__);
                        $arr_player_update['status'] = $arr_player_status['CANCEL'];
                    }

                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M09_CancelSuccess');
                    $mt_content = str_replace(self::KEY_GOI_CUOC, "G7", $mt_content);
                    $arr_mt['content'] = $mt_content;
                    //unv
                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M09_CancelSuccess');
                    //eunv
                    $this->logAnyFile("$phone HUY thanh cong G7", __CLASS__ . '_' . __FUNCTION__);
                }
            }

            $arr_player_update['time_last_action'] = new MongoDate();
            $this->Player->save($arr_player_update);
        }

        if (empty($mt_content)) {

            $mt_content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $mt_content", __CLASS__ . '_' . __FUNCTION__);
        }

        // thực hiện lưu lại charge
        $arr_charge['status'] = $arr_mo['status'];
        $this->Charge->insert($arr_charge);

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));

        //unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
        //eunv

        /* $result = $this->sendmt($phone, $mt_content);
          $arr_mt['note'] = $result; */

        $this->Mt->insert($arr_mt);

        if (!empty($mt_question)) {

            $index = (($arr_player_update['count_group_aday'] - count($arr_player_update ['num_questions']) ) * 5 );
            $question_current_index = $arr_player_update['question_group']['question_current']['index'] + 1;
            $index = $index - (5 - $question_current_index);

            if (!empty($arr_player_update['num_questions_pending']['package_day']['question_group'])) {
                //Câu hỏi pending đang ở câu số: 
                $index_pending = $player['num_questions_pending']['package_day']['question_group']['question_current']['index'] + 1;
                $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
            }
            if (!empty($arr_player_update['num_questions_pending']['package_week']['question_group'])) {
                //Câu hỏi pending đang ở câu số: 
                $index_pending = $player['num_questions_pending']['package_week']['question_group']['question_current']['index'] + 1;
                $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
            }

            $mt_content_new = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M16_Choi');
            $mt_guide = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M10_GuideAnswer');

            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M16_Choi');
            //eunv
            $mt_content_new = str_replace(self::KEY_CAU_HOI_SO, $index, $mt_content_new);
            $mt_content_new = str_replace(self::KEY_CAU_HOI, $mt_question, $mt_content_new);
            $mt_content_new = str_replace(self::KEY_HUONG_DAN, $mt_guide, $mt_content_new);

            $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));

            //unv
            if (!$denied_send_mt) {

                $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content_new, array(
                    'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
                ));
                $arr_mt['details'] = $send_sms;
                $arr_mt['status'] = $send_sms['status'];
            } else {

                $arr_mt['status'] = 2;
            }
            //eunv

            /* $result = $this->sendmt($phone, $mt_content_new);
              $arr_mt['note'] = $result; */

            $arr_mt['content'] = $mt_content_new;
            $this->Mt->insert($arr_mt);
        }

        $log_content = "================ END processingCmdHUY($short_code, $phone, content: $content)";
        $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    private function processingCmdMUA($arr_mo) {

        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];
        $content = $arr_mo ['content'];
        $this->logAnyFile("================ START processingCmdMUA($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        $this->arr_player_status = Configure::read('sysconfig.Players.STATUS');
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
        $RESULT_CHARGE_OK = Configure::read('sysconfig.ChargingVMS.RESULT_CHARGE_OK');
        $MUA_price = Configure::read('sysconfig.ChargingVMS.MUA_price');
        $mt_content = "";
        $mt_content2 = ""; //trường hợp trả lời hết gói trước, mua thêm thì trả về câu hỏi luôn

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
        $player = $this->Player->getInfoByMobile($phone);
        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,
        ];
        $denied_send_mt = false; //unv
        // khi chưa sử dụng dịch vụ - gửi mt về thuê bao
        if (empty($player) || ($player['Player']['package_day'] ['status'] == 0 && $player['Player']['package_week']['status'] == 0)) {

            $this->logAnyFile("$phone Chua dang ky", __CLASS__ . '_' . __FUNCTION__);

            $arr_mo['status'] = 0;
            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M02_NotRegister');
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M02_NotRegister');
            //eunv
        }
        // trường hợp thuê bao đã tồn tại
        else {

            $this->logAnyFile("$phone Da ton tai trong he thong.", __CLASS__ . '_' . __FUNCTION__);

            $player = $player['Player'];

            $arr_charge = array(
                'phone' => $phone,
                'amount' => 0,
                'service_code' => $service_code,
                'trans_id' => '',
                'channel' => 'SMS',
                'package' => 'MUA',
                'action' => $arr_mo['action'],
                'cp' => '',
                'cp_sharing' => 0,
                'status' => 0,
                'details' => null,
                //unv
                'distributor_code' => "",
                'distribution_channel_code' => "",
                'distributor_sharing' => "",
                'distribution_channel_sharing' => ""
                    //eunv
            );
            $arr_player_update = $this->checkNewDayAndResetPlayer($player);

            $arr_charge['amount'] = $MUA_price;
            $arr_mo['amount'] = $MUA_price;

            $charge_result = $this->MobifoneCommon->charge($phone, $MUA_price, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_charge['details'] = $charge_result;

            $log_content = "CHARGE_OK($RESULT_CHARGE_OK), result(" . $charge_result['pretty_message'] . ")";
            $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

            // lưu lại thời điểm charge cước
            $arr_player_update['time_charge'] = new MongoDate();
            $arr_player_update['time_last_action'] = new MongoDate();

            // nếu charge cước thành công
            if ($charge_result['status'] == 1) {

                // lưu lại thời điểm charge cước thành công
                $arr_player_update['time_last_charge'] = new MongoDate();
                $arr_player_update['time_last_buy_question'] = new MongoDate();

                $arr_group_all = $this->QuestionGroup->getArrGroupPackageOthers(1, $player['answered_groups'], 'MUA');
                $arr_group_package = $arr_group_all['arr_group_package'];
                $arr_group_id = $arr_group_all['arr_group_id'];

                $arr_player_update['num_questions'] = array_merge($player['num_questions'], $arr_group_package);
                $arr_player_update['answered_groups'] = array_merge($player['answered_groups'], $arr_group_id);
                $arr_player_update['count_group_aday'] = $player['count_group_aday'] + count($arr_group_id);

                $arr_mo['status'] = 1;
                $arr_charge['status'] = 1;

                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M12_BuySuccess');
                //unv
                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M12_BuySuccess');
                //eunv
                $this->logAnyFile("$phone Charge MUA(DK PAN) result successful: " . $charge_result['pretty_message'], __CLASS__ . '_' . __FUNCTION__);

                //Nếu chưa có câu hỏi thì trả về cho Player câu hỏi mới
                $num_questions = $arr_player_update['num_questions'];
                $question_group = $player['question_group'];
                if (empty($question_group) && !empty($num_questions)) {

                    $this->logAnyFile("Neu tra loi het goi truoc thi tra ve cau hoi cua goi moi luon", __CLASS__ . '_' . __FUNCTION__);
                    $group_id_new = array_shift($num_questions);
                    $question_group_db = $this->QuestionGroup->getById($group_id_new['group_id']);
                    if (!empty($question_group_db)) {

                        $question_group_db = $question_group_db['QuestionGroup'];

                        $cate_db = $this->QuestionCategory->find('first', ['conditions' => ['code' => $question_group_db['cate_code']]]);
                        $question_group = [
                            'group_id' => $group_id_new['group_id'],
                            'package' => $group_id_new['package'],
                            'cate' => ['code' => '', 'name' => '',],
                            'time_start' => new MongoDate(),
                            'question_current' => $question_group_db['questions'][0],
                        ];

                        if (!empty($cate_db)) {

                            $question_group['cate'] = ['code' => $cate_db['QuestionCategory']['code'], 'name' => $cate_db['QuestionCategory']['name']];
                        }

                        $arr_player_update['question_group'] = $question_group;
                        $arr_player_update['num_questions'] = $num_questions;

                        $index = (($arr_player_update['count_group_aday'] - count($arr_player_update ['num_questions'])) * 5);
                        $question_current_index = $arr_player_update['question_group']['question_current']['index'] + 1;
                        $index = $index - (5 - $question_current_index);

                        $mt_content2 = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M16_Choi');
                        $mt_guide = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M10_GuideAnswer');
                        $mt_question = $question_group['question_current']['content_unsigned'];
                        $mt_content2 = str_replace(self::KEY_CAU_HOI_SO, $index, $mt_content2);
                        $mt_content2 = str_replace(self::KEY_CAU_HOI, $mt_question, $mt_content2);
                        $mt_content2 = str_replace(self::KEY_HUONG_DAN, $mt_guide, $mt_content2);
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M16_Choi');
                        //eunv
                    } else {

                        $arr_player_update ['question_group'] = null;
                        $this->logAnyFile("ERROR: Không lấy được câu hỏi trong DB với ID " . $group_id_new['group_id'], __CLASS__ . '_' . __FUNCTION__);
                    }
                }
            }
            // nếu không charge cước thành công
            else {

                $arr_mo['status'] = 0;
                $arr_charge['status'] = 0;

                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M13_BuyFailue');
                //unv
                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M13_BuyFailue');
                //eunv
                $mt_content = str_replace(self::KEY_TIEN, Configure::read('sysconfig.Packages.extra'), $mt_content);
                $this->logAnyFile("$phone Charge MUA(DK PAN) result failue: " . $charge_result['pretty_message'], __CLASS__ . '_' . __FUNCTION__);
            }

            $this->Player->save($arr_player_update);
            //unv
            $this->generate_distributor_data($arr_charge, $player);
            //eunv
            $this->Charge->insert($arr_charge);
        }

        if (empty($mt_content)) {

            $arr_mo['status'] = 0;
            $mt_content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $mt_content ", __CLASS__ . '_' . __FUNCTION__);
        }

        //unv
        if (!empty($player)) {

            $this->generate_distributor_data($arr_mo, $player['Player']);
        }
        //eunv

        $arr_mo['id'] = $this->Mo->insert($arr_mo);
        $arr_mt['mo_id'] = new MongoId($arr_mo['id']);

        //unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
        //eunv

        /* $result = $this->sendmt($phone, $mt_content);
          $arr_mt['note'] = $result; */
        $arr_mt['content'] = $mt_content;
        $this->Mt->insert($arr_mt);

        if (!empty($mt_content2)) {

            $this->logAnyFile("Gui MT cau hoi:  $mt_content2", __CLASS__ . '_' . __FUNCTION__);

            //unv
            if (!$denied_send_mt) {

                $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content2, array(
                    'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
                ));
                $arr_mt['details'] = $send_sms;
                $arr_mt['status'] = $send_sms['status'];
            } else {

                $arr_mt['status'] = 2;
            }
            //eunv

            /* $result = $this->sendmt($phone, $mt_content2);
              $arr_mt['note'] = $result; */
            $arr_mt['content'] = $mt_content2;
            $this->Mt->insert($arr_mt);
        }
        $log_content = "================ END processingCmdMUA($short_code, $phone, content: $content)";
        $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    private function processingCmdCHOI($arr_mo) {

        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];
        $content = $arr_mo ['content'];
        $this->logAnyFile("================ START processingCmdCHOI($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        $this->arr_player_status = Configure::read('sysconfig.Players.STATUS');

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
        $player = $this->Player->getInfoByMobile($phone);

        $mt_content = "";
        $mt_question = "";
        $mt_guide = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M10_GuideAnswerPlay');

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,
        ];
        $denied_send_mt = false; //unv
        //đang sử dụng dịch vụ rồi
        if (empty($player) || ($player['Player']['package_day'] ['status'] == 0 && $player['Player']['package_week']['status'] == 0)) {

            $this->logAnyFile("$phone Chua dang ky", __CLASS__ . '_' . __FUNCTION__);

            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M02_NotRegister');
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M02_NotRegister');
            //eunv
        } else if ($player['Player']['channel_play'] != 'SMS') {
            $this->logAnyFile("$phone Dang choi tren " . $player['Player']['channel_play'] . " nen khong the choi tren SMS", __CLASS__ . '_' . __FUNCTION__);
            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M21_ChannelNotOK');
            $mt_content = str_replace(self::KEY_CHANNEL_CURRENT, $player['Player']['channel_play'], $mt_content);
            $mt_content = str_replace(self::KEY_CHANNEL_TO, "SMS", $mt_content);
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M21_ChannelNotOK');
            //eunv
        } else {
            $this->logAnyFile("$phone Da ton tai trong he thong. Lay cau hoi tra ve cho Player...", __CLASS__ . '_' . __FUNCTION__);

            $player = $player['Player'];

            $reward_first_register = Configure::read('sysconfig.Packages.reward_first_register');
            $arr_player_update = $this->checkNewDayAndResetPlayer($player);

            $num_questions = $player['num_questions'];
            $question_group = $player['question_group'];
            $arr_player_update['package_day'] = $player['package_day'];
            $arr_player_update['package_week'] = $player['package_week'];
            $arr_player_update['count_group_aday'] = $player['count_group_aday'];
            $arr_player_update['num_questions'] = $player['num_questions'];
            $arr_player_update['question_group'] = $player['question_group'];

            //KHI ĐÃ GIA HẠN MÀ GÓI LÀ SMS THÌ SẼ CẤP PHÁT CÂU HỎI KHI NHẮN TIN "CHOI"
            if ($player['package_day'] ['status_charge'] == 1 && (empty($player['package_day']['time_send_question']) || date('Ymd', $player['package_day']['time_send_question']->sec) < date('Ymd'))) {
                $arr_group_all = $this->QuestionGroup->getArrGroupPackageOthers($reward_first_register['question_group'], $player['answered_groups'], 'G1');
                $arr_group_package = $arr_group_all['arr_group_package'];
                $arr_group_id = $arr_group_all['arr_group_id'];

                $arr_player_update['package_day']['time_send_question'] = new MongoDate();
                $arr_player_update['num_questions'] = $num_questions = array_merge($num_questions, $arr_group_package);
                $arr_player_update['answered_groups'] = array_merge($player ['answered_groups'], $arr_group_id);
                ;
                $arr_player_update['count_group_aday'] = $player['count_group_aday'] + count($arr_group_id);
            }

            if ($player['package_week']['status_charge'] == 1 && (empty($player['package_week']['time_send_question']) || date('Ymd', $player['package_week']['time_send_question']->sec) < date('Ymd'))) {
                $arr_group_all = $this->QuestionGroup->getArrGroupPackageOthers($reward_first_register['question_group'], $player['answered_groups'], 'G7');
                $arr_group_package = $arr_group_all['arr_group_package'];
                $arr_group_id = $arr_group_all['arr_group_id'];

                $arr_player_update['package_week']['time_send_question'] = new MongoDate();
                $arr_player_update['num_questions'] = $num_questions = array_merge($num_questions, $arr_group_package);
                $arr_player_update['answered_groups'] = array_merge($player ['answered_groups'], $arr_group_id);
                ;
                $arr_player_update['count_group_aday'] = $player['count_group_aday'] + count($arr_group_id);
            }

            //END: KHI ĐÃ GIA HẠN MÀ GÓI LÀ SMS THÌ SẼ CẤP PHÁT CÂU HỎI KHI NHẮN TIN "CHOI"


            if (!empty($question_group['question_current'])) {
                $question_current = $question_group['question_current'];

                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M16_Choi');
                //unv
                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M16_Choi');
                //eunv
                $mt_question = $question_current['content_unsigned'];
                $arr_player_update['package_day']['time_send_question'] = new MongoDate();
                $arr_player_update['package_week']['time_send_question'] = new MongoDate();
            } else if (!empty($num_questions)) {
                $this->logAnyFile("empty(question_group['question_current']) va van con goi khac", __CLASS__ . '_' . __FUNCTION__);
                $group_id_new = array_shift($num_questions);
                $question_group_db = $this->QuestionGroup->getById($group_id_new['group_id']);
                if (!empty($question_group_db)) {
                    $question_group_db = $question_group_db['QuestionGroup'];

                    $cate_db = $this->QuestionCategory->find('first', ['conditions' => ['code' => $question_group_db['cate_code']]]);
                    $question_group = [
                        'group_id' => $group_id_new['group_id'],
                        'package' => $group_id_new['package'],
                        'cate' => ['code' => '', 'name' => '',],
                        'time_start' => new MongoDate(),
                        'question_current' => $question_group_db['questions'][0],
                    ];
                    if (!empty($cate_db)) {
                        $question_group['cate'] = ['code' => $cate_db['QuestionCategory']['code'], 'name' => $cate_db['QuestionCategory']['name']];
                    }

                    $arr_player_update['question_group'] = $question_group;
                    $arr_player_update['num_questions'] = $num_questions;
                    $arr_player_update['package_day']['time_send_question'] = new MongoDate();
                    $arr_player_update ['package_week']['time_send_question'] = new MongoDate();
                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M16_Choi');
                    //unv
                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M16_Choi');
                    //eunv
//                    $question_number = ($player['count_group_aday'] - count($player['num_questions']) - 1) * 5 + $index;
                    $mt_question = $question_group['question_current']['content_unsigned'];
                } else {
                    $arr_mo['status'] = 0;
                    $arr_player_update['question_group'] = null;
                    $this->logAnyFile("ERROR: Không lấy được câu hỏi trong DB với ID " . $group_id_new['group_id'], __CLASS__ . '_' . __FUNCTION__);
                }
            } else {
                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M01_NoQuestion');
                //unv
                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M01_NoQuestion');
                //eunv
                $this->logAnyFile("!empty (num_questions): van con goi cau hoi, nhung khong ton tai tron DB", __CLASS__ . '_' . __FUNCTION__);
            }

            $this->Player->save($arr_player_update);
        }

        if (empty($mt_content)) {
            $mt_content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $mt_content", __CLASS__ . '_' . __FUNCTION__);
        } else if (!empty($mt_question)) {
            $time_answer = $this->getSecondCountDown($player['question_group']['time_start']);
            if ($time_answer <= 0) {
                $time_p = '0';
                $time_g = '0';
            } else {
                $time_p = floor($time_answer / 60);
                $time_g = $time_answer - ($time_p * 60);
            }

            $mt_guide = str_replace(self::KEY_TIME_PHUT, $time_p, $mt_guide);
            $mt_guide = str_replace(self::KEY_TIME_GIAY, $time_g, $mt_guide);

            $index = (($arr_player_update['count_group_aday'] - count($arr_player_update ['num_questions']) ) * 5 );
            $question_current_index = $arr_player_update['question_group']['question_current']['index'] + 1;
            $index = $index - ( 5 - $question_current_index);

            $this->logAnyFile("arr_player_update['count_group_aday']: " . $arr_player_update ['count_group_aday'] . ",count(arr_player_update['num_questions']): " . count($arr_player_update['num_questions']), __CLASS__ . '_' . __FUNCTION__);

            if (!empty($player['num_questions_pending']['package_day']['question_group'])) {
                //Câu hỏi pending đang ở câu số: 
                $index_pending = $player['num_questions_pending']['package_day']['question_group']['question_current']['index'] + 1;
                $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
                $this->logAnyFile("package_day.index_pending($index_pending)", __CLASS__ . '_' . __FUNCTION__);
            }
            if (!empty($player['num_questions_pending']['package_week']['question_group'])) {
                //Câu hỏi pending đang ở câu số: 
                $index_pending = $player['num_questions_pending']['package_week']['question_group']['question_current']['index'] + 1;
                $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
                $this->logAnyFile("  package_week.index_pending($index_pending)", __CLASS__ . '_' . __FUNCTION__);
            } $mt_content = str_replace(self::KEY_CAU_HOI_SO, $index, $mt_content);
            $mt_content = str_replace(self::KEY_CAU_HOI, $mt_question, $mt_content);
            $mt_content = str_replace(self::KEY_HUONG_DAN, $mt_guide, $mt_content);
        }

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));

        //unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
        //eunv

        /* $result = $this->sendmt($phone, $mt_content);
          $arr_mt['note'] = $result; */

        $arr_mt['content'] = $mt_content;
        $this->Mt->insert($arr_mt);
        $log_content = "================ END processingCmdCHOI($short_code, $phone, content: $content)";
        $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    const KEY_GOI_CUOC = "[GOI_CUOC]";
    const KEY_DIEM = "[DIEM]";
    const KEY_TONG_DIEM = "[TONG_DIEM]";
    const KEY_DAP_AN = "[DAP_AN]";
    const KEY_CAU_HOI = "[CAU_HOI]";
    const KEY_CAU_HOI_SO = "[CAU_HOI_SO]";
    const KEY_HUONG_DAN = "[HUONG_DAN]";
    const KEY_PHONE = "[PHONE]";
    const KEY_CHANNEL_CURRENT = "[CHANNEL_CURRENT]";
    const KEY_CHANNEL_TO = "[CHANNEL_TO]";

    private function processingCmdTRALOI($arr_mo) {

        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];
        $content = $arr_mo ['content'];
        $this->logAnyFile("================ START processingCmdTRALOI($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        $this->arr_player_status = Configure::read('sysconfig.Players.STATUS');
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
        $mt_content = "";

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
//        $this->logAnyFile($arr_mt_db, __CLASS__ . '_' . __FUNCTION__);
        $player = $this->Player->getInfoByMobile($phone);

        //$mt_notify = "";
        $mt_question = "";
        $mt_guide = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M10_GuideAnswer');

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,
        ];
        $denied_send_mt = false; //ungnv
        $question_current_score = 0;
        $question_current_answer = 1;
        //đang sử dụng dịch vụ rồi
        if (empty($player) || ($player['Player']['package_day'] ['status'] == 0 && $player['Player']['package_week']['status'] == 0)) {

            $this->logAnyFile("$phone Chua dang ky", __CLASS__ . '_' . __FUNCTION__);

            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M02_NotRegister');
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M02_NotRegister');
            //eunv
        } else if ($player['Player']['channel_play'] != 'SMS') {
            $this->logAnyFile("$phone Dang choi tren " . $player['Player']['channel_play'] . " nen khong the choi tren SMS", __CLASS__ . '_' . __FUNCTION__);
            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M21_ChannelNotOK');
            $mt_content = str_replace(self::KEY_CHANNEL_CURRENT, $player['Player']['channel_play'], $mt_content);
            $mt_content = str_replace(self::KEY_CHANNEL_TO, "SMS", $mt_content);
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M21_ChannelNotOK');
            //eunv
        } else {
            $this->logAnyFile("$phone Da ton tai trong he thong. Lay cau hoi tra ve cho Player...", __CLASS__ . '_' . __FUNCTION__);

            $player = $player['Player'];

            $arr_player_update = $this->checkNewDayAndResetPlayer($player);
            //Mảng cập nhật lịch sử điểm
            $arr_score_his = [
                'phone' => $phone,
                'score_total' => $player['score_total'],
                'score' => 0,
                'action' => $arr_mo['action'],
                'channel' => 'SMS',
                'service_code' => $service_code,
                "question" => null,
                "details" => null,
                'status' => 0,
            ];
            $num_questions = $player['num_questions'];
            $question_group = $player['question_group'];
            $arr_player_update['count_group_aday'] = $player['count_group_aday'];
            $arr_player_update['num_questions'] = $player['num_questions'];

            $answer = (int) $content;

            $this->logAnyFile("PLAYER TRẢ LỜI: $answer", __CLASS__ . '_' . __FUNCTION__);

            //Check co cau hoi ko va con goi khac khong
            if (empty($question_group) && empty($num_questions)) {

                $this->logAnyFile("Không có câu hỏi, y/c Player mua thêm: $answer", __CLASS__ . '_' . __FUNCTION__);
                $arr_mo['status'] = 0;
                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M01_NoQuestion');
                //unv
                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M01_NoQuestion');
                //eunv
            } else if (empty($question_group)) {
                $this->logAnyFile("Tro choi chua bat dau: $phone  gui đáp án khi chua gui CHOI", __CLASS__ . '_' . __FUNCTION__);
                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M17_TiepNotPlay');
                //unv
                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M17_TiepNotPlay');
                //eunv
            } else {
                $question_index_next = 0;
                if (!empty($question_group)) {
                    if (!empty($question_group['question_current'])) {
                        $outTimeCheck = false;
                        $question_current = $question_group['question_current'];

                        $question_index_next = $question_current['index'] + 1;
                        $time_answer = $this->getSecondFromNowNMongoDate($question_group["time_start"]);

                        if ($time_answer <= 180) {
                            $question_current_answer = $question_current['answer'];
                            if ($question_current['answer'] == $answer) {
                                $this->logAnyFile("PLAYER TRẢ LỜI ĐÚNG: $answer", __CLASS__ . '_' . __FUNCTION__);

                                $arr_mt["question"] = [
                                    'group_id' => $question_group['group_id'],
                                    'index' => $question_current['index'],
                                    'time_start' => $question_group["time_start"],
                                    'time' => $time_answer,
                                    'answer' => $answer,
                                    'correct' => true,
                                ];
                                $question_current_score = $question_current["score"];
                                //Điểm tổng
                                $arr_player_update['score_total'] = [
                                    "score" => $player['score_total']["score"] + $question_current_score,
                                    "time" => $player['score_total']["score"] + $time_answer];
                                //Điểm ngày
                                $arr_player_update['score_day'] = [
                                    "score" => $player['score_day']["score"] + $question_current_score,
                                    "time" => $player['score_day']["score"] + $time_answer];
                                //Điểm tuần
                                $arr_player_update['score_week'] = [
                                    "score" => $player['score_week']["score"] + $question_current_score,
                                    "time" => $player['score_week']["score"] + $time_answer];
                                //Điểm tháng
                                $arr_player_update['score_month'] = [
                                    "score" => $player['score_month']["score"] + $question_current_score,
                                    "time" => $player['score_month']["score"] + $time_answer];

                                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M14_AnswerCorrect');
//                                $mt_content = str_replace(self::KEY_CAU_HOI_SO, $new_question_number, $mt_content);
                                $mt_content = str_replace(self::KEY_DIEM, $question_current_score, $mt_content);
                                //unv
                                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M14_AnswerCorrect');
                                //eunv

                                $arr_score_his['score'] = $question_current_score;

                                //START Cộng điểm realtime cho Player
                                $score_day = $this->ScoreDay->checkExistScoreDay($phone);
                                $score_week = $this->Score->checkExistScoreWeek($phone);
                                $score_month = $this->Score->checkExistScoreMon($phone);

                                $score_day1 = $arr_player_update['score_day'];
                                $score_day1['date'] = $arr_player_update['time_last_action'];
                                $arr_score_day = [
                                    'phone' => $player['phone'],
                                    'comment' => '',
                                    'day' => $score_day1,
                                    'service_code' => $service_code,
                                ];

                                $time = getdate();
                                $score_week1 = $arr_player_update['score_week'];
                                $score_week1['index'] = $this->Score->getWeek($time['year'], $time['mon'], $time['mday']);
                                $arr_score_week = [
                                    'phone' => $player['phone'],
                                    'comment' => '',
                                    'week' => $score_week1,
                                    'month' => null,
                                    'service_code' => $service_code,
                                ];
                                $score_month1 = $arr_player_update['score_month'];
                                $score_month1['index'] = $time['mon'];
                                $arr_score_month = [
                                    'phone' => $player['phone'],
                                    'comment' => '',
                                    'week' => null,
                                    'month' => $score_month1,
                                    'service_code' => $service_code,
                                ];

                                if (!empty($score_day)) {
                                    $arr_score_day['id'] = $score_day['AppModel']['id'];
                                }
                                $this->ScoreDay->insert($arr_score_day);
                                if (!empty($score_week)) {
                                    $arr_score_week['id'] = $score_week['AppModel']['id'];
                                }
                                $this->Score->insert($arr_score_week);
                                if (!empty($score_month)) {
                                    $arr_score_month['id'] = $score_month['AppModel']['id'];
                                }
                                $this->Score->insert($arr_score_month);
                                //END Cộng điểm realtime cho Player
                            } else {
                                $this->logAnyFile("PLAYER TRẢ LỜI SAI: $answer", __CLASS__ . '_' . __FUNCTION__);

                                $arr_mt["question"] = [
                                    'group_id' => $question_group['group_id'],
                                    'index' => $question_current['index'],
                                    'time_start' => $question_group["time_start"],
                                    'time' => $time_answer,
                                    'answer' => $answer,
                                    'correct' => false,
                                ];

                                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M15_AnswerWrong');
//                                $mt_content = str_replace(self::KEY_CAU_HOI_SO, $new_question_number, $mt_content);
                                $mt_content = str_replace(self::KEY_DAP_AN, $question_current['answer'], $mt_content);
                                //unv
                                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M15_AnswerWrong');
                                //eunv
                            }
                        } else {
                            $this->logAnyFile("PLAYER TRẢ LỜI mất $time_answer Quá thời gian cho phép 3p(=180s): $answer", __CLASS__ . '_' . __FUNCTION__);
                            $arr_mt["question"] = [
                                'group_id' => $question_group['group_id'],
                                'index' => $question_current['index'],
                                'time_start' => $question_group["time_start"],
                                'time' => $time_answer,
                                'answer' => $answer,
                                'correct' => false,
                            ];
                            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M14_AnswerOutTime');
                            //unv
                            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M14_AnswerOutTime');
                            //eunv

                            $outTimeCheck = true;
                        }
                    }

                    if ($question_index_next < 5) {
                        if (!$outTimeCheck) {
                            $this->logAnyFile("CÂU HỎI TIẾP THEO: $question_index_next", __CLASS__ . '_' . __FUNCTION__);
                            //Lấy câu hỏi tiếp theo
                            $question_group_db = $this->QuestionGroup->getById($question_group["group_id"]);
                            if (!empty($question_group_db)) {
                                $question_group_db = $question_group_db['QuestionGroup'];

                                $question_group['time_start'] = new MongoDate();
                                $question_group['question_current'] = $question_group_db['questions'][$question_index_next];

                                $arr_player_update['question_group'] = $question_group;

                                $mt_question = $question_group['question_current']["content_unsigned"];
                            }
                            $this->logAnyFile("END CÂU HỎI TIẾP THEO: $question_index_next", __CLASS__ . '_' . __FUNCTION__);
                        } else {
                            $this->logAnyFile("Hết thời gian trả lời, MT: $mt_content", __CLASS__ . '_' . __FUNCTION__);
                        }
                    } else {
                        $question_index_next = 0;
                        $question_group['question_current'] = null;
                    }
                }
                $arr_score_his["question"] = $arr_mt["question"];

                $this->Score->insert_his($arr_score_his);


                $arr_player_update['num_questions_pending'] = $player['num_questions_pending'];
                //Check ngày hôm nay: đăng ký lại thì trả lại số câu hỏi còn lại
//                if (empty($question_group['question_current']) && !empty($player['num_questions_pending']['package_day']['question_group'])) {
//                    $this->logAnyFile("Hết gói và còn trong num_questions_pending.package_day", __CLASS__ . '_' . __FUNCTION__);
//                    $arr_player_update['question_group'] = $player['num_questions_pending']['package_day']['question_group'];
//                    $arr_player_update['num_questions_pending']['package_day'] = null;
//                } else if (empty($question_group['question_current']) && !empty($player['num_questions_pending']['package_week']['question_group'])) {
//                    $this->logAnyFile("Hết gói và còn trong num_questions_pending.package_week", __CLASS__ . '_' . __FUNCTION__);
//                    $arr_player_update['question_group'] = $player['num_questions_pending']['package_week']['question_group'];
//                    $arr_player_update['num_questions_pending']['package_week'] = null;
//                }
//                //Kiểm tra xem con gói nào không
                //                else if (empty($question_group['question_current']) && !empty($num_questions)) {
                if (empty($question_group ['question_current']) && !empty($num_questions)) {
                    $this->logAnyFile("TRẢ LỜI HẾT GÓI HIỆN TẠI VÀ CÒN GÓI KHÁC", __CLASS__ . '_' . __FUNCTION__);
                    $group_id_new = array_shift($num_questions);
                    $question_group_db = $this->QuestionGroup->getById($group_id_new['group_id']);
                    if (empty($question_group_db)) {
                        $question_group_db = $this->QuestionGroup->getOne();
                        if (!empty($question_group_db)) {
                            $group_id_new['group_id'] = new MongoId($question_group_db['QuestionGroup']['id']);
                        }
                    }
                    if (!empty($question_group_db)) {
                        $question_group_db = $question_group_db['QuestionGroup'];

                        $cate_db = $this->QuestionCategory->find('first', ['conditions' => ['code' => $question_group_db['cate_code']]]);
                        $question_group = [
                            'group_id' => $group_id_new['group_id'],
                            'package' => $group_id_new['package'],
                            'cate' => ['code' => '', 'name' => '',],
                            'time_start' => new MongoDate(),
                            'question_current' => $question_group_db['questions'][0],
                        ];
                        if (!empty($cate_db)) {
                            $question_group['cate'] = ['code' => $cate_db['QuestionCategory']['code'], 'name' => $cate_db['QuestionCategory']['name']];
                        }

                        $arr_player_update['question_group'] = $question_group;

                        $arr_player_update['num_questions'] = $num_questions;

                        $mt_question = $question_group['question_current']['content_unsigned'];
                    } else {
                        $arr_player_update ['question_group'] = null;
                        $this->logAnyFile("ERROR: Không lấy được câu hỏi trong DB với ID " . $group_id_new['group_id'], __CLASS__ . '_' . __FUNCTION__);
                    }
                } else if (empty($question_group['question_current'])) {
                    $this->logAnyFile("TRẢ LỜI HẾT TẤT CẢ CÁC GÓI CÂU HỎI", __CLASS__ . '_' . __FUNCTION__);
                    $arr_player_update['question_group'] = null;
                    $mt_question = "";
                    if ($question_current_score > 0) {
                        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M14_AnswerCorrectLast');
                        $mt_content = str_replace(self::KEY_DIEM, $question_current_score, $mt_content);
                        $mt_content = str_replace(self::KEY_TONG_DIEM, ($player['score_total']['score'] + $question_current_score), $mt_content);
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M14_AnswerCorrectLast');
                        //eunv
                    } else {
                        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M15_AnswerWrongLast');
                        $mt_content = str_replace(self:: KEY_DAP_AN, $question_current_answer, $mt_content);
                        $mt_content = str_replace(self::KEY_TONG_DIEM, $player['score_total']['score'], $mt_content);
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M15_AnswerWrongLast');
                        //eunv
                    }
                }
            }
            $this->Player->save($arr_player_update);
        }

        if (empty($mt_content)) {
            $mt_content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $mt_content", __CLASS__ . '_' . __FUNCTION__);
            $arr_mo['status'] = 0;
        } else if (!empty($mt_question)) {

            $index = (($arr_player_update['count_group_aday'] - count($arr_player_update ['num_questions']) ) * 5 );
            $question_current_index = $arr_player_update['question_group']['question_current']['index'] + 1;
            $index = $index - (5 - $question_current_index);

            if (!empty($arr_player_update['num_questions_pending']['package_day']['question_group'])) {
                //Câu hỏi pending đang ở câu số: 
                $index_pending = $player['num_questions_pending']['package_day']['question_group']['question_current']['index'] + 1;
                $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
            }
            if (!empty($arr_player_update['num_questions_pending']['package_week']['question_group'])) {
                //Câu hỏi pending đang ở câu số: 
                $index_pending = $player['num_questions_pending']['package_week']['question_group']['question_current']['index'] + 1;
                $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
            } $mt_content = str_replace(self::KEY_CAU_HOI_SO, $index, $mt_content);
            $mt_content = str_replace(self::KEY_CAU_HOI, $mt_question, $mt_content);
            $mt_content = str_replace(self::KEY_HUONG_DAN, $mt_guide, $mt_content);
        }

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));

        //unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
        //eunv

        /* $result = $this->sendmt($phone, $mt_content);
          $arr_mt['note'] = $result; */

        $arr_mt['content'] = $mt_content;
        $this->Mt->insert($arr_mt);
        $log_content = "================ END processingCmdTRALOI($short_code, $phone, content: $content)";
        $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    private function processingCmdTIEP($arr_mo) {

        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];
        $content = $arr_mo ['content'];
        $this->logAnyFile("================ START processingCmdTIEP($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        $this->arr_player_status = Configure::read('sysconfig.Players.STATUS');
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
//        $this->logAnyFile($arr_mt_db, __CLASS__ . '_' . __FUNCTION__);
        $player = $this->Player->getInfoByMobile($phone);
        $mt_content = "";
        $mt_question = "";
        $mt_guide = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M10_GuideAnswer');

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,
        ];
        $denied_send_mt = false; //unv
        $question_current_score = 0;
        $question_current_answer = 1;
        //đang sử dụng dịch vụ rồi
        if (empty($player) || ($player['Player']['package_day'] ['status'] == 0 && $player['Player']['package_week']['status'] == 0)) {

            $this->logAnyFile("$phone Chua dang ky", __CLASS__ . '_' . __FUNCTION__);

            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M02_NotRegister');
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M02_NotRegister');
            //eunv
        } else if ($player['Player']['channel_play'] != 'SMS') {
            $this->logAnyFile("$phone Dang choi tren " . $player['Player']['channel_play'] . " nen khong the choi tren SMS", __CLASS__ . '_' . __FUNCTION__);
            $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M21_ChannelNotOK');
            $mt_content = str_replace(self::KEY_CHANNEL_CURRENT, $player['Player']['channel_play'], $mt_content);
            $mt_content = str_replace(self::KEY_CHANNEL_TO, "SMS", $mt_content);
            //unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M21_ChannelNotOK');
            //eunv
        } else {
            $this->logAnyFile("$phone Da ton tai trong he thong. Lay cau hoi tra ve cho Player...", __CLASS__ . '_' . __FUNCTION__);

            $player = $player['Player'];

            $arr_player_update = $this->checkNewDayAndResetPlayer($player);
            //Mảng cập nhật lịch sử điểm
            $arr_score_his = [
                'phone' => $phone,
                'score_total' => $player['score_total'],
                'score' => 0,
                'action' => $arr_mo['action'],
                'channel' => 'SMS',
                'service_code' => $service_code,
                "question" => null,
                "details" => null,
                'status' => 0,
            ];
            $num_questions = $player['num_questions'];
            $question_group = $player['question_group'];
            $arr_player_update['package_day'] = $player['package_day'];
            $arr_player_update['package_week'] = $player['package_week'];
            $arr_player_update['count_group_aday'] = $player['count_group_aday'];
            $arr_player_update['num_questions'] = $player['num_questions'];
            $arr_player_update['question_group'] = $player['question_group'];

            $question_index_next = 0;
            if (!empty($question_group)) {
                if (!empty($question_group['question_current'])) {
                    $question_current = $question_group['question_current'];

                    $question_index_next = $question_current['index'] + 1;
                    $time_answer = $this->getSecondFromNowNMongoDate($question_group ["time_start"]);

                    $question_current_answer = $question_current['answer'];
                    if ($time_answer <= 180) {//còn thời gian
                        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M17_TiepInTime');
//                        $mt_content = str_replace(self::KEY_CAU_HOI_SO, $new_question_number, $mt_content);
                        $mt_content = str_replace(self::KEY_DAP_AN, $question_current_answer, $mt_content);
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M17_TiepInTime');
                        //eunv
                    } else {//hết thời gian
                        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M17_TiepOutTime');
//                        $mt_content = str_replace(self::KEY_CAU_HOI_SO, $new_question_number, $mt_content);
                        $mt_content = str_replace(self::KEY_DAP_AN, $question_current_answer, $mt_content);
                        //unv
                        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M17_TiepOutTime');
                        //eunv
                    }
                }

                $this->logAnyFile("CÂU HỎI TIẾP THEO: $question_index_next", __CLASS__ . '_' . __FUNCTION__);
                if ($question_index_next < 5) {

                    //Lấy câu hỏi tiếp theo
                    $question_group_db = $this->QuestionGroup->getById($question_group["group_id"]);
                    if (!empty($question_group_db)) {
                        $question_group_db = $question_group_db['QuestionGroup'];

                        $question_group['time_start'] = new MongoDate();
                        $question_group['question_current'] = $question_group_db['questions'][$question_index_next];

                        $arr_player_update['question_group'] = $question_group;

                        $mt_question = $question_group['question_current']["content_unsigned"];
                    } else {
                        $arr_player_update ['question_group'] = null;
                        $this->logAnyFile("ERROR: Không lấy được câu hỏi trong DB với ID " . $group_id_new['group_id'], __CLASS__ . '_' . __FUNCTION__);
                    }
                } else {
                    $question_index_next = 0;
                    $question_group['question_current'] = null;
                }

//Kiểm tra xem con gói nào không
                if (empty($question_group ['question_current']) && !empty($num_questions)) {
                    $this->logAnyFile("TRẢ LỜI HẾT GÓI HIỆN TẠI VÀ CÒN GÓI KHÁC", __CLASS__ . '_' . __FUNCTION__);
                    $group_id_new = array_shift($num_questions);
                    $question_group_db = $this->QuestionGroup->getById($group_id_new['group_id']);
                    if (!empty($question_group_db)) {
                        $question_group_db = $question_group_db['QuestionGroup'];

                        $cate_db = $this->QuestionCategory->find('first', ['conditions' => ['code' => $question_group_db['cate_code']]]);
                        $question_group = [
                            'group_id' => $group_id_new['group_id'],
                            'package' => $group_id_new['package'],
                            'cate' => ['code' => '', 'name' => '',],
                            'time_start' => new MongoDate(),
                            'question_current' => $question_group_db['questions'][0],
                        ];
                        if (!empty($cate_db)) {
                            $question_group['cate'] = ['code' => $cate_db['QuestionCategory']['code'], 'name' => $cate_db['QuestionCategory']['name']];
                        }

                        $arr_player_update['question_group'] = $question_group;
                        $arr_player_update['num_questions'] = $num_questions;

                        //                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M17_TiepLast');
                        $mt_question = $question_group['question_current']['content_unsigned'];
                    } else {
                        $arr_player_update ['question_group'] = null;
                        $this->logAnyFile("ERROR: Không lấy được câu hỏi trong DB với ID " . $group_id_new['group_id'], __CLASS__ . '_' . __FUNCTION__);
                    }
                } else if (empty($question_group['question_current'])) {
                    $this->logAnyFile("TRẢ LỜI HẾT TẤT CẢ CÁC GÓI CÂU HỎI", __CLASS__ . '_' . __FUNCTION__);
                    $mt_question = "";
                    $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M17_TiepLast');
                    $mt_content = str_replace(self::KEY_DAP_AN, $question_current_answer, $mt_content);
                    $mt_content = str_replace(self::KEY_TONG_DIEM, ($player['score_total']['score'] + $question_current_score), $mt_content);
//unv
                    $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M17_TiepLast');
                    //eunv
                }
            } else {
                $this->logAnyFile("Tro choi chua bat dau: $phone  gui TIEP khi chua gui CHOI", __CLASS__ . '_' . __FUNCTION__);
                $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M17_TiepNotPlay');
            }
//LỊCH SỬ ĐIỂM
            $arr_score_his["question"] = $arr_mt["question"];

            $this->Score->insert_his($arr_score_his);
            $this->Player->save($arr_player_update);
        }

        if (empty($mt_content)) {
            $arr_mo['status'] = 0;
            $mt_content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $mt_content", __CLASS__ . '_' . __FUNCTION__);
        } else if (!empty($mt_question)) {

            $index = (($arr_player_update['count_group_aday'] - count($arr_player_update ['num_questions']) ) * 5 );
            $question_current_index = $arr_player_update['question_group']['question_current']['index'] + 1;
            $index = $index - (5 - $question_current_index);

            if (!empty($arr_player_update['num_questions_pending']['package_day']['question_group'])) {
                //Câu hỏi pending đang ở câu số: 
                $index_pending = $player['num_questions_pending']['package_day']['question_group']['question_current']['index'] + 1;
                $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
            }
            if (!empty($arr_player_update['num_questions_pending']['package_week']['question_group'])) {
                //Câu hỏi pending đang ở câu số: 
                $index_pending = $player['num_questions_pending']['package_week']['question_group']['question_current']['index'] + 1;
                $index = $index - (5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
            }
            $this->logAnyFile("Số liệu: mt_content($mt_content), index($index), count_group_aday:" . $arr_player_update['count_group_aday'] . ",num_questions:" . count($arr_player_update ['num_questions']) . ",question_current.index:" . $arr_player_update['question_group']['question_current']['index'], __CLASS__ . '_' . __FUNCTION__);
            $mt_content = str_replace(self::KEY_CAU_HOI_SO, $index, $mt_content);
            $mt_content = str_replace(self::KEY_CAU_HOI, $mt_question, $mt_content);
            $mt_content = str_replace(self::KEY_HUONG_DAN, $mt_guide, $mt_content);
        }

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));

//unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
//eunv

        /* $result = $this->sendmt($phone, $mt_content);
          $arr_mt['note'] = $result; */

        $arr_mt['content'] = $mt_content;
        $this->Mt->insert($arr_mt);
        $log_content = "================ END processingCmdTIEP($short_code, $phone, content: $content)";
        $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    private function processingCmdCHUYEN($arr_mo) {
        $short_code = $arr_mo['short_code'];
        $phone = trim($arr_mo ['phone']);

        $this->logAnyFile("================ START processingCmdCHUYEN($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
        $player = $this->Player->getInfoByMobile($phone);

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,
        ];
        $denied_send_mt = false; //unv
        //đang sử dụng dịch vụ rồi

        if (
                !empty($player) &&
                $player['Player']['status'] == 1 &&
                (
                $player['Player']['package_day']['status'] == 1 ||
                $player['Player']['package_week']['status'] == 1
                )
        ) {

            $player = $player['Player'];

            $arr_player_update = $this->checkNewDayAndResetPlayer($player);

            if ($player['channel_play'] == 'SMS') {
                $arr_player_update['channel_play'] = 'WAP';
                $mtCode = 'M19_ChangeSuccessSW';
                $this->logAnyFile("$phone Chuyen thanh cong SMS --> WAP: $mtCode", __CLASS__ . '_' . __FUNCTION__);
            } else if ($player['channel_play'] == 'WAP') {
                $arr_player_update['channel_play'] = 'SMS';
                $mtCode = 'M19_ChangeSuccessWS';
                $this->logAnyFile("$phone Chuyen thanh cong WAP --> SMS: $mtCode", __CLASS__ . '_' . __FUNCTION__);
            } else {
                $mtCode = 'M02_NotRegister';
                $this->logAnyFile("$phone Chuyen khong thanh cong 1 : $mtCode", __CLASS__ . '_' . __FUNCTION__);
            }

            $this->Player->save($arr_player_update);
        } else if (empty($player)) {
            $mtCode = 'M02_NotRegister';
            $this->logAnyFile("$phone Chuyen khong thanh cong 2 : $mtCode", __CLASS__ . '_' . __FUNCTION__);
        } else {
            $mtCode = 'M02_NotRegister';
            $this->logAnyFile("$phone  Chuyen khong thanh cong 3: $mtCode", __CLASS__ . '_' . __FUNCTION__);
        }

        $content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, $mtCode);
//unv
        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, $mtCode);
        //eunv
        $arr_mt['content'] = $content;
        if (empty($content)) {
            $arr_mo['status'] = 0;
            $content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $content", __CLASS__ . '_' . __FUNCTION__);
        }

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));

//unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
//eunv

        /* $result = $this->sendmt($phone, $content);
          $arr_mo['note'] = $result; */
        $this->Mt->insert($arr_mt);

        $log_content = "End processingCmdCHUYEN($short_code, $phone, content: $content)";
        $this->logAnyFile($log_content, __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    private function processingCmdHD($arr_mo) {
        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];

        $this->logAnyFile("================ START processingCmdHD($short_code, $phone)", __CLASS__ . '_' . __FUNCTION__);

        $arr_mt_db = $this->Configuration->getArrMt($short_code);

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'], 'status' => 0,
        ];
        $denied_send_mt = false; //unv
        $contents = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M18_HD');
        $content = str_replace(self::KEY_PHONE, $phone, $contents);
        //unv
        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M18_HD');
        //eunv
        $this->logAnyFile("$phone HD : $content", __CLASS__ . '_' . __FUNCTION__);

        if (empty($content)) {
            $arr_mo['status'] = 0;
            $content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $content", __CLASS__ . '_' . __FUNCTION__);
        }

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));
        $arr_mt['content'] = $content;

//unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
//eunv

        /* $result = $this->sendmt($phone, $content);
          $arr_mt['note'] = $result; */ $this->Mt->insert($arr_mt);

        $this->logAnyFile("End processingCmdHD($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    private function processingCmdKQ($arr_mo) {
        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];
        $this->logAnyFile("================ START processingCmdKQ($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
        $player = $this->Player->getInfoByMobile($phone);

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,];

        $denied_send_mt = false; //unv

        if (!empty($player)) {
            if ($player['Player']['package_day']['status'] == 1 || $player['Player']['package_week']['status'] == 1) {
                $content_1 = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M20_KQSuccess');
                $content_2 = str_replace(self::KEY_PHONE, $phone, $content_1);
                $content = str_replace(self::KEY_DIEM, $player['Player']['score_total']['score'], $content_2);
                $this->logAnyFile("$phone KQ thanh cong :  $content", __CLASS__ . '_' . __FUNCTION__);
//unv
                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M20_KQSuccess');
                //eunv
            } else {
                $content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M20_KQNotSuccess');
                $this->logAnyFile("$phone KQ that bai :  $content", __CLASS__ . '_' . __FUNCTION__);
//unv
                $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M20_KQNotSuccess');
                //eunv
            }
        } else {
            $content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M20_KQNotSuccess');
            $this->logAnyFile("$phone KQ that bai 2 :  $content", __CLASS__ . '_' . __FUNCTION__);
//unv
            $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M20_KQNotSuccess');
            //eunv
        }
        if (empty($content)) {
            $arr_mo['status'] = 0;
            $content = "He thong dang qua tai. Ban vui long quay lai sau.";
            $this->logAnyFile("$phone MT fix in source: $content", __CLASS__ . '_' . __FUNCTION__);
        }

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));
        $arr_mt['content'] = $content;

//unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
        //eunv

        /* $result = $this->sendmt($phone, $content);
          $arr_mt['note'] = $result; */

        $this->Mt->insert($arr_mt);

        $this->logAnyFile("End processingCmdKQ($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        return "1";
    }

    private function processingCmdDefault($arr_mo) {
        $short_code = $arr_mo['short_code'];
        $phone = $arr_mo['phone'];
        $this->logAnyFile("================ START processingCmdDefault($short_code, $phone)", __CLASS__ . '_' . __FUNCTION__);
        $arr_mt_db = $this->Configuration->getArrMt($short_code);

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => "",
            'channel' => "SMS",
            'action' => $arr_mo['action'], 'status' => 0,
        ];

        $denied_send_mt = false; //unv
        $content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'M01_WrongSyntax');
        $this->logAnyFile("$phone  Sai cu phap : $content", __CLASS__ . '_' . __FUNCTION__);
        //unv
        $denied_send_mt = $this->Configuration->is_denied_sendmt($arr_mt_db, 'M01_WrongSyntax');
        //eunv

        $arr_mt['mo_id'] = new MongoId($this->Mo->insert($arr_mo));
        $arr_mt['content'] = $content;

//unv
        if (!$denied_send_mt) {

            $send_sms = $this->MobifoneCommon->sendSms($phone, $content, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_mt['details'] = $send_sms;
            $arr_mt['status'] = $send_sms['status'];
        } else {

            $arr_mt['status'] = 2;
        }
//eunv

        /* $result = $this->sendmt($phone, $content);
          $arr_mt['note'] = $result; */ $this->Mt->insert($arr_mt);


        $this->logAnyFile("End processingCmdDefault($short_code, $phone, content: $content)", __CLASS__ . '_' . __FUNCTION__);

        return "1";
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

    /**
     * limitBlacklist
     * Thực hiện giới hạn đối với thuê bao trong blacklist
     * 
     * @param type $short_code
     * @param type $phone
     * @param type $content
     * 
     * @return boolean
     */
    protected function limitBlacklist($short_code, $phone, $content) {

        $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        $is_blacklist = $this->VisitorBlacklistCommon->isLimit($phone);
        if (!$is_blacklist) {

            return false;
        }

        $this->logAnyFile(__('The phone="%s" is limited', $phone), $log_file_name);
        $arr_mo = [
            'phone' => $phone,
            'short_code' => $short_code,
            'package_day' => 0,
            'package_week' => 0,
            'package_month' => 0,
            'channel' => "SMS",
            'amount' => 0,
            'content' => $content,
            'note' => "",
            'action' => $this->arr_action['CANH_BAO_BLACKLIST'],
            'details' => array(),
            'status' => 1,
            'distributor_code' => "",
            'distribution_channel_code' => "",
            'distributor_sharing' => "",
            'distribution_channel_sharing' => ""
        ];

        $this->Mo->init();
        $this->Mo->save($arr_mo);

        $mo_id = $this->Mo->getLastInsertID();

        $arr_mt_db = $this->Configuration->getArrMt($short_code);
        $mt_content = $this->Configuration->getMtByCodeFromArrMT($arr_mt_db, 'CANH_BAO_BLACKLIST');

        $arr_mt = [
            'phone' => $phone,
            'short_code' => $short_code,
            "question" => null,
            'content' => $mt_content,
            'channel' => "SMS",
            'action' => $arr_mo['action'],
            'status' => 0,
            'mo_id' => new MongoId($mo_id),
        ];

        $send_sms = $this->MobifoneCommon->sendSms($phone, $mt_content, array(
            'log_file_name' => $log_file_name,
        ));

        $arr_mt['status'] = $send_sms['status'];
        $arr_mt['details'] = $send_sms;

        $this->Mt->init();
        $this->Mt->save($arr_mt);

        exit();
    }

    //eunv
}
