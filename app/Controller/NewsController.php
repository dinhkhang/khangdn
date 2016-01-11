<?php

class NewsController extends AppController {

        public $uses = array('NewModel', 'NewEntity', 'Country', 'Region', 'Location', 'NewCategory', 'NewCollection');
        public $components = array('FileCommon');

        public function beforeRender() {
                parent::beforeRender();
                $this->set('entity', $this->NewEntity);
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
                    'label' => __('new_title'),
                );
                $this->set('breadcrumb', $breadcrumb);
                $this->set('page_title', __('new_title'));

                $options = array();
                $options['order'] = array('modified' => 'DESC');

                $this->setSearchConds($options);
                $this->Paginator->settings = $options;

                $list_data = $this->Paginator->paginate($this->modelClass);
                $this->set('list_data', $list_data);
                $this->set('listLocation', $this->Location->getListLocationId());
                if (isset($this->request->query['location'])) {
                        $this->set('locationInfo', $this->Location->getCountryRegion($this->request->query['location'], $this->Country, $this->Region));
                }
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
                    'label' => __('new_title'),
                );
                $breadcrumb[] = array(
                    'url' => Router::url(array('action' => __FUNCTION__)),
                    'label' => __('add_action_title'),
                );
                $this->set('breadcrumb', $breadcrumb);
                $this->set('page_title', __('new_title'));

                if ($this->request->is('post') || $this->request->is('put')) {
                        // upload file
                        $this->FileCommon->autoProcess($this->request->data[$this->modelClass]);

                        // check location exist from db, if not this is region, save to location
                        $this->_validateLocation($this->request->data);

                        if ($this->{$this->modelClass}->save($this->request->data[$this->modelClass])) {

                                $this->Session->setFlash(__('save_successful_message'), 'default', array(), 'good');
                                $this->redirect(array('action' => 'index'));
                        } else {

                                $this->Session->setFlash(__('save_error_message'), 'default', array(), 'bad');
                        }
                }
        }

        private function _validateLocation(&$data) {
                $this->Location->_validateLocation($data, $this->modelClass);
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
                            'label' => __('new_title'),
                        );
                        $breadcrumb[] = array(
                            'url' => Router::url(array('action' => __FUNCTION__, $id)),
                            'label' => __('edit_action_title'),
                        );
                        $this->set('breadcrumb', $breadcrumb);
                        $this->set('page_title', __('new_title'));

                        $data = $this->{$this->modelClass}->read(null, $id);
                        $this->FileCommon->autoSetFiles($data[$this->modelClass]);
                        $this->request->data = $data;

                        $this->set('locationInfo', $this->Location->getCountryRegion($this->request->data['NewModel']['location'], $this->Country, $this->Region));
                }

                $this->render('add');
        }

        protected function setSearchConds(&$options) {
                if (isset($this->request->query['name']) && strlen(trim($this->request->query['name'])) > 0) {
                        $name = trim($this->request->query['name']);
                        $this->request->query['name'] = $name;
                        $options['conditions']['name']['$regex'] = new MongoRegex("/" . mb_strtolower($name) . "/i");
                }
                if (isset($this->request->query['source']) && strlen(trim($this->request->query['source'])) > 0) {
                        $source = trim($this->request->query['source']);
                        $this->request->query['source'] = $source;
                        $options['conditions']['source']['$regex'] = new MongoRegex("/" . mb_strtolower($source) . "/i");
                }
                if (isset($this->request->query['status']) && strlen($this->request->query['status']) > 0) {
                        $status = (int) $this->request->query['status'];
                        $options['conditions']['status']['$eq'] = $status;
                }
                if (isset($this->request->query['location']) && strlen($this->request->query['location']) > 0) {
                        $location = $this->request->query['location'];
                        $this->request->query['location'] = $location;
                        $options['conditions']['location']['$eq'] = new MongoId($location);
                }
                if (isset($this->request->query['news_categories']) && strlen($this->request->query['news_categories']) > 0) {
                        $news_categories = $this->request->query['news_categories'];
                        $this->request->query['news_categories'] = $news_categories;
                        $options['conditions']['news_categories']['$eq'] = $news_categories;
                }
                if (isset($this->request->query['news_collections']) && strlen($this->request->query['news_collections']) > 0) {
                        $news_collections = $this->request->query['news_collections'];
                        $this->request->query['news_collections'] = $news_collections;
                        $options['conditions']['news_collections'] = $news_collections;
                }
                if (isset($this->request->query['modified_start']) && strlen(trim($this->request->query['modified_start']))) {
                        $modified_start = trim($this->request->query['modified_start']);
                        $this->request->query['modified_start'] = $modified_start;
                        $options['conditions']['modified']['$gte'] = new MongoDate(strtotime($modified_start . ' 00:00:00'));
                }
                if (isset($this->request->query['modified_end']) && strlen(trim($this->request->query['modified_end']))) {
                        $modified_end = trim($this->request->query['modified_end']);
                        $this->request->query['modified_end'] = $modified_end;
                        $options['conditions']['modified']['$lte'] = new MongoDate(strtotime($modified_end . ' 23:59:59'));
                }
        }

        protected function setInit() {
                $this->set('controller_name', $this->name);
                $this->set('model_name', $this->modelClass);
                $this->set('status', Configure::read('sysconfig.App.status'));
                $this->set('categories', $this->NewCategory->find('list', $this->optionSortByOrder));
                $this->set('collections', $this->NewCollection->find('list', $this->optionSortByOrder));
        }

        public function beforeFilter() {
                parent::beforeFilter();
                //$this->Auth->allow();
        }

}
