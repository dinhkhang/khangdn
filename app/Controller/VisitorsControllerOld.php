<?php

class VisitorsControllerOldController extends AppController {

    public $uses = array(
        'Visitor',
        'VisitorHistoryLocation',
    );
    public $components = array(
        'TrackingLogCommon',
    );
    public $debug_mode = 3;

    public function profile() {
        
    }

    public function detect() {

        $this->setInit();

        $msisdn = $this->detectMobile();
        if (empty($msisdn)) {

            $this->resError('#vis001');
        }

        // thực hiện tìm kiếm trong visitior xem đã tồn tại chưa?
        $visitor = $this->{$this->modelClass}->find('first', array(
            'conditions' => array(
                'mobile' => array(
                    '$regex' => new MongoRegex("/^" . $msisdn . "$/"),
                ),
            ),
        ));

        // nếu chưa tồn tại thì thực hiện insert
        if (empty($visitor)) {

            $default_notification_group_id = Configure::read('sysconfig.Visitor.default_notification_group_id');
            $save_data = array(
                'mobile' => $msisdn,
                'username' => $msisdn,
                'name' => $msisdn,
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                'visitor_notification_groups' => array(
                    new MongoId($default_notification_group_id),
                ),
            );

            $this->{$this->modelClass}->create();
            $visitor = $this->{$this->modelClass}->save($save_data);
            if (!$visitor) {

                $this->resError('#vis002');
            }
            $visitor_id = $this->{$this->modelClass}->getLastInsertID();
        }
        // nếu đã tồn tại visitor
        else {

            $visitor_id = $visitor[$this->modelClass]['id'];
        }

        // lưu lại vị trí hiện tại của visitor
        $this->saveVisitorGeo($visitor_id);

        // tạo token cho user
        $token = $this->generateToken($visitor);

        $this->TrackingLogCommon->logAction('login', 'detect', null, array(
            'token' => $token,
        ));

        $res = array(
            'status' => 'success',
            'data' => array(
                'id' => $visitor[$this->modelClass]['id'],
                'username' => $visitor[$this->modelClass]['username'],
                'name' => $visitor[$this->modelClass]['name'],
                'mobile' => $visitor[$this->modelClass]['mobile'],
                'email' => $visitor[$this->modelClass]['email'],
                'token' => $token,
            ),
        );

        $this->resSuccess($res);
    }

    protected function saveVisitorGeo($visitor_id) {

        if (empty($this->lng) || empty($this->lat)) {

            return;
        }

        // lưu lại vị trí hiện tại của visitor
        $save_data = array(
            'id' => $visitor_id,
            'loc' => array(
                'type' => Configure::read('sysconfig.App.GeoJSON_type'),
                'coordinates' => array(
                    $this->lng, $this->lat,
                ),
            ),
        );
        $this->{$this->modelClass}->save($save_data);

        // lưu lại lịch sử vị trí của visitor
        $this->VisitorHistoryLocation->create();
        $this->VisitorHistoryLocation->save(array(
            'visitor' => new MongoId($visitor_id),
            'loc' => $save_data['loc'],
        ));
    }

    public function login() {

        $this->setInit();

        $token = $this->request->header('token');

        $password = $this->request->data('password');
        $username = trim($this->request->data('username'));

        // nếu không thì xác thực theo kiểu thông thường
        if (!empty($username) || !empty($password)) {

            $this->authorize();
        }
        // nếu truyền lên token thì xác định token
        elseif (!empty($token)) {

            $this->authorizeToken($token);
        } else {

            $this->resError('#vis012');
        }
    }

    /**
     * logout
     * thực hiện đăng xuất cho visitor
     * chú ý ở client cũng thực hiện xóa bỏ token, và khi đã xóa bỏ thì không truyền lên token ở header nữa
     */
    public function logout() {

        if (!$this->request->is('post')) {

            $this->resError('#vis006');
        }

        $this->setInit();

//        $this->TrackingLogCommon->logAction('access', 'logout', null, array(
//            'payload' => $this->request->data,
//        ));

        $user_id = trim($this->request->data('user_id'));
        if (empty($user_id)) {

            $this->resError('#vis009');
        }

        // kiểm tra visitor xem có tồn tại hay không?
        if (!$this->{$this->modelClass}->exists($user_id)) {

            $this->resError('#vis010', array('message_args' => $user_id));
        }

        $save_data = array(
            'id' => $user_id,
            'token' => '',
        );
        if (!$this->{$this->modelClass}->save($save_data)) {

            $this->resError('#vis011', array('message_args' => $user_id));
        }

        $res = array(
            'status' => 'success',
            'data' => '',
        );

        $this->resSuccess($res);
    }

    /**
     * authorize
     * xác thực thông thường qua username và password
     */
    protected function authorize() {

        if (!$this->request->is('post')) {

            $this->resError('#vis006');
        }

        $username_fields = Configure::read('sysconfig.App.authorize.username_fields');
        $password = $this->request->data('password');

        // dù khi đăng nhập bằng mobile, email thì trường field từ client truyền lên vẫn là username
        $username = trim($this->request->data('username'));

        // chuẩn hóa số mobile, chuyển số 0 ở đầu nếu có thành 84
        $mobile = $this->standardizeMobile($username);

        // bắt chặt nếu nhập thiếu trường username và password
        if (!strlen($username) || !strlen($password)) {

            $this->resError('#vis007');
        }

        $options = array(
            'conditions' => array(
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                'password' => Security::hash($password, null, true)
            ),
        );

        foreach ($username_fields as $v) {

            // với trường hợp là trường mobile
            if ($v == 'mobile') {

                $options['conditions']['$or'] = array(
                    array(
                        $v => array(
                            '$regex' => new MongoRegex("/^" . $mobile . "$/"),
                        ),
                    ),
                );
            } else {

                $options['conditions']['$or'] = array(
                    array(
                        $v => array(
                            '$regex' => new MongoRegex("/^" . $username . "$/"),
                        ),
                    ),
                );
            }
        }

        $visitor = $this->{$this->modelClass}->find('first', $options);
        // nếu đăng nhập thất bại
        if (empty($visitor)) {

            $this->resError('#vis005');
        }

        // nếu đăng nhập thành công, tạo token
        $token = $this->generateToken($visitor);
        $res = array(
            'status' => 'success',
            'data' => array(
                'id' => $visitor[$this->modelClass]['id'],
                'username' => $visitor[$this->modelClass]['username'],
                'name' => $visitor[$this->modelClass]['name'],
                'mobile' => $visitor[$this->modelClass]['mobile'],
                'email' => $visitor[$this->modelClass]['email'],
                'token' => $token,
            ),
        );

        $this->TrackingLogCommon->logAction('login', 'login');

        $this->resSuccess($res);
    }

    /**
     * authorizeToken
     * thực hiện xác thực thông qua token
     * 
     * @param string $token
     */
    protected function authorizeToken($token) {

        App::import('Vendor', 'JWT', array('file' => 'JWT' . DS . 'Authentication' . DS . 'JWT.php'));

        try {

            $token_config = Configure::read('App.token');
            $token_decode = JWT::decode($token, $token_config['secret']);
            $res = array(
                'status' => 'success',
                'data' => array(
                    'id' => $token_decode->visitor->id,
                    'username' => $token_decode->visitor->username,
                    'name' => $token_decode->visitor->name,
                    'mobile' => $token_decode->visitor->mobile,
                    'email' => $token_decode->visitor->email,
                    'token' => $token,
                ),
            );

            $this->TrackingLogCommon->logAction('login', 'login');

            $this->resSuccess($res);
        } catch (Exception $ex) {

            // nếu token hết hạn
            if ($ex instanceof ExpiredException) {

                $this->resError('#vis008');
            } else {

                $this->resError('#vis004', array('message_args' => $ex->getMessage()));
            }
        }
    }

    /**
     * generateToken
     * thực hiện tạo lại token cho visitor, sau đó update lại token vào visitor
     * 
     * @param array $visitor
     * @return string
     */
    protected function generateToken($visitor) {

        App::import('Vendor', 'JWT', array('file' => 'JWT' . DS . 'Authentication' . DS . 'JWT.php'));
        $token_config = Configure::read('App.token');
        $params = array(
            "iss" => $token_config['iss'],
            "aud" => $token_config['aud'],
            "iat" => time(),
            "nbf" => time(),
            "exp" => strtotime($token_config['ttl']['short']),
            'visitor' => array(
                'id' => $visitor[$this->modelClass]['id'],
                'username' => $visitor[$this->modelClass]['username'],
                'name' => $visitor[$this->modelClass]['name'],
                'mobile' => $visitor[$this->modelClass]['mobile'],
                'email' => $visitor[$this->modelClass]['email'],
            ),
        );

        // tạo token cho visitor
        $token = JWT::encode($params, $token_config['secret']);

        // khi tạo xong token thì update lại token cho visitor
        $save_data = array(
            'id' => $visitor[$this->modelClass]['id'],
            'token' => $token,
            'last_login' => new MongoDate(),
        );
        if (!$this->{$this->modelClass}->save($save_data)) {

            $this->resError('#vis003', array('message_args' => $visitor[$this->modelClass]['id']));
        }

        return $token;
    }

    /**
     * api for screen favorites 31
     */
    public function bookmarks() {
        $this->setInit();
//        $this->TrackingLogCommon->logAction('access', 'bookmark_home');
        // get limit
        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {
            $limit = Configure::read('sysconfig.Home.search.limit');
        }
        // check token
        $tokenDecode = $this->validateToken();
        // get visitor id
        $visitorId = $tokenDecode->visitor->id;
        // get all object_type
        $this->loadModel('ObjectType');
        $listObjType = $this->ObjectType->find('list', ['fields' => ['code', 'name']]);
        // get bookmarks of visitor id
        $bookmarks = $this->Visitor->find('first', [
            'fields' => ['bookmarks'],
            'conditions' => ['id' => new MongoId($visitorId)],
            'order' => ['order' => 'asc', 'bookmarks.items.modified' => 'desc']
        ]);
        // fix 5 record with each object type
        $result = ['arr_bookmarks' => []];
        if (isset($bookmarks['Visitor']['bookmarks']) && is_array($bookmarks['Visitor']['bookmarks']) && count($bookmarks['Visitor']['bookmarks'])) {
            foreach ($bookmarks['Visitor']['bookmarks'] AS $bookmark) {
                if (array_key_exists($bookmark['object_type_code'], $listObjType)) {
                    $bookmark['items'] = array_slice($bookmark['items'], 0, $limit);
                    $result['arr_bookmarks'][] = $this->standardResult($bookmark, $listObjType);
                }
            }
        }
        // return result
        $this->resSuccess(array(
            'status' => 'success',
            'data' => $result
        ));
    }

    /**
     * api for screen favorites 32
     */
    public function favorites() {
        $this->setInit();
//        $this->TrackingLogCommon->logAction('access', 'favorite_home');
        // get limit
        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {
            $limit = Configure::read('sysconfig.Home.search.limit');
        }
        // check token
        $tokenDecode = $this->validateToken();
        // get visitor id
        $visitorId = $tokenDecode->visitor->id;
        // get all object_type
        $this->loadModel('ObjectType');
        $listObjType = $this->ObjectType->find('list', ['fields' => ['code', 'name']]);
        // get favorites of visitor id
        $favorites = $this->Visitor->find('first', [
            'fields' => ['favorites'],
            'conditions' => ['id' => new MongoId($visitorId)],
            'order' => ['order' => 'asc', 'favorites.items.modified' => 'desc']
        ]);
        // fix 5 record with each object type
        $result = ['arr_favorites' => []];
        if (isset($favorites['Visitor']['favorites']) && is_array($favorites['Visitor']['favorites']) && count($favorites['Visitor']['favorites'])) {
            foreach ($favorites['Visitor']['favorites'] AS $favorite) {
                if (array_key_exists($favorite['object_type_code'], $listObjType)) {
                    $favorite['items'] = array_slice($favorite['items'], 0, $limit);
                    $result['arr_favorites'][] = $this->standardResult($favorite, $listObjType);
                }
            }
        }
        // return result
        $this->resSuccess(array(
            'status' => 'success',
            'data' => $result
        ));
    }

    /**
     * link: /visitors/bookmarkList?object_type_code=hotels&limit=2&page=1
     */
    public function bookmarkList() {
        $this->setInit();
//        $this->TrackingLogCommon->logAction('access', 'bookmark_list');
        // get limit
        $page = (int) trim($this->request->query('page')) - 1;
        $object_type_code = trim($this->request->query('object_type_code'));
        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {
            $limit = Configure::read('sysconfig.Home.search.limit');
        }
        // check token
        $tokenDecode = $this->validateToken();
        // get visitor id
        $visitorId = $tokenDecode->visitor->id;
        // get all object_type
        $this->loadModel('ObjectType');
        $listObjType = $this->ObjectType->find('list', ['fields' => ['code', 'name']]);
        // get bookmarks of visitor id
        $bookmarks = $this->Visitor->find('first', [
            'fields' => ['bookmarks'],
            'conditions' => [
                'id' => new MongoId($visitorId),
                'bookmarks.object_type_code' => ['$eq' => $object_type_code]
            ],
            'order' => ['bookmarks.items.modified' => 'desc']
        ]);
        // fix 5 record with each object type
        $result = ['arr_bookmarks' => []];
        $i = 0;
        if (isset($bookmarks['Visitor']['bookmarks']) && is_array($bookmarks['Visitor']['bookmarks']) && count($bookmarks['Visitor']['bookmarks'])) {
            foreach ($bookmarks['Visitor']['bookmarks'] AS $bookmark) {
                if (array_key_exists($bookmark['object_type_code'], $listObjType) && $object_type_code == $bookmark['object_type_code']) {
                    $bookmark['items'] = array_slice($bookmark['items'], $page * $limit, $limit);
                    $result['arr_bookmarks'][] = $this->standardResult($bookmark, $listObjType);
                    $i++;
                }
            }
        }
        // return result
        $this->resSuccess(array(
            'status' => 'success',
            'data' => $result,
            'limit' => $limit,
            'total' => $i,
            'object_type_code' => $object_type_code
        ));
    }

    /**
     * link: /visitors/favoriteList?object_type_code=hotels&limit=2&page=1
     */
    public function favoriteList() {
        $this->setInit();
//        $this->TrackingLogCommon->logAction('access', 'favorite_list');
        // get limit
        $page = (int) trim($this->request->query('page')) - 1;
        $object_type_code = trim($this->request->query('object_type_code'));
        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {
            $limit = Configure::read('sysconfig.Home.search.limit');
        }
        // check token
        $tokenDecode = $this->validateToken();
        // get visitor id
        $visitorId = $tokenDecode->visitor->id;
        // get all object_type
        $this->loadModel('ObjectType');
        $listObjType = $this->ObjectType->find('list', ['fields' => ['code', 'name']]);
        // get favorites of visitor id
        $favorites = $this->Visitor->find('first', [
            'fields' => ['favorites'],
            'conditions' => [
                'id' => new MongoId($visitorId),
                'favorites.object_type_code' => ['$eq' => $object_type_code]
            ],
            'order' => ['favorites.items.modified' => 'desc']
        ]);
        // fix 5 record with each object type
        $result = ['arr_favorites' => []];
        $i = 0;
        if (isset($favorites['Visitor']['favorites']) && is_array($favorites['Visitor']['favorites']) && count($favorites['Visitor']['favorites'])) {
            foreach ($favorites['Visitor']['favorites'] AS $favorite) {
                if (array_key_exists($favorite['object_type_code'], $listObjType) && $object_type_code == $favorite['object_type_code']) {
                    $favorite['items'] = array_slice($favorite['items'], $page * $limit, $limit);
                    $result['arr_favorites'][] = $this->standardResult($favorite, $listObjType);
                    $i++;
                }
            }
        }
        // return result
        $this->resSuccess(array(
            'status' => 'success',
            'data' => $result,
            'limit' => $limit,
            'total' => $i,
            'object_type_code' => $object_type_code
        ));
    }

    /**
     * standard Result for return to app
     * @param array $data
     * @return array
     */
    protected function standardResult($data = array(), $listObjType = array()) {
        if (array_key_exists('object_type_code', $data)) {
            $data['type'] = $data['object_type_code'];
            unset($data['object_type_code']);

            if (array_key_exists($data['type'], $listObjType)) {
                $data['name'] = $listObjType[$data['type']];
            }
        }
        if (array_key_exists('items', $data) && is_array($data['items'])) {
            foreach ($data['items'] AS $key => $item) {
                if (is_object($item['_id'])) {
                    $data['items'][$key]['id'] = $data['items'][$key]['_id']->{'$id'};
                    unset($data['items'][$key]['_id']);
                }
            }
        }

        // get info from parent table
        $model = Inflector::classify($data['type']);
        if (!$this->{$model}) {
            $this->loadModel($model);
        }
        foreach ($data['items'] AS $key => $value) {
            $objectInfo = $this->{$model}->find('first', ['conditions' => ['id' => new MongoId($value['id'])]]);
            $data['items'][$key]['price'] = $this->getPrices($objectInfo[$model], $this->currency_code);
            $data['items'][$key]['banner'] = $this->getFileUris($objectInfo[$model], 'banner');
            $data['items'][$key]['logo'] = $this->getFileUris($objectInfo[$model], 'logo');
            $data['items'][$key]['score'] = isset($objectInfo[$model]['rating']['score']) ? $objectInfo[$model]['rating']['score'] : '';
            $data['items'][$key]['rate_count'] = isset($objectInfo[$model]['rating']['count']) ? $objectInfo[$model]['rating']['count'] : '';
            $data['items'][$key]['address'] = isset($objectInfo[$model]['address']) ? $objectInfo[$model]['address'] : '';
            $data['items'][$key]['star'] = isset($objectInfo[$model]['standard_rate']) ? $objectInfo[$model]['standard_rate'] : '';
        }
        $data['arr_object'] = $data['items'];
        unset($data['items']);
        return $data;
    }

}
