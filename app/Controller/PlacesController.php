<?php

App::uses('AppController', 'Controller');

class PlacesController extends AppController {

    public $uses = array('FileManaged', 'Place', 'Country', 'Region', 'Location', 'Category', 'Streaming', 'Weather', 'WeatherDescription');
    public $components = array('FileCommon', 'TrackingLogCommon');

    /**
     *  1.  url: http://124.158.5.134:8086/places/index
      2. Input
      - user_id, lang_code(ngôn ngữ format vi, ja, en...), lat, lng
      3. output:
      { status: success/fail, msg: thành công/thất bại, data: { arr_cate: [{id, name, arr_place: [{id, name, banner, logo, icon, address, score: điểm trung bình rate, rate_count: số người rate }] }] } }
     */
    public function home() {
        $this->set('page_title', "ĐỊA ĐIỂM");

        //Tracking Report
        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.place_home');
//        $this->insertTrackingAccess($sreen_code);

        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $STATUS_SUCCESS_MSG = Configure::read('sysconfig.Common.STATUS_SUCCESS_MSG');
        $STATUS_SUCCESS = Configure::read('sysconfig.Common.STATUS_SUCCESS');
        $STATUS_FAIL = Configure::read('sysconfig.Common.STATUS_FAIL');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
        $LIST_CATEGORY_ID = Configure::read('sysconfig.ScreenPlaceHome.LIST_CATEGORY_ID');

        $arr_result = [ 'status' => $STATUS_SUCCESS, 'msg' => $STATUS_SUCCESS_MSG, 'data' => ['arr_cate' => []]];

        $lang_code = $this->request->query("lang_code");
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");
        $limit = 5; //lấy 5, muốn xem thêm thì click more
        $offset = 0;

        $this->logAnyFile("START: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng)", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);

        foreach ($LIST_CATEGORY_ID as $cate) {

            $query_place = [];
            $query_place['conditions'] = [
                "status" => $STATUS_APPROVED,
                "categories" => new MongoId($cate["id"])
            ];

            $cate["arr_place"] = [];
//                $total = $this->Place->find('count', $query_place);
            $query_place['limit'] = $limit;
            $query_place['offset'] = $offset;
            $query_place['order'] = array('order' => 'ASC');
            $query_place['fields'] = array('id', 'name', 'address', 'location', 'file_uris', 'object_icon', 'rating');

            $arr_place = $this->Place->find('all', $query_place);

            $this->log($arr_place, 'PlacesController.index.arr_place');

            if (!empty($arr_place)) {
                foreach ($arr_place as $key => $value) {

                    $value = $value['Place'];

                    $place = array(
                        'id' => $value["id"],
                        'name' => $value['name'],
                        'address' => $value['address'],
                        'icon' => "", //TODO
                        'banner' => "",
                        'logo' => "",
                        'score' => 0,
                        'rate_count' => 0,
                    );

                    if (!empty($value["file_uris"]['banner'])) {
                        $place["banner"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['banner'])[0];
                    } else {
                        $place['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
                    }

                    if (!empty($value["file_uris"]['logo'])) {
                        $place["logo"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['logo'])[0];
                    }

                    if (!empty($value["rating"])) {
                        $place["score"] = $value["rating"]["score"];
                        $place["rate_count"] = $value["rating"]["count"];
                    }

                    $cate["arr_place"][] = $place;
                }
            }
            $arr_result['data']['arr_cate'][] = $cate;
        }

        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);

        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng) ", __CLASS__ . '_' . __FUNCTION__);
    }

    /**
     *  1.  url: http://124.158.5.134:8086/places/index
      2. Input
      - user_id, lang_code(ngôn ngữ format vi, ja, en...), lat, lng
      3. output:
      { status: success/fail, msg: thành công/thất bại, data: { arr_cate: [{id, name, arr_place: [{id, name, banner, logo, icon, address, score: điểm trung bình rate, rate_count: số người rate }] }] } }
     */
    public function index() {

        $this->set('page_title', "Danh mục địa điểm");

        //Tracking Report
        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.place_list');
//        $this->insertTrackingAccess($sreen_code);

        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $STATUS_SUCCESS_MSG = Configure::read('sysconfig.Common.STATUS_SUCCESS_MSG');
        $STATUS_SUCCESS = Configure::read('sysconfig.Common.STATUS_SUCCESS');
        $STATUS_FAIL = Configure::read('sysconfig.Common.STATUS_FAIL');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
        $PLACE_OBJECT_TYPE_ID = new MongoId(Configure::read('sysconfig.ScreenPlaceList.PLACE_OBJECT_TYPE_ID'));

        $arr_result = [ 'status' => $STATUS_SUCCESS, 'msg' => $STATUS_SUCCESS_MSG, 'data' => ['arr_cate' => []]];

        $lang_code = $this->request->query("lang_code");
        $lang_code = "vi";
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");
        $region_id = $this->request->query("region_id");
        if (!empty($region_id)) {
            $region_id = new MongoId($region_id);
        }
        $limit = 5; //lấy 5, muốn xem thêm thì click more
        $offset = 0;

        $this->logAnyFile("START: lang_code: $lang_code, user_id: $user_id, lat,lng: ($lat,$lng), region_id: $region_id", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);

        $query_cate = [];
        $query_cate['conditions'] = [
            "status" => $STATUS_APPROVED,
            "object_type" => $PLACE_OBJECT_TYPE_ID
        ];

        $query_cate['order'] = array('order' => 'ASC');
        $query_cate['fields'] = array('id', 'name');

        $arr_cate = $this->Category->find('all', $query_cate);

        $query_place = [];
        $query_place['limit'] = $limit;
        $query_place['offset'] = $offset;
        $query_place['order'] = array('order' => 'ASC');
        $query_place['fields'] = array('id', 'name', 'address', 'location', 'file_uris', 'object_icon', 'rating');

        $query_place['conditions'] = [
            "status" => $STATUS_APPROVED,
        ];

        foreach ($arr_cate as $cate) {
            $cate = $cate["Category"];

            $query_place['conditions']["categories"] = new MongoId($cate["id"]);

            if (!empty($region_id)) {
                $query_place['conditions']["location.region"] = $region_id;
            }

            $cate["arr_place"] = [];
            $arr_place = $this->Place->find('all', $query_place);

            $this->log($arr_place, 'PlacesController.index.arr_place');

            if (!empty($arr_place)) {
                foreach ($arr_place as $value) {

                    $value = $value['Place'];

                    $place = array(
                        'id' => $value["id"],
                        'name' => $value['name'],
                        'address' => $value['address'],
                        'icon' => "", //TODO
                        'banner' => "",
                        'logo' => "",
                        'score' => 0,
                        'rate_count' => 0,
                    );

                    if (!empty($value["file_uris"]['banner'])) {
                        $place["banner"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['banner'])[0];
                    } else {
                        $place['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
                    }

                    if (!empty($value["file_uris"]['logo'])) {
                        $place["logo"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['logo'])[0];
                    }

                    if (!empty($value["rating"])) {
                        $place["score"] = $value["rating"]["score"];
                        $place["rate_count"] = $value["rating"]["count"];
                    }

                    $cate["arr_place"][] = $place;
                }
            }
            $arr_result['data']['arr_cate'][] = $cate;
        }

        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);

        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat: $lat, lng: $lng, region_id: $region_id ", __CLASS__ . '_' . __FUNCTION__);
    }

    /**
     *  1. url: http://124.158.5.134:8086/places/listplace
      2. Input
      - user_id, lang_code(ngôn ngữ format vi, ja, en...), lat, lng
      - region_id: ID địa danh(nếu có)
      - cate_id: ID danh mục(nếu có)
      3. output:
      { status: success/fail, msg: thành công/thất bại, data: { arr_place: [{id, name, banner, logo, icon, address, score: điểm trung bình rate, rate_count: số người rate }] , 'page' => $page, 'limit' => $limit, 'total } }
     */
    public function listplace() {
        $page_title = 'Danh sách địa điểm';
        $this->set('page_title', $page_title);
        //Tracking Report
        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.place_listbycate');
//        $this->insertTrackingAccess($sreen_code);

        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $STATUS_SUCCESS_MSG = Configure::read('sysconfig.Common.STATUS_SUCCESS_MSG');
        $STATUS_SUCCESS = Configure::read('sysconfig.Common.STATUS_SUCCESS');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');

        $arr_result = [ 'status' => $STATUS_SUCCESS, 'msg' => $STATUS_SUCCESS_MSG, 'data' => null];

        $lang_code = $this->request->query("lang_code");
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");
        $page = (int) $this->request->query('page');
        $limit = (int) $this->request->query('limit');

        $region_id = $this->request->query("region_id");
        $cate_id = $this->request->query("cate_id");

        $this->logAnyFile("START: lang_code: $lang_code, user_id: $user_id, lat: $lat, lng: $lng, region_id: $region_id, cate_id: $cate_id", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);

        if ($page < 1) {
            $page = 1;
        }
        if (!empty($region_id)) {
            $region_id = new MongoId($region_id);
        }

        $arr_cate = [];
        if (!empty($cate_id)) {
            $cate_id = new MongoId($cate_id);
            $query_cate = [];
            $query_cate['conditions'] = [
                "status" => $STATUS_APPROVED,
                "id" => $cate_id
            ];

            $query_cate['order'] = array('order' => 'ASC');
            $query_cate['fields'] = array('id', 'name');

            $cate = $this->Category->find('first', $query_cate);

            if (!empty($cate)) {
                $cate = $cate["Category"];

                $this->set('page_title', $cate['name']);

                $query_place = [];
                $query_place['conditions'] = [
                    "status" => $STATUS_APPROVED,
                    "categories" => $cate_id,
                ];
                if (!empty($region_id)) {
                    $query_place['conditions']["location.region"] = $region_id;
                }

                $total = $this->Place->find('count', $query_place);

                $query_place['order'] = array('order' => 'ASC');
                $query_place['fields'] = array('id', 'name', 'address', 'location', 'file_uris', 'object_icon', 'rating');

                if ($limit > 0) {
                    $offset = ($page - 1) * $limit;

                    $query_place['offset'] = $offset;
                    $query_place['limit'] = $limit;
                }

                $arr_place = $this->Place->find('all', $query_place);

                $cate["arr_place"] = [];
                if (!empty($arr_place)) {
                    foreach ($arr_place as $value) {

                        $value = $value['Place'];

                        $place = array(
                            'id' => $value["id"],
                            'name' => $value['name'],
                            'address' => $value['address'],
                            'icon' => "", //TODO
                            'banner' => "",
                            'logo' => "",
                            'score' => 0,
                            'rate_count' => 0,
                        );

                        if (!empty($value["file_uris"]['banner'])) {
                            $place["banner"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['banner'])[0];
                        } else {
                            $place['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
                        }
                        if (!empty($value["file_uris"]['logo'])) {
                            $place["logo"] = $URL_BASE_FILE_MANAGER . array_values($value['file_uris']['logo'])[0];
                        }

                        if (!empty($value["rating"])) {
                            $place["score"] = $value["rating"]["score"];
                            $place["rate_count"] = $value["rating"]["count"];
                        }

                        $cate["arr_place"][] = $place;
                    }
                }

                $cate['page'] = $page;
                $cate['limit'] = $limit;
                $cate['total'] = $total;
            }
            $arr_cate[] = $cate;
        }

        $arr_result['data'] = ['arr_cate' => $arr_cate];

        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);

        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat: $lat, lng: $lng, region_id: $region_id, cate_id: $cate_id ", __CLASS__ . '_' . __FUNCTION__);
    }

    /**
     *  1. url: http://124.158.5.134:8086/places/info
      2. input:
      - user_id, lang_code(ngôn ngữ format vi, ja, en...), lat, lng
      - id: id địa điểm
      3. output:
      { status: success/fail, msg: thành công/thất bại, data: { place: {id, name, loc, arr_slide_img, arr_thumb, video: {thumb, url}, audio_url, distance:{"text":"1.4 km","value":1442}, score: điểm trung bình rate, rate_count: số người rate, favorite: 1-đã thích/0-chưa thích, bookmark: 1/0, share: text,  short_description,  description, map_img_url, weather: {type, temperature_max, temperature_min, icon, content, current:{temperature, weather_description_code, icon, content}}, arr_utility: [{id, icon, name}]}, arr_cate: {id, name, type(tour, place, hotel, restaurant), arr_object: [{id, name, short_description, address, tel, banner, logo, icon, price, score: điểm trung bình rate, rate_count: số người rate }]} }
     */
    public function info() {
        $this->layout = 'detail';
        //Tracking Report
        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.place_info');
//        $this->insertTrackingAccess($sreen_code);

        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');
        $STATUS_SUCCESS_MSG = Configure::read('sysconfig.Common.STATUS_SUCCESS_MSG');
        $STATUS_SUCCESS = Configure::read('sysconfig.Common.STATUS_SUCCESS');
        $PLACE_OBJECT_TYPE_ID = Configure::read('sysconfig.ScreenPlaceInfo.PLACE_OBJECT_TYPE_ID');
        $DISTANCE_AROUND = Configure::read('sysconfig.ScreenPlaceInfo.DISTANCE_AROUND');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');

        $lang_code = $this->request->query("lang_code");
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");

        $id = $this->request->query("id");
        $os_name = $this->request->query("os_name");
        $os_version = $this->request->query("os_version");

        $this->logAnyFile("START: lang_code: $lang_code, user_id: $user_id, lat: $lat, lng: $lng, os_name: $os_name, os_version: $os_version", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);

        $arr_result = [ 'status' => $STATUS_SUCCESS, 'msg' => $STATUS_SUCCESS_MSG, 'data' => null];

        $query_place = [];
        $query_place['fields'] = array('id', 'name', 'address', 'short_description', 'description', 'email', 'website', 'tel', 'file_uris', 'loc', 'object_icon', 'location', 'rating');
        $query_place['conditions'] = [
            "status" => $STATUS_APPROVED,
            "id" => new MongoId($id)
        ];

        $place = $this->Place->find('first', $query_place);
        if (!empty($place)) {
            $place = $place['Place'];

            $place['description'] = $this->convertAbsolutePathInContent($place['description']);
            $place['icon'] = ""; //TODO
            $place['banner'] = "";
            $place['logo'] = "";
            $place['score'] = 0;
            $place['rate_count'] = 0;
            $place['favorite'] = 0;
            $place['bookmark'] = 0;
            $place['share'] = "Địa diểm đẹp tuyệt vời!";
            $place['map'] = "";
            if (!empty($place["file_uris"]["banner"])) {
                $place['banner'] = $URL_BASE_FILE_MANAGER . array_values($place['file_uris']['banner'])[0];
            } else {
                $place['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
            }

            if (!empty($place["file_uris"]["logo"])) {
                $place['logo'] = $URL_BASE_FILE_MANAGER . array_values($place['file_uris']['logo'])[0];
            }

            if (!empty($place["file_uris"]["map"])) {
                $place['map'] = $URL_BASE_FILE_MANAGER . array_values($place['file_uris']['map'])[0];
            }

            $place["arr_img"] = [];
            $place["arr_slide_img"] = [];
            $count = 0;
            if (!empty($place["file_uris"]["thumbnails"])) {
                foreach ($place["file_uris"]["thumbnails"] as $thumbnails) {

                    $thumbnails_url = $URL_BASE_FILE_MANAGER . $thumbnails;
                    $place["arr_img"][] = $thumbnails_url;
                    $count++;

                    if ($count <= 5) {
                        $place["arr_slide_img"][] = $thumbnails_url;
                    }
                }
            } else {
                $place["arr_slide_img"][] = $place['banner'];
            }

            if (!empty($place["rating"])) {
                $place["score"] = $place["rating"]["score"];
                $place["rate_count"] = $place["rating"]["count"];
            }

            //Lấy link video streaming
            $query_streaming = [];
            $query_streaming['fields'] = array('id', 'name', 'file_path', 'file_size', 'duration', 'resolution_w', 'resolution_h', 'bitrate', 'file_uris', 'file_mime');
            $query_streaming['conditions'] = [
                'object_type' => new MongoId($PLACE_OBJECT_TYPE_ID),
                'object_id' => new MongoId($id),
            ];

            $arr_streaming = $this->Streaming->find('all', $query_streaming);

            $place["audio_url"] = null;
            $place["video"] = null;
            if (!empty($arr_streaming)) {
                foreach ($arr_streaming as $streaming) {
                    $streaming = $streaming["Streaming"];

                    if (!empty($streaming['file_mime'])) {
                        if ($this->startsWith($streaming['file_mime'], "audio")) {

                            $place["audio_url"] = $this->getStreamingUrl($os_name, $os_version, $streaming['file_mime'], $streaming['file_path']);
                        } else {
                            $place["video"] = [
                                "url" => $this->getStreamingUrl($os_name, $os_version, $streaming['file_mime'], $streaming['file_path']),
                                "thumb" => ""];

                            if (!empty($streaming["file_uris"]["poster"])) {
                                $place["video"]["thumb"] = $URL_BASE_FILE_MANAGER . array_values($streaming["file_uris"]["poster"])[0];
                            }
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
                        $favorites = Hash::extract($visitor["favorites"], '{n}[object_type_code=/places/]');
                        if (!empty($favorites["items"]) && is_array($favorites["items"])) {
                            foreach ($favorites["items"] as $fav) {
                                if (new MongoId($id) == $fav["_id"]) {
                                    $place['favorite'] = 1;
                                }
                            }
                        }
                    }

                    if (!empty($visitor["bookmarks"]) && is_array($visitor["bookmarks"])) {
                        $bookmarks = Hash::extract($visitor["bookmarks"], '{n}[object_type_code=/places/]');
                        if (!empty($bookmarks["items"]) && is_array($bookmarks["items"])) {
                            foreach ($bookmarks["items"] as $bookmark) {
                                if (new MongoId($id) == $bookmark["_id"]) {
                                    $place['bookmark'] = 1;
                                }
                            }
                        }
                    }
                }
            }

            //------------- TÍNH KHOẢNG CÁCH -----------------
            $place_lng = $place["loc"]["coordinates"][0];
            $place_lat = $place["loc"]["coordinates"][1];
            $place["distance"] = $this->caculateDistance($lat, $lng, $place_lat, $place_lng);

            //------------- TÍNH THỜI TIẾT -----------------
            $query_weather = [];
            $query_weather['fields'] = array('type', 'informations', 'current');
            $query_weather['conditions'] = [
                "region" => $place["location"]["region"]
            ];

            $weather = $this->Weather->find('first', $query_weather);

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
                //Current
                if (!empty($weather["Weather"]["current"])) {
                    $current = $weather["Weather"]["current"];

                    $weather_detail = $this->getDetailWeather($current["weather_description_code"], $lang_code);
                    $arr_weather["current"] = $current;
                    $arr_weather["current"]["icon"] = $URL_BASE_FILE_MANAGER . $weather_detail["icon"];
                    $arr_weather["current"]["content"] = $weather_detail["content"];
                }
            }

            $place["weather"] = $arr_weather;

            $point = $place["loc"];

            $query_place_around = array(
                'conditions' => array(
                    'status' => $STATUS_APPROVED,
                    'id' => ['$ne' => new MongoId($id)],
                    'loc' => array(
                        '$near' => array(
                            '$geometry' => $point,
                            '$maxDistance' => $DISTANCE_AROUND,
                        ),
                    ),
                ),
            );

            $count_place_around = $this->Place->find('count', $query_place_around);

            $query_region_around = array(
                'conditions' => array(
                    'status' => $STATUS_APPROVED,
                    'loc' => array(
                        '$near' => array(
                            '$geometry' => $point,
                            '$maxDistance' => $DISTANCE_AROUND,
                        ),
                    ),
                ),
            );

            $count_region_around = $this->Region->find('count', $query_region_around);

            unset($place["object_icon"]);
            unset($place["rating"]);
            unset($place["file_uris"]);
            unset($place["location"]);

            $arr_result['data'] = ['place' => $place, 'count_around' => ($count_place_around + $count_region_around)];
        }

        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);
        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat: $lat, lng: $lng ", __CLASS__ . '_' . __FUNCTION__);
    }

    public function top100() {
        $page_title = 'Top 100 địa điểm hot';
        $this->set('page_title', $page_title);
        //Tracking Report
        $sreen_code = Configure::read('sysconfig.Common.SCREEN_CODE.place_top100');
//        $this->insertTrackingAccess($sreen_code);

        $this->logAnyFile("URL: " . Router::url('/', true) . $this->request->here(), __CLASS__ . '_' . __FUNCTION__);


        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
        $PLACE_COLLECTION_ID = Configure::read('sysconfig.ScreenTop100Place.PLACE_COLLECTION_ID');
        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');

        $lang_code = $this->request->query("lang_code");
        $user_id = $this->request->query("user_id");
        $lat = $this->request->query("lat");
        $lng = $this->request->query("lng");

        $page = (int) $this->request->query('page');
        $limit = (int) $this->request->query('limit');
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1) {
            $limit = 10;
        }
        $offset = ($page - 1) * $limit;

        $query_place = [];
        $query_place['conditions'] = [
            "status" => $STATUS_APPROVED,
//            "categories" => new MongoId($PLACE_CATEGORY_ID),
            "collections" => new MongoId($PLACE_COLLECTION_ID)
        ];


        $total = $this->Place->find('count', $query_place);

        $query_place['limit'] = $limit;
        $query_place['offset'] = $offset;
        $query_place['order'] = array('order' => 'ASC');
        $query_place['fields'] = array('id', 'name', 'address', 'location', 'files', 'file_uris', 'object_icon');
        $this->Paginator->settings = $query_place;
        $list_data = $this->Paginator->paginate('Place', []);
//        $arr_place = $this->Place->find('all', $query_place);
        $arr_place_result = $this->modifydata($list_data, $URL_BASE_FILE_MANAGER);

//        $arr_result = ['arr_slideshow' => $arr_slideshow_result, 'arr_place' => $arr_place_result, 'page' => $page, 'limit' => $limit, 'total' => $total];
        // return result
        $arr_result = array(
            'status' => 'success',
            'data' => ['arr_place' => $arr_place_result],
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
        );

        $this->logAnyFile("OUTPUT of URL " . Router::url('/', true) . $this->request->here() . ": ", __CLASS__ . '_' . __FUNCTION__);

        $this->logAnyFile($arr_result, __CLASS__ . '_' . __FUNCTION__);

        $this->set('arr_result', $arr_result);

        $this->logAnyFile("END: lang_code: $lang_code, user_id: $user_id, lat: $lat, lng: $lng", __CLASS__ . '_' . __FUNCTION__);
    }

    protected function modifydata($arr_place, $URL_BASE_FILE_MANAGER) {
        $arr_place_result = [];
        if (is_array($arr_place) && count($arr_place) > 0) {
            $count = 0;
            if (count($arr_place) > 100) {
                $count = 100;
            } else {
                $count = count($arr_place);
            }
            for ($i = 0; $i < $count; $i++) {
                $place = array(
                    'id' => $arr_place[$i]['Place']["id"],
                    'name' => $arr_place[$i]['Place']['name'],
                    'address' => $arr_place[$i]['Place']['address'],
                    'icon' => "", //TODO
                    'banner' => "",
                    'logo' => "",
                );

                if (!empty($arr_place[$i]['Place']['file_uris']['banner'])) {
                    $place['banner'] = $URL_BASE_FILE_MANAGER . array_values($arr_place[$i]['Place']['file_uris']['banner'])[0];
                } else {
                    $place['banner'] = $URL_BASE_FILE_MANAGER . "data_files/halo-default.jpg";
                }
                if (!empty($arr_place[$i]['Place']['file_uris']['logo'])) {
                    $place['logo'] = $URL_BASE_FILE_MANAGER . array_values($arr_place[$i]['Place']['file_uris']['logo'])[0];
                }


                array_push($arr_place_result, $place);
            }
        }
        return $arr_place_result;
    }

}
