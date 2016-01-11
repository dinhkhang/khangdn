<?php

App::uses('AppController', 'Controller');

class QuestionCategoriesController extends AppController {

	public $uses = array(
		'QuestionCategory', 
	);
	
	public function index() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->setInit();

		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => 'index')),
			'label' => __('cat_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('cat_title'));

		$options = array();
		$options['order'] = array('updated_at' => 'DESC');

		$this->setSearchConds($options);
		$this->Paginator->settings = $options;

		$list_data = $this->Paginator->paginate($this->modelClass);
		$this->set('list_data', $list_data);
	}

	public function add() {

		// nếu không có quyền truy cập, thì buộc user phải đăng xuất
		if (!$this->isAllow()) {

			return $this->redirect($this->Auth->loginRedirect);
		}

		$this->setInit();

		$breadcrumb = array();
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => 'index')),
			'label' => __('cat_title'),
		);
		$breadcrumb[] = array(
			'url' => Router::url(array('action' => __FUNCTION__)),
			'label' => __('add_action_title'),
		);
		$this->set('breadcrumb', $breadcrumb);
		$this->set('page_title', __('cat_title'));

		if ($this->request->is('post') || $this->request->is('put')) {

			if ($this->{$this->modelClass}->save($this->request->data[$this->modelClass])) {

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
				'label' => __('cat_title'),
			);
			$breadcrumb[] = array(
				'url' => Router::url(array('action' => __FUNCTION__, $id)),
				'label' => __('edit_action_title'),
			);
			$this->set('breadcrumb', $breadcrumb);
			$this->set('page_title', __('cat_title'));

			$this->request->data = $this->{$this->modelClass}->read(null, $id);
		}

		$this->render('add');
	}

	protected function setSearchConds(&$options) {

		if (isset($this->request->query['name']) && strlen(trim($this->request->query['name'])) > 0) {

			$name = trim($this->request->query['name']);
			$options['conditions']['name']['$regex'] = new MongoRegex("/" . mb_strtolower($name) . "/i");
		}

		if (isset($this->request->query['code']) && strlen(trim($this->request->query['code'])) > 0) {

			$code = trim($this->request->query['code']);
			$options['conditions']['code']['$regex'] = new MongoRegex("/" . mb_strtolower($code) . "/i");
		}

		if (isset($this->request->query['status']) && strlen($this->request->query['status']) > 0) {

			$status = (int) $this->request->query['status'];
			$options['conditions']['status']['$eq'] = $status;
		}
	}

	protected function setInit() {

		$this->set('model_name', $this->modelClass);
		$this->set('status', Configure::read('sysconfig.App.status'));
	}

	public function beforeFilter() {
		parent::beforeFilter();

//		$this->Auth->allow();
	}

}
