<?php

App::uses('AppController', 'Controller');

class WinnerController extends AppController {

	public $uses = array(
		'Winner',
		'Player',
		'Configuration',
	);
	public $components = array('DistributedHis');

	const DAILY_WINNER_TYPE = 0;
	const MONTHLY_WINNER_TYPE = 2;
	const DAILY_WINNER_MT_TYPE = 'M11';
	const MONTHLY_WINNER_MT_TYPE = 'M12';
	const DEFAULT_CHOOSE_DAILY = '-1 day';
	const DEFAULT_CHOOSE_MONTHLY = 'first day of previous month';
	const MAX_TOP_DAILY = 5;
	const HIDE_PHONE_LETTER = 'xxx';
	const HIDE_PHONE_PORTION = 5;
	const PLAYING_STATUS = 1; // trạng thái đang chơi gameshow
	const TIME_START_SERVICE_FIELD = 'time_start_service'; // tên trường field trong Configuration collection, đánh dấu ngày bắt đầu dịch vụ
	const FORCE_CREATE_POINT_MONTHLY_REPORT = 1; // cờ xác định việc cưỡng ép tạo lại PlayerPointMonthlyReport
	const WINNER_DAILY_CMS = 'WINNER_DAILY_CMS';
	const WINNER_MONTHLY_CMS = 'WINNER_MONTHLY_CMS';

	public function index() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->setInit();
		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => 'index')),
			'label' => __('win_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('win_title'));

		$type = Configure::read('sysconfig.' . $this->name . '.type');
		$this->set('type', $type);

		$options = array();
		$options['order'] = array('date' => 'DESC');
		$this->setSearchConds($options);

		$this->Paginator->settings = $options;
		$list_data = $this->Paginator->paginate($this->modelClass);

		$this->setPlayer($list_data);

		$this->set('list_data', $list_data);

		$this->set('status', Configure::read('sysconfig.Player.status'));

		// khởi tạo class dùng css cho status
		$status_clss = array(
			-1 => 'label label-danger',
			0 => 'label label-danger',
			1 => 'label label-primary',
			2 => 'label label-warning',
			3 => 'label label-warning',
			4 => 'label label-danger',
		);
		$this->set('status_clss', $status_clss);

		// lấy ra mt_msg tương ứng với loại winner
		$mt_msgs = array();
		$config = $this->Configuration->find('first');
		if (empty($config)) {

			throw new CakeException(__('The config is not exist'));
		}

		$mt = $config['Configuration']['mt'];
		if (empty($mt) && !is_array($mt)) {

			throw new CakeException(__('The config is invalid'));
		}

		foreach ($mt as $v) {

			if (empty($v['code']) || empty($v['msg'])) {

				continue;
			}

			if ($v['code'] == self::DAILY_WINNER_MT_TYPE) {

				$mt_msgs[self::DAILY_WINNER_TYPE] = $v['msg'];
			} elseif ($v['code'] == self::MONTHLY_WINNER_MT_TYPE) {

				$mt_msgs[self::MONTHLY_WINNER_TYPE] = $v['msg'];
			}
		}
		$this->set('mt_msgs', $mt_msgs);
	}

	protected function setPlayer(&$list_data) {

		if (empty($list_data)) {

			return;
		}

		foreach ($list_data as $k => $v) {

			$phone = $v[$this->modelClass]['phone'];

			$player = $this->getPlayer($phone);
			$list_data[$k][$this->modelClass]['player'] = $player;

			// tạo ra link tới màn hình quản lý thuê bao
			$query_str = array(
				'from_date' => $this->request->query('from_date'),
				'to_date' => $this->request->query('to_date'),
				'phone' => $phone,
			);
			$list_data[$k][$this->modelClass]['link'] = Router::url(array(
						'controller' => 'Player',
						'action' => 'index',
						'?' => $query_str,
							), true);
		}
	}

	protected function getPlayer($phone) {

		$player = $this->Player->find('first', array(
			'conditions' => array(
				'phone' => array(
					'$eq' => $phone,
				),
			),
		));

		return $player;
	}

	public function chooseDaily() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => 'index')),
			'label' => __('win_title'),
		);
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => __FUNCTION__)),
			'label' => __('poi_dai_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('poi_dai_title'));
		$this->set('winner_type', self::DAILY_WINNER_TYPE);

		$this->set('status', Configure::read('sysconfig.Player.status'));

		// khởi tạo class dùng css cho status
		$status_clss = array(
			-1 => 'label label-danger',
			0 => 'label label-danger',
			1 => 'label label-primary',
			2 => 'label label-warning',
			3 => 'label label-warning',
			4 => 'label label-danger',
		);
		$this->set('status_clss', $status_clss);

		$btn_clss = array(
			-1 => 'btn-danger',
			0 => 'btn-danger',
			1 => 'btn-primary',
			2 => 'btn-warning',
			3 => 'btn-warning',
			4 => 'btn-danger',
		);
		$this->set('btn_clss', $btn_clss);

		// tiêu chí trọn ra người trúng thưởng là đạt điểm cao nhất và nhanh nhất
		$options = array(
			'order' => array(
				'point' => 'DESC',
				'point_last_date' => 'ASC',
			),
		);
		$model_pattern = 'player_point_daily_%s_%s_report';

		if (empty($this->request->query)) {

			$this->request->query['date'] = date('d-m-Y', strtotime(self::DEFAULT_CHOOSE_DAILY));
		}

		if (isset($this->request->query['date']) && strlen($this->request->query['date'])) {

			$date = trim($this->request->query['date']);
		} else {

			exit();
		}

		$list_data = $this->DistributedHis->paginate($model_pattern, $date . ' 00:00:00', $date . ' 23:59:59', $options);
		$type = self::DAILY_WINNER_TYPE;
		$this->setWinner($list_data, $type);

		// lọc chỉ lấy các thuê bao đang chơi gameshow
		$this->setStatus($list_data);

		$this->set('list_data', $list_data);
	}

	public function chooseMonthly() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => 'index')),
			'label' => __('win_title'),
		);
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => __FUNCTION__)),
			'label' => __('poi_mon_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('poi_mon_title'));
		$this->set('winner_type', self::MONTHLY_WINNER_TYPE);

		$this->set('status', Configure::read('sysconfig.Player.status'));

		// khởi tạo class dùng css cho status
		$status_clss = array(
			-1 => 'label label-danger',
			0 => 'label label-danger',
			1 => 'label label-primary',
			2 => 'label label-warning',
			3 => 'label label-warning',
			4 => 'label label-danger',
		);
		$this->set('status_clss', $status_clss);

		$btn_clss = array(
			-1 => 'btn-danger',
			0 => 'btn-danger',
			1 => 'btn-primary',
			2 => 'btn-warning',
			3 => 'btn-warning',
			4 => 'btn-danger',
		);
		$this->set('btn_clss', $btn_clss);

		// tiêu chí trọn ra người trúng thưởng là đạt điểm cao nhất và nhanh nhất
		$model_pattern = 'player_point_monthly_%s_%s_report';

		if (empty($this->request->query)) {

			$this->request->query['monthly'] = date('m-Y', strtotime(self::DEFAULT_CHOOSE_MONTHLY));
		}

		if (isset($this->request->query['monthly']) && strlen($this->request->query['monthly'])) {

			$monthly = trim($this->request->query['monthly']);
			$monthly = '01-' . $monthly;
		} else {

			exit();
		}

		// tạo collection chứa point theo ngày của tất cả player theo monthly
		$this->makePlayerPointMonthlyReport($model_pattern, $monthly);

		// thực hiện aggregate trên collection này để tính ra tổng point trong 1 tháng
		// xác định year và month
		$year = date('Y', strtotime($monthly));
		$month = date('m', strtotime($monthly));
		$model_name = sprintf($model_pattern, $year, $month);

		$PlayerPointMonthly = new AppModel(array(
			'table' => $model_name,
		));

		$options = array();
		$options['conditions']['aggregate'][] = array(
			'$group' => array(
				'_id' => '$phone',
				'point' => array(
					'$sum' => '$point',
				),
				'point_last_date' => array(
					'$max' => '$point_last_date',
				),
				'date' => array(
					'$max' => '$point_last_date',
				),
			),
		);
		$options['conditions']['aggregate'][] = array(
			'$sort' => array(
				'point' => -1,
				'point_last_date' => 1,
			),
		);

		$this->Paginator->settings = $options;
		$list_data = $this->Paginator->paginate($PlayerPointMonthly);
		$type = self::MONTHLY_WINNER_TYPE;
		$this->convertDate($list_data);
		$this->setWinner($list_data, $type, '_id');

		// lọc chỉ lấy các thuê bao đang chơi gameshow
		$this->setStatus($list_data, '_id');

		$this->set('model_name', 'AppModel');
		$this->set('list_data', $list_data);
	}

	public function reqSendMt($id = null) {

		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->autoRender = false;

		$config = $this->Configuration->find('first');
		if (empty($config)) {

			throw new CakeException(__('The config is not exist'));
		}

		$mt = $config['Configuration']['mt'];
		if (empty($mt) && !is_array($mt)) {

			throw new CakeException(__('The config is invalid'));
		}

		$type = $this->request->query('type');
		$phone = $this->request->query('phone');
		$mt_code = self::DAILY_WINNER_MT_TYPE;
		$mt_msg = '';
		if ($type == self::DAILY_WINNER_TYPE) {

			$mt_code = self::DAILY_WINNER_MT_TYPE;
		} elseif ($type == self::MONTHLY_WINNER_TYPE) {

			$mt_code = self::MONTHLY_WINNER_MT_TYPE;
		}
		foreach ($mt as $v) {

			if (empty($v['code']) || empty($v['msg'])) {

				continue;
			}

			if ($v['code'] == $mt_code) {

				$mt_msg = $v['msg'];
			}
		}

		if (empty($mt_msg)) {

			throw new CakeException(__('Dont have "msg" in "mt" config'));
		}

		$winner = $this->{$this->modelClass}->find('first', array(
			'conditions' => array(
				'id' => new MongoId($id),
			),
		));
		if (empty($winner)) {

			throw new CakeException(__('The winner is not exist'));
		}

		// nếu winner chưa được gửi mt
//		if (empty($winner[$this->modelClass]['is_send_mt'])) {

		$msisdn = $winner[$this->modelClass]['phone'];
		$service_id = $config['Configuration']['center_number_only_this'];
		$transid = 'cms_' . uniqid();

		$sent = $this->sendMt($msisdn, $service_id, $mt_msg, $transid);
		if ($sent) {

			// thực hiện log vào bảng MO - MT
			$this->logSendMtInMoHis($phone, $type, $mt_msg);

			$this->{$this->modelClass}->save(array(
				'id' => $id,
				'is_send_mt' => 1,
			));
			echo json_encode(array(
				'error_code' => 0,
				'message' => __('send_mt_to_winner_success', $phone),
			));
		} else {

			echo json_encode(array(
				'error_code' => 2,
				'message' => __('send_mt_to_winner_error', $phone),
			));
		}
//	}
		// nếu winner đã được gửi mt
//		else {
//
//			echo json_encode(array(
//				'error_code' => 1,
//				'message' => __('cant_send_mt_to_winner'),
//			));
//		}
	}

	protected function logSendMtInMoHis($phone, $type, $mt_msg) {

		App::uses('AppModel', 'Model');
		$year = date('Y');
		$month = date('m');

		$configuration = $this->Configuration->find('first');
		if (empty($configuration)) {

			throw new Exception("Configuration doesn't exist");
		}

		$service_code = $configuration['Configuration']['code'];
		$center_number_only_this = $configuration['Configuration']['center_number_only_this'];

		$player = $this->Player->find('first', array(
			'conditions' => array(
				'phone' => array(
					'$eq' => $phone,
				),
			),
		));

		$action_type = self::WINNER_DAILY_CMS;
		if ($type == self::DAILY_WINNER_TYPE) {

			$action_type = self::WINNER_DAILY_CMS;
		} elseif ($type == self::MONTHLY_WINNER_TYPE) {

			$action_type = self::WINNER_MONTHLY_CMS;
		}

		$mo_his_pattern = 'mo_%s_%s_history';
		$mo_his = sprintf($mo_his_pattern, $year, $month);

		$mt_his_pattern = 'mt_%s_%s_history';
		$mt_his = sprintf($mt_his_pattern, $year, $month);

		$MoHis = new AppModel(array(
			'table' => $mo_his,
		));

		$MtHis = new AppModel(array(
			'table' => $mt_his,
		));

		$mo_data = array(
			'service_code' => $service_code,
			'channel' => !empty($player['Player']['channel']) ? $player['Player']['channel'] : '',
			'package' => !empty($player['Player']['package']) ? $player['Player']['package'] : '',
			'service' => $center_number_only_this,
			'phone' => $phone,
			'amount' => 0,
			'action' => $action_type,
			'reaction' => '',
			'content' => '',
			'date' => new MongoDate(time()),
			'receive_date' => new MongoDate(time()),
		);
		$MoHis->save($mo_data);

		$mt_data = array(
			'mo' => new MongoId($MoHis->getLastInsertID()),
			'service' => $center_number_only_this,
			'phone' => $phone,
			'action' => $action_type,
			'content' => $mt_msg,
			'status' => 1,
			'date' => new MongoDate(time()),
		);
		$MtHis->save($mt_data);
	}

	/**
	 * makePlayerPointMonthlyReport
	 * Tạo ra PlayerPointMonthlyReport collection từ các collection daily
	 * 
	 * @param string $model_pattern
	 * @param string $monthly
	 */
	protected function makePlayerPointMonthlyReport($model_pattern, $monthly) {

		App::uses('AppModel', 'Model');
		$date = $monthly;

// xác định year và month
		$year = date('Y', strtotime($date));
		$month = date('m', strtotime($date));

// lấy ra ngày khởi tạo chu kỳ tính của dịch vụ
		$config = $this->Configuration->find('first');
		if (empty($config['Configuration']['time_start_service'])) {

			throw new CakeException(__('time_start_service_exception'));
		}
		$time_start_service = $config['Configuration']['time_start_service'];
// lấy ra thứ tự ngày bắt đầu của chu kỳ
		$peroid_date_th = date('d', strtotime($time_start_service));

// ngày bắt đầu mỗi chu kỳ
		$raw_peroid_start_date = $peroid_date_th . '-' . $month . '-' . $year . ' 00:00:00';
		$d = new DateTime($raw_peroid_start_date);
		$d->modify('next month');
// ngày kết thúc mỗi chu kỳ
		$peroid_end_date = $d->format(DATE_ISO8601);
		$peroid_start_date = date(DATE_ISO8601, strtotime($raw_peroid_start_date));

		$model_name = sprintf($model_pattern, $year, $month);
		$PointMonthlyReport = new AppModel(array(
			'table' => $model_name,
		));
// thực hiện chuyển sang cấu hình database dành cho admin
		$PointMonthlyReport->useDbConfig = 'admin';
		$mongo = $PointMonthlyReport->getDataSource();

// kiểm tra xem $PointMonthlyReport đã tồn tại chưa, nếu chưa thì tạo
// hoặc đặt self::FORCE_CREATE_POINT_MONTHLY_REPORT = 1 thì cưỡng ép tạo
		$is_collection_exist = $this->isCollectionExists($mongo, $model_name);
		if (!$is_collection_exist || self::FORCE_CREATE_POINT_MONTHLY_REPORT) {

			$this->createPlayerPointMonthlyReport($mongo, $PointMonthlyReport, $peroid_start_date, $peroid_end_date);
			return true;
		}

// nếu collection đã tồn tại (từ tháng trước đó) sẽ không thực hiện tạo lại nữa
		$current_monthly = date('Ym');
		if ($year . $month < $current_monthly) {

			return true;
		}
// nếu collection trong tháng hiện tại thì tạo lại liên tục
		else {

			$this->createPlayerPointMonthlyReport($mongo, $PointMonthlyReport, $peroid_start_date, $peroid_end_date);
			return true;
		}
	}

	/**
	 * isCollectionExists
	 * Kiểm tra xem collection đã tồn tại trong database hay không?
	 * 
	 * @param object $mongo
	 * @param string $collection_name
	 * 
	 * @return boolean
	 */
	protected function isCollectionExists($mongo, $collection_name) {

		App::uses('ConnectionManager', 'Model');
		$dataSource = ConnectionManager::getDataSource('default');
		$database_name = $dataSource->config['database'];
		$mongoDb = $mongo->getMongoDb();

		if ($mongoDb->system->namespaces->findOne(array('name' => $database_name . '.' . $collection_name)) === null) {

			return false;
		}

		return true;
	}

	/**
	 * createPlayerPointMonthlyReport
	 * Tạo ra PlayerPointMonthlyReport collection, 
	 * chứa các dữ liệu gộp từ các PlayerPointDailyReport trong chu kỳ 1 tháng
	 * 
	 * @param object $mongo
	 * @param object $PointMonthlyReport
	 * @param string $peroid_start_date
	 * @param string $peroid_end_date
	 * 
	 * @return boolean
	 */
	protected function createPlayerPointMonthlyReport($mongo, $PointMonthlyReport, $peroid_start_date, $peroid_end_date) {

		$date_range = $this->extractDateRange($peroid_start_date, $peroid_end_date);
		if ($date_range === false) {

			return false;
		}

		$mongoCollectionObject = $mongo->getMongoCollection($PointMonthlyReport);
		$mongoCollectionObject->drop();

		$conds_json = '{"date":{"$gte":ISODate("' . $peroid_start_date . '"),"$lt":ISODate("' . $peroid_end_date . '")}}';

		App::uses('ConnectionManager', 'Model');
		$dataSource = ConnectionManager::getDataSource('default');

		$connection = $mongo->connection;
		$mongo = $connection->selectDB($dataSource->config['database']);

		$command = '';
		foreach ($date_range as $v) {

			$year = $v['year'];
			$month = $v['month'];
			$point_daily_report_pattern = 'player_point_daily_%s_%s_report';
			$point_daily_report = sprintf($point_daily_report_pattern, $year, $month);
			$command .= 'db.' . $point_daily_report . '.find(' . $conds_json . ').forEach(function(obj){ 
   db.' . $PointMonthlyReport->table . '.insert(obj)
});';
		}

// chú ý phương thức execute có thể không sử dụng được ở bản Mongo 4 trở lên 
		$res = $mongo->execute($command);
		$mongoDataSource = $PointMonthlyReport->getDataSource();
		$mongoDataSource->logQuery($command);
		if (empty($res['code'])) {

			return true;
		}
// nếu không thực hiện được lệnh thì ném ra CakeException
		else {

			$this->log($res, 'notice');
			throw new CakeException($res['errmsg'], $res['code']);
		}

		return false;
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

		return $date_range;
	}

	protected function setWinner(&$list_data, $type, $phone_field = 'phone') {

		if (empty($list_data)) {

			return;
		}

		foreach ($list_data as $k => $v) {

			$player_point = $v['AppModel'];
			$is_winner = $this->isWinner($player_point, $type, $phone_field);
			$list_data[$k]['AppModel']['is_winner'] = $is_winner;
		}
	}

	protected function isWinner($player_point, $type, $phone_field = 'phone') {

		$is_winner = $this->Winner->find('first', array(
			'conditions' => array(
				'type' => $type,
				'phone' => $player_point[$phone_field],
				'date' => new MongoDate(strtotime($player_point['date'])),
			),
		));

		if (!empty($is_winner)) {

			return 1;
		}

		return 0;
	}

	protected function setStatus(&$list_data, $phone_field = 'phone') {

		if (empty($list_data)) {

			return;
		}

		foreach ($list_data as $k => $v) {

			$player_point = $v['AppModel'];
			$phone = $player_point[$phone_field];
			$player = $this->getPlayerByPhone($phone);

// nếu thuê bao không tồn tại trong player thì loại khỏi danh sách winner
			if (empty($player)) {

				$list_data[$k]['AppModel']['play_status'] = 0;
				$list_data[$k]['AppModel']['status'] = -1;
				continue;
			}

			$play_status = $player['Player']['play_status'];
			$list_data[$k]['AppModel']['play_status'] = $play_status;

			$status = $player['Player']['status'];
			$list_data[$k]['AppModel']['status'] = $status;
		}
	}

	protected function convertDate(&$list_data, $date_fields = array('date', 'point_last_date')) {

		if (empty($date_fields) || empty($list_data)) {

			return;
		}

		foreach ($list_data as $k => $v) {

			foreach ($date_fields as $field) {

				$raw_value = $v['AppModel'][$field];
				if ($raw_value instanceof MongoDate) {

					$list_data[$k]['AppModel'][$field] = date('d-m-Y H:i:s', $raw_value->sec);
				}
			}
		}
	}

	protected function getPlayerByPhone($phone) {

		$player = $this->Player->find('first', array(
			'conditions' => array(
				'phone' => array(
					'$eq' => $phone,
				),
			),
		));

		return $player;
	}

	public function reqSetWinner() {

// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->autoRender = false;
		$request_data = $this->request->data;
		if (empty($request_data)) {

			exit();
		}

		$save_data = array(
			'phone' => $request_data['phone'],
			'date' => new MongoDate(strtotime($request_data['date'])),
			'type' => $request_data['type'],
			'point' => $request_data['point'],
			'user' => $this->Auth->user('id'),
		);
		$this->{$this->modelClass}->create();
		if ($this->{$this->modelClass}->save($save_data)) {

			echo json_encode(array(
				'error_code' => 0,
				'message' => __('set_winner_success'),
			));

// thực hiện update cache ở wapsite
			$this->updateTopDailyCache($request_data['date']);
		} else {

			echo json_encode(array(
				'error_code' => 1,
				'message' => __('set_winner_error'),
			));
		}
	}

	public function reqRemoveWinner($id = null) {

// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->autoRender = false;
		$res = array(
			'error_code' => 0,
			'message' => __('delete_successful_message'),
		);
		if (!$this->request->is('post')) {

			$res = array(
				'error_code' => 1,
				'message' => __('invalid_data'),
			);
			echo json_encode($res);
			return;
		}
		$model_name = $this->modelClass;
		$check_exist = $this->$model_name->find('first', array(
			'conditions' => array(
				'id' => array(
					'$eq' => $id,
				),
			),
		));
		if (empty($check_exist)) {

			$res = array(
				'error_code' => 2,
				'message' => __('invalid_data'),
			);
			echo json_encode($res);
			return;
		}

		if ($this->$model_name->delete($id)) {

			echo json_encode($res);

// lấy ra ngày trúng thưởng của winner
			$date = date('d-m-Y', strtotime($check_exist[$model_name]['date']));

// thực hiện update cache ở wapsite
			$this->updateTopDailyCache($date);
		} else {

			$res = array(
				'error_code' => 3,
				'message' => __('remove_winner_error_message'),
			);
			echo json_encode($res);
			return;
		}
	}

	/**
	 * reqTopDaily
	 * Lấy ra danh sách 5 người điểm cao nhất ngày
	 * đánh dấu người trúng thưởng winner ngày
	 */
	public function reqTopDaily() {

		$this->autoRender = false;
		header("Access-Control-Allow-Origin: *");
		header('Content-Type: application/json');

// tiêu chí trọn ra người trúng thưởng là đạt điểm cao nhất và nhanh nhất
		$options = array(
			'order' => array(
				'point' => 'DESC',
				'point_last_date' => 'ASC',
			),
			'limit' => self::MAX_TOP_DAILY,
		);
		$model_pattern = 'player_point_daily_%s_%s_report';

// nếu không chọn ngày, mặc định là ngày hôm trước
		if (empty($this->request->query)) {

			$this->request->query['date'] = date('d-m-Y', strtotime(self::DEFAULT_CHOOSE_DAILY));
		}

		if (isset($this->request->query['date']) && strlen($this->request->query['date'])) {

			$date = trim($this->request->query['date']);
// nếu date lớn hơn date hiện tại - 1
			if (date('Ymd', strtotime($date)) > date('Ymd', strtotime('-1 day'))) {

				echo json_encode(array());
				return;
			}
		} else {

			echo json_encode(array());
			return;
		}

// làm sạch date
		$date = date('d-m-Y', strtotime($date));

		$list_data = $this->DistributedHis->paginate($model_pattern, $date . ' 00:00:00', $date . ' 23:59:59', $options);
		$type = self::DAILY_WINNER_TYPE;
		$this->setWinner($list_data, $type);

// thực hiện lọc thuê bao đang ở trạng thái chơi gameshow
		$this->setStatus($list_data);

// thực hiện ẩn số điện thoại
		$this->setHidePhone($list_data);

		echo json_encode($list_data);

		exit();
	}

	public function beforeFilter() {
		parent::beforeFilter();

		$this->Auth->allow(array('reqTopDaily'));
	}

	protected function setSearchConds(&$options) {

// lần đầu tiên load, set mặc định from_date là ngày đầu của tháng
// to_date là ngày hiện tại
		if (empty($this->request->query)) {

			$this->request->query['from_date'] = date('d-m-Y', strtotime('first day of this month'));
			$this->request->query['to_date'] = date('d-m-Y');
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

		if (isset($this->request->query['phone']) && strlen(trim($this->request->query['phone']))) {

			$phone = trim($this->request->query['phone']);
			$this->request->query['phone'] = $phone;
			$target_phone = $this->convertPhoneNumber($phone);
			$options['conditions']['phone']['$regex'] = new MongoRegex("/" . $target_phone . "/");
		}

		if (isset($this->request->query['type']) && strlen(trim($this->request->query['type']))) {

			$type = trim($this->request->query['type']);
			$this->request->query['type'] = $type;
			$options['conditions']['type']['$eq'] = (int) $type;
		}
	}

	protected function setHidePhone(&$list_data) {

		if (empty($list_data)) {

			return;
		}

		foreach ($list_data as $k => $v) {

			$phone = $v['AppModel']['phone'];
			$hide_phone = $this->getHidePhone($phone);
			$list_data[$k]['AppModel']['phone'] = $hide_phone;
		}
	}

	protected function getHidePhone($phone) {

		$count_hide_letter = strlen(self::HIDE_PHONE_LETTER);
		$hide = substr_replace($phone, self::HIDE_PHONE_LETTER, self::HIDE_PHONE_PORTION, $count_hide_letter);

		return $hide;
	}

	/**
	 * updateTopDailyCache
	 * Thực hiện update lại cache ở wapsite
	 * 
	 * @param string $date
	 */
	protected function updateTopDailyCache($date) {

		App::uses('HttpSocket', 'Network/Http');

		$cache_update_top_daily = Configure::read('sysconfig.service.player.cache_update_top_daily');
		$HttpSocket = new HttpSocket();
		$results = $HttpSocket->get($cache_update_top_daily, array('date' => $date));
		if (!$results->isOk()) {

			$this->log($results, 'notice');
		}
	}

	protected function setInit() {

		$this->set('model_name', $this->modelClass);
	}

}
