<?php

App::uses('AppController', 'Controller');

class CategoriesController extends AppController {

        public $uses = array('Category');
        public $helpers = array('TreeCommon');

        public function index() {

                $this->setInit();

                $breadcrumb = array();
                $breadcrumb[] = array(
                    'url' => Router::url(array('action' => 'index')),
                    'label' => __('category_title'),
                );
                $this->set('breadcrumb', $breadcrumb);
                $this->set('page_title', __('category_title'));

                if ($this->request->is('post') || $this->request->is('put')) {

                        if ($this->{$this->modelClass}->saveSerialize($this->request->data[$this->modelClass])) {

                                $this->Session->setFlash(__('save_successful_message'), 'default', array(), 'good');
                        } else {

                                $this->Session->setFlash(__('save_error_message'), 'default', array(), 'bad');
                        }
                }

                $list_data = $this->{$this->modelClass}->find('threaded');
                $this->set('list_data', $list_data);
        }

        public function add() {

                $this->setInit();
                $opts = array(
                    'order' => array(
                        'order' => 'ASC',
                    ),
                    'conditions' => array(
                        'parent_id' => null,
                    ),
                );
                $parent = $this->{$this->modelClass}->generateTreeList($opts);
                $this->set('parent', $parent);

                $breadcrumb = array();
                $breadcrumb[] = array(
                    'url' => Router::url(array('action' => 'index')),
                    'label' => __('category_title'),
                );
                $breadcrumb[] = array(
                    'url' => Router::url(array('action' => __FUNCTION__)),
                    'label' => __('add_action_title'),
                );
                $this->set('breadcrumb', $breadcrumb);
                $this->set('page_title', __('category_title'));

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

                $this->{$this->modelClass}->id = $id;
                if (!$this->{$this->modelClass}->exists()) {

                        throw new NotFoundException(__('invalid_data'));
                }

                if ($this->request->is('post') || $this->request->is('put')) {

                        $this->add();
                } else {

                        $this->setInit();
                        $opts = array(
                            'order' => array(
                                'order' => 'ASC',
                            ),
                            'conditions' => array(
                                'parent_id' => "",
                                'id' => array(
                                    '$ne' => $id,
                                ),
                            ),
                        );
                        $parent = $this->{$this->modelClass}->generateTreeList($opts);
                        $this->set('parent', $parent);

                        $breadcrumb = array();
                        $breadcrumb[] = array(
                            'url' => Router::url(array('action' => 'index')),
                            'label' => __('category_title'),
                        );
                        $breadcrumb[] = array(
                            'url' => Router::url(array('action' => __FUNCTION__, $id)),
                            'label' => __('edit_action_title'),
                        );
                        $this->set('breadcrumb', $breadcrumb);
                        $this->set('page_title', __('category_title'));

                        $this->request->data = $this->{$this->modelClass}->read(null, $id);
                }

                $this->render('add');
        }

        protected function setInit() {

                $this->set('model_name', $this->modelClass);
                $this->set('status', Configure::read('sysconfig.App.status'));
        }

}
