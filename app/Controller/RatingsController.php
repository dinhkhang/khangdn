<?php

class RatingsController extends AppController {

    public $debug_mode = 3;
    // đối tượng được bookmark
    public $object_type = null;
    public $object_class = null;
    public $object_id = null;
    public $model_class = null;
    public $visitor_id = null;
    public $relation_field = null;
    public $uses = array(
        'Visitor',
    );

    public function add() {

        $this->setInit();

        $score = (int) trim($this->request->data('score'));
        if ($score <= 0) {

            $this->resError('#rat005');
        }

        // kiểm tra xen object_class tương ứng với object_id được truyền lên có tồn tại k?
        $check_object = $this->{$this->object_class}->find('first', array(
            'conditions' => array(
                'id' => new MongoId($this->object_id),
            ),
        ));
        // nếu không tồn tại
        if (empty($check_object)) {

            $this->resError('#rat002', array('message_args' => array($this->object_class, $this->object_id)));
        }
        // nếu trạng thái không phải là public
        if ($check_object[$this->object_class]['status'] != Configure::read('sysconfig.App.constants.STATUS_APPROVED')) {

            $this->resError('#rat003', array('message_args' => array($this->object_class, $this->object_id)));
        }

        $token_decode = $this->validateToken();
        $this->visitor_id = $token_decode->visitor->id;

        // kiểm tra xem visitor đã rating object hiện tại chưa?
        $check_rating = $this->{$this->model_class}->find('first', array(
            'conditions' => array(
                $this->relation_field => new MongoId($this->object_id),
                'visitor' => new MongoId($this->visitor_id),
            ),
        ));
        // nếu đã rating rồi thì trả về success luôn
        if (!empty($check_rating)) {

            $res = array(
                'status' => 'success',
                'data' => array(
                    'type' => $this->object_type,
                    'object_id' => $this->object_id,
                    'rate_name' => !empty($check_object[$this->object_class]['rating']['name']) ?
                            $check_object[$this->object_class]['rating']['name'] : '',
                    'rate_count' => !empty($check_object[$this->object_class]['rating']['count']) ?
                            $check_object[$this->object_class]['rating']['count'] : 0,
                    'score' => !empty($check_object[$this->object_class]['rating']['score']) ?
                            $check_object[$this->object_class]['rating']['score'] : 0,
                ),
            );
            $this->resSuccess($res);
        }

        // thực hiện insert vào object_ratings collection
        $this->{$this->model_class}->create();
        $create_rating = $this->{$this->model_class}->save(array(
            $this->relation_field => new MongoId($this->object_id),
            'visitor' => new MongoId($this->visitor_id),
            'score' => $score,
            'os_name' => $this->os_name,
            'os_version' => $this->os_version,
        ));
        if (!$create_rating) {

            $this->resError('#rat006', array('message_args' => array($this->object_class, $this->visitor_id)));
        }

        // nếu chưa tồn tại rating_statistics nào
        if (
                empty($check_object[$this->object_class]['rating_statistics']) ||
                !is_array($check_object[$this->object_class]['rating_statistics'])
        ) {

            $save_data = array(
                'id' => $this->object_id,
                'rating_statistics' => array(
                    $score => array(
                        'score' => $score,
                        'count' => 1,
                    ),
                ),
                'rating' => array(
                    'score' => $score,
                    'count' => 1,
                    'name' => $this->getRateName($score),
                ),
            );
            if (!$this->{$this->object_class}->save($save_data)) {

                $this->resError('#rat007', array('message_args' => array($this->object_class, $this->object_id, $this->visitor_id)));
            }
            $res = array(
                'status' => 'success',
                'data' => array(
                    'type' => $this->object_type,
                    'object_id' => $this->object_id,
                    'rate_name' => $this->getRateName($score),
                    'rate_count' => 1,
                    'score' => $score,
                ),
            );
            $this->resSuccess($res);
        }

        $rating_statistics = $check_object[$this->object_class]['rating_statistics'];
        if (empty($rating_statistics[$score])) {

            $rating_statistics[$score]['score'] = $score;
            $rating_statistics[$score]['count'] = 1;
        } else {

            $rating_statistics[$score]['count'] = $rating_statistics[$score]['count'] + 1;
            $rating_statistics[$score]['score'] = $score;
        }

        $rating = array(
            'score' => !empty($check_object[$this->object_class]['rating']['score']) ?
                    $check_object[$this->object_class]['rating']['score'] : 0,
            'name' => !empty($check_object[$this->object_class]['rating']['name']) ?
                    $check_object[$this->object_class]['rating']['name'] : 0,
            'count' => !empty($check_object[$this->object_class]['rating']['count']) ?
                    $check_object[$this->object_class]['rating']['count'] : 0,
        );
        $rating['count'] = (int) $rating['count'] + 1;
        $rating['score'] = $this->caculateRateScore($rating_statistics);
        $rating['name'] = $this->getRateName($rating['score']);

        $save_data = array(
            'id' => $this->object_id,
            'rating_statistics' => $rating_statistics,
            'rating' => $rating,
        );
        if (!$this->{$this->object_class}->save($save_data)) {

            $this->resError('#rat008', array('message_args' => array($this->object_class, $this->object_id, $this->visitor_id)));
        }
        $res = array(
            'status' => 'success',
            'data' => array(
                'type' => $this->object_type,
                'object_id' => $this->object_id,
                'rate_name' => $rating['name'],
                'rate_count' => $rating['count'],
                'score' => $rating['score'],
            ),
        );
        $this->resSuccess($res);
    }

    /**
     * caculateRateScore
     * tính toán ra các chỉ số rating 
     * 
     * @param array $rating_statistics
     * @return int
     */
    protected function caculateRateScore($rating_statistics) {

        if (empty($rating_statistics)) {

            return 0;
        }

        $total = 0;
        $total_weight = 0;
        foreach ($rating_statistics as $v) {

            $score = $v['score'];
            $count = $v['count'];
            $total += $score * $count;
            $total_weight += $count;
        }

        $result = round($total / $total_weight, 1);
        return $result;
    }

    /**
     * getRateName
     * lấy ra tên chỉ số rating dựa vào điểm số rating
     * 
     * @param float $score
     * @return string
     */
    protected function getRateName($score) {

        if ($score > 0 && $score < 1) {

            return __('Kém');
        } elseif ($score >= 1 && $score < 2) {

            return __('Trung bình');
        } elseif ($score >= 2 && $score < 3) {

            return __('Tốt');
        } elseif ($score >= 3 && $score < 4) {

            return __('Rất tốt');
        } elseif ($score <= 0) {

            return '';
        } else {

            return __('Tuyệt vời');
        }
    }

    public function setInit() {
        parent::setInit();

        if (!$this->request->is('post')) {

            $this->resError('#rat004');
        }

        $this->object_type = trim($this->request->data('type'));
        $this->object_id = trim($this->request->data('object_id'));
        if (empty($this->object_type) || empty($this->object_id)) {

            $this->resError('#rat001');
        }

        $this->object_class = Inflector::classify($this->object_type);
        $this->loadModel($this->object_class);

        // nạp động vào Commet model tương ứng với type
        $this->model_class = Inflector::classify($this->object_type) . 'Rating';
        $this->loadModel($this->model_class);

        $relation_field = strtolower(Inflector::singularize($this->object_type));
        $this->relation_field = $relation_field;
    }

}
