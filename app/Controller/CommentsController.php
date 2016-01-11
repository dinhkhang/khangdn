<?php

class CommentsController extends AppController {

    public $debug_mode = 3;
    public $discussion_type = null;
    public $discussion_id = null;
    public $thread_id = null;
    public $model_class = null;
    public $discussion_class = null;
    public $relation_field = null;

    public function index() {

        $this->setInit();

        $this->discussion_id = trim($this->request->query('discussion_id'));
        $this->discussion_type = trim($this->request->query('discussion_type'));

        // nạp động vào Commet model tương ứng với type
        $model = Inflector::classify($this->discussion_type) . 'Comment';
        $this->loadModel($model);
        $this->model_class = $model;

        $this->discussion_class = Inflector::classify($this->discussion_type);
        $this->loadModel($this->discussion_class);

        $relation_field = strtolower(Inflector::singularize($this->discussion_type));
        $this->relation_field = $relation_field;

        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {

            $limit = Configure::read('sysconfig.Comments.limit');
        }
        $page = (int) trim($this->request->query('page'));
        if ($page <= 0) {

            $page = 1;
        }

        if (empty($this->discussion_id) || empty($this->discussion_type)) {

            $this->resError('#com011');
        }

        $options = array(
            'conditions' => array(
                $this->relation_field => new MongoId($this->discussion_id),
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
                'modified' => 'DESC',
            ),
            'page' => $page,
            'limit' => $limit,
        );

        $comments = $this->{$this->model_class}->find('all', $options);
        $res = array(
            'status' => 'success',
            'data' => array(
                'arr_comment' => array(),
                'limit' => $limit,
                'page' => $page,
                'total' => 0,
                'discussion_type' => $this->discussion_type,
                'discussion_id' => $this->discussion_id,
            ),
        );

        if (empty($comments)) {

            $this->resSuccess($res);
        }

        // đếm tổng số thread comment
        $res['data']['total'] = $this->{$this->model_class}->getTotal($options);

        foreach ($comments as $k => $v) {

            $thread_id = $v[$this->model_class]['id'];

            // đếm số subcomment của thread
            $total_sub_comment = $this->{$this->model_class}->getTotalSubComment($thread_id);
            $res['data']['arr_comment'][$k] = array(
                'thread_id' => $thread_id,
                'content' => $v[$this->model_class]['content'],
                'user_id' => $v[$this->model_class]['visitor']['username'],
                'user_name' => $v[$this->model_class]['visitor']['name'],
                'avatar_url' => $this->getAvatarUrl($v[$this->model_class]),
                'total_sub_comment' => $total_sub_comment,
                'modified' => $v[$this->model_class]['modified'],
            );
        }

        $this->resSuccess($res);
    }

    public function getSubComments() {

        $this->setInit();

        $this->discussion_type = trim($this->request->query('discussion_type'));

        // nạp động vào Commet model tương ứng với type
        $model = Inflector::classify($this->discussion_type) . 'Comment';
        $this->loadModel($model);
        $this->model_class = $model;

        $this->discussion_class = Inflector::classify($this->discussion_type);
        $this->loadModel($this->discussion_class);

        $relation_field = strtolower(Inflector::singularize($this->discussion_type));
        $this->relation_field = $relation_field;

        $this->thread_id = trim($this->request->query('thread_id'));
        if (empty($this->thread_id)) {

            $this->resError('#com015');
        }

        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {

            $limit = Configure::read('sysconfig.Comments.limit');
        }
        $page = (int) trim($this->request->query('page'));
        if ($page <= 0) {

            $page = 1;
        }

        if (empty($this->discussion_type)) {

            $this->resError('#com016');
        }

        // validate thread_id, kiểm tra xem thread_id có tồn tại và public hay k?
        // đồng thời thread_id là comment cấp 1
        $this->validateThread();

        $options = array(
            'conditions' => array(
                'parent_id' => new MongoId($this->thread_id),
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
            ),
            'order' => array(
                'modified' => 'DESC',
            ),
            'page' => $page,
            'limit' => $limit,
        );

        $sub_comments = $this->{$this->model_class}->find('all', $options);
        $res = array(
            'status' => 'success',
            'data' => array(
                'arr_sub_comment' => array(
                ),
                'limit' => $limit,
                'page' => $page,
                'total' => 0,
                'discussion_type' => $this->discussion_type,
                'thread_id' => $this->thread_id,
            ),
        );
        if (empty($sub_comments)) {

            $this->resSuccess($res);
        }

        // đếm tổng số thread comment
        $res['data']['total'] = $this->{$this->model_class}->getTotal($options);

        foreach ($sub_comments as $k => $v) {

            $sub_comment_id = $v[$this->model_class]['id'];

            $res['data']['arr_sub_comment'][$k] = array(
                'sub_comment_id' => $sub_comment_id,
                'content' => $v[$this->model_class]['content'],
                'user_id' => $v[$this->model_class]['visitor']['username'],
                'user_name' => $v[$this->model_class]['visitor']['name'],
                'avatar_url' => $this->getAvatarUrl($v[$this->model_class]),
                'modified' => $v[$this->model_class]['modified'],
            );
        }

        $this->resSuccess($res);
    }

    public function add() {

        $this->setPostInit();

        if (!$this->request->is('post')) {

            $this->resError('#com002');
        }

        if (empty($this->discussion_id)) {

            $this->resError('#com008');
        }

        $content = trim($this->request->data('content'));
        if (!strlen($content)) {

            $this->resError('#com004');
        }

        // kiểm tra xem discussion_id có tồn tại hay không?
        $check_discussion = $this->{$this->discussion_class}->find('first', array(
            'conditions' => array(
                'id' => new MongoId($this->discussion_id),
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
            ),
        ));
        if (empty($check_discussion)) {

            $this->resError('#com010', array('message_args' => array($this->discussion_class, $this->discussion_id)));
        }

        // kiểm tra token của visitor
        $token_decode = $this->validateToken();

        // validate thread_id, kiểm tra xem thread_id có tồn tại và public hay k?
        // đồng thời thread_id là comment cấp 1
        $this->validateThread();

        if (empty($this->thread_id)) {

            $parent_id = null;
        } else {

            $parent_id = new MongoId($this->thread_id);
        }

        $avatar_uri = $this->getRelativeFileUris($token_decode->Visitor['Visitor'], 'avatar');
        $save_data = array(
            $this->relation_field => new MongoId($this->discussion_id),
            'parent_id' => $parent_id,
            'visitor' => array(
                '_id' => new MongoId($token_decode->visitor->id),
                'username' => $token_decode->visitor->username,
                'name' => $token_decode->visitor->name,
                'avatar_uri' => $avatar_uri,
            ),
            'content' => $content,
            'status' => Configure::read('sysconfig.Comments.default_status'),
        );

        $this->{$this->model_class}->create();
        $comment = $this->{$this->model_class}->save($save_data);
        if (!$comment) {

            $this->resError('#com005', array('message_args' => array($this->model_class, $token_decode->visitor->id)));
        }

        // đọc lại nội dung comment vừa save để trả về
        $return = $this->{$this->model_class}->find('first', array(
            'conditions' => array(
                'id' => new MongoId($this->{$this->model_class}->getLastInsertID()),
            ),
        ));

        $res = array(
            'status' => 'success',
            'data' => $return[$this->model_class],
        );
        $this->resSuccess($res);
    }

    protected function validateThread() {

        // validate thread_id, kiểm tra xem thread_id có tồn tại và public hay k?
        // đồng thời thread_id là comment cấp 1
        if (!empty($this->thread_id)) {

            $check_thread = $this->{$this->model_class}->find('first', array(
                'conditions' => array(
                    'id' => new MongoId($this->thread_id),
                ),
            ));

            if (empty($check_thread)) {

                $this->resError('#com012', array('message_args' => $this->thread_id));
            }

            // nếu thread không có trạng thái là public
            if ($check_thread[$this->model_class]['status'] != Configure::read('sysconfig.App.constants.STATUS_APPROVED')) {

                $this->resError('#com013', array('message_args' => $this->thread_id));
            }

            // check xem đây có phải thread cấp 1 không?
            if (!empty($check_thread[$this->model_class]['parent_id'])) {

                $this->resError('#com014', array('message_args' => $this->thread_id));
            }

            return $check_thread;
        }
    }

    public function edit() {

        $this->setPostInit();

        if (!$this->request->is('post')) {

            $this->resError('#com002');
        }

        $id = trim($this->request->data('id'));
        if (empty($id)) {

            $this->resError('#com006');
        }

        $content = trim($this->request->data('content'));
        if (!strlen($content)) {

            $this->resError('#com004');
        }

        // kiểm tra token của visitor
        $token_decode = $this->validateToken();

        // thực hiện check xem comment có tồn tại và thuộc về visitor đang chỉnh sửa không?
        $check = $this->{$this->model_class}->find('first', array(
            'conditions' => array(
                'id' => new MongoId($id),
                'visitor._id' => new MongoId($token_decode->visitor->id),
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
            ),
        ));
        if (empty($check)) {

            $this->resError('#com003', array('message_args' => array($token_decode->visitor->id, $this->model_class, $id)));
        }

        $save_data = array(
            'id' => $id,
            'content' => $content,
        );

        $comment = $this->{$this->model_class}->save($save_data);
        if (!$comment) {

            $this->resError('#com005', array('message_args' => $token_decode->visitor->id));
        }

        // đọc lại nội dung comment vừa save để trả về
        $return = $this->{$this->model_class}->find('first', array(
            'conditions' => array(
                'id' => new MongoId($id),
            ),
        ));

        $res = array(
            'status' => 'success',
            'data' => $return[$this->model_class],
        );
        $this->resSuccess($res);
    }

    public function delete() {

        $this->setPostInit();

        if (!$this->request->is('delete')) {

            $this->resError('#com007');
        }

        $id = trim($this->request->data('id'));
        if (empty($id)) {

            $this->resError('#com006');
        }

        // kiểm tra token của visitor
        $token_decode = $this->validateToken();

        // thực hiện check xem comment có tồn tại và thuộc về visitor đang chỉnh sửa không?
        $check = $this->{$this->model_class}->find('first', array(
            'conditions' => array(
                'id' => new MongoId($id),
                'visitor._id' => new MongoId($token_decode->visitor->id),
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
            ),
        ));
        if (empty($check)) {

            $this->resError('#com003', array('message_args' => array($token_decode->visitor->id, $this->model_class, $id)));
        }

        $save_data = array(
            'id' => $id,
            'status' => Configure::read('sysconfig.App.constants.STATUS_HIDDEN'),
        );

        $comment = $this->{$this->model_class}->save($save_data);
        if (!$comment) {

            $this->resError('#com009', array('message_args' => array($this->model_class, $id, $token_decode->visitor->id)));
        }

        $res = array(
            'status' => 'success',
            'data' => null,
        );
        $this->resSuccess($res);
    }

    protected function setPostInit() {
        parent::setInit();

        $type = trim($this->request->data('discussion_type'));
        $discussion_id = trim($this->request->data('discussion_id'));
        $thread_id = trim($this->request->data('thread_id'));

        $this->type = $type;
        $this->discussion_id = $discussion_id;
        $this->thread_id = $thread_id;

        if (empty($type)) {

            $this->resError('#com001');
        }

        // nạp động vào Commet model tương ứng với type
        $model = Inflector::classify($type) . 'Comment';
        $this->loadModel($model);
        $this->model_class = $model;

        $this->discussion_class = Inflector::classify($type);
        $this->loadModel($this->discussion_class);

        $relation_field = strtolower(Inflector::singularize($this->type));
        $this->relation_field = $relation_field;
    }

}
