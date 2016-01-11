<?php
App::uses('AppQuizController', 'Controller');
class GamequizController extends AppQuizController {

    public $uses = array('GameRating', 'CheckScore', 'Rule');
 
    public function index() {
 
    }

    public function GameRatingSearch() {
        $this->layout = null;
        $this->autoRender = false;
        header('Content-Type: application/json');
        if ($this->request->is('post')) {
            $data = $this->request['data'];
            $year = $data['year'];
            $mon_week = $data['mon_week'];
            $num_count = $data['num_count'];
            $check = true;
            $page = 1;
            $limit = Configure::read('sysconfig.Rating.limit');

            if (empty($year) || $year == 0 || ($mon_week != 0 && $mon_week != 1) || empty($num_count) || $num_count == 0) {
                $check = false;
                $listTopPlayer['error'] = 0;
                $listTopPlayer['error_msg'] = Configure::read('sysconfig.Rating.error_msg');
            }
            if ($mon_week == 0) {
                $score = 'week.score';
                $time = 'week.time';
            } elseif ($mon_week == 1) {
                $score = 'month.score';
                $time = 'month.time';
            } else {
                $check = false;
                $listTopPlayer['error'] = 0;
                $listTopPlayer['error_msg'] = Configure::read('sysconfig.Rating.error_msg');
            }
            if ($check) {
                $option = $this->getOption($mon_week, $num_count, $page, $limit);
                $this->{$this->modelClass}->useTable = 'score_' . $year;

                $this->Paginator->settings = $option;
                $list_data = $this->Paginator->paginate($this->modelClass, [], [$score, $time]);
                $listTopPlayer = $this->GameRating->modifyData($list_data, $mon_week, $limit, $page);
            }

            if (empty($listTopPlayer)) {
                $listTopPlayer['error'] = 0;
                $listTopPlayer['error_msg'] = '<tr><td colspan="4">' . Configure::read('sysconfig.Rating.error_msg') . '</td><td>';
            }
            return json_encode($listTopPlayer);
        }
    }

    public function ScoreSearch() {
        $this->layout = null;
        $this->autoRender = false;
        header('Content-Type: application/json');
        $visitor = $this->Session->read('Auth.User');
        if (empty($visitor)) {
//            return json_encode($listPoint['error'] = 1);
        }
        $phone_num = $visitor['mobile'];

        if ($this->request->is('post')) {
            $data = $this->request['data'];
            $year = $data['year'];
            $mon_week = $data['mon_week'];
            $num_count = $data['num_count'];
            $check = true;

            if (empty($year) || $year == 0 || ($mon_week != 0 && $mon_week != 1) || empty($num_count) || $num_count == 0) {
                $check = false;                
            }
            if ($mon_week != 0 && $mon_week != 1) {
                $check = false;                
            }
            if ($check) {
//                $listPoint = $this->CheckScore->getListPointByDay($year, $mon_week, $num_count, $phone_num);
                $listPoint = $this->CheckScore->getListPointByDay($year, $mon_week, $num_count, '84979930750');
                $this->set('listPoint', $listPoint);
            }

            if (empty($listPoint || !$check)) {
                $listPoint['error'] = 0;
                $listPoint['error_msg'] = '<tr><td colspan="3">' . Configure::read('sysconfig.Rating.error_msg') . '</td><td>';
            }
            return json_encode($listPoint);
        }
    }

    public function checkLoged() {
        $this->layout = null;
        $this->autoRender = false;
        header('Content-Type: application/json');
        $visitor = $this->Session->read('Auth.User');
        if (empty($visitor)) {
            return json_encode($loged['loged'] = 'false');
        }
        return json_encode($loged['loged'] = 'ok');
    }

    public function getListOfNum() {
        if ($this->request->is('post')) {
            $this->layout = null;
            $this->autoRender = false;
            $data = $this->request['data'];
            if (is_numeric($data['mon_week'])) {
                $mon_week = $data['mon_week'];
            } else {
                $mon_week = 0;
            }
            $arr_num = $this->GameRating->getNumOfMW($mon_week);

            return json_encode($arr_num);
        }
    }

    protected function getOption($mon_week, $num_count, $page, $limit) {
        if (empty($page) || $page == 0) {
            $page = Configure::read('sysconfig.Rating.page');
        }

        $option = array(
            'limit' => $limit,
            'page' => $page,
        );

        if ($mon_week == 0) {
            $option['order'] = array(
                'week.score' => 'DESC',
                'week.time' => 'ASC',
            );
            $option['conditions']['week.index'] = (int) $num_count;
        }

        if ($mon_week == 1) {
            $option['order'] = array(
                'month.score' => 'DESC',
                'month.time' => 'ASC',
            );
            $option['conditions']['month.index'] = (int) $num_count;
        }

        return $option;
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow();
    }

}
