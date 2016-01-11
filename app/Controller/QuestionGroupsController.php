<?php

App::uses('AppController', 'Controller');

class QuestionGroupsController extends AppController {

	public $uses = array(
		'QuestionGroup',
		'ContentProvider',
		'QuestionCategory',
		'Configuration',
		'User',
	);
	protected $validate_import_error_messages = array(); // lưu trữ các chỉ dẫn xác định nội dung không hợp lệ trong file dùng import

	public function add() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->setInit();

		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => 'index')),
			'label' => __('qes_gro_index_title'),
		);
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => __FUNCTION__)),
			'label' => __('add_action_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('qes_gro_add_title'));

		// lấy ra số lượng câu hỏi mặc định có trong 1 bộ câu hỏi
		$questions_in_group = Configure::read('sysconfig.Common.number_question_1package');
		$this->set('questions_in_group', $questions_in_group);

		if ($this->request->is('post') || $this->request->is('put')) {

			// thực hiện kiểm tra tính hợp lệ và tái cấu trúc dữ liệu
			$request_data = $this->parseSaveData($this->request->data[$this->modelClass]);
			if ($request_data === false) {

				$this->Session->setFlash(__('invalid_data'), 'default', array(), 'bad');
				return;
			}
			if ($this->{$this->modelClass}->save($request_data)) {

				$this->Session->setFlash(__('save_successful_message'), 'default', array(), 'good');
				$this->redirect(array('action' => 'index'));
			} else {

				$this->Session->setFlash(__('save_error_message'), 'default', array(), 'bad');
			}
		}
	}

	public function edit($id = null) {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->{$this->modelClass}->id = $id;
		if (!$this->{$this->modelClass}->exists()) {

			throw new NotFoundException(__('invalid_data'));
		}

		if ($this->request->is('post') || $this->request->is('put')) {

			$this->add();
		} else {

			$this->setInit();

			$breadcrumb = array();
			$breadcrumb[] = array(
				'url' => Router::url(array('action' => 'index')),
				'label' => __('qes_gro_index_title'),
			);
			$breadcrumb[] = array(
				'url' => Router::url(array('action' => __FUNCTION__, $id)),
				'label' => __('edit_action_title'),
			);
			$this->set('breadcrumb', $breadcrumb);
			$this->set('page_title', __('qes_gro_edit_title'));

			$this->request->data = $this->{$this->modelClass}->read(null, $id);
		}

		$this->render('add');
	}

	public function index() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->setInit();
		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => 'index')),
			'label' => __('qes_gro_index_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('qes_gro_index_title'));

		$options = array();
		$options['order'] = array('modified' => 'DESC');
		$this->setSearchConds($options);

		$this->Paginator->settings = $options;
		$list_data = $this->Paginator->paginate($this->modelClass);
//                var_dump($list_data);
                
		$this->setCP($list_data);
		$this->setUser($list_data);
		$this->set('list_data', $list_data);
	}

	public function import() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => 'index')),
			'label' => __('qes_gro_index_title'),
		);
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => __FUNCTION__)),
			'label' => __('qes_gro_import_short_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('qes_gro_import_title'));
		$this->set('file_import_type', Configure::read('sysconfig.' . $this->name . '.file_import_type'));
		 
		$this->set('model_name', $this->modelClass);

		$content_provider = $this->ContentProvider->find('list', array(
			'fields' => array(
				'code', 'name',
			),
			'conditions' => array(
				'status' => 2,
			),
		));
//                var_dump($content_provider);
                
		$this->set('content_provider', $content_provider);

//		$gameshow_codes = Configure::read('sysconfig.Configuration.codes');
//		$this->set('gameshow_codes', $gameshow_codes);

		if ($this->request->is('post')) {

			// thực hiện upload file lên server
			if (!empty($this->request->params['form']['file'])) {

				$file_obj = $this->request->params['form']['file'];
				$file_name = $file_obj['name'];

				// tạo ra file_name trên server để unique
				$unique = $this->generateRandomLetters(5);
				$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
				$origin_file_name = basename($file_name, "." . $file_ext);
				$target_path = APP . WEBROOT_DIR . DS . 'tmp' . DS . $origin_file_name . '_' . $unique . '.' . $file_ext;

				if (!move_uploaded_file($file_obj['tmp_name'], $target_path)) {

					throw new BadRequestException(__('file_upload_exception'));
				}
			} else {

				throw new BadRequestException(__('file_upload_error'));
			}
 
			// với trường hợp user type là cp
			$user = $this->Auth->user();
			if ($user['type'] == 1) {

				$cp = $user['cp'];
			} else {

				$cp = $this->request->data($this->modelClass . '.cp');
			}
			if ( empty($cp)) {

				// thực hiện xóa file vừa tải lên
				unlink($target_path);
				throw new BadRequestException(__('invalid_data'));
			}

			$number_question_1package = Configure::read('sysconfig.Common.number_question_1package');

			// thực hiện đọc dữ liệu trong file exel
			App::import('Vendor', 'PHPExcel_IOFactory', array(
				'file' => 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php'
			));
			$file_type = PHPExcel_IOFactory::identify($target_path);
			$objReader = PHPExcel_IOFactory::createReader($file_type);
			$objReader->setReadDataOnly(true);
			$objPHPExcel = $objReader->load($target_path);
                        $arr_sheet = $objPHPExcel->getAllSheets();
                        $category_code = "";
//                        $arr_sheet->get
                        foreach ($arr_sheet as $key => $objWorksheet) {
                            $sheet_title = $objWorksheet->getTitle();
//                            var_dump($sheet_title);
                            
                            $this->log("key: $key, $sheet_title", 'notice');
                            if(empty($sheet_title))
                            {
                                $this->log("ERROR: key: $key, $sheet_title", 'notice');
                                continue;
                            }
                            else 
                            {
                                $arr_sheet_title = split('-', $sheet_title);
                                if(count($arr_sheet_title) < 2) 
                                {
                                    $this->log("ERROR: key: $key, $sheet_title", 'notice');
                                    continue;
                                }
                                $category_code = trim($arr_sheet_title[0]);
                                $category_name = trim($arr_sheet_title[1]);
                                
                                $category = $this->QuestionCategory->find('first', array( 
                                        'conditions' => array(
                                                'code' => $category_code,
                                        ),
                                ));
                                if(empty($category))
                                {
                                    $arr_cate = [
                                        'code' => $category_code,
                                        'name' => $category_name, 
                                        'order' => 0,
                                        'status' => 2,
                                        'user' => new MongoId($user["id"]),
                                        'created_at' => new MongoDate(time()),
                                        'updated_at' => new MongoDate(time()),
                                    ];
                                    $this->QuestionCategory->create();
                                    $this->QuestionCategory->save($arr_cate);
                                }
                            }
                            
//                            var_dump($sheet_title);
//                            die();
//			$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
                            $reader = $objWorksheet->toArray();
                            if (empty($reader) || !is_array($reader)) {

                                    // thực hiện xóa file vừa tải lên
                                    unlink($target_path);
                                    throw new NotImplementedException(__('file_import_empty', $file_name));
                            }

                            $save_data = array(
                                    'name' => '',
                                    'description' => '', 
                                    'cate_code' => $category_code,
                                    'cp_code' => $cp,
                                    'order' => 0,
                                    'status' => 2,
                                    'user' => new MongoId($user["id"]),
                                    'modified' => new MongoDate(time()),
                                    'questions' => array(),
                            );
                            $question_index = 0; // index của từng câu hỏi trong 1 bộ câu hỏi
                            $qes_gro_index = ''; // index của bộ câu hỏi
                            $total_qes_gro_index = 0; // tổng số câu hỏi được duyệt qua file exel
                            // thực hiện validate dữ liệu
                            $is_valid = $this->validateImport($reader, $file_name);
                            // nếu dữ liệu k hợp lệ
                            if (!$is_valid) {

                                    echo json_encode(array(
                                            'error_code' => 1,
                                            'message' => implode(PHP_EOL, $this->validate_import_error_messages),
                                    ));
                                    // thực hiện xóa file vừa tải lên
                                    unlink($target_path);
                                    exit();
                            }

                            foreach ($reader as $k => $item) {

                                    // bỏ qua dòng header đầu tiên
                                    if ($k == 0) {

                                         continue;
                                    }

                                    $save_data['questions'][] = array(
                                            'index' => $question_index,
                                            'content' => trim($item[1]),
                                            'answer' => (int)trim($item[2]),
                                            'point' => (int)trim($item[3]),
                                    );
                                     
                                    $question_index++;
                                    $total_qes_gro_index++;

                                    // lấy về thứ tự của bộ câu hỏi
                                    if (strlen(trim($item[0]))) {

                                            $qes_gro_index = trim($item[0]);
                                    }

                                    // khi thỏa mãn có đủ $number_question_1package thì thực hiện insert vào bộ câu hỏi
                                    if ($question_index == $number_question_1package) {

//                                        var_dump($save_data);
                                        
                                            $save_data['name'] = $cp . '_' . $category_code . '_' . $qes_gro_index;
                                            $save_data['description'] = $cp . '_' . $category_code . '_' . $qes_gro_index;
                                            $qes_gro_index = '';

                                            $this->{$this->modelClass}->create();
                                            if (!$this->{$this->modelClass}->save($save_data)) {

                                                    $this->log('*******************************', 'notice');
                                                    $this->log('Index row of file exel = ' . ($k + 1), 'notice');
                                                    $this->log('Raw data', 'notice');
                                                    $this->log($item, 'notice');
                                                    $this->log('Save data', 'notice');
                                                    $this->log($save_data, 'notice');
                                                    $this->log('Validate error', 'notice');
                                                    $this->log($this->{$this->modelClass}->validationErrors, 'notice');
                                                    $this->log('*******************************', 'notice');
                                            }

                                            // đồng thời thực hiện reset lại thông tin bộ câu hỏi
                                            $question_index = 0;
                                            $save_data = array(
                                                    'name' => '',
                                                    'description' => '', 
                                                    'cate_code' => $category_code,
                                                    'cp_code' => $cp,
                                                    'order' => 0,
                                                    'status' => 2,
                                                    'user' => new MongoId($user["id"]),
                                                    'modified' => new MongoDate(time()),
                                                    'questions' => array(),
                                            );
                                    }
                            }

                        }
			$this->log('Total rows was imported = ' . $total_qes_gro_index, 'notice');

			echo json_encode(array(
				'error_code' => 0,
				'message' => __('file_import_success', $file_name),
			));

			unlink($target_path);
			exit();
		}
	}

	/**
	 * validateImport
	 * Thực hiện validate dữ liệu nằm trong file import
	 * 
	 * @param reference array &$reader
	 * @param string $file_name
	 * 
	 * @return boolean
	 */
	protected function validateImport(&$reader, $file_name) {

		if (empty($reader) || !is_array($reader)) {

			$this->validate_import_error_messages[] = __('file_import_empty', $file_name);
			return false;
		}

		$is_valid = true;
		foreach ($reader as $k => $item) {

			// bỏ qua dòng header đầu tiên
			if ($k == 0) {

				continue;
			}

			// với trường hợp dữ liệu rỗng, thường là các cell thừa ở cuối file
			// thì thực hiện clean
			if (
					!empty($item) &&
					is_array($item) &&
					count($item) >= 4 &&
					!strlen(trim($item[0])) &&
					!strlen(trim($item[1])) &&
					!strlen(trim($item[2])) &&
					!strlen(trim($item[3]))
			) {

				$this->log('*******************************', 'notice');
				$this->log('Index row of file exel = ' . ($k + 1), 'notice');
				$this->log('Raw data is invalid', 'notice');
				$this->log($item, 'notice');
				$this->log('*******************************', 'notice');

				unset($reader[$k]);
				continue;
			}

			// kiểm tra tính hợp lệ của dữ liệu
			if (
					!empty($item) &&
					is_array($item) &&
					count($item) >= 4 &&
					strlen(trim($item[1])) &&
					strlen(trim($item[2])) &&
					strlen(trim($item[3]))
			) {

				// kiểm tra xem bên trong nội dung 1 cell có chứa kí tự đặc biệt không
				$content = trim($item[1]);
				$ascii_content = $this->convert_vi_to_en($content);
				if (!$this->isASCII($ascii_content)) {

					$error_message = __('file_import_invalid_letter_error', $k + 1, 2, $file_name);
					$this->validate_import_error_messages[] = $error_message;

					$this->log('*******************************', 'notice');
					$this->log($error_message, 'notice');
					$this->log('Raw data is invalid', 'notice');
					$this->log($item[1], 'notice');
					$this->log('Ascii data is invalid', 'notice');
					$this->log($ascii_content, 'notice');
					$this->log('*******************************', 'notice');
				}

				$answer = trim($item[2]);
				if (!$this->isASCII($answer)) {

					$error_message = __('file_import_invalid_letter_error', $k + 1, 3, $file_name);
					$this->validate_import_error_messages[] = $error_message;

					$this->log('*******************************', 'notice');
					$this->log($error_message, 'notice');
					$this->log('Raw data is invalid', 'notice');
					$this->log($item[2], 'notice');
					$this->log('*******************************', 'notice');
				}

				$point = trim($item[3]);
				if (!$this->isASCII($point)) {

					$error_message = __('file_import_invalid_letter_error', $k + 1, 4, $file_name);
					$this->validate_import_error_messages[] = $error_message;

					$this->log('*******************************', 'notice');
					$this->log($error_message, 'notice');
					$this->log('Raw data is invalid', 'notice');
					$this->log($item[3], 'notice');
					$this->log('*******************************', 'notice');
				}
			}
			// validate lỗi khi nội dung trong file import không điền đủ vào các cột cần thiết
			else {

				$error_message = __('file_import_invalid_format_error', $k + 1, $file_name);
				$this->validate_import_error_messages[] = $error_message;

				$this->log('*******************************', 'notice');
				$this->log($error_message, 'notice');
				$this->log('Raw data is invalid', 'notice');
				$this->log($item, 'notice');
				$this->log('*******************************', 'notice');
			}
		}

		if (!empty($this->validate_import_error_messages)) {

			$is_valid = false;
		}

		return $is_valid;
	}

	protected function setSearchConds(&$options) {

		if (isset($this->request->query['name']) && strlen(trim($this->request->query['name']))) {

			$name = trim($this->request->query['name']);
			$this->request->query['name'] = $name;
			$options['conditions']['name']['$regex'] = new MongoRegex("/" . mb_strtolower($name) . "/i");
		}

		if (isset($this->request->query['cp']) && strlen(trim($this->request->query['cp']))) {

			$cp = trim($this->request->query['cp']);
			$this->request->query['cp'] = $cp;
			$options['conditions']['cp_code']['$eq'] = $cp;
		}

		if (isset($this->request->query['cate_code']) && strlen(trim($this->request->query['cate_code']))) {

			$cate_code = trim($this->request->query['cate_code']);
			$this->request->query['cate_code'] = $cate_code;
			$options['conditions']['cate_code']['$eq'] = $cate_code;
		}

		if (isset($this->request->query['status']) && strlen($this->request->query['status']) > 0) {

			$status = (int) $this->request->query['status'];
			$options['conditions']['status']['$eq'] = $status;
		}

		if (isset($this->request->query['from_date']) && strlen(trim($this->request->query['from_date']))) {

			$from_date = trim($this->request->query['from_date']);
			$this->request->query['from_date'] = $from_date;
			$options['conditions']['modified']['$gte'] = new MongoDate(strtotime($from_date . ' 00:00:00'));
		}

		if (isset($this->request->query['to_date']) && strlen(trim($this->request->query['to_date']))) {

			$to_date = trim($this->request->query['to_date']);
			$this->request->query['to_date'] = $to_date;
			$options['conditions']['modified']['$lte'] = new MongoDate(strtotime($to_date . ' 23:59:59'));
		}

		if (isset($this->request->query['content']) && strlen(trim($this->request->query['content']))) {

			$content = trim($this->request->query['content']);
			$this->request->query['content'] = $content;
			$options['conditions']['questions']['$elemMatch']['content']['$regex'] = new MongoRegex("/" . $content . "/i");
		}
	}

	protected function setCP(&$list_data) {

		if (empty($list_data)) {

			return;
		}
		$cps = array();
		foreach ($list_data as $k => $v) {

			$cp_code = $v[$this->modelClass]['cp_code'];
			if (empty($cps[$cp_code])) {

				$cps[$cp_code] = $this->getCP($cp_code) . ' (' . $cp_code . ')';
			}
			$list_data[$k][$this->modelClass]['cp_name'] = $cps[$cp_code];
		}
	}

	protected function setUser(&$list_data) {

		if (empty($list_data)) {

			return;
		}
//                var_dump($this->modelClass);
		$users = array();
		foreach ($list_data as $k => $v) {
 
			$user_id = (string)$v[$this->modelClass]['user'];
			if (empty($users[$user_id])) {

				$users[$user_id] = $this->getUser($user_id);
			}
			$list_data[$k][$this->modelClass]['user_name'] = $users[$user_id];
		}
	}

	protected function getUser($user_id) {

		$get_user = $this->User->find('first', array(
			'conditions' => array(
				'id' => array(
					'$eq' => $user_id,
				),
			),
		));

		return !empty($get_user) ? $get_user['User']['username'] : __('unknown');
	}

	protected function parseSaveData($request_data) {

		if (empty($request_data['questions'])) {

			return false;
		}
 
		foreach ($request_data['questions'] as $k => $v) {

			if (!strlen(trim($v['content'])) || !strlen(trim($v['point'])) || !strlen(trim($v['answer']))) {

				return false;
			}

			$request_data['questions'][$k]['content'] = trim($v['content']);
//			$request_data['questions'][$k]['answer_time'] = trim($v['answer_time']);
			$request_data['questions'][$k]['answer'] = (int)($v['answer']);
 
		}

		return $request_data;
	}

	protected function getCP($cp_code) {

		$get_cp = $this->ContentProvider->find('first', array(
			'conditions' => array(
				'code' => array(
					'$eq' => $cp_code,
				),
			),
		));

		return !empty($get_cp) ? $get_cp['ContentProvider']['name'] : __('unknown');
	}

	protected function setInit() {

		$this->set('model_name', $this->modelClass);
		$this->set('status', Configure::read('sysconfig.Common.status'));

		// lấy ra danh sách các CP
		$content_provider = $this->ContentProvider->find('list', array(
			'fields' => array(
				'code', 'name',
			),
			'conditions' => array(
				'status' => array(
					'$eq' => 2,
				),
			),
		));
		$this->set('content_provider', $content_provider);

		// lấy ra category
		$category = $this->QuestionCategory->find('list', array(
			'fields' => array(
				'code', 'name',
			),
			'conditions' => array(
				'status' => array(
					'$eq' => 2,
				),
			),
		));
		$this->set('category', $category);
	}

}
