<?php

class RatingCommentController extends AppController {

    public $debug_mode = 3;
    // đối tượng được bookmark
    public $object_type = null;
    public $object_class = null;
    public $object_id = null;
    public $object_comment_class = null;
    public $object_rating_class = null;
    public $visitor_username = null;
    public $visitor_id = null;
    public $relation_field = null;
    public $uses = array(
        'Visitor',
    );

    public function index() {

        $this->setInit();

        // kiểm tra xen object_class tương ứng với object_id được truyền lên có tồn tại k?
        $check_object = $this->{$this->object_class}->find('first', array(
            'conditions' => array(
                'id' => new MongoId($this->object_id),
            ),
        ));
        // nếu không tồn tại
        if (empty($check_object)) {

            $this->resError('#rac002', array('message_args' => array($this->object_class, $this->object_id)));
        }
        // nếu trạng thái không phải là public
        if ($check_object[$this->object_class]['status'] != Configure::read('sysconfig.App.constants.STATUS_APPROVED')) {

            $this->resError('#rac003', array('message_args' => array($this->object_class, $this->object_id)));
        }

        $res = array(
            'status' => 'success',
            'data' => array(
                'id' => $check_object[$this->object_class]['id'],
                'type' => $this->object_type,
                'name' => $check_object[$this->object_class]['name'],
            ),
        );

        $this->visitor_username = trim($this->request->query('user_id'));

        $visitor = array();
        if (!empty($this->visitor_username)) {

            $visitor = $this->Visitor->getInfoByUsername($this->visitor_username);
        }

        $this->setRatings($visitor, $check_object, $res);
        $this->setComments($res);

        $this->resSuccess($res);
    }

    protected function setRatings($visitor, $check_object, &$res) {

        $is_rated = 0;
        if (!empty($visitor)) {

            $this->visitor_id = $visitor['Visitor']['id'];
            $check_rating = $this->{$this->object_rating_class}->find('first', array(
                'conditions' => array(
                    $this->relation_field => new MongoId($this->object_id),
                    'visitor' => new MongoId($this->visitor_id),
                ),
            ));
            if (!empty($check_rating)) {

                $is_rated = 1;
            }
        }
        $res['data']['is_rated'] = $is_rated;
        $score_levels = Configure::read('sysconfig.RatingComment.score_levels');
        $rating_statistics = array();

        // nếu chưa tồn tại rating_statistics nào
        if (
                empty($check_object[$this->object_class]['rating_statistics']) ||
                !is_array($check_object[$this->object_class]['rating_statistics'])
        ) {

            foreach ($score_levels as $lvl) {

                $rating_statistics['star_' . $lvl] = array(
                    'score' => $lvl,
                    'count' => 0,
                );
            }
        } else {

            $raw_rating_statistics = $check_object[$this->object_class]['rating_statistics'];
            foreach ($score_levels as $lvl) {

                $rating_statistics['star_' . $lvl] = !empty($raw_rating_statistics[$lvl]) ? $raw_rating_statistics[$lvl] : array(
                    'score' => $lvl,
                    'count' => 0,
                );
            }
        }

        $res['data']['rating_statistics'] = $rating_statistics;

        $rating = array(
            'score' => !empty($check_object[$this->object_class]['rating']['score']) ?
                    $check_object[$this->object_class]['rating']['score'] : 0,
            'name' => !empty($check_object[$this->object_class]['rating']['name']) ?
                    $check_object[$this->object_class]['rating']['name'] : '',
            'count' => !empty($check_object[$this->object_class]['rating']['count']) ?
                    $check_object[$this->object_class]['rating']['count'] : 0,
        );
        $res['data']['rating'] = $rating;
    }

    protected function setComments(&$res) {

        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {

            $limit = Configure::read('sysconfig.Comments.limit');
        }
        $page = (int) trim($this->request->query('page'));
        if ($page <= 0) {

            $page = 1;
        }

        $options = array(
            'conditions' => array(
                $this->relation_field => new MongoId($this->object_id),
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                '$or' => array(
                    array(
                        'parent_id' => '', // lấy ra comment cấp 1
                    ),
                    array(
                        'parent_id' => null, // lấy ra comment cấp 1
                    ),
                ),
            ),
            'order' => array(
                'order' => 'ASC',
                'modified' => 'DESC',
            ),
            'page' => $page,
            'limit' => $limit,
        );

        $comments = $this->{$this->object_comment_class}->find('all', $options);
        if (empty($comments)) {

            $res['data']['total_comment'] = 0;
            $res['data']['arr_comment'] = array();
            return;
        }

        // đếm tổng số thread comment
        $res['data']['total_thread_comment'] = $this->{$this->object_comment_class}->getTotal($options);
        $res['data']['page'] = $page;
        $res['data']['limit'] = $limit;

        // đếm tổng số tất cả comment bao gồm cả thread và subcomment
        unset($options['$or']);
        $res['data']['total_comment'] = $this->{$this->object_comment_class}->getTotal($options);

        foreach ($comments as $k => $v) {

            $thread_id = $v[$this->object_comment_class]['id'];

            // đếm số subcomment của thread
            $total_sub_comment = $this->{$this->object_comment_class}->getTotalSubComment($thread_id);
            $res['data']['arr_comment'][$k] = array(
                'id' => $thread_id,
                'thread_id' => $thread_id,
                'content' => $v[$this->object_comment_class]['content'],
                // user_id trả về chính là username
                'user_id' => $v[$this->object_comment_class]['visitor']['username'],
                'user_name' => $v[$this->object_comment_class]['visitor']['name'],
                'avatar_url' => $this->getAvatarUrl($v[$this->object_comment_class]),
                'total_sub_comment' => $total_sub_comment,
                'modified' => $v[$this->object_comment_class]['modified'],
            );
        }

        return $res;
    }

    public function setInit() {
        parent::setInit();

        $this->object_type = trim($this->request->query('type'));
        $this->object_id = trim($this->request->query('object_id'));
        if (empty($this->object_type) || empty($this->object_id)) {

            $this->resError('#rac001');
        }

        $this->object_class = Inflector::classify($this->object_type);
        $this->loadModel($this->object_class);

        // nạp vào Rating model 
        $this->object_rating_class = Inflector::classify($this->object_type) . 'Rating';
        $this->loadModel($this->object_rating_class);

        // nạp vào Comment model 
        $this->object_comment_class = Inflector::classify($this->object_type) . 'Comment';
        $this->loadModel($this->object_comment_class);

        $relation_field = strtolower(Inflector::singularize($this->object_type));
        $this->relation_field = $relation_field;
    }

}
