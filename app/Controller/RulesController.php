<?php

App::uses('AppQuizController', 'Controller');
class RulesController extends AppQuizController {

    public $uses = array('Rule');

    public function index() {
        $getRule = $this->Rule->getRule();
        $this->set('model_name', $this->modelClass);
        $this->set('getRule', $getRule);
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
    }

}
