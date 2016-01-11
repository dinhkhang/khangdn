<?php

App::uses('AppQuizController', 'Controller');

class PlayersController extends AppQuizController {

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
    }

    public $uses = array(
        'Player',
        'QuestionGroup',
        'Counter',
        'QuestionCategory',
        'Mo',
        'Mt',
        'Score',
        'ScoreDay',
        'Charge',
        'Distributor',
        'DistributionChannel',
    );
    public $components = array(
        'MobifoneCommon',
    );

    const STATUS_UNREGISTERED = 0;
    const MO_CHECK_STATUS = 'CHECK';

    public function index() {

        // nếu không có quyền truy cập, thì buộc user phải đăng xuất
        /* if (!$this->isAllow()) {

          return $this->redirect($this->Auth->loginRedirect);
          } */

        $this->setInit();
        $breadcrumb = array();
        $breadcrumb[] = array(
            'url' => Router::url(array('action' => 'index')),
            'label' => __('player_title'),
        );
        $this->set('breadcrumb', $breadcrumb);
        $this->set('page_title', __('player_title'));

        $mo_action = Configure::read('sysconfig.Player.mo_action');
        $this->set('mo_action', $mo_action);

        $this->set('status_unregistered', self::STATUS_UNREGISTERED);

        // khởi tạo class dùng css cho status
        $status_clss = array(
            0 => 'label label-danger',
            1 => 'label label-primary',
            2 => 'label label-warning',
            3 => 'label label-warning',
            4 => 'label label-danger',
        );
        $this->set('status_clss', $status_clss);

        // khởi tạo class dùng css cho play_status
        $play_status_clss = array(
            0 => 'label label-danger',
            1 => 'label label-primary',
        );
        $this->set('play_status_clss', $play_status_clss);

        // thực hiện lấy ra danh sách từ bảng player
        $options = array();
        $options['order'] = array('last_action_at' => 'DESC');
        $search_by_phone = 0;

        // nếu thực hiện search theo số thuê bao
        if (isset($this->request->query['phone']) && strlen(trim($this->request->query['phone']))) {

            $phone = trim($this->request->query['phone']);
            $this->request->query['phone'] = $phone;
            $pretty_phone = $this->convertPhoneNumber($phone);
            $options['conditions']['phone']['$eq'] = $pretty_phone;
            $search_by_phone = 1;
        }

        $this->set('search_by_phone', $search_by_phone);

        if (!isset($this->request->query['from_date']) || !strlen(trim($this->request->query['from_date']))) {

            $this->request->query['from_date'] = date('d-m-Y', strtotime('first day of this month'));
        }

        if (!isset($this->request->query['to_date']) || !strlen(trim($this->request->query['to_date']))) {

            $this->request->query['to_date'] = date('d-m-Y');
        }

        // nếu thực hiện search theo số thuê bao, thì $list_data chỉ trả về 1 giá trị
        // thực hiện hiện thị ra lịch sử liên quan tới số điện thoại
        if ($search_by_phone) {

            $list_data = $this->{$this->modelClass}->find('all', $options);

            // lấy ra thông tin distributor
            $this->setDistributor($list_data);

            $this->set('list_data', $list_data);
            $opts_his = array(
                'order' => array(
                    'date' => 'DESC',
                ),
            );

            $from_date = trim($this->request->query('from_date'));
            $to_date = trim($this->request->query('to_date'));

            if (strlen($from_date)) {

                $opts_his['conditions']['date']['$gte'] = new MongoDate(strtotime($from_date . ' 00:00:00'));
            }

            if (strlen($to_date)) {

                $opts_his['conditions']['date']['$lte'] = new MongoDate(strtotime($to_date . ' 23:59:59'));
            }

            $opts_his['conditions']['phone']['$eq'] = $pretty_phone;
            $model_pattern = 'mo_%s_%s_%s';
            $his_data = $this->paginateDistributedHis($model_pattern, $opts_his);

            // lấy ra danh sách mt tương ứng với mỗi mo
            $this->setMT($his_data);

            $this->set('his_data', $his_data);
            $this->set('his_model', 'AppModel');

            $player_data = !empty($list_data[0]) ? $list_data[0] : array();
            // thực hiện set thông tin về player
            $this->set('player_data', $player_data);
        } else {

            $this->Paginator->settings = $options;
            $list_data = $this->Paginator->paginate($this->modelClass);

            // lấy ra thông tin distributor
            $this->setDistributor($list_data);

            $this->set('list_data', $list_data);
        }

        // lấy ra thông số thống kê
        $user_type = $this->Auth->user('type');
        if ($user_type == 0) {

            $stats = $this->getStats();
            $this->set('stats', $stats);
        }
    }

    public function buy() {

        $this->logAnyFile('================ START BUY...', __CLASS__ . '_' . __FUNCTION__);
        $arr_visitor_session = $this->Session->read('Auth.User');

        if (empty($arr_visitor_session)) {

            return $this->redirect('/visitors/login');
        } else {

            $phone = $arr_visitor_session['mobile'];
        }

        $this->logAnyFile("Play with Phone: $phone", __CLASS__ . '_' . __FUNCTION__);
        $player = $this->Player->getInfoByMobile($phone);

        if (empty($player)) {

            $this->logAnyFile("$phone: Khong ton tai Player", __CLASS__ . '_' . __FUNCTION__);
            return $this->redirect('/Packages/view');
        }

        $player = $player['Player'];

        $arr_player_update = [
            'id' => $player['id'],
            'time_last_action' => new MongoDate(),
        ];
        $this->logAnyFile("Play status: " . $player['status'], __CLASS__ . '_' . __FUNCTION__);

        if ($player['package_day']['status'] == 0 && $player['package_week']['status'] == 0 && $player['package_month']['status'] == 0) {

            $this->logAnyFile("$phone: Player chua dang ky goi", __CLASS__ . '_' . __FUNCTION__);
            return $this->redirect('/Packages/view');
        } else {

            $arr_player_update['time_charge'] = new MongoDate();
            $this->arr_action = Configure::read('sysconfig.Players.ACTION');
            $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
            $RESULT_CHARGE_OK = Configure::read('sysconfig.ChargingVMS.RESULT_CHARGE_OK');
            $MUA_price = Configure::read('sysconfig.ChargingVMS.MUA_price');

            $arr_mo = [
                'phone' => $phone,
                'short_code' => "",
                'package' => 'MUA',
                'package_day' => $player["package_day"]['status'],
                'package_week' => $player["package_week"]['status'],
                'package_month' => $player["package_month"]['status'],
                'channel' => 'WAP',
                'amount' => $MUA_price,
                'content' => "",
                'action' => $this->arr_action['MUA'],
                'status' => 0,
                //unv
                'distributor_code' => "",
                'distribution_channel_code' => "",
                'distributor_sharing' => "",
                'distribution_channel_sharing' => ""
                    //eunv
            ];

            $arr_mt = [
                'phone' => $phone,
                'short_code' => "",
                "question" => null,
                'content' => "",
                'channel' => 'WAP',
                'action' => $arr_mo['action'],
                'status' => 0,
            ];

            $arr_charge = array(
                'phone' => $phone,
                'amount' => $MUA_price,
                'service_code' => $service_code,
                'trans_id' => '',
                'channel' => 'WAP',
                'package' => 'MUA',
                'action' => $arr_mo['action'],
                'status' => 0,
                'details' => array(),
                'note' => '',
                //unv
                'distributor_code' => "",
                'distribution_channel_code' => "",
                'distributor_sharing' => "",
                'distribution_channel_sharing' => ""
                    //eunv
            );

            $arr_charge['amount'] = $MUA_price;
            $charge_result = $this->MobifoneCommon->charge($phone, $MUA_price, array(
                'log_file_name' => __CLASS__ . '_' . __FUNCTION__,
            ));
            $arr_charge['details'] = $charge_result;

            $this->logAnyFile("CHARGE_OK($RESULT_CHARGE_OK), result(" . $charge_result['pretty_message'] . ")", __CLASS__ . '_' . __FUNCTION__);

            if ($charge_result['status'] == 1) {

                //unv
                $this->generate_distributor_data($arr_charge, $player);
                //eunv
                $arr_charge['status'] = 1;
                $arr_mo['status'] = 1;
                $num_questions = $player['num_questions'];
                $answered_groups = $player['answered_groups'];

                $arr_group_all = $this->QuestionGroup->getArrGroupPackageOthers(1, $answered_groups, 'MUA');
                $arr_group_package = $arr_group_all['arr_group_package'];
                $arr_group_id = $arr_group_all['arr_group_id'];

                $arr_player_update['num_questions'] = array_merge($num_questions, $arr_group_package);
                $arr_player_update['answered_groups'] = array_merge($answered_groups, $arr_group_id);
                $arr_player_update['count_group_aday'] = $player['count_group_aday'] + count($arr_group_id);

                $arr_mt['content'] = "success";
                $arr_mt['status'] = 1;

                $this->logAnyFile("$phone Charge MUA(DK PAN) result successful: " . $charge_result['pretty_message'], __CLASS__ . '_' . __FUNCTION__);
            } else {

                $arr_charge['status'] = 0;
                $arr_mt['content'] = "failue";
                $arr_mt['status'] = 0;
                $arr_mo['status'] = 0;

                $this->logAnyFile("$phone Charge MUA(DK PAN) result failue: " . $charge_result['pretty_message'], __CLASS__ . '_' . __FUNCTION__);
            }
            $this->logAnyFile($arr_player_update, __CLASS__ . '_' . __FUNCTION__);

            $this->Player->save($arr_player_update);
            $this->Charge->insert($arr_charge);

            //unv
            $this->generate_distributor_data($arr_mo, $player);
            //eunv
            $mo_id = $this->Mo->insert($arr_mo);
            $arr_mt['mo_id'] = new MongoId($mo_id);
            $this->Mt->insert($arr_mt);
        }

        $this->logAnyFile('END BUY... redirect to /players/play', __CLASS__ . '_' . __FUNCTION__);
        return $this->redirect('/players/play');
    }

    public function play() {

        $this->logAnyFile('================ START PLAY...', __CLASS__ . '_' . __FUNCTION__);
        $arr_visitor_session = $this->Session->read('Auth.User');

//        $phone = $this->request->query("phone") ; 
        if (empty($arr_visitor_session)) {
//            return $this->redirect('/visitors/login');
        }
//        $phone = $arr_visitor_session['mobile'];

        $phone = "841254512999";
        $this->logAnyFile("Play with Phone: $phone", __CLASS__ . '_' . __FUNCTION__);
        $player = $this->Player->getInfoByMobile($phone);


        if (empty($player)) {
            $this->logAnyFile("$phone: Player khong ton tai", __CLASS__ . '_' . __FUNCTION__);
            return $this->redirect('/Packages/view');
        }

        $player = $player['Player'];

        $this->logAnyFile("Play status: " . $player['status'], __CLASS__ . '_' . __FUNCTION__);
        //debug($player);die;
        if ($player['package_day']['status'] == 0 && $player['package_week']['status'] == 0 && $player['package_month']['status'] == 0) {
            $this->logAnyFile("$phone: Player chua dang ky goi nao", __CLASS__ . '_' . __FUNCTION__);
            return $this->redirect('/Packages/view');
        } else {
            //Mảng cập nhật lại người chơi
            $arr_player_update = [
                "id" => $player['id'],
                "time_last_action" => new MongoDate(),
            ];

            $channel_play = $this->request->query('channel_play');

            $this->logAnyFile("CHANNEL_PLAY: $channel_play, player['channel_play']: " . $player['channel_play'] . $player['status'], __CLASS__ . '_' . __FUNCTION__);
            if ($channel_play == 'WAP' && $player['channel_play'] != 'WAP') {
                $player['channel_play'] = 'WAP';
                $arr_player_update['channel_play'] = 'WAP';
            }

            if ($player['channel_play'] != 'WAP') {
                $arr_data = [
                    'status' => 1,
                    'msg' => "Bạn đang chơi trên kênh " . $player['channel_play'] . " nên không thể chơi trên kênh WAP. " .
                    PHP_EOL . "Bạn có muốn chuyển sang kênh WAP để chơi không?",
                    'question_content' => "",
                    'question_index' => -1,
                    'time_remain' => 0,
                    'time_start' => 0,
                    'time_now' => 0,
                ];
            } else {
                $question_group = $player['question_group'];
                $num_questions = $player['num_questions'];

                $arr_data = null;
                //Kiểm tra xem con gói nào không
                if (empty($question_group) && !empty($num_questions)) {
                    $this->logAnyFile("Case: empty(question_group) && !empty(num_questions)", __CLASS__ . '_' . __FUNCTION__);

                    $group_id_new = array_shift($num_questions);
//                    var_dump($num_questions);
//                    var_dump($group_id_new);
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
                    }
                }

                //Câu hỏi đang trả lời
                if (!empty($question_group)) {

                    //                $question_current = $question_group['question_current'];

                    $time_start = $question_group['time_start']->sec;
                    $time_now = strtotime(date('Y-m-d H:i:s'));
                    $time_remain = ($time_start + 3 * 60) - $time_now;

                    $this->logAnyFile("Dang tra loi cau hoi, time_remain: $time_remain", __CLASS__ . '_' . __FUNCTION__);

                    if ($time_remain > 0) {
                        $msg = '<strong style="color:#43a047;"></strong><br>Bạn đang có ' . $player['score_total']['score'] . ' điểm, trả lời câu hỏi dưới đây nhé.';
                        $content = $question_group['question_current']['content'];
                        $index = $question_group['question_current']['index'];
                    } else {
                        $msg = '<strong style="color:#43a047;"></strong><br>Đã hết thời gian trả lời câu hỏi hiện tại, mời bạn nhấn TIẾP TỤC để trả lời câu hỏi tiếp theo!';
                        $content = "";
                        $index = 0;
                    }

                    $used_group_aday = ($player['count_group_aday'] - count($player['num_questions']));
                    $used_group_aday = empty($player['question_group']) ? $used_group_aday : $used_group_aday - 1;
                    $arr_data = [
                        'status' => 0,
                        'cate' => $question_group['cate'],
                        'msg' => $msg,
                        'question_content' => $content,
                        'question_index' => $used_group_aday * 5 + $index,
                        'time_remain' => $time_remain,
                        'time_start' => $time_start,
                        'time_now' => $time_now,
                    ];
                }
                //Hết gói câu hỏi
                else {
                    $this->logAnyFile("Da het goi cau hoi", __CLASS__ . '_' . __FUNCTION__);

                    $msg = '<strong style="color:#43a047;"></strong><br>Bạn đang có ' . $player['score_total']['score'] . ' điểm, bạn đã trả lời hết câu hỏi!';

                    $arr_data = [
                        'status' => 0,
                        'cate' => $question_group['cate'],
                        'msg' => $msg,
                        'question_content' => "",
                        'question_index' => -1,
                        'time_remain' => 0,
                        'time_start' => 0,
                        'time_now' => 0,
                    ];
                    $this->logAnyFile("$phone: Het goi cau hoi-> chuyen qua /Packages/view", __CLASS__ . '_' . __FUNCTION__);
                    //Hết gói thì chuyển qua màn hình MUA
                    return $this->redirect('/Packages/view');
                }
            }

            $this->Player->save($arr_player_update);
        }
        $this->logAnyFile('END PLAY!', __CLASS__ . '_' . __FUNCTION__);
        $this->set('arr_data', $arr_data);
    }

    /**
     * 1. Lấy câu trả lời
     * 2. Lấy câu hỏi của Player so sánh kết quả
     * 3. Trả về kết quả và câu hỏi tiếp theo
     * @return type
     */
    public function answer() {
        $this->layout = null;
        $this->autoRender = false;
        header('Content-Type: application/json');

        $this->logAnyFile('================ START ANSWER!', __CLASS__ . '_' . __FUNCTION__);

        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
        $this->arr_action = Configure::read('sysconfig.Players.ACTION');
//        $phone = $this->request->query("phone") ;
        $phone = $this->request->data['phone'];
        $arr_visitor_session = $this->Session->read('Auth.User');
        if (empty($arr_visitor_session) && $phone != '84942291166') {
            $arr_result['status'] = 0;
            $arr_result['msg'] = "Bạn chưa đăng nhập hoặc phiên làm việc của bạn đã hết! Bạn cần đăng nhập để chơi";
            return $arr_result;
        } else {
            $phone = $arr_visitor_session['mobile'];
        }
        //$phone = '841266317787'; 
        $this->logAnyFile("PLAYER PHONE: $phone", __CLASS__ . '_' . __FUNCTION__);

        $player = $this->Player->getInfoByMobile($phone);

//        $this->logAnyFile($player, __CLASS__ . '_' . __FUNCTION__);

        $arr_result = ['status' => 0, "msg" => "", "url" => "", "data" => []];

        if (empty($player)) {
            $arr_result['status'] = 1;
            $arr_result['msg'] = "Bạn chưa đăng ký dịch vụ. Vui lòng đăng ký dịch vụ để chơi game";
            $arr_result['url'] = "/visitors/login";
            return json_encode($arr_result);
        } else if ($player['Player']['channel_play'] != 'WAP') {
            $this->logAnyFile("$phone Dang choi tren " . $player['Player']['channel_play'] . " nen khong the choi tren WAP", __CLASS__ . '_' . __FUNCTION__);

            $arr_result['status'] = -1;
            $arr_result['msg'] = "Bạn đang chơi trên kênh " . $player['Player']['channel_play'] . " nên không thể chơi trên kênh WAP. " .
                    PHP_EOL . "Bạn có muốn chuyển sang kênh WAP để chơi không?";
            $arr_result['url'] = "/players/play?channel_play=WAP";
            return json_encode($arr_result);
        }

        $player = $player['Player'];

        if ($player['package_day']['status'] == 0 && $player['package_week']['status'] == 0 && $player['package_month']['status'] == 0) {
            $this->logAnyFile('PLAYER CANCEL!', __CLASS__ . '_' . __FUNCTION__);

            $arr_result['status'] = 2;
            $arr_result['msg'] = "Bạn cần đăng ký gói để chơi";
            $arr_result['url'] = "/Packages/view";
        } else {
            $this->logAnyFile('PLAYER REGISTER!', __CLASS__ . '_' . __FUNCTION__);

            $answer = (int) $this->request->data['answer'];
            $arr_mo = [
                'phone' => $phone,
                'short_code' => "",
                'package_day' => $player["package_day"]['status'],
                'package_week' => $player["package_week"]['status'],
                'package_month' => $player["package_month"]['status'],
                'channel' => 'WAP',
                'amount' => 0,
                'content' => $answer . "",
                'action' => $this->arr_action['TRA_LOI'],
                'status' => 1,
                //unv
                'distributor_code' => "",
                'distribution_channel_code' => "",
                'distributor_sharing' => "",
                'distribution_channel_sharing' => ""
                    //eunv
            ];

            //unv
            $this->generate_distributor_data($arr_mo, $player);
            //eunv

            $mo_id = $this->Mo->insert($arr_mo);

            $arr_mt = [
                'phone' => $phone,
                'mo_id' => new MongoId($mo_id),
                'short_code' => "",
                "question" => null,
                'content' => "",
                'channel' => 'WAP',
                'action' => $arr_mo['action'],
                'status' => 1,
            ];

            //Mảng cập nhật lịch sử điểm
            $arr_score_his = [
                'phone' => $phone,
                'score_total' => $player['score_total']['score'],
                'score' => 0,
                'action' => $arr_mo['action'],
                'channel' => 'WAP',
                'service_code' => $service_code,
                "question" => null,
                "details" => null,
                'status' => 0,
            ];

            //Mảng cập nhật lại người chơi
            $arr_player_update = $this->checkNewDayAndResetPlayer($player);

            $score_total = $player['score_total']['score'];
            $question_group = $player['question_group'];
            $num_questions = $player['num_questions'];
            $arr_player_update['package_day'] = $player['package_day'];
            $arr_player_update['package_week'] = $player['package_week'];
            $arr_player_update['count_group_aday'] = $player['count_group_aday'];
            $arr_player_update['num_questions'] = $player['num_questions'];
            $arr_player_update['question_group'] = $player['question_group'];

            $msg_answer = "";
            $msg_more = "";
            $question_index_next = 0;
            if ($answer == -1 || $answer == 0) {//bỏ qua
                $this->logAnyFile('PLAYER BỎ QUA!', __CLASS__ . '_' . __FUNCTION__);

                if (!empty($question_group['question_current'])) {
                    $time_answer = $this->getSecondFromNowNMongoDate($question_group["time_start"]);
                    $question_current = $question_group['question_current'];
                    $question_index_next = $question_current['index'] + 1;
                    $arr_mt["question"] = [
                        'group_id' => $question_group['group_id'],
                        'index' => $question_current['index'],
                        'time_start' => $question_group["time_start"],
                        'time' => $time_answer,
                        'answer' => $answer,
                        'correct' => false,
                    ];
                }

                $answer_stt = -1;
//                $arr_result['data']['msg'] = "Bạn đã bỏ qua câu hỏi trước!";
                //////$msg_answer = "Bạn đã bỏ qua câu hỏi trước!";
                $msg_answer = "Rất tiếc, bạn đã bỏ qua câu hỏi này, đáp án đúng là " . $question_current['answer'] . ". Bạn không ghi được điểm.";

                if ($this->request->data['lost_time'] != '' && $this->request->data['lost_time'] == '1') {
                    $msg_answer = "Rất tiếc, bạn đã hết thời gian trả lời câu hỏi và xem như bỏ qua câu hỏi này. Bạn đã không ghi được điểm nào trong câu hỏi này.";
                }
//                $msg_more = " Hãy tiếp tục trả lời câu hỏi sau đây nhé.";
            } else {
                $this->logAnyFile("PLAYER TRẢ LỜI: $answer", __CLASS__ . '_' . __FUNCTION__);

                if (!empty($question_group)) {
                    if (!empty($question_group['question_current'])) {
                        $question_current = $question_group['question_current'];

                        $question_index_next = $question_current['index'] + 1;

                        $time_answer = $this->getSecondFromNowNMongoDate($question_group["time_start"]);

                        if ($time_answer <= 180) {
                            if ($question_current['answer'] == $answer) {
                                $answer_stt = 1;
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

                                $score_total += $question_current_score;
                                $msg_answer = "Tuyệt vời, bạn đã trả lời chính xác. Số điểm bạn ghi được là $question_current_score.";
                                //                            $msg_more = " Hãy tiếp tục trả lời câu hỏi sau đây nhé.";
                                $arr_score_his['score_total'] = $arr_player_update['score_total'];
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
                                $answer_stt = 0;
                                $arr_mt["question"] = [
                                    'group_id' => $question_group['group_id'],
                                    'index' => $question_current['index'],
                                    'time_start' => $question_group["time_start"],
                                    'time' => $time_answer,
                                    'answer' => $answer,
                                    'correct' => false,
                                ];
                                $msg_answer = "Rất tiếc, bạn đã trải lời sai, đáp án đúng là " . $question_current['answer'] . ". Bạn không ghi được điểm cho câu hỏi này.";
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
                            $answer_stt = -1;
                            //$msg_answer = "Rất tiếc, bạn đã hết thời gian trả lời câu hỏi và xem như đã bỏ qua câu hỏi này và không ghi được điểm nào trong câu hỏi này.";
                            $msg_answer = "Rất tiếc, bạn đã hết thời gian trả lời câu hỏi và xem như bỏ qua câu hỏi này. Bạn đã không ghi được điểm nào trong câu hỏi này. Hãy tiếp tục trả lời câu hỏi tiếp theo nhé.";
                        }
                    }
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

                    $arr_result['data']['question_content'] = $question_group['question_current']['content'];
                    $arr_result['data']['time_remain'] = 180;
                    $arr_result['data']['cate'] = ['code' => '', 'name' => '',];
                    $used_group_aday = ($player['count_group_aday'] - count($player['num_questions']));
                    $used_group_aday = empty($player['question_group']) ? $used_group_aday : $used_group_aday - 1;
                    $arr_result['data']['question_index'] = $used_group_aday * 5 + $question_index_next;

                    $arr_player_update['question_group'] = $question_group;
                    $msg_more = " Hãy tiếp tục trả lời câu hỏi sau đây nhé.";
                }
            } else {
                $question_index_next = 0;
                $question_group['question_current'] = null;
                $arr_result['data']['question_index'] = -1;
            }

            $arr_score_his["question"] = $arr_mt["question"];

            $this->Score->insert_his($arr_score_his);

            $arr_player_update['num_questions_pending'] = $player['num_questions_pending'];
            //Check ngày hôm nay: đăng ký lại thì trả lại số câu hỏi còn lại
            if (empty($question_group['question_current']) && !empty($player['num_questions_pending']['package_day']['question_group'])) {
                $arr_player_update['question_group'] = $player['num_questions_pending']['package_day']['question_group'];
                $arr_player_update['num_questions_pending']['package_day'] = null;
            } else if (empty($question_group['question_current']) && !empty($player['num_questions_pending']['package_week']['question_group'])) {
                $arr_player_update['question_group'] = $player['num_questions_pending']['package_week']['question_group'];
                $arr_player_update['num_questions_pending']['package_week'] = null;
            }
            //Kiểm tra xem con gói nào không
            else if (empty($question_group['question_current']) && !empty($num_questions)) {
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
                        'answers' => null,
                    ];
                    if (!empty($cate_db)) {
                        $question_group['cate'] = ['code' => $cate_db['QuestionCategory']['code'], 'name' => $cate_db['QuestionCategory']['name']];
                    }

                    $arr_player_update['question_group'] = $question_group;

                    $arr_player_update['num_questions'] = $num_questions;
                    $msg_more = " Hãy tiếp tục trả lời câu hỏi sau đây nhé.";

                    $arr_result['data']['question_content'] = $question_group['question_current']['content'];
                    $arr_result['data']['time_remain'] = 180;
                    $arr_result['data']['cate'] = $question_group['cate'];
                    $used_group_aday = ($player['count_group_aday'] - count($player['num_questions']));
                    $used_group_aday = empty($player['question_group']) ? $used_group_aday : $used_group_aday - 1;
                    $arr_result['data']['question_index'] = $used_group_aday * 5;
                }
            } else if (empty($question_group['question_current'])) {
                $this->logAnyFile("Trả lời hết tất cả các gói câu hỏi", __CLASS__ . '_' . __FUNCTION__);

//                $arr_result['data']['msg']  = (empty($arr_result['data']['msg']) ? "" : $arr_result['data']['msg']);
                //$msg_more = "<br/>Xin chúc mừng! Bạn thật xuất giành được: $score_total điểm. Bạn đang nằm trong TOP thuê bao dẫn đầu của chương trình và cơ hội trúng THƯỞNG 10.000.000đ mỗi tuần rất cao. Để đến gần hơn với giải thưởng, nhấn TIẾP TỤC để mua thêm gói 5 câu hỏi và giành giải thưởng(2.000d/gói). ĐT hỗ trợ 9090 (200d/phút).";
                $msg_more = " Bạn đã dùng hết câu hỏi trong ngày, chọn TIẾP TỤC để mua thêm câu hỏi hoặc chọn DỪNG để dừng lại.";
            }

            $arr_mt["content"] = $arr_result['data']['msg'] = $msg_answer . $msg_more;
            if (!empty($arr_result['data']['question_content'])) {
//                $arr_result['data']['msg'] = $msg_answer . $msg_more;
                $arr_mt["content"] .= PHP_EOL + $arr_result['data']['question_content'];

                $index = (($arr_player_update['count_group_aday'] - count($arr_player_update['num_questions'])) * 5);
                $question_current_index = $arr_player_update['question_group']['question_current']['index'] + 1;
                $index = $index - (5 - $question_current_index);

                if (!empty($player['num_questions_pending']['package_day']['question_group'])) {
                    //Câu hỏi pending đang ở câu số: 
                    $index_pending = $player['num_questions_pending']['package_day']['question_group']['question_current']['index'] + 1;
                    $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
                }
                if (!empty($player['num_questions_pending']['package_week']['question_group'])) {
                    //Câu hỏi pending đang ở câu số: 
                    $index_pending = $player['num_questions_pending']['package_week']['question_group']['question_current']['index'] + 1;
                    $index = $index - ( 5 - $index_pending); //vì gói này được tính là đã trả lời(đã + 5 vào index) nên phải trừ đi số câu chưa trả lời
                }
                $arr_result['data']['question_index'] = $index - 1;
            }

            $this->Mt->insert($arr_mt);

            //Hết câu hỏi
            if (empty($question_group['question_current']) && empty($num_questions)) {
                $arr_player_update['question_group'] = null;
            }

            $this->Player->save($arr_player_update);
        }

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);
        $this->logAnyFile('================ END ANSWER!', __CLASS__ . '_' . __FUNCTION__);
        return json_encode($arr_result);
    }

    /**
     * Service Reset trạng thái người chơi(đã đk gói và đang chơi), chạy lúc 00:01 hàng ngày
     * 
     * 1. Check ngày mới
     *  - Chuyển điểm về lịch sử điểm ngày
     * 2. Check tuần mới
     *  - Chuyển điểm về lịch sử điểm tuần
     * 3. Check tháng mới
     *  - Chuyển điểm về lịch sử điểm tháng
     * 4. Cập nhật lại trạng thái Player
     *  - Reset lại điểm
     *  - Reset lại trạng thái
     */
    public function dailyReset() {
//        echo date("w");
//        $new_week = date('w', time());
//        var_dump($new_week);
//        die();
        $this->logAnyFile('================ START dailyReset...', __CLASS__ . '_' . __FUNCTION__);

        $this->initResponseText();
        $this->arr_player_status = Configure::read('sysconfig.Players.STATUS');
        $service_code = Configure::read('sysconfig.SERVICE_CODE.GAME_QUIZ');
        $offset = 0; //luôn = 0 vì sau đó record sẽ đc update;
        $limit = 20;
        $query = [
            'conditions' => [
                'status' => $this->arr_player_status['REGISTER'],
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
            'offset' => $offset,
        ];

        $arr_player_update = [
            'status' => $this->arr_player_status['NOT_CHARGE'],
            'package_day.status_charge' => 0,
            'package_day.daily_send_question' => 0,
            'package_week.status_charge' => 0,
            'package_week.daily_send_question' => 0,
            'package_month.status_charge' => 0,
            'package_month.daily_send_question' => 0,
            'score_day' => ["score" => 0, "time" => 0],
            'num_questions' => [],
        ];
        $arr_score = [
            'phone' => null,
            'comment' => '',
            'week' => null,
            'month' => null,
            'service_code' => $service_code,
        ];
        $arr_score_day = [
            'phone' => null,
            'comment' => '',
            'day' => null,
            'service_code' => $service_code,
        ];
        $new_week = (date("w") == 1);
        $new_month = (date("d") == 1);
        while (true) {

            $arr_player = $this->Player->find('all', $query);
            $count_player = count($arr_player);
            $this->logAnyFile("Lay $limit PLAYER, thuc te $count_player duoc lay", __CLASS__ . '_' . __FUNCTION__);

            if ($count_player > 0) {
                foreach ($arr_player as $key => $player) {
                    $player = $player['Player'];
                    $phone = $player['phone'];
                    // ======== CHUYỂN ĐIỂM ========
                    //Chuyển điểm qua bảng lịch sử điểm ngày
                    $this->logAnyFile("Chuyen diem $phone -> bang diem lich su Ngay, Tuan, Thang", __CLASS__ . '_' . __FUNCTION__);
                    $score_day = $player['score_day'];
                    $score_day['date'] = $player['time_last_action'];

                    $arr_score_day['phone'] = $player['phone'];
                    $arr_score_day['day'] = $score_day;

                    $this->ScoreDay->insert($arr_score_day);


                    //Chuyển điểm qua bảng lịch sử điểm tuần/tháng
//                    $this->logAnyFile("Chuyen diem $phone qua bang lich su diem Tuan/Thang", __CLASS__ . '_' . __FUNCTION__);
                    if ($new_week || $new_month) {
                        $arr_score['phone'] = $player['phone'];

                        if ($new_week) {
                            $index_week = date('W');
                            $index_week = ($index_week > 1 ) ? ($index_week - 1) : 52;

                            $score_week = $player['score_week'];
                            $score_week['index'] = $index_week;

                            $arr_score['week'] = $score_week;
                            $arr_player_update['score_week'] = ["score" => 0, "time" => 0];
                        }
                        if ($new_month) {
                            $index_month = date('m');
                            $index_month = ($index_month > 1 ) ? ($index_month - 1) : 12;
                            $score_month = $player['score_month'];
                            $score_month['index'] = $index_month;

                            $arr_score['month'] = $score_month;
                            $arr_player_update['score_month'] = ["score" => 0, "time" => 0];
                        }

                        $this->Score->insert($arr_score);
                    }


                    // END ======== CHUYỂN ĐIỂM ========
                    //Cập nhật lại trạng thái Player
//                    $this->logAnyFile("Cap nhat trang thai va Reset diem cua $phone", __CLASS__ . '_' . __FUNCTION__);
                    $arr_player_update['id'] = $player['id'];

                    $this->Player->save($arr_player_update);
                }

                $this->logAnyFile("Reset $count_player completed", __CLASS__ . '_' . __FUNCTION__);
            } else {
                $this->logAnyFile("$phone", __CLASS__ . '_' . __FUNCTION__);

                break;
            }
        }

        $this->logAnyFile('END dailyReset...', __CLASS__ . '_' . __FUNCTION__);
        return "1";
    }

    public function viewpackage() {
        
    }

    /**
     * 1. Check login
     * 2. Check tồn tại Player
     * 3. Check đã đăng ký gói
     * 4. Lưu vào bảng Charge và Redirect tới cổng thanh toán của VMS, truyền theo: "$sp_id&$trans_id&$pkg&$price&$url_return&$information"
     * 5. Check kết quả trả về ở action registresult
     * @param type $type
     * @return type
     * HoàngNN
     */
    public function registpackage($type = "G1") {

        $type = strtoupper($type);
        if ($type != 'G1' && $type != 'G7') {
            return $this->redirect('/Packages/view');
        }

        $arr_visitor_session = $this->Session->read('Auth.User');

        $phone = $arr_visitor_session['mobile'];
        $query = [
            'conditions' => [
                'phone' => $phone
            ]
        ];
        $player = $this->Player->find('first', $query);

        if (empty($player)) {
            $arr_player = [

                'phone' => $phone,
                'package_day' => ["status" => 0, 'status_charge' => 0, 'daily_send_question' => 0],
                'package_week' => ["status" => 0, 'status_charge' => 0, 'daily_send_question' => 0],
                'package_month' => ["status" => 0, 'status_charge' => 0, 'daily_send_question' => 0],
                'channel' => 'WAP',
                'channel_play' => "WAP",
                'score_total' => ['score' => 0, 'time' => 0,],
                'score_day' => ['score' => 0, 'time' => 0,],
                'score_week' => ['score' => 0, 'time' => 0,],
                'score_month' => ['score' => 0, 'time' => 0,],
                'status' => 1,
                'status_play' => 0,
                'time_register' => new MongoDate(),
                'time_register_first' => new MongoDate(),
                'time_last_action' => new MongoDate(),
                'distributor' => 'distributor_code',
                'comment' => '',
                'count_group_aday' => 0,
                'num_questions' => null,
                'answered_groups' => null,
                'question_group' => null,
                'current_question' => null,
                'created' => new MongoDate(),
                'modified' => new MongoDate(),
            ];

            $this->Player->save($arr_player);
        }

        $price = 0;
        if ($type == 'G1') {
            $price = Configure::read('sysconfig.ChargingVMS.G1_price');
        } else if ($type != 'G7') {
            $price = Configure::read('sysconfig.ChargingVMS.G7_price');
        }

        $seq_tbl_key = Configure::read('sysconfig.SEQ_TBL_KEY.seq_tbl_charge');
        $seq_tbl_charge = $this->Counter->getNextSequence($seq_tbl_key);
        $key = Configure::read('sysconfig.ChargingVMS.key');
        $sp_id = Configure::read('sysconfig.ChargingVMS.sp_id');
        $msisdn = "84902254086";
        $trans_id = $seq_tbl_charge;
        $pkg = $type;
        $url_return = Configure::read('sysconfig.ChargingVMS.url_return');
        $status = 1;
        $information = $type;
//        $key = "fB5FQeyUdvh7vvHf"; 
        $link = base64_encode($this->aes128_ecb_encrypt("$key", "$trans_id&$pkg&$price&$url_return&$information", ""));
        //$mahoa = $this->base64_encode(aes128_ecb_encrypt("$key",$link,""));
        $url_charge = Configure::read('sysconfig.ChargingVMS.url_charge') . "?sp_id=$sp_id&link=$link";

        //Thêm mới vào bảng CHARGE
//        charge_2015_01_01:
//[
//  {
//      _id: <ObjectId_money_history_id>,
//      phone: '',                                                  // Số điện thoại của player
//      amount: 2000,                                               // Số tiền trừ của khách hàng (VND)
//      service_code: 'S01',                                        // Mã dịch vụ
//      trans_id: 12345,                                            // Mã giao dịch: số tự tăng
//      channel: '',                                                // Kênh dùng để charge: SMS, WAP
//      package: '',                                                // gói dịch vụ, NGAY, TUAN, THANG
//      action: 'MUA',                                              // DK/GH/MUA/...    
//      cp: 'cp_code',                                              // Mã của cp tương ứng với số tiền bị trừ (nếu có)
//      distributor: 'distributor_code',                            // Mã của nhà phân phối tương ứng với số tiền bị trừ
//      cp_sharing: 0.2,                                            // Phần trăm của cp (xác định theo tỉ lệ chia thời điêm trừ tiền)
//      distributor_sharing: 0.2,                                   // Phần trăm của distributor (xác định theo tỉ lệ chia thời điểm trừ tiền)
//      status: 1,                                                  // 0: thành công, 1: ko thành công
//      created: "2015-01-01 00:00:00",
//      modified: "2015-01-01 00:00:00",
//  }
//]



        return $this->redirect($url_charge);
    }

    public function testseq() {
        $seq_tbl_key = Configure::read('sysconfig.SEQ_TBL_KEY.seq_tbl_charge');
        $seq_tbl_charge = $this->Counter->getNextSequence($seq_tbl_key);

        echo $seq_tbl_charge;
    }

    public function registresult() {
        $link = $this->request->query('link');

        $link = str_replace(" ", "+", $link);

        $key = "9ABxlwWpn6mGzdlU";
        $giaima = $this->aes128_ecb_decrypt("$key", base64_decode($link), "");
        echo "($link) Sau khi giai ma: $giaima";
    }

    public function pointHis() {

        if (!$this->isAllow()) {

            return $this->redirect($this->Auth->loginRedirect);
        }

        $this->set('model_name', 'AppModel');
        $breadcrumb = array();
        $breadcrumb[] = array(
            'url' => Router::url(array('action' => 'index')),
            'label' => __('player_title'),
        );
        $breadcrumb[] = array(
            'url' => Router::url(array('action' => __FUNCTION__)),
            'label' => __('point_title'),
        );
        $this->set('breadcrumb', $breadcrumb);
        $this->set('page_title', __('point_title'));

        // bắt buộc phải truyền vào số điện thoại mới có kết quả
        if (!$this->requiredPhone()) {

            return;
        }

        $options = array(
            'order' => array(
                'date' => 'DESC',
            ),
        );

        if (!isset($this->request->query['from_date']) || !strlen(trim($this->request->query['from_date']))) {

            $this->request->query['from_date'] = date('d-m-Y', strtotime('first day of this month'));
        }

        if (!isset($this->request->query['to_date']) || !strlen(trim($this->request->query['to_date']))) {

            $this->request->query['to_date'] = date('d-m-Y');
        }

        $this->setSearchConds($options);

        $point_action = Configure::read('sysconfig.' . $this->name . '.point_action');
        $this->set('point_action', $point_action);
        $model_pattern = 'point_%s_%s_history';

        $list_data = $this->paginateDistributedHis($model_pattern, $options);
        $this->set('list_data', $list_data);
    }

    public function exportChargeHis() {

        $this->autoRender = false;

        $from_date = $this->request->query('from_date');
        $to_date = $this->request->query('to_date');
        $options = array(
            'order' => array(
                'date' => 'DESC',
            ),
        );
        $options['conditions']['status']['$eq'] = 0;

        if (empty($from_date) || empty($to_date)) {

            exit();
        }

        $this->setSearchConds($options);

        $model_pattern = 'charge_%s_%s_history';
        $list_data = $this->findDistributedHis($model_pattern, $options);
        $this->setConvertDate($list_data, array('date', 'receive_date'));
        if (empty($list_data)) {

            exit();
        }

        $charge_action = Configure::read('sysconfig.Player.charge_action');

        $headers = array('Số thuê bao', 'Tác vụ', 'Giá tiền', 'Nguồn', 'MO', 'Thời gian');
        $file_path = APP . WEBROOT_DIR . DS . 'tmp' . DS . 'doisoat.csv';
        $fp = fopen($file_path, 'w');
        fputcsv($fp, $headers);
        foreach ($list_data as $item) {

            $line = array(
                $item['AppModel']['phone'],
                !empty($item['AppModel']['action']) && !empty($charge_action[$item['AppModel']['action']]) ? $charge_action[$item['AppModel']['action']] : __('unknown'),
                $item['AppModel']['amount'],
                !empty($item['AppModel']['channel']) ? $item['AppModel']['channel'] : '',
                !empty($item['AppModel']['action']) ? $item['AppModel']['action'] : '',
                $item['AppModel']['date'],
            );
            fputcsv($fp, $line);
        }
        fclose($fp);

        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=doisoat.csv');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        header("Pragma: no-cache");
        header("Expires: 0");

        readfile($file_path);
        exit;
    }

    protected function setSearchConds(&$options) {

        if (isset($this->request->query['phone']) && strlen(trim($this->request->query['phone']))) {

            $phone = trim($this->request->query['phone']);
            $this->request->query['phone'] = $phone;
            $pretty_phone = $this->convertPhoneNumber($phone);
            $options['conditions']['phone']['$eq'] = $pretty_phone;
        }

        if (isset($this->request->query['from_date']) && strlen(trim($this->request->query['from_date']))) {

            $from_date = trim($this->request->query['from_date']);
            $this->request->query['from_date'] = $from_date;
            $options['conditions']['date']['$gte'] = new MongoDate(strtotime($from_date . ' 00:00:00'));
        }

        if (isset($this->request->query['to_date']) && strlen(trim($this->request->query['to_date']))) {

            $to_date = trim($this->request->query['to_date']);
            $this->request->query['to_date'] = $to_date;
            $options['conditions']['date']['$lte'] = new MongoDate(strtotime($to_date . ' 23:59:59'));
        }
    }

    protected function setMT(&$his_data) {

        if (empty($his_data)) {

            return;
        }

        foreach ($his_data as $k => $v) {

            $mo_id = $v['AppModel']['id'];
            $date = $v['AppModel']['date'];
            $mt = $this->getMTByMO($mo_id, $date);
            $his_data[$k]['AppModel']['mt'] = $mt;
        }
    }

    protected function setDistributor(&$list_data) {

        if (empty($list_data)) {

            return;
        }

        $distributor = array();
        foreach ($list_data as $k => $v) {

            $dis_code = $v[$this->modelClass]['distributor'];
            if (empty($distributor[$dis_code])) {

                $distributor[$dis_code] = $this->getDistributor($dis_code);
            }

            $list_data[$k][$this->modelClass]['distributor'] = $distributor[$dis_code] . ' (' . $dis_code . ')';
        }
    }

    protected function getMTByMO($mo_id, $date) {

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $model_pattern = 'mt_%s_%s_history';

        $options = array(
            'conditions' => array(
                'mo' => array(
                    '$eq' => new MongoId($mo_id),
                ),
            ),
        );

        App::uses('AppModel', 'Model');
        $His = new AppModel(array(
            'table' => sprintf($model_pattern, $year, $month),
        ));

        $mt = $His->find('all', $options);
        return $mt;
    }

    protected function paginateDistributedHis($model_pattern, $options) {

        // kiểm tra bắt buộc phải có khoảng thời gian from_date và to_date
        if (!$this->requiredDateRange()) {

            return;
        }

        $from_date = trim($this->request->query['from_date']);
//      $to_date = trim($this->request->query['to_date']);
//      $date_range = $this->extractDateRange($from_date, $to_date);
        if (empty($from_date)) {

            $from_date = date('d-m-Y');
        }

        $start = strtotime($from_date);

        $table_name = "mo_" . date('Y_m_d', $start);

        $this->Paginator->settings = $options;
        // tạo Model object động theo $year và $month
        App::uses('AppModel', 'Model');
        $His = new AppModel(array(
            'table' => $table_name,
        ));

        $list_data = $this->Paginator->paginate($His);

        return $list_data;
        // với trường hợp lý tưởng tìm kiếm trong phạm vi của đúng 1 tháng
//      if (count($date_range) == 1) {
//
//          $year = $date_range[0]['year'];
//          $month = $date_range[0]['month'];
//          $day = $date_range[0]['day'];
//
//          $this->Paginator->settings = $options;
//          // tạo Model object động theo $year và $month
//          App::uses('AppModel', 'Model');
//          $His = new AppModel(array(
//              'table' => sprintf($model_pattern, $year, $month),
//          ));
//
//          $list_data = $this->Paginator->paginate($His);
//          $this->setConvertDate($list_data, array('date', 'receive_date'));
//
//          return $list_data;
//      }
//      // với trường hợp tìm kiếm qua nhiều tháng khác nhau
//      else {
//
//          $options['date_range'] = $date_range;
//          $options['model_pattern'] = $model_pattern;
//          App::uses('DistributedHis', 'Model');
//          $His = new DistributedHis();
//
//          $this->Paginator->settings = $options;
//          $list_data = $this->Paginator->paginate($His);
//          $this->setConvertDate($list_data, array('date', 'receive_date'));
//
//          return $list_data;
//      }
    }

    protected function findDistributedHis($model_pattern, $options) {

        // kiểm tra bắt buộc phải có khoảng thời gian from_date và to_date
        if (!$this->requiredDateRange()) {

            return;
        }

        $from_date = trim($this->request->query['from_date']);
        $to_date = trim($this->request->query['to_date']);

        $date_range = $this->extractDateRange($from_date, $to_date);
        if ($date_range === false) {

            return;
        }

        // với trường hợp lý tưởng tìm kiếm trong phạm vi của đúng 1 tháng
        if (count($date_range) == 1) {

            $year = $date_range[0]['year'];
            $month = $date_range[0]['month'];

            // tạo Model object động theo $year và $month
            App::uses('AppModel', 'Model');
            $His = new AppModel(array(
                'table' => sprintf($model_pattern, $year, $month),
            ));

            $list_data = $His->find('all', $options);
            $this->setConvertDate($list_data, array('date', 'receive_date'));

            return $list_data;
        }
        // với trường hợp tìm kiếm qua nhiều tháng khác nhau
        else {

            $options['date_range'] = $date_range;
            $options['model_pattern'] = $model_pattern;
            App::uses('DistributedHis', 'Model');
            $His = new DistributedHis();

            $list_data = $His->findDistributed($options);
            $this->setConvertDate($list_data, array('date', 'receive_date'));

            return $list_data;
        }
    }

    protected function setConvertDate(&$list_data, $datetime_fields) {

        if (empty($list_data)) {

            return;
        }

        foreach ($list_data as $k => $v) {

            foreach ($datetime_fields as $field) {

                if (isset($v['AppModel'][$field]) && $v['AppModel'][$field] instanceof MongoDate) {

                    $list_data[$k]['AppModel'][$field] = date('d-m-Y H:i:s', $v['AppModel'][$field]->sec);
                }
            }
        }
    }

    protected function extractDateRange($from_date, $to_date) {

        if (strtotime($from_date) > strtotime($to_date)) {

            return false;
        }

        $date_range = array();

        // lấy ra danh sách cặp (year, month) trong khoảng from_date và to_date
        // cần thiết phải lấy ra do với các bảng history, đều được lưu trữ 1 tháng 1 collection
        $start = strtotime($from_date);
        $end = strtotime($to_date);

        $start_month = date('Ym', $start);
        $end_month = date('Ym', $end);
        while ($start_month <= $end_month) {

            $date_range[] = array(
                'year' => date('Y', $start),
                'month' => date('m', $start),
            );
            $start = strtotime("+1 month", $start);
            $start_month = date('Ym', $start);
        }

        return array_reverse($date_range); // đảo ngược thứ tự, cho năm tháng gần nhất lên đầu
    }

    protected function requiredDateRange() {

        if (!isset($this->request->query['from_date']) || strlen(trim($this->request->query['from_date'])) <= 0) {

            return false;
        }

        if (!isset($this->request->query['to_date']) || strlen(trim($this->request->query['to_date'])) <= 0) {

            return false;
        }

        return true;
    }

    protected function requiredPhone() {

        if (!isset($this->request->query['phone']) || strlen(trim($this->request->query['phone'])) <= 0) {

            return false;
        }

        return true;
    }

    protected function getDistributor($dis_code) {

        $get_dis_code = $this->Distributor->find('first', array(
            'conditions' => array(
                'code' => array(
                    '$regex' => new MongoRegex("/" . $dis_code . "/"),
                ),
            ),
        ));

        return !empty($get_dis_code) ? $get_dis_code['Distributor']['name'] : __('unknown');
    }

    protected function getStats($options = array()) {

        $options['conditions']['aggregate'][] = array(
            '$group' => array(
                '_id' => null,
                'active' => array(
                    '$sum' => '$active',
                ),
                'register' => array(
                    '$sum' => '$register',
                ),
                'buy_question' => array(
                    '$sum' => '$buy_question',
                ),
                'deactive' => array(
                    '$sum' => '$deactive',
                ),
                'self_deactive' => array(
                    '$sum' => '$self_deactive',
                ),
                'system_deactive' => array(
                    '$sum' => '$system_deactive',
                ),
                'renew' => array(
                    '$sum' => '$renew',
                ),
                'renew_success' => array(
                    '$sum' => '$renew_success',
                ),
            ),
        );
        $this->loadModel('DailyReport');
        $stats = $this->DailyReport->find('first', $options);

        return !empty($stats['DailyReport']) ? $stats['DailyReport'] : array();
    }

    protected function setInit() {

        $this->set('model_name', $this->modelClass);
        $this->set('status', Configure::read('sysconfig.' . $this->name . '.status'));
        $this->set('play_status', Configure::read('sysconfig.' . $this->name . '.play_status'));
        $this->set('mo_status', Configure::read('sysconfig.' . $this->name . '.mo_status'));
        $this->set('mo_check_status', self::MO_CHECK_STATUS);
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

    //eunv
}
