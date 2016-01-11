<?php

App::uses('AppController', 'Controller');

class UsersController extends AppController {

        public $uses = array(
            'User',
            'UserGroup',
            'ContentProvider',
        );

        public function beforeFilter() {
                parent::beforeFilter();

//                $this->Auth->allow();
                $this->Auth->allow(array('login', 'logout'));
                $this->set('model_name', $this->modelClass);
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
                    'label' => __('user_title'),
                );
                $breadcrumb[] = array(
                    'url' => Router::url(array('action' => __FUNCTION__)),
                    'label' => __('add_action_title'),
                );
                $this->set('breadcrumb', $breadcrumb);
                $this->set('page_title', __('user_add_title'));

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
//		if (!$this->isAllow()) {
//
//			return $this->redirect($this->Auth->loginRedirect);
//		}

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
                            'label' => __('user_title'),
                        );
                        $breadcrumb[] = array(
                            'url' => Router::url(array('action' => __FUNCTION__, $id)),
                            'label' => __('edit_action_title'),
                        );
                        $this->set('breadcrumb', $breadcrumb);
                        $this->set('page_title', __('user_edit_title'));

                        $this->request->data = $this->{$this->modelClass}->read(null, $id);
                }

//		$this->render('add');
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
                    'label' => __('user_title'),
                );
                $this->set('breadcrumb', $breadcrumb);
                $this->set('page_title', __('user_title'));

                $options = array();

                // với user là admin, tuy nhiên admin cấp dưới, không được phép nhìn thấy user thuộc group dành cho admin cấp trên
                $user = $this->Auth->user();
                if (!empty($user['deny_groups'])) {

                        $options['conditions']['group']['$nin'] = $user['deny_groups'];
                }

                $this->setSearchConds($options);
                $this->Paginator->settings = $options;

                $list_data = $this->Paginator->paginate($this->modelClass);
                $this->set('list_data', $list_data);
        }

        protected function setSearchConds(&$options) {

                if (isset($this->request->query['username']) && strlen(trim($this->request->query['username'])) > 0) {

                        $name = trim($this->request->query['username']);
                        $this->request->query['username'] = $name;
                        $options['conditions']['username']['$regex'] = new MongoRegex("/" . mb_strtolower($name) . "/i");
                }

                if (isset($this->request->query['user_group']) && strlen(trim($this->request->query['user_group'])) > 0) {

                        $group = trim($this->request->query['user_group']);
                        $this->request->query['user_group'] = $group;
                        $options['conditions']['user_group']['$eq'] = new MongoId($group);
                }

                if (isset($this->request->query['status']) && strlen($this->request->query['status']) > 0) {

                        $status = (int) $this->request->query['status'];
                        $options['conditions']['status']['$eq'] = $status;
                }
        }

        public function login() {

                $this->layout = 'login';
                if ($this->request->is('post') || $this->request->is('put')) {

                        if ($this->Auth->login()) {

                                return $this->redirect($this->Auth->loginRedirect);
                        }

                        $this->Session->setFlash(__('invalid_username_or_password', 'login', array(), 'bad'));
                }
        }

        public function logout() {

                return $this->redirect($this->Auth->logout());
        }

        /*
         * resetPassword
         * Thực hiện reset password
         */

        public function resetPassword($id = null) {

                if ($this->request->is('post') || $this->request->is('put')) {

                        if ($this->{$this->modelClass}->save($this->request->data[$this->modelClass])) {

                                $this->Session->setFlash(__('reset_password_successful_message'), 'default', array(), 'good');
                                $this->redirect(array('action' => 'edit', $id));
                        } else {

                                $this->Session->setFlash(__('reset_password_error_message'), 'default', array(), 'bad');
                        }
                }
        }

        protected function setInit() {

                $type = Configure::read('sysconfig.Users.type');
                $this->set('type', $type);

                $opts_group = array(
                    'conditions' => array(
                        'status' => array(
                            '$eq' => 1,
                        ),
                    ),
                );
                $group = $this->getList('UserGroup', $opts_group);
                $this->set('group', $group);

                $status = Configure::read('sysconfig.Users.status');
                $this->set('status', $status);

                $opts_cp = array(
                    'fields' => array(
                        'code', 'name',
                    ),
                );
                $cp = $this->getList('ContentProvider', $opts_cp);
                $this->set('cp', $cp);
        }

}
