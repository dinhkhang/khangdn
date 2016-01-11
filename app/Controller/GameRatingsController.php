<?php

App::uses('AppQuizController', 'Controller');
class GameRatingsController extends AppQuizController {

    public $uses = array('GameRating');

    public function index_Ajax() {
        header('Content-Type: application/json');
        $this->set('model_name', $this->modelClass);
        $data = $this->request['data'];
        if (empty($data)) {
            $time = getdate();
            $mon_week = 0;
            $year = $time['year'];
            $week_num = $this->GameRating->getWeek($time['year'], $time['mon'], $time['mday']);
        } else {
            $year = $data['year'];
            $mon_week = $data['mon_week'];
            $week_num = $data['num_count'];
        }
        $check = true;
        $limit = Configure::read('sysconfig.Rating.limit');

        if (empty($year) || $year == 0 || ($mon_week != 0 && $mon_week != 1) || empty($week_num) || $week_num == 0) {
            $check = false;
        }
        if ($mon_week == 0) {
            $score = 'week.score';
            $time = 'week.time';
        } elseif ($mon_week == 1) {
            $score = 'month.score';
            $time = 'month.time';
        } else {
            $check = false;
        }
        if ($check) {
            $option = $this->getOption($mon_week, $week_num, $limit);
            $this->{$this->modelClass}->useTable = 'score_' . $year;

            $this->Paginator->settings = $option;
            $list_data = $this->Paginator->paginate($this->modelClass, [], [$score, $time]);
            $listTopPlayer = $this->GameRating->modifyData($list_data, $mon_week, $limit);
        }

        if (empty($listTopPlayer)) {
            $listTopPlayer['error'] = 0;
            $listTopPlayer['error_msg'] = '<tr><td colspan="4" style="text-align:center;">' . Configure::read('sysconfig.Rating.error_msg') . '</td></tr>';
        }

        $this->set('year', $year);
        $this->set('week', $week_num);
        if (!$this->request->is('post')) {
            $this->set('listYear', $this->GameRating->getYear($year));
            $this->set('listMonthWeek', $this->GameRating->getMonthWeek());
        }

        if ($this->request->is('post')) {
            $this->layout = null;
            $this->autoRender = false;
            return json_encode($listTopPlayer);
        } else {
            $this->set('listTopPlayer', $listTopPlayer);
        }
    }

    public function index() {
        header('Content-Type: application/json');
        $this->set('model_name', $this->modelClass);
        $time = getdate();

        $year = is_numeric($this->request->query('year')) ? (int) $this->request->query('year') : null;
        if (empty($year)) {
            $year = $time['year'];
        }
        $mon_week = is_numeric($this->request->query('mon_week')) ? (int) $this->request->query('mon_week') : -1;
        if ($mon_week != 0 && $mon_week != 1) {
            $mon_week = 0;
        }
        $week_num = is_numeric($this->request->query('num_count')) ? (int) $this->request->query('num_count') : -1;
        if ($mon_week == 0 && ($week_num > 52 || $week_num < 0)) {
            $week_num = $this->GameRating->getWeek($time['year'], $time['mon'], $time['mday']);
        } elseif ($mon_week == 1 && ($week_num > 12 || $week_num < 0)) {
            $week_num = $time['mon'];
        }

        $page = !empty($this->passedArgs) ? (int) $this->passedArgs['page'] : 0;
        $check = true;
        $limit = Configure::read('sysconfig.Rating.limit');
        if (empty($year) || $year == 0 || ($mon_week != 0 && $mon_week != 1) || empty($week_num) || $week_num == 0 || $page < 0 || $page > 10) {
            $check = false;
        }
        if ($mon_week == 0) {
            $score = 'week.score';
            $time = 'week.time';
            $total_num = 52;
        } elseif ($mon_week == 1) {
            $score = 'month.score';
            $time = 'month.time';
            $total_num = 12;
        } else {
            $check = false;
        }
        if ($check) {
            $option = $this->getOption($mon_week, $week_num, $limit);
            $this->{$this->modelClass}->useTable = 'score_' . $year;

            $this->Paginator->settings = $option;
            $list_data = $this->Paginator->paginate($this->modelClass, [], [$score, $time]);
            $listTopPlayer = $this->GameRating->modifyData($list_data, $mon_week, $limit, $page);
        }

        if (empty($listTopPlayer)) {
            $listTopPlayer['error'] = 0;
            $listTopPlayer['error_msg'] = '<tr><td colspan="4" style="text-align:center;">' . Configure::read('sysconfig.Rating.error_msg') . '</td></tr>';
        }

        $this->set('year', $year);
        $this->set('week_num', $mon_week);
        $this->set('week_count', $week_num);
        $this->set('total_num', $total_num);
        $this->set('listYear', $this->GameRating->getYear($year));
        $this->set('listMonthWeek', $this->GameRating->getMonthWeek());
        $this->set('listTopPlayer', $listTopPlayer);
    }

    public function ratingSearch() {
        $this->layout = NULL;
        $this->autoRender = false;
        header('Content-Type: application/json');
        if ($this->request->is('post')) {

            $data = $this->request['data'];
            $year = $data['year'];
            $mon_week = $data['mon_week'];
            $week_num = $data['num_count'];

            $check = true;
            $limit = Configure::read('sysconfig.Rating.limit');

            if (empty($year) || $year == 0 || ($mon_week != 0 && $mon_week != 1) || empty($week_num) || $week_num == 0) {
                $check = false;
            }
            if ($mon_week == 0) {
                $score = 'week.score';
                $time = 'week.time';
            } elseif ($mon_week == 1) {
                $score = 'month.score';
                $time = 'month.time';
            } else {
                $check = false;
            }
            if ($check) {
                $option = $this->getOption($mon_week, $week_num, $limit);
                $this->{$this->modelClass}->useTable = 'score_' . $year;

                $this->Paginator->settings = $option;
                $list_data = $this->Paginator->paginate($this->modelClass, [], [$score, $time]);
                $listTopPlayer = $this->GameRating->modifyData($list_data, $mon_week, $limit);
            }

            if (empty($listTopPlayer)) {
                $listTopPlayer['error'] = 0;
                $listTopPlayer['error_msg'] = '<tr><td colspan="4" style="text-align:center;">' . Configure::read('sysconfig.Rating.error_msg') . '</td></tr>';
            }
            return json_encode($listTopPlayer);
        }
    }

    protected function getOption($mon_week, $num_count, $limit) {
        $option = array(
            'limit' => $limit,
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
