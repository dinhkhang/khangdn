<?php

App::uses('AppQuizController', 'Controller');

class VisitorBlacklistsController extends AppQuizController {

    public $uses = array('VisitorBlacklist');

    public function index() {
        
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
    }

}
