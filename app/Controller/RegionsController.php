<?php

App::uses('AppController', 'Controller');

class RegionsController extends AppController {

    public $uses = array('Region', 'Tour', 'Place', 'Hotel', 'Restaurant', 'Event', 'Guide', 'Streaming', 'FileManaged', 'WeatherDescription', 'Weather', 'Activity', 'Category');
    public $components = array('FileCommon');

    /**
     *  1.  url: http://124.158.5.134:8086/regions/index
      2. Input
      - user_id, lang_code(ngôn ngữ format vi, ja, en...), lat, lng
      3. output:
      { status: success/fail, msg: thành công/thất bại, data: { arr_cate: [{id, name, arr_place: [{id, name, banner, logo, icon, address, score: điểm trung bình rate, rate_count: số người rate }] }] } }
     */
    public function home() {

        $this->set('page_title', "Địa điểm");
        //Tracking Report
        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.region_home');
//        $this->insertTrackingAccess($sreen_code);

        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $STATUS_SUCCESS_MSG = Configure::read('sysconfig.Common.STATUS_SUCCESS_MSG');
        $STATUS_SUCCESS = Configure::read('sysconfig.Common.STATUS_SUCCESS');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
        $LIST_CATEGORY_ID = Configure::read('sysconfig.ScreenRegionHome.LIST_CATEGORY_ID');

        $arr_result = [ 'status' => $STATUS_SUCCESS, 'msg' => $STATUS_SUCCESS_MSG, 'data' => ['arr_cate' => []]];

        $lang_code = $this->request->query("lang_code");
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");
        $limit = 5; //lấy 5, muốn xem thêm thì click more 

        $this->logAnyFile("START: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng), limit: $limit", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);

//            var_dump($LIST_CATEGORY_ID);
        foreach ($LIST_CATEGORY_ID as $cate) {

            $query_region = [];
            $query_region['conditions'] = [
                "status" => $STATUS_APPROVED,
                "categories" => new MongoId($cate["id"])
            ];

            $cate["arr_region"] = [];
//                $total = $this->Region->find('count', $query_region);
            $query_region['limit'] = $limit;
            $query_region['order'] = array('order' => 'ASC');
            $query_region['fields'] = array('id', 'name', 'address', 'location', 'file_uris', 'object_icon', 'rating');

            $arr_region = $this->Region->find('all', $query_region);

//            var_dump($cate["id"]);
//            var_dump($arr_region);

            $this->log($arr_region, 'RegionsController.index.arr_region');

            if (!empty($arr_region)) {
                foreach ($arr_region as $value) {

                    $value = $value['Region'];

                    $region = array(
                        'id' => $value["id"],
                        'name' => $value['name'],
                        'address' => $value['address'],
                        'icon' => "", //TODO
                        'banner' => "",
                        'logo' => "",
                    );

                    if (!empty($value["file_uris"]['banner'])) {
                        $region["banner"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['banner'])[0];
                    } else {
                        $region['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
                    }
                    if (!empty($value["file_uris"]['logo'])) {
                        $region["logo"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['logo'])[0];
                    }

                    if (!empty($value["rating"])) {
                        $region["score"] = $value["rating"]["score"];
                        $region["rate_count"] = $value["rating"]["count"];
                    }

                    $cate["arr_region"][] = $region;
                }
            }
            $arr_result['data']['arr_cate'][] = $cate;
        }

        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);
//        $json_encode_result = json_encode($arr_result);

        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng)", __CLASS__ . '_' . __FUNCTION__);
    }

    /**
     *  1.  url: http://124.158.5.134:8086/regions/index
      2. Input
      - user_id, lang_code(ngôn ngữ format vi, ja, en...), lat, lng
      3. output:
      { status: success/fail, msg: thành công/thất bại, data: { arr_cate: [{id, name, arr_place: [{id, name, banner, logo, icon, address, score: điểm trung bình rate, rate_count: số người rate }] }] } }
     */
    public function index() {

        $this->set('page_title', "Danh mục Địa điểm");
        //Tracking Report
        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.region_list');
//        $this->insertTrackingAccess($sreen_code);

        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $STATUS_SUCCESS_MSG = Configure::read('sysconfig.Common.STATUS_SUCCESS_MSG');
        $STATUS_SUCCESS = Configure::read('sysconfig.Common.STATUS_SUCCESS');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
        $REGION_OBJECT_TYPE_ID = new MongoId(Configure::read('sysconfig.ScreenRegionList.REGION_OBJECT_TYPE_ID'));

        $arr_result = [ 'status' => $STATUS_SUCCESS, 'msg' => $STATUS_SUCCESS_MSG, 'data' => ['arr_cate' => []]];

        $lang_code = $this->request->query("lang_code");
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");
        $limit = 5; //lấy 5, muốn xem thêm thì click more 

        $this->logAnyFile("START: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng)", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);


        $query_cate = [];
        $query_cate['conditions'] = [
            "status" => $STATUS_APPROVED,
            "object_type" => new MongoId($REGION_OBJECT_TYPE_ID)
        ];

        $query_cate['order'] = array('order' => 'ASC');
        $query_cate['fields'] = array('id', 'name');

        $arr_cate = $this->Category->find('all', $query_cate);

        if (!empty($arr_cate)) {
            foreach ($arr_cate as $cate) {
                $cate = $cate["Category"];

                $query_region = [];
                $query_region['conditions'] = [
                    "status" => $STATUS_APPROVED,
                    "categories" => new MongoId($cate["id"])
                ];

                $cate["arr_region"] = [];
                //                $total = $this->Region->find('count', $query_region);
                $query_region['limit'] = $limit;
                $query_region['order'] = array('order' => 'ASC');
                $query_region['fields'] = array('id', 'name', 'address', 'location', 'file_uris', 'object_icon', 'rating');

                $arr_region = $this->Region->find('all', $query_region);

                $this->log($arr_region, 'RegionsController.index.arr_region');

                if (!empty($arr_region)) {
                    foreach ($arr_region as $key => $value) {

                        $value = $value['Region'];

                        $region = array(
                            'id' => $value["id"],
                            'name' => $value['name'],
                            'address' => $value['address'],
                            'icon' => "", //TODO
                            'banner' => "",
                            'logo' => "",
                        );

                        if (!empty($value["file_uris"]['banner'])) {
                            $region["banner"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['banner'])[0];
                        } else {
                            $region['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
                        }
                        if (!empty($value["file_uris"]['logo'])) {
                            $region["logo"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['logo'])[0];
                        }

                        if (!empty($value["rating"])) {
                            $region["score"] = $value["rating"]["score"];
                            $region["rate_count"] = $value["rating"]["count"];
                        }

                        $cate["arr_region"][] = $region;
                    }
                }
                $arr_result['data']['arr_cate'][] = $cate;
            }
        }
        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);
        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng)", __CLASS__ . '_' . __FUNCTION__);
    }

    /**
     * API cho màn hình danh sách Địa điểm: hiển thị phân theo category
     * @return type
     */
    public function listregion() {
        $this->set('page_title', "Danh mục Địa điểm");

        //Tracking Report
        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.region_listbycate');
//        $this->insertTrackingAccess($sreen_code);

        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $STATUS_SUCCESS_MSG = Configure::read('sysconfig.Common.STATUS_SUCCESS_MSG');
        $STATUS_SUCCESS = Configure::read('sysconfig.Common.STATUS_SUCCESS');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');

        $arr_result = [ 'status' => $STATUS_SUCCESS, 'msg' => $STATUS_SUCCESS_MSG, 'data' => ['arr_cate' => []]];

        $lang_code = $this->request->query("lang_code");
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");
        $cate_id = $this->request->query("cate_id");

        $page = (int) $this->request->query('page');
        $limit = (int) $this->request->query('limit');

        $this->logAnyFile("START: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng), page: $page, limit: $limit , cate_id: $cate_id ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);

        if ($page < 1) {
            $page = 1;
        }

        if (!empty($cate_id)) {
            $query_cate = [];
            $query_cate['conditions'] = [
                "status" => $STATUS_APPROVED,
                "id" => new MongoId($cate_id)
            ];

            $query_cate['order'] = array('order' => 'ASC');
            $query_cate['fields'] = array('id', 'name');

            $cate = $this->Category->find('first', $query_cate);

            if (!empty($cate)) {
                $cate = $cate["Category"];
                $this->set('page_title', $cate["name"]);

                $query_region = [];
                $query_region['conditions'] = [
                    "status" => $STATUS_APPROVED,
                    "categories" => new MongoId($cate["id"])
                ];

                $cate["arr_region"] = [];
                $total = $this->Region->find('count', $query_region);
                $query_region['limit'] = $limit;
                $query_region['order'] = array('order' => 'ASC');
                $query_region['fields'] = array('id', 'name', 'address', 'location', 'file_uris', 'object_icon', 'rating');

                $arr_region = $this->Region->find('all', $query_region);

                $this->log($arr_region, 'RegionsController.index.arr_region');

                if (!empty($arr_region)) {
                    foreach ($arr_region as $value) {

                        $value = $value['Region'];

                        $region = array(
                            'id' => $value["id"],
                            'name' => $value['name'],
                            'address' => $value['address'],
                            'icon' => "", //TODO
                            'banner' => "",
                            'logo' => "",
                        );

                        if (!empty($value["file_uris"]['banner'])) {
                            $region["banner"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['banner'])[0];
                        } else {
                            $region['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
                        }
                        if (!empty($value["file_uris"]['logo'])) {
                            $region["logo"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['logo'])[0];
                        }

                        if (!empty($value["rating"])) {
                            $region["score"] = $value["rating"]["score"];
                            $region["rate_count"] = $value["rating"]["count"];
                        }

                        $cate["arr_region"][] = $region;
                    }
                }
                $arr_result['data']['arr_cate'][] = $cate;
            }
        }
        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);
        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng), cate_id: $cate_id ", __CLASS__ . '_' . __FUNCTION__);
    }

    /**
     *  1. url: http://124.158.5.134:8086/regions/info
      2. input:
      - user_id, lang_code(ngôn ngữ format vi, ja, en...), lat, lng
      - id: id địa danh
      3. output:
      { status: success/fail, msg: thành công/thất bại,
     *      data: { region: {id, name, loc, arr_slide_img, distance:{"text":"1.4 km","value":1442}, short_description,  description, video, audio,  weather: {type, temperature_max, temperature_min, icon, content, current:{temperature, weather_description_code, icon, content}}}}

     */
    public function info() {
        $this->layout = 'detail';
        $this->set('page_title', "Chi tiết Địa điểm");
        //Tracking Report
//        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.region_info');
//        $this->insertTrackingAccess($sreen_code);


        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $STATUS_SUCCESS_MSG = Configure::read('sysconfig.Common.STATUS_SUCCESS_MSG');
        $STATUS_SUCCESS = Configure::read('sysconfig.Common.STATUS_SUCCESS');
        $REGION_OBJECT_TYPE_ID = Configure::read('sysconfig.ScreenRegionInfo.REGION_OBJECT_TYPE_ID');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');

        $lang_code = $this->request->query("lang_code");
        if (empty($lang_code)) {
            $lang_code = 'vi';
        }
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");

        $id = $this->request->query("id");
        $os_name = $this->request->query("os_name");
        $os_version = $this->request->query("os_version");

        $this->logAnyFile("START: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng), os_name: $os_name, os_version: $os_version, id: $id ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);

        $arr_result = [ 'status' => $STATUS_SUCCESS, 'msg' => $STATUS_SUCCESS_MSG, 'data' => null];


        $query = [];
        $query['fields'] = array('id', 'name', 'address', 'short_description', 'description', 'email', 'website', 'tel', 'file_uris', 'loc', 'object_icon', 'location');
        $query['conditions'] = array("status" => $STATUS_APPROVED, "id" => new MongoId($id));

        $region = $this->Region->find('first', $query);
//            var_dump($region); 
        if ($region != null) {
            $region = $region["Region"];
            $this->set('page_title', $region['name']);

            $region['description'] = $this->convertAbsolutePathInContent($region['description']);
            $region['icon'] = ""; //TODO
            $region['banner'] = "";
            $region['logo'] = "";
            $region['map'] = "";
            $region['favorite'] = 0;
            $region['bookmark'] = 0;
            $region['share'] = "Tour giá thật hấp dẫn";
            if (!empty($region["file_uris"]["banner"])) {
                $region['banner'] = $URL_BASE_FILE_MANAGER . array_values($region['file_uris']['banner'])[0];
            } else {
                $region['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
            }


            if (!empty($region["file_uris"]["logo"])) {
                $region['logo'] = $URL_BASE_FILE_MANAGER . array_values($region['file_uris']['logo'])[0];
            }

            if (!empty($region["file_uris"]["map"])) {
                $region['map'] = $URL_BASE_FILE_MANAGER . array_values($region['file_uris']['map'])[0];
            }

            $region["arr_img"] = [];
            $region["arr_slide_img"] = [];
            $count = 0;
            if (!empty($region["file_uris"]["thumbnails"])) {
                foreach ($region["file_uris"]["thumbnails"] as $thumbnails) {

                    $thumbnails_url = $URL_BASE_FILE_MANAGER . $thumbnails;
                    $region["arr_img"][] = $thumbnails_url;
                    $count++;

                    if ($count <= 5) {
                        $region["arr_slide_img"][] = $thumbnails_url;
                    }
                }
            } else {
                $region["arr_slide_img"][] = $region['banner'];
            }

            //Lấy link video streaming
            $query_streaming = [];
            $query_streaming['fields'] = array('id', 'name', 'file_path', 'file_size', 'duration', 'resolution_w', 'resolution_h', 'bitrate', 'file_mime', 'file_uris');
            $query_streaming['conditions'] = [
                'object_type' => new MongoId($REGION_OBJECT_TYPE_ID),
                'object_id' => new MongoId($id),
            ];

//                $this->logAnyFile($query_streaming, __CLASS__ . '_' . __FUNCTION__)
            $arr_streaming = $this->Streaming->find('all', $query_streaming);

            $region["audio_url"] = null;
            $region["video"] = null;
            if (!empty($arr_streaming)) {
                foreach ($arr_streaming as $streaming) {
                    $streaming = $streaming["Streaming"];
                    if ($this->startsWith($streaming['file_mime'], "audio")) {

                        $region["audio_url"] = $this->getStreamingUrl($os_name, $os_version, $streaming['file_mime'], $streaming['file_path']);
                    } else {
                        $region["video"] = [
                            "url" => $this->getStreamingUrl($os_name, $os_version, $streaming['file_mime'], $streaming['file_path']),
                            "thumb" => ""
                        ];

                        if (!empty($streaming["file_uris"]["poster"])) {
                            $region["video"]["thumb"] = $URL_BASE_FILE_MANAGER . array_values($streaming["file_uris"]["poster"])[0];
                        }
                    }
                }
            }

            //FAVORITE, BOOKMARK
            if (!empty($user_id)) {
                $visitor = $this->getVisitorByUserId($user_id);

                if (!empty($visitor)) {
                    $visitor = $visitor["Visitor"];
                    if (!empty($visitor["favorites"]) && is_array($visitor["favorites"])) {
                        $favorites = Hash::extract($visitor["favorites"], '{n}[object_type_code=/regions/]');
                        if (!empty($favorites["items"]) && is_array($favorites["items"])) {
                            foreach ($favorites["items"] as $fav) {
                                if (new MongoId($id) == $fav["_id"]) {
                                    $region['favorite'] = 1;
                                }
                            }
                        }
                    }

                    if (!empty($visitor["bookmarks"]) && is_array($visitor["bookmarks"])) {
                        $bookmarks = Hash::extract($visitor["bookmarks"], '{n}[object_type_code=/regions/]');
                        if (!empty($bookmarks["items"]) && is_array($bookmarks["items"])) {
                            foreach ($bookmarks["items"] as $bookmark) {
                                if (new MongoId($id) == $bookmark["_id"]) {
                                    $region['bookmark'] = 1;
                                }
                            }
                        }
                    }
                }
            }

            //------------- TÍNH KHOẢNG CÁCH -----------------
            $region_lng = $region["loc"]["coordinates"][0];
            $region_lat = $region["loc"]["coordinates"][1];
            $region["distance"] = $this->caculateDistance($lat, $lng, $region_lat, $region_lng);

            //------------- TÍNH THỜI TIẾT -----------------
            $query_weather = [];
            $query_weather['fields'] = array('type', 'informations', 'current');
            $query_weather['conditions'] = [
                "region" => new MongoId($id)
            ];

            $weather = $this->Weather->find('first', $query_weather);

//            var_dump($weather);

            $arr_weather = NULL;
            if (!empty($weather)) {
                if (!empty($weather["Weather"]["informations"])) {
                    $informations = $weather["Weather"]["informations"][0];
                    $weather_detail = $this->getDetailWeather($informations["weather_description_code"], $lang_code);

                    $arr_weather = [
                        "type" => $weather["Weather"]["type"],
                        "temperature_max" => $informations["temperature_max"],
                        "temperature_min" => $informations["temperature_min"],
                        "icon" => $URL_BASE_FILE_MANAGER . $weather_detail["icon"],
                        "content" => $weather_detail["content"],
                        "current" => null
                    ];
                }
            }

            //Current
            if (!empty($weather["Weather"]["current"])) {
                $current = $weather["Weather"]["current"];
                {
                    $weather_detail = $this->getDetailWeather($current["weather_description_code"], $lang_code);
                    $arr_weather["current"] = $current;
                    $arr_weather["current"]["icon"] = $URL_BASE_FILE_MANAGER . $weather_detail["icon"];
                    $arr_weather["current"]["content"] = $weather_detail["content"];
                }
            }

            $region["weather"] = $arr_weather;

            unset($region["file_uris"]);
            unset($region["location"]);

            $query_guide = [];
            $query_guide['limit'] = 5;
            $query_guide['fields'] = ['id', 'name', 'short_description', 'file_uris', 'created'];
            $query_guide['conditions'] = [
                'status' => $STATUS_APPROVED,
                "location.region" => new MongoId($id)
            ];
            $arr_guide = $this->Guide->find('all', $query_guide);
            $arr_guide_result = [];
            if (!empty($arr_guide)) {
                foreach ($arr_guide as $value) {
                    $value = $value['Guide'];
                    $guide = [
                        'id' => $value['id'],
                        'name' => $value['name'],
                        'short_description' => $value['short_description'],
                        'logo' => '',
                        'banner' => '',
                    ];

                    if (!empty($value["file_uris"]["logo"])) {
                        $guide["logo"] = $URL_BASE_FILE_MANAGER . array_values($value["file_uris"]["logo"])[0];
                    }
                    $arr_guide_result[] = $guide;
                }
            }

            $query_tour_count = [];
            $query_tour_count['conditions'] = [
                'status' => $STATUS_APPROVED,
                "location.region" => new MongoId($id)
            ];

            $tour_count = $this->Tour->find('count', $query_tour_count);

            $query_place_count = [];
            $query_place_count['conditions'] = [
                'status' => $STATUS_APPROVED,
                "location.region" => new MongoId($id)
            ];

            $place_count = $this->Place->find('count', $query_place_count);

            $query_hotel_count = [];
            $query_hotel_count['conditions'] = [
                'status' => $STATUS_APPROVED,
                "location.region" => new MongoId($id)
            ];

            $hotel_count = $this->Hotel->find('count', $query_hotel_count);

            $query_restaurant_count = [];
            $query_restaurant_count['conditions'] = [
                'status' => $STATUS_APPROVED,
                "location.region" => new MongoId($id)
            ];

            $restaurant_count = $this->Restaurant->find('count', $query_restaurant_count);

            $query_event_count = [];
            $query_event_count['conditions'] = [
                'status' => $STATUS_APPROVED,
                "location.region" => new MongoId($id)
            ];

            $event_count = $this->Event->find('count', $query_event_count);

            $arr_result['data'] = ['region' => $region,
                'arr_guide' => $arr_guide_result,
                'arr_cate' => [
                    [
                        'type' => "tours",
                        'count' => $tour_count,
                        'name' => "Tour",
                    ],
                    [
                        'type' => "places",
                        'count' => $place_count,
                        'name' => "Địa điểm",
                    ],
                    [
                        'type' => "hotels",
                        'count' => $hotel_count,
                        'name' => "Khách sạn",
                    ],
                    [
                        'type' => "restaurants",
                        'count' => $restaurant_count,
                        'name' => "Nhà hàng",
                    ],
                    [
                        'type' => "events",
                        'count' => $event_count,
                        'name' => "Hoạt động - Sự kiện",
                    ],
                ]
            ];
        }

        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);

        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng), os_name: $os_name, os_version: $os_version, id: $id ", __CLASS__ . '_' . __FUNCTION__);
    }

    public function choose() {

        $this->layout = 'choose_region';
        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $options = array(
            'conditions' => array(
                'status' => $STATUS_APPROVED,
            ),
            'order' => array(
                'name' => 'ASC',
            ),
        );
        $regions = $this->Region->find('all', $options);
        $this->set('regions', $regions);
        $this->set('referer', $this->referer(array('controller' => 'Home', 'action' => 'index')));
        $this->set('page_title', 'Tỉnh/thành phố');
    }

}
