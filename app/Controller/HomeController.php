<?php

App::uses('AppController', 'Controller');

class HomeController extends AppController {

    public $uses = array(
        'Category',
        'ObjectType',
        'Setting',
    );
//    public $components = array(
//        'TrackingLogCommon',
//    );
    public $debug_mode = 3;

    public function index() {

        $this->setInit();
        $this->lang_code = 'vi';

        // thực hiện tracking
//        $this->TrackingLogCommon->logAction('access', 'home');
        // lấy ra setting
        $setting = $this->Setting->find('first', array(
            'conditions' => array(
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                'lang_code' => $this->lang_code,
            ),
        ));
        if (empty($setting['Setting']['configuration']['Home_index']['categories'])) {

            $this->resError('#hom001');
        }

        // lấy ra danh sách các categories từ cấu hình
        $config_categories = $setting['Setting']['configuration']['Home_index']['categories'];
        usort($config_categories, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        // thực hiện xác định $user_region_id
        $user_region_id = trim($this->request->query('user_region_id'));

        $res = array(
            'status' => 'success',
            'data' => array(
                'arr_cate' => array(),
            ),
        );
        $index = 0;
        foreach ($config_categories as $cate) {

            $cate_id = $cate['id'];
            $cate_max_item = $cate['max_item'];

            // xác định xem cate có lọc theo user_region_id hay không?
            $cate_user_region_filter = $cate['user_region_filter'];

            // lấy thông tin về cate
            $cate_info = $this->Category->find('first', array(
                'conditions' => array(
                    'id' => new MongoId($cate_id)
                ),
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
            ));
            if (empty($cate_info)) {

                continue;
            }

            $cate_code = $cate['code'];
            $cate = $res['data']['arr_cate'][$index] = array(
                'id' => $cate_id,
                'name' => $cate_info['Category']['name'],
                'type' => $cate_code,
            );

            // hardcode trường hiện thị release_date cho events
            $release_date_field = 'release_date';
            if ($cate_code == 'events') {

                $release_date_field = 'modified';
            }

            $object_model = Inflector::classify($cate_code);
            if (!isset($this->$object_model)) {

                $this->loadModel($object_model);
            }

            $opts_object = array(
                'limit' => $cate_max_item,
                'order' => array(
                    'order' => 'ASC',
                ),
                'conditions' => array(
                    'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                    'categories' => new MongoId($cate_id),
                ),
            );

            // nếu cần lọc theo $user_region_id
            if (!empty($user_region_id) && !empty($cate_user_region_filter)) {

                $opts_object['conditions']['location.region'] = new MongoId($user_region_id);
            }

            // lấy ra danh sách object content liên quan tới cate
            $arr_object = $this->$object_model->find('all', $opts_object);
            if (empty($arr_object)) {

                $res['data']['arr_cate'][$index]['arr_object'] = array();
                continue;
            }
            foreach ($arr_object as $k => $obj) {

                $rating = $this->getRating($obj[$object_model]);
                $res['data']['arr_cate'][$index]['arr_object'][$k] = array(
                    'id' => $obj[$object_model]['id'],
                    'name' => $obj[$object_model]['name'],
                    'price' => $this->getPrices($obj[$object_model], $this->currency_code),
                    'banner' => $this->getFileUris($obj[$object_model], 'banner'),
                    'logo' => $this->getFileUris($obj[$object_model], 'logo'),
                    'score' => $rating['score'],
                    'rate_count' => $rating['count'],
                    'address' => !empty($obj[$object_model]['address']) ? $obj[$object_model]['address'] : '',
                    'star' => !empty($obj[$object_model]['standard_rate']) ? $obj[$object_model]['standard_rate'] : '',
                    'release_date' => $this->getDate($obj[$object_model], $release_date_field),
                );
            }

            $index++;
        }

        $this->set('res', $res);
    }

    public function search() {

        $this->setInit();
        $this->set('page_title', 'Kết quả tìm kiếm');

//        $this->logTrackingSearch();

        $raw_keyword = trim($this->request->query('keyword'));
        // thực hiện biến keyword thành tiếng việt không dấu
        $keyword = $this->convert_vi_to_en($raw_keyword);
        $this->set('keyword', $keyword);

        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {

            $limit = Configure::read('sysconfig.Home.search.limit');
        }
        $this->set('limit', $limit);

        $page = (int) trim($this->request->query('page'));
        if ($page <= 0) {

            $page = 1;
        }
        $this->set('page', $page);

        $region_id = trim($this->request->query('region_id'));
        $this->set('region_id', $region_id);

        $type = $this->proccessType(Configure::read('sysconfig.Home.search.types'));

        // đối với trường hợp search cho region - thực hiện tìm kiếm dành riêng
        if (count($type) == 1 && $type[0] == 'regions') {

            $region_object_id = trim($this->request->query('object_id'));
            $region_id = $region_object_id;
            $this->set('region_id', $region_id);
            $this->searchInRegion($region_object_id);
            return;
        }

        if (count($type) == 1) {

            $type_alias = Configure::read('sysconfig.Home.type_alias');
            $alias = !empty($type_alias[$type[0]]) ? $type_alias[$type[0]] : '';
            $this->set('page_title', 'Kết quả tìm kiếm ' . strtolower($alias));
        }
        $this->set('type', $type);

        $res = array(
            'status' => 'success',
            'data' => array(
                'arr_type' => array(),
            ),
        );
        $index = 0;
        foreach ($type as $v) {

            $object_model = Inflector::classify($v);
            if (!isset($this->$object_model)) {

                $this->loadModel($object_model);
            }

            $object_type = $this->ObjectType->getInfoByCode($v);
            if (empty($object_type)) {

                continue;
            }
            // hardcode trường hiện thị release_date cho events
            $release_date_field = 'release_date';
            if ($v == 'events') {

                $release_date_field = 'modified';
            }
            $res['data']['arr_type'][$index] = array(
                'id' => $object_type['id'],
                'name' => $object_type['name'],
                'type' => $v,
                'limit' => $limit,
                'page' => $page,
            );

            $options = array(
                'limit' => $limit,
                'page' => $page,
                'order' => array(
                    'order' => 'ASC',
                ),
                'conditions' => array(
                    'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                    '$or' => array(
                        array(
                            'tags' => array(
                                '$regex' => new MongoRegex("/" . mb_strtolower($keyword) . "/i"),
                            ),
                        ),
                        array(
                            'tags_ascii' => array(
                                '$regex' => new MongoRegex("/" . mb_strtolower($keyword) . "/i"),
                            ),
                        ),
                    ),
                ),
            );

            if (!empty($region_id)) {

                $options['conditions']['location.region'] = new MongoId($region_id);
            }

            // đối với $v = places, thì thực hiện hardcode để lấy về regions
            if ($v == 'places' || $v == 'regions') {

                $this->searchByPlace($options, $res, $index, $page, $limit);
            } else {

                $arr_object = $this->$object_model->find('all', $options);
                $res['data']['arr_type'][$index]['total'] = $this->$object_model->getTotal($options);

                if (empty($arr_object)) {

                    $res['data']['arr_type'][$index]['arr_object'] = array();
                    $index++;
                    continue;
                }

                foreach ($arr_object as $kk => $obj) {

                    $rating = $this->getRating($obj[$object_model]);
                    $res['data']['arr_type'][$index]['arr_object'][$kk] = array(
                        'id' => $obj[$object_model]['id'],
                        'name' => $obj[$object_model]['name'],
                        'price' => $this->getPrices($obj[$object_model], $this->currency_code),
                        'banner' => $this->getFileUris($obj[$object_model], 'banner'),
                        'logo' => $this->getFileUris($obj[$object_model], 'logo'),
                        'score' => $rating['score'],
                        'rate_count' => $rating['count'],
                        'type' => $v,
                        'release_date' => $this->getDate($obj[$object_model], $release_date_field),
                        'address' => !empty($obj[$object_model]['address']) ? $obj[$object_model]['address'] : '',
                        'star' => !empty($obj[$object_model]['standard_rate']) ? $obj[$object_model]['standard_rate'] : '',
                    );
                }
            }

            $index++;
        }

        $this->set('res', $res);
        if (count($type) == 1) {

            $this->render('search_by_type');
        }
    }

    protected function logTrackingSearch() {

        $type = trim($this->request->query('type'));
        if (empty($type)) {

//            return $this->TrackingLogCommon->logAction('access', 'search');
        }

        $types = Configure::read('sysconfig.Home.search.types');
        if (in_array($type, $types)) {

            $prefix = Inflector::singularize($type);
//            return $this->TrackingLogCommon->logAction('access', $prefix . '_search');
        }
    }

    protected function getDate($data, $type) {

        if (isset($data[$type])) {

            return $data[$type];
        }

        return '';
    }

    /**
     * searchInRegion
     * thực hiện tìm kiếm bên trong region
     * 
     * @param int $region_object_id
     */
    protected function searchInRegion($region_object_id) {

        if (empty($region_object_id)) {

            $this->resError('#hom006');
        }
        // lấy ra types dùng để tìm kiếm trong region
        $types = Configure::read('sysconfig.Home.search.search_in_region.types');

        $raw_keyword = trim($this->request->query('keyword'));
        // thực hiện biến keyword thành tiếng việt không dấu
        $keyword = $this->convert_vi_to_en($raw_keyword);

        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {

            $limit = Configure::read('sysconfig.Home.search.limit');
        }
        $page = (int) trim($this->request->query('page'));
        if ($page <= 0) {

            $page = 1;
        }
        $res = array(
            'status' => 'success',
            'data' => array(
                'arr_type' => array(),
            ),
        );
        // trả về thêm region_id cho client
        $res['data']['region_id'] = $region_object_id;

        $index = 0;
        foreach ($types as $v) {

            $object_model = Inflector::classify($v);
            if (!isset($this->$object_model)) {

                $this->loadModel($object_model);
            }

            $object_type = $this->ObjectType->getInfoByCode($v);
            if (empty($object_type)) {

                continue;
            }
            // hardcode trường hiện thị release_date cho events
            $release_date_field = 'release_date';
            if ($v == 'events') {

                $release_date_field = 'modified';
            }
            $res['data']['arr_type'][$index] = array(
                'id' => $object_type['id'],
                'name' => $object_type['name'],
                'type' => $v,
                'limit' => $limit,
                'page' => $page,
            );

            $options = array(
                'limit' => $limit,
                'page' => $page,
                'order' => array(
                    'order' => 'ASC',
                ),
                'conditions' => array(
                    'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                    '$or' => array(
                        array(
                            'tags' => array(
                                '$regex' => new MongoRegex("/" . mb_strtolower($keyword) . "/i"),
                            ),
                        ),
                        array(
                            'tags_ascii' => array(
                                '$regex' => new MongoRegex("/" . mb_strtolower($keyword) . "/i"),
                            ),
                        ),
                    ),
                    'location.region' => new MongoId($region_object_id), // gắn tìm kiếm theo region
                ),
            );

            $arr_object = $this->$object_model->find('all', $options);
            $res['data']['arr_type'][$index]['total'] = $this->$object_model->getTotal($options);

            if (empty($arr_object)) {

                $res['data']['arr_type'][$index]['arr_object'] = array();
                $index++;
                continue;
            }

            foreach ($arr_object as $kk => $obj) {

                $rating = $this->getRating($obj[$object_model]);
                $res['data']['arr_type'][$index]['arr_object'][$kk] = array(
                    'id' => $obj[$object_model]['id'],
                    'name' => $obj[$object_model]['name'],
                    'price' => $this->getPrices($obj[$object_model], $this->currency_code),
                    'banner' => $this->getFileUris($obj[$object_model], 'banner'),
                    'logo' => $this->getFileUris($obj[$object_model], 'logo'),
                    'score' => $rating['score'],
                    'rate_count' => $rating['count'],
                    'type' => $v,
                    'release_date' => $this->getDate($obj[$object_model], $release_date_field),
                    'address' => !empty($obj[$object_model]['address']) ? $obj[$object_model]['address'] : '',
                    'star' => !empty($obj[$object_model]['standard_rate']) ? $obj[$object_model]['standard_rate'] : '',
                );
            }

            $index++;
        }

        $this->set('region_type', 'regions');
        $this->set('region_object_id', $region_object_id);
        $this->set('res', $res);
    }

    /**
     * searchByPlace
     * tìm kiếm theo gộp place (tức là gộp cả places và regions)
     * 
     * @param array $options
     * @param reference array &$res
     * @param int $index
     * @param int $page
     * @param int $limit
     * @param array $extra
     * 
     * @return mixed
     */
    protected function searchByPlace($options, &$res, $index, $page, $limit, $extra = array()) {

        if (!isset($this->Region)) {

            $this->loadModel('Region');
        }
        $regions = $this->Region->find('all', $options);
        $region_count = count($regions);
        $place_insert = $limit - $region_count; // số place sẽ được điền thêm vào nếu region không có đủ
        // đếm tổng số region
        $region_total = $this->Region->getTotal($options);
        // tính ra số page mà region sẽ chiếm
        $region_max_page = ceil($region_total / $limit);

        // tính ra số place page bắt đầu
        if ($region_total % $limit == 0) {

            $place_begin_page = $region_max_page + 1;
        } else {

            $place_begin_page = $region_max_page;
        }

        // tính ra số trang place thực tế sẽ truy vấn
        // đếm tổng số place
        if (!isset($this->Place)) {

            $this->loadModel('Place');
        }
        $place_total = $this->Place->getTotal($options);

        $res['data']['arr_type'][$index]['total'] = $region_total + $place_total;

        // nếu số region lấy về không đủ limit, thì thực hiện lấy thêm places để lấp đầy
        if ($place_insert > 0 && ($page - $place_begin_page) >= 0) {

            $options['limit'] = $place_insert;
            $options['page'] = ($page - $place_begin_page) + 1;

            $places = $this->Place->find('all', $options);
        }

        // định dạng lại dữ liệu trả về
        if (empty($regions) && empty($places)) {

            $res['data']['arr_type'][$index]['arr_object'] = array();
            return;
        }

        $object_index = 0;
        if (!empty($regions)) {

            foreach ($regions as $r) {

                $rating = $this->getRating($r['Region']);
                if (!empty($extra['caculate_distance'])) {

                    $point = $extra['caculate_distance']['point'];
                    $loc = $r['Region']['loc'];
                    $caculate_distance = $this->caculateDistance($point['coordinates'][1], $point['coordinates'][0], $loc['coordinates'][1], $loc['coordinates'][0]);
                }
                $res['data']['arr_type'][$index]['arr_object'][$object_index] = array(
                    'id' => $r['Region']['id'],
                    'name' => $r['Region']['name'],
                    'price' => $this->getPrices($r['Region'], $this->currency_code)['amount'],
                    'banner' => $this->getFileUris($r['Region'], 'banner'),
                    'logo' => $this->getFileUris($r['Region'], 'logo'),
                    'score' => $rating['score'],
                    'rate_count' => $rating['count'],
                    'type' => 'regions',
                    'release_date' => '',
                    'address' => !empty($r['Region']['address']) ? $r['Region']['address'] : '',
                    'star' => '',
                );
                if (!empty($extra['caculate_distance'])) {

                    $res['data']['arr_type'][$index]['arr_object'][$object_index]['distance'] = $caculate_distance;
                }
                $object_index++;
            }
        }

        if (!empty($places)) {

            foreach ($places as $p) {

                $rating = $this->getRating($p['Place']);
                if (!empty($extra['caculate_distance'])) {

                    $point = $extra['caculate_distance']['point'];
                    $loc = $p['Place']['loc'];
                    $caculate_distance = $this->caculateDistance($point['coordinates'][1], $point['coordinates'][0], $loc['coordinates'][1], $loc['coordinates'][0]);
                }
                $res['data']['arr_type'][$index]['arr_object'][$object_index] = array(
                    'id' => $p['Place']['id'],
                    'name' => $p['Place']['name'],
                    'price' => $this->getPrices($p['Place'], $this->currency_code)['amount'],
                    'banner' => $this->getFileUris($p['Place'], 'banner'),
                    'logo' => $this->getFileUris($p['Place'], 'logo'),
                    'score' => $rating['score'],
                    'rate_count' => $rating['count'],
                    'type' => 'places',
                    'release_date' => '',
                    'address' => !empty($p['Place']['address']) ? $p['Place']['address'] : '',
                    'star' => '',
                );
                if (!empty($extra['caculate_distance'])) {

                    $res['data']['arr_type'][$index]['arr_object'][$object_index]['distance'] = $caculate_distance;
                }
                $object_index++;
            }
        }
    }

    public function nearby() {

        $this->setInit();
        $this->set('page_title', 'Xung quanh bạn');
        $this->set('lat', $this->lat);
        $this->set('lng', $this->lng);

//        $this->logTrackingNearby();

        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {

            $limit = Configure::read('sysconfig.Home.nearby.limit');
        }
        $this->set('limit', $limit);

        $page = (int) trim($this->request->query('page'));
        if ($page <= 0) {

            $page = 1;
        }
        $this->set('page', $page);

        $distance = (int) trim($this->request->query('distance'));
        if ($distance <= 0) {

            $distance = Configure::read('sysconfig.Home.nearby.DISTANCE');
        }
        if ($distance > 30000) {

            $distance = 30000;
        }
        $this->set('distance', $distance);

        $raw_type = $this->proccessType(Configure::read('sysconfig.Home.nearby.types'));

        // chỉ khi kiểu type từ 2 trở lên mới thực hiện sắp xếp
        if (count($raw_type) > 1) {

            $type = array();

            // xử lý thứ tự của types giống như thiết lập mặc định
            $order_types = Configure::read('sysconfig.Home.nearby.types');
            $order_index = 0;
            foreach ($order_types as $v) {

                if (in_array($v, $raw_type)) {

                    $type[$order_index] = $v;
                    $order_index++;
                }
            }
        } else {

            $type = $raw_type;
        }
        $this->set('type', $type);

        if (is_string($this->request->query('type')) && count($type) == 1) {

            $type_alias = Configure::read('sysconfig.Home.type_alias');
            $alias = !empty($type_alias[$type[0]]) ? $type_alias[$type[0]] : '';
            $this->set('page_title', $alias . ' xung quanh bạn');
        }

        // tọa độ điểm cần nearby
        $point = array(
            'type' => Configure::read('sysconfig.App.GeoJSON_type'),
            'coordinates' => array(
                $this->lng, $this->lat
            ),
        );

        $res = array(
            'status' => 'success',
            'data' => array(
                'arr_type' => array(),
            ),
        );
        $index = 0;
        foreach ($type as $v) {

            $object_model = Inflector::classify($v);
            if (!isset($this->$object_model)) {

                $this->loadModel($object_model);
            }

            $object_type = $this->ObjectType->getInfoByCode($v);
            if (empty($object_type)) {

                continue;
            }

            // hardcode trường hiện thị release_date cho events
            $release_date_field = 'release_date';
            if ($v == 'events') {

                $release_date_field = 'modified';
            }

            $res['data']['arr_type'][$index] = array(
                'id' => $object_type['id'],
                'name' => $object_type['name'],
                'type' => $v,
                'limit' => $limit,
                'page' => $page,
            );

            $options = array(
                'limit' => $limit,
                'page' => $page,
                'conditions' => array(
                    'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                    'loc' => array(
                        '$near' => array(
                            '$geometry' => $point,
                            '$maxDistance' => $distance,
                        ),
                    ),
                ),
            );

            // đối với $v = places, thì thực hiện hardcode để lấy về regions
            if ($v == 'places' || $v == 'regions') {

                $extra = array(
                    'caculate_distance' => array(
                        'point' => $point,
                    ),
                );
                $this->searchByPlace($options, $res, $index, $page, $limit, $extra);
            } else {

                $arr_object = $this->$object_model->find('all', $options);
                $res['data']['arr_type'][$index]['total'] = $this->$object_model->getTotal($options);
                if (empty($arr_object)) {

                    $res['data']['arr_type'][$index]['arr_object'] = array();
                    $index++;
                    continue;
                }

                foreach ($arr_object as $kk => $obj) {

                    $rating = $this->getRating($obj[$object_model]);

                    // tính khoảng cách
                    $loc = $obj[$object_model]['loc'];
                    $caculate_distance = $this->caculateDistance($point['coordinates'][1], $point['coordinates'][0], $loc['coordinates'][1], $loc['coordinates'][0]);

                    $res['data']['arr_type'][$index]['arr_object'][$kk] = array(
                        'id' => $obj[$object_model]['id'],
                        'name' => $obj[$object_model]['name'],
                        'price' => $this->getPrices($obj[$object_model], $this->currency_code),
                        'banner' => $this->getFileUris($obj[$object_model], 'banner'),
                        'logo' => $this->getFileUris($obj[$object_model], 'logo'),
                        'score' => $rating['score'],
                        'rate_count' => $rating['count'],
                        'release_date' => $this->getDate($obj[$object_model], $release_date_field),
                        'address' => !empty($obj[$object_model]['address']) ? $obj[$object_model]['address'] : '',
                        'star' => !empty($obj[$object_model]['standard_rate']) ? $obj[$object_model]['standard_rate'] : '',
                        'distance' => $caculate_distance, // đơn vị Km
                        'type' => $v,
                    );
                }
            }

            $index++;
        }

        $this->set('res', $res);

        if (is_string($this->request->query('type')) && count($type) == 1) {

            $this->render('nearby_by_type');
        }
    }

    protected function logTrackingNearby() {

        $type = trim($this->request->query('type'));
        if (empty($type)) {

//            return $this->TrackingLogCommon->log('access', 'nearby');
        }
        $types = Configure::read('sysconfig.Home.search.types');
        if (in_array($type, $types)) {

            $prefix = Inflector::singularize($type);
//            return $this->TrackingLogCommon->logAction('access', $prefix . '_nearby');
        } else {

//            return $this->TrackingLogCommon->log('access', 'nearby');
        }
    }

    public function nearPoint() {

        $this->setInit();
        $this->set('lat', $this->lat);
        $this->set('lng', $this->lng);

        $input_type = trim($this->request->query('type'));
        $this->set('type', array($input_type));

        $type_alias = Configure::read('sysconfig.Home.type_alias');
        $alias = !empty($type_alias[$input_type]) ? $type_alias[$input_type] : '';
        $this->set('page_title', $alias . ' xung quanh');

//        $this->logTrackingNearPoint();

        $limit = (int) trim($this->request->query('limit'));
        if ($limit <= 0) {

            $limit = Configure::read('sysconfig.Home.nearby.limit');
        }
        $this->set('limit', $limit);

        $page = (int) trim($this->request->query('page'));
        if ($page <= 0) {

            $page = 1;
        }
        $this->set('page', $page);

        $distance = (int) trim($this->request->query('distance'));
        if ($distance <= 0) {

            $distance = Configure::read('sysconfig.Home.nearby.DISTANCE');
        }
        if ($distance > 30000) {

            $distance = 30000;
        }
        $this->set('distance', $distance);

        // truyền lên object_id
        $object_id = trim($this->request->query('object_id'));
        if (empty($object_id)) {

            $this->resError('#hom003');
        }
        $this->set('object_id', $object_id);

        $model_class = Inflector::classify($input_type);
        if (!isset($this->$model_class)) {

            $this->loadModel($model_class);
        }
        // xác định xem object có tồn tại và public hay không?
        $model_data = $this->$model_class->find('first', array(
            'conditions' => array(
                'id' => new MongoId($object_id),
            ),
        ));
        if (empty($model_data)) {

            $this->resError('#hom004', array('message_args' => array($model_class, $object_id)));
        }
        if ($model_data[$model_class]['status'] != Configure::read('sysconfig.App.constants.STATUS_APPROVED')) {

            $this->resError('#hom005', array('message_args' => array($model_class, $object_id)));
        }
        // lấy ra thông tin lat long
        if (empty($model_data[$model_class]['loc']) || !is_array($model_data[$model_class]['loc'])) {

            $res = array(
                'status' => 'success',
                'data' => null,
            );
        }

        $type = $this->proccessType(Configure::read('sysconfig.Home.nearby.types'));

        // tọa độ điểm cần nearby
        $point = $model_data[$model_class]['loc'];

        $res = array(
            'status' => 'success',
            'data' => array(
                'arr_type' => array(),
            ),
        );
        $index = 0;
        foreach ($type as $v) {

            $object_model = Inflector::classify($v);
            if (!isset($this->$object_model)) {

                $this->loadModel($object_model);
            }

            $object_type = $this->ObjectType->getInfoByCode($v);
            if (empty($object_type)) {

                continue;
            }

            // hardcode trường hiện thị release_date cho events
            $release_date_field = 'release_date';
            if ($v == 'events') {

                $release_date_field = 'modified';
            }

            $res['data']['arr_type'][$index] = array(
                'id' => $object_type['id'],
                'name' => $object_type['name'],
                'type' => $v,
                'limit' => $limit,
                'page' => $page,
            );

            $options = array(
                'limit' => $limit,
                'page' => $page,
                'conditions' => array(
                    'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
                    'loc' => array(
                        '$near' => array(
                            '$geometry' => $point,
                            '$maxDistance' => $distance,
                        ),
                    ),
                    'id' => array(
                        '$ne' => new MongoId($object_id),
                    ),
                ),
            );

            // đối với $v = places, thì thực hiện hardcode để lấy về regions
            if ($v == 'places' || $v == 'regions') {

                $extra = array(
                    'caculate_distance' => array(
                        'point' => $point,
                    ),
                );
                $this->searchByPlace($options, $res, $index, $page, $limit, $extra);
            } else {

                $arr_object = $this->$object_model->find('all', $options);
                $res['data']['arr_type'][$index]['total'] = $this->$object_model->getTotal($options);
                if (empty($arr_object)) {

                    $res['data']['arr_type'][$index]['arr_object'] = array();
                    $index++;
                    continue;
                }

                foreach ($arr_object as $kk => $obj) {

                    $rating = $this->getRating($obj[$object_model]);

                    // tính khoảng cách
                    $loc = $obj[$object_model]['loc'];
                    $caculate_distance = $this->caculateDistance($point['coordinates'][1], $point['coordinates'][0], $loc['coordinates'][1], $loc['coordinates'][0]);

                    $res['data']['arr_type'][$index]['arr_object'][$kk] = array(
                        'id' => $obj[$object_model]['id'],
                        'name' => $obj[$object_model]['name'],
                        'price' => $this->getPrices($obj[$object_model], $this->currency_code),
                        'banner' => $this->getFileUris($obj[$object_model], 'banner'),
                        'logo' => $this->getFileUris($obj[$object_model], 'logo'),
                        'score' => $rating['score'],
                        'rate_count' => $rating['count'],
                        'release_date' => $this->getDate($obj[$object_model], $release_date_field),
                        'address' => !empty($obj[$object_model]['address']) ? $obj[$object_model]['address'] : '',
                        'star' => !empty($obj[$object_model]['standard_rate']) ? $obj[$object_model]['standard_rate'] : '',
                        'distance' => $caculate_distance,
                        'type' => $v,
                    );
                }
            }

            $index++;
        }

        $this->set('res', $res);
    }

    protected function logTrackingNearPoint() {

        $type = trim($this->request->query('type'));
        if (empty($type)) {

            return false;
        }
        $types = Configure::read('sysconfig.Home.search.types');
        if (in_array($type, $types)) {

            $prefix = Inflector::singularize($type);
//            return $this->TrackingLogCommon->logAction('access', $prefix . '_nearpoint');
        }

        return false;
    }

    /**
     * proccessType
     * xử lý $this->request->query('type') nhận từ client
     * @param array $default_type - giá trị types mặc định
     * 
     * @return array
     */
    protected function proccessType($default_type) {

        if (is_array($this->request->query('type'))) {

            return $this->request->query('type');
        }

        // xử lý kiểu type 
        $input_type = trim($this->request->query('type'));
        if (!strlen($input_type)) {

            $type = $default_type;
        } else {

            $type = explode(',', $input_type);
        }
        if (!is_array($type)) {

            $type = array($type);
        }
        foreach ($type as $k => $v) {

            $type[$k] = trim($v);
        }

        return $type;
    }

}
