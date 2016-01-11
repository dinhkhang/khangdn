<?php


App::uses('AppQuizController', 'Controller');
class CheckScoresController extends AppQuizController {

    public $uses = array('CheckScore', 'Player');

    public function index() {
        header('Content-Type: application/json');
        $visitor = $this->Session->read('Auth.User');
        if (empty($visitor)) {
            $this->redirect('/gamequiz');
        }
        $phone_num = $visitor['mobile'];

        if ($this->Player->checkPlayer($phone_num)) {
            $this->redirect('/gamequiz');
        }

        $this->set('model_name', $this->modelClass);
        $data = $this->request['data'];
        if (empty($data)) {
            $time = getdate();
            $mon_week = 0;
            $year = $time['year'];
            $week_num = $this->CheckScore->getWeek($year, $time['mon'], $time['mday']);
        } else {
            $year = $data['year'];
            $mon_week = $data['mon_week'];
            $week_num = $data['num_count'];
        }

        $this->set('year', $year);
        $this->set('week', $week_num);
        if (!$this->request->is('post')) {
            $this->set('listYear', $this->CheckScore->getYear($year));
            $this->set('listMonthWeek', $this->CheckScore->getMonthWeek());
        }

        $check = true;

        if (empty($year) || $year == 0 || ($mon_week != 0 && $mon_week != 1) || empty($week_num) || $week_num == 0) {
            $check = false;
        }
        if ($check) {
            $listPoint = $this->CheckScore->getListPointByDay($year, $mon_week, $week_num, $phone_num);
//            $listPoint = $this->CheckScore->getListPointByDay($year, $mon_week, $week_num, '84979930750');
            $this->set('listPoint', $listPoint);
        }

        if (empty($listPoint || !$check)) {
            $listPoint['error'] = 0;
            $listPoint['error_msg'] = '<tr><td colspan="3">' . Configure::read('sysconfig.Rating.error_msg') . '</td><td>';
        }
        if ($this->request->is('post')) {
            $this->layout = null;
            $this->autoRender = false;
            return json_encode($listPoint);
        } else {
            $this->set('listPoint', $listPoint);
        }
    }

    protected function redirectHome() {

        if ($this->referer() == Router::url(array('action' => 'login'), true)) {

            return $this->redirect(array('action' => 'index', 'controller' => 'Home'));
        }

        if ($this->referer() != '/') {

            return $this->redirect($this->referer());
        }

        return $this->redirect(array('action' => 'index', 'controller' => 'Home'));
    }

//    public function beforeFilter() {
//        parent::beforeFilter();
//
//        $this->Auth->allow();
//    }
}
