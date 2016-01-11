<?php

App::uses('AppController', 'Controller');

class ConfigurationsController extends AppController {

	public $uses = array('Configuration');

	public function index() { 

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->set('model_name', $this->modelClass);
		$this->set('panel_title_prefix', __('panel_title_prefix'));
		$this->set('panel_child_title', __('panel_child_title'));
		$gameshow_codes = Configure::read('sysconfig.' . $this->name . '.codes');
		$this->set('gameshow_codes', $gameshow_codes);
		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => __FUNCTION__)),
			'label' => __('config_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('config_title'));

		$code = $this->request->query('code');
		if (empty($code)) {

			$codes = array_keys($gameshow_codes);
			$code = $codes[0];
		}
		$this->set('code', $code);

		if ($this->request->is('post') || $this->request->is('put')) {

			$request_data = $this->request->data[$this->modelClass];
			$save_data = $this->parseSaveData($request_data);
			if ($save_data === false) {

				throw new BadRequestException(__('config_manage_exception'));
			}

			// thực hiện replace mức document trong Mongodb
			$id = $save_data['id'];
			unset($save_data['id']);
			$mongo = $this->{$this->modelClass}->getDataSource();
			$mongoCollectionObject = $mongo->getMongoCollection($this->{$this->modelClass});
			
			// sau khi thực hiện update vào database, thực hiện update cache ở phía business server
			if ($mongoCollectionObject->update(array('_id' => new MongoId($id)), $save_data)) {

				$this->updateBusinessCache($save_data);
				$this->Session->setFlash(__('save_successful_message'), 'default', array(), 'good');
			} else {

				$this->Session->setFlash(__('save_error_message'), 'default', array(), 'bad');
			}
		}

		// đọc ra cấu hình config
//		$get_config = $this->Configuration->find('first', array(
//			'conditions' => array(
//				'code' => array(
//					'$eq' => $code,
//				),
//			),
//		));
		$get_config = $this->Configuration->find('first');
		if (empty($get_config)) {

			throw new CakeException(__('Configuration was not initialed'));
		}
		$config = $get_config[$this->modelClass];

		$id = $config['id'];
		$this->set('id', $id);

		unset($config['id']);
//		unset($config['code']);
		$this->set('config', $config);
	}

	public function reqInputContainer() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->layout = 'ajax';
		$input_flg = $this->request->query('input_flg');
		if (empty($input_flg)) {

			exit();
		}

		$this->set('input_flg', $input_flg);
		$this->set('input_prefix', $input_flg . '.' . uniqid());
		$this->set('label', '');
		$this->set('value', '');
	}

	public function reqChildGroupContainer() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->layout = 'ajax';
		$items = $this->request->data('items');
		$input_flg = $this->request->data('input_flg');
		$index = !empty($this->request->data['index']) ? $this->request->data['index'] + 1 : 1;
		if (empty($items) || empty($input_flg)) {

			exit();
		}

		foreach ($items as $k => $v) {

			$items[$k] = '';
		}

		$panel_id = uniqid();
		$panel_index = $index + 1;

		$this->set('panel_id', $panel_id);
		$this->set('panel_index', $panel_index);
		$this->set('input_flg', $input_flg);
		$this->set('panel_key', $panel_id);
		$this->set('input_name_prefix', $input_flg . '.' . $panel_id);
		$this->set('panel_title_prefix', __('panel_title_prefix'));
		$this->set('panel_child_title', __('panel_child_title'));
		$this->set('items', $items);
	}

	protected function parseSaveData($request_data) {

		$save_data = array();
		// chuyển đổi format dữ liệu từ thô sang format dữ liệu để lưu trong database
		foreach ($request_data as $k => $v) {

			if (!is_array($v)) {

				return false;
			}

			$array_depth = $this->getArrayDepth($v);
			if ($array_depth == 1) {

				if (!isset($v['key']) || !isset($v['value'])) {

					return false;
				}
				$save_data[trim($v['key'])] = trim($v['value']);
			} else {

				$parent_index = 0;
				foreach ($v as $v1) {

					if (!is_array($v1)) {

						return false;
					}
					foreach ($v1 as $v2) {

						if (!isset($v2['key']) || !isset($v2['value'])) {

							return false;
						}
						// thực hiện check giá trị value có phải là chuỗi json k?
						$v2_value = trim($v2['value']);
						$v2_key = trim($v2['key']);
						// nếu là chuỗi json, thực hiện decode để lưu vào db dưới dạng object
						if ($this->isJson($v2_value)) {

							$save_data[$k][$parent_index][$v2_key] = json_decode($v2_value);
						} else {

							$save_data[$k][$parent_index][$v2_key] = $v2_value;
						}
					}
					$parent_index++;
				}
			}
		}

		return $save_data;
	}

	protected function getArrayDepth($array) {

		$max_indentation = 1;

		$array_str = print_r($array, true);
		$lines = explode("\n", $array_str);

		foreach ($lines as $line) {
			$indentation = (strlen($line) - strlen(ltrim($line))) / 4;

			if ($indentation > $max_indentation) {
				$max_indentation = $indentation;
			}
		}

		return ceil(($max_indentation - 1) / 2) + 1;
	}

	/**
	 * updateBusinessCache
	 * Thực hiện update cache ở business server
	 * 
	 * @param array $save_data
	 * @return mixed
	 */
	protected function updateBusinessCache($save_data) {

		if (!empty($save_data['url_cache_services'])) {

			$url_cache_services = explode(',', $save_data['url_cache_services']);
			if (empty($url_cache_services)) {

				return true;
			}

			$is_success = true;
			foreach ($url_cache_services as $url) {

				$HttpSocket = new HttpSocket();
				$results = $HttpSocket->get($url);
				// nếu thực hiện gọi service để update cache có vấn đề thì log lại
				if (!$results->isOk()) {

					$this->log($results, 'notice');
					$is_success = false;
				}
			}

			return $is_success;
		}

		return false;
	}

}
