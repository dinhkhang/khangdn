<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppQuizController extends Controller {

    public $layout = "gamequiz";
    public $components = array(
        'Session',
        'Paginator',
        'Auth' => array(
            'loginAction' => array(
                'controller' => 'Visitors',
                'action' => 'login',
            ),
            'loginRedirect' => array('controller' => 'Home', 'action' => 'index'),
            'logoutRedirect' => array('controller' => 'Home', 'action' => 'index'),
            'authorize' => array('Controller'),
            'authenticate' => array(
                'Form' => array(
                    'userModel' => 'Visitor',
                    'fields' => array('username' => 'username', 'password' => 'password')
                )
            )
        ),
//        'DebugKit.Toolbar',
        'TrackingAccessCommon',
        'VisitorBlacklistCommon',
    );
    protected $_mergeParent = 'AppQuizController';
    public $paginate = array(
        'limit' => 20,
    );
    public $response = null;
    public $lang_code = null;
    public $currency_code = null;
    public $lat = null;
    public $lng = null;
    public $os_name = null;
    public $os_version = null;
    public $user_region_id = null;
    public $username = null;
    public $service_sendmt = null;
    public $arr_charge_info = null;
    public $arr_player_status = null;
    public $arr_action = null;
    public $auto_trace = 1; // thực hiện tự động lưu vết 

    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
    /* ::                                                                         : */
    /* ::  This routine calculates the distance between two points (given the     : */
    /* ::  latitude/longitude of those points). It is being used to calculate     : */
    /* ::  the distance between two locations using GeoDataSource(TM) Products    : */
    /* ::                                                                         : */
    /* ::  Definitions:                                                           : */
    /* ::    South latitudes are negative, east longitudes are positive           : */
    /* ::                                                                         : */
    /* ::  Passed to function:                                                    : */
    /* ::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  : */
    /* ::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  : */
    /* ::    unit = the unit you desire for results                               : */
    /* ::           where: 'M' is statute miles (default)                         : */
    /* ::                  'K' is kilometers                                      : */
    /* ::                  'N' is nautical miles                                  : */
    /* ::  Worldwide cities and other features databases with latitude longitude  : */
    /* ::  are available at http://www.geodatasource.com                          : */
    /* ::                                                                         : */
    /* ::  For enquiries, please contact sales@geodatasource.com                  : */
    /* ::                                                                         : */
    /* ::  Official Web site: http://www.geodatasource.com                        : */
    /* ::                                                                         : */
    /* ::         GeoDataSource.com (C) All Rights Reserved 2015		   		     : */
    /* ::                                                                         : */
    /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
        //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
        //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    public function getList($model_name = null, $options = array()) {

        $status_approved = Configure::read('sysconfig.App.constants.STATUS_APPROVED');
        if (!empty($options['fields'])) {

            $fields = $options['fields'];
        }
        $default_options = array(
            'conditions' => array(
                'status' => array(
                    '$eq' => $status_approved,
                ),
            ),
            'fields' => array(
                'id', 'name',
            ),
        );
        $options = Hash::merge($default_options, $options);
        if (!empty($fields)) {

            $options['fields'] = $fields;
        }
        if (empty($model_name)) {

            $model_name = $this->modelClass;
        }

        $list_data = $this->$model_name->find('list', $options);
        return $list_data;
    }

    /**
     * convert_vi_to_en method
     * hàm chuyền đổi tiếng việt có dấu sang tiếng việt không dấu
     * @param string $str
     * @return string
     */
    public function convert_vi_to_en($str) {

        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ|Ð)/", 'D', $str);
        //$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
        // thực hiện cưỡng ép chuyển sang ascii
        $str = $this->forceConvertASCII($str);

        return $str;
    }

    /**
     * forceConvertASCII
     * 
     * @param string $str
     * @return string
     */
    public function forceConvertASCII($str) {

        try {

            $ascii_str = @iconv("UTF-8", "us-ascii//TRANSLIT", $str);
        } catch (Exception $e) {

            $this->log($e, 'notice');
            $this->log($str, 'notice');
        }
        return $ascii_str;
    }

    /**
     * isASCII
     * Thực hiện kiểm tra chuỗi string có phải là ASCII k?
     * 
     * @param string $str
     * @return boolean
     */
    public function isASCII($str) {

        return mb_detect_encoding($str, 'ASCII', true);
    }

    /**
     * Tính khoảng cách giữa 2 điểm
     * dựa vào GOOGLE_API_DISTANCE_URL
     *  
     * HoangNN
     */
    protected function caculateDistance($from_lat, $from_lng, $to_lat, $to_lng) {

//        $GOOGLE_API_DISTANCE_URL = Configure::read('sysconfig.Locations.GOOGLE_API_DISTANCE_URL');
//
//        if (!empty($from_lat) && !empty($from_lng) && !empty($to_lat) && !empty($to_lng)) {
//            $geo_url_distance = $GOOGLE_API_DISTANCE_URL . "&origins=" . $from_lat . "," . $from_lng . "&destinations=" . $to_lat . "," . $to_lng;
//            $result_distance = file_get_contents($geo_url_distance);
//
//            $arr_result_distance = json_decode($result_distance, true);
//
//            if (empty($arr_result_distance) || empty($arr_result_distance["rows"][0]["elements"][0]["distance"])) {
//                $this->log(__CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'service');
//                $this->log(__('The service at %s was invalid', $geo_url_distance), 'service');
//                $this->log(__($result_distance), 'service');
//            } else {
//                return $arr_result_distance["rows"][0]["elements"][0]["distance"];
//            }
//        }
        if (empty($from_lat) || empty($from_lng) || $from_lat < 0 || $from_lng < 0) {

            return null;
        }

        $distance_value = $this->distance($from_lat, $from_lng, $to_lat, $to_lng, 'K');
        $distance_text = round($distance_value, 1) . " km";

        $arr_distance = ["value" => $distance_value, "text" => $distance_text];

        return $arr_distance;
    }

    /**
     * Lấy StreamingUrl
     * 
     * @param type $os : $ANDROID, $IOS, $WINDOWPHONE, $SYMBIAN
     * @param type $os_version
     * @param type $filetype : audio/mp3, video/mp4...
     * @param type $filename
     * @return string
     * @author HoangNN
     */
    public function getStreamingUrl($os, $os_version, $filetype, $filename) {
        $ANDROID = Configure::read('sysconfig.OS.ANDROID');
        $IOS = Configure::read('sysconfig.OS.IOS');
        $WINDOWPHONE = Configure::read('sysconfig.OS.WINDOWPHONE');
        $SYMBIAN = Configure::read('sysconfig.OS.SYMBIAN');

        $HTTP = Configure::read('sysconfig.STREAMING_SERVER.HTTP');
        $RTSP = Configure::read('sysconfig.STREAMING_SERVER.RTSP');
        $HLS = Configure::read('sysconfig.STREAMING_SERVER.HLS');

        $url = "";
        $streaming_server = $HTTP;
        if ($WINDOWPHONE == $os && $os_version >= 7.0) {
            $streaming_server = $HTTP;
        }
        if ($ANDROID == $os) {
            if ($os_version < 3.0) {
                $streaming_server = $RTSP;
            } else {
                $streaming_server = $HLS;
            }
        } else if ($SYMBIAN == $os) {
            $streaming_server = $RTSP;
        } else if ($IOS == $os) {
            $streaming_server = $HLS; // hls
        } else {
            $streaming_server = $HTTP; // default http
        }

        if ($this->startsWith($filetype, "audio")) {
            $url = $streaming_server . '/vod/' . $filename;
        } else if ($this->startsWith($filetype, "video")) {

            if ($streaming_server == $HLS || $streaming_server == $RTSP) {
                $url = $streaming_server . '/vod/_definst_/' . $filename;
                if ($streaming_server == $HLS) {
                    $url .= '/playlist.m3u8';
                }
            } else {
                $url = $streaming_server . '/vod/' . $filename;
            }
        } else {
            $url = $streaming_server . '/vod/_definst_/' . $filename;
            if ($streaming_server == $HLS) {
                $url .= '/playlist.m3u8';
            }
        }


        return $url;
    }

    /**
     * Lấy MAP URL, dùng để lấy ảnh bản đồ
     *  
     * HoangNN
     */
    protected function getMapUrl($loc, $label) {

        if (empty($loc["coordinates"])) {

            return null;
        }
        $GOOGLE_API_STATICMAP_URL = Configure::read('sysconfig.Locations.GOOGLE_API_STATICMAP_URL');

        $url = $GOOGLE_API_STATICMAP_URL . $loc["coordinates"][1] . "," . $loc["coordinates"][0] .
                "&markers=color:blue|label:$label|" . $loc["coordinates"][1] . "," . $loc["coordinates"][0];


        return $url;
    }

    /**
     * Chuyển 1 mảng ObjectId thành mảng String Id
     * @param type $arr_obj_id
     * @return type
     */
    protected function arrObjId2ArrStrId($arr_obj_id) {

        if (!empty($arr_obj_id)) {
            $arrStrId = [];
            foreach ($arrStrId as $value) {
                $arrStrId[] = (string) $value;
            }
            return $arrStrId;
        }
        return null;
    }

    /**
     * Lấy chi tiết thời tiết
     *  
     * return: [ 
      "icon" => "",
      "content" => "",
      ]
     * HoangNN
     */
    protected function insertTrackingAccess($screen_code, $options = array()) {

        $path = $this->request->here();
        $os_name = $this->request->query("os_name");
        $os_version = $this->request->query("os_version");
        $user_id = $this->request->query("user_id");
        $token = $this->request->header('token');

        if (empty($user_id)) {

            $this->extractUserInfoFromToken();
            $user_id = $this->username;
        }

        $data = [
            'screen_code' => $screen_code,
            'host' => $this->request->host(),
            'client_ip' => $this->request->clientIp(),
            "path" => $path,
            'payload' => $this->request->query,
            "user_agent" => $this->request->header('User-Agent'),
            "os_name" => strtoupper($os_name),
            "os_version" => $os_version,
            'visitor_username' => $user_id,
            "token" => !empty($token) ? $token : null,
            "distribution_channel_code" => "",
            "created" => new MongoDate(),
            "modified" => new MongoDate(),
        ];

        $table_name = "tracking_access_" . date('Y_m_d');
        App::uses('TrackingLog', 'Model');
        $TrackingLog = new TrackingLog(array(
            'table' => $table_name,
        ));

        if (!empty($options['async']) && $options['async'] === false) {

            $TrackingLog->create();
            return $TrackingLog->save($data);
        } else {

            $mongo = $TrackingLog->getDataSource();
            $mongoCollectionObject = $mongo->getMongoCollection($TrackingLog);
            return $mongoCollectionObject->insert($data, array('w' => 0));
        }
    }

    /**
     * Lấy chi tiết thời tiết
     *  
     * return: [ 
      "icon" => "",
      "content" => "",
      ]
     * HoangNN
     */
    protected function getDetailWeather($code, $lang_code) {


        $this->logAnyFile("getDetailWeather: code($code), lang_code($lang_code)", __CLASS__ . '_' . __FUNCTION__);


        $query_weather_description = [];
        $query_weather_description['conditions'] = [
            "code" => $code,
        ];
        $weather_description = $this->WeatherDescription->find('first', $query_weather_description);

        $arr_weather = [
            "icon" => "",
            "content" => "",
        ];

        if (!empty($weather_description["WeatherDescription"]["files"]["icon_d_uri"])) {
            $arr_weather["icon"] = array_values($weather_description["WeatherDescription"]["files"]["icon_d_uri"])[0];
        }
        if (!empty($weather_description["WeatherDescription"]["datas"])) {
            foreach ($weather_description["WeatherDescription"]["datas"] as $value) {
                if ($value["lang_code"] == $lang_code) {
                    $arr_weather["content"] = $value["content"];
                    break;
                }
            }
        }

        return $arr_weather;
    }

    /**
     * Lấy User by user_id(username)
     * (Phải khai báo model ở Controller)
     * @return boolean
     * HoangNN
     */
    protected function getVisitorByUserId($user_id) {
        $visitor = null;
        if (!empty($user_id)) {
            $query_visitor = [];
            $query_visitor['conditions'] = [
                "username" => $user_id
            ];
            if (!$this->Visitor) {
                $this->loadModel('Visitor');
            }
            $visitor = $this->Visitor->find('first', $query_visitor);
        }

        return $visitor;
    }

    /**
     * detectRegionCodeName
     * xác định mã tỉnh thành từ request dựa vào lat, lng
     * dựa vào GOOGLE_API_GEO_URL
     * 
     * @return boolean
     * HoangNN
     */
    protected function detectRegionCodeName() {

        $lat = trim($this->request->query('lat'));
        $lng = trim($this->request->query('lng'));

        if (empty($lat) || empty($lng)) {

            return false;
        }

        $geo_url = Configure::read('sysconfig.Locations.GOOGLE_API_GEO_URL');
        $params = array(
            'latlng' => $lat . ',' . $lng,
        );

        $geo_service = $geo_url . '&' . http_build_query($params);
        $result = file_get_contents($geo_service);
        $arr_result = json_decode($result, true);
        if (empty($arr_result) || empty($arr_result["results"][0]["address_components"])) {

            $this->log(__CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'service');
            $this->log(__('The service at %s was invalid', $geo_service), 'service');
            $this->log(__($result), 'service');

            return false;
        }

        $address_components = $arr_result["results"][0]["address_components"];
        foreach ($address_components as $v) {

            if (!empty($v['types']['0']) && $v['types']['0'] == 'administrative_area_level_1') {

                $geo_region_alias = $v['short_name'];
                break;
            }
        }

        if (empty($geo_region_alias)) {

            return false;
        }

        $en_geo_region_alias = $this->convert_vi_to_en($geo_region_alias);
        $region_code_name = strtolower(str_replace(' ', '', $en_geo_region_alias));

        return $region_code_name;
    }

    /**
     * detectRegionId
     * xác định region id từ request
     * nếu không truyền vào tham số region_id, thì tự động detect region_id dựa vào lat, lng
     * dựa vào GOOGLE_API_GEO_URL
     * 
     * @return boolean
     */
    protected function detectRegionId($options = array()) {

        $region_id = trim($this->request->query('region_id'));
        $user_region_id = trim($this->request->query('user_region_id'));
        if (!empty($region_id) && empty($options['object_return'])) {

            return $region_id;
        }

        if (!empty($user_region_id) && empty($options['object_return'])) {

            return $user_region_id;
        }

        if (!isset($this->Region)) {

            $this->loadModel('Region');
        }

        // nếu truyền vào $region_id thì ưu tiên xác định theo region_id
        if (!empty($region_id) && !empty($options['object_return'])) {

            $region = $this->Region->find('first', array(
                'conditions' => array(
                    'id' => new MongoId($region_id),
                ),
            ));

            if (empty($region)) {

                return false;
            }
            return $region['Region'];
        }

        // nếu truyền vào $user_region_id thì ưu tiên xác định theo $user_region_id
        if (!empty($user_region_id) && !empty($options['object_return'])) {

            $region = $this->Region->find('first', array(
                'conditions' => array(
                    'id' => new MongoId($user_region_id),
                ),
            ));

            if (empty($region)) {

                return false;
            }
            return $region['Region'];
        }

        $lat = (float) trim($this->request->query('lat'));
        $lng = (float) trim($this->request->query('lng'));

        // nếu truyền lên là rỗng, hoặc nhỏ hơn 0
        if (empty($lat) || empty($lng) || $lat < 0 || $lng < 0) {

            return false;
        }

        $region = $this->findUserRegion($lat, $lng);
        if (empty($region)) {

            return false;
        }

        if (empty($options['object_return'])) {

            return $region['Region']['id'];
        } else {

            return $region['Region'];
        }
    }

    /**
     * findUserRegion
     * tìm kiếm vị trí của user qua google api dựa vào $lat, $lng
     * 
     * @param float $lat
     * @param float $lng
     * @return boolean
     */
    protected function findUserRegion($lat, $lng) {

        $geo_url = Configure::read('sysconfig.Locations.GOOGLE_API_GEO_URL');
        $params = array(
            'latlng' => $lat . ',' . $lng,
        );

//        $geo_service = $geo_url . '&' . http_build_query($params);
        $geo_service = $geo_url . '&latlng=' . $lat . ',' . $lng;

        $result = file_get_contents($geo_service);
        $arr_result = json_decode($result, true);
        if (empty($arr_result) || empty($arr_result["results"][0]["address_components"])) {

            $this->log(__CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'service');
            $this->log(__('The service at %s was invalid', $geo_service), 'service');
            $this->log(__($result), 'service');

            return false;
        }

        $address_components = $arr_result["results"][0]["address_components"];
        foreach ($address_components as $v) {

            if (!empty($v['types']['0']) && $v['types']['0'] == 'administrative_area_level_1') {

                $geo_region_alias = $v['short_name'];
                break;
            }
        }

        if (empty($geo_region_alias)) {

            return false;
        }

        $en_geo_region_alias = $this->convert_vi_to_en($geo_region_alias);
        $region_code_name = strtolower(str_replace(' ', '', $en_geo_region_alias));
        $region = $this->Region->find('first', array(
            'conditions' => array(
                'code_name' => new MongoRegex("/^" . $region_code_name . "$/"),
            ),
        ));
        if (empty($region)) {

            return false;
        }

        return $region;
    }

    protected function convertAbsolutePathInContent($content) {

        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
        $arr_search = ["src=\"/", "src=\'/"];
        $arr_replace = ["src=\"$URL_BASE_FILE_MANAGER", "src=\'$URL_BASE_FILE_MANAGER"];

        $content = str_replace($arr_search, $arr_replace, $content);

        return $content;
    }

    /**
     * logAnyFile
     * 
     * @param mixed $content
     * @param string $file_name
     */
    protected function logAnyFile($content, $file_name) {

        CakeLog::config($file_name, array(
            'engine' => 'File',
            'types' => array($file_name),
            'file' => $file_name,
        ));

        $this->log($content, $file_name);
    }

    /**
     * debugInit
     * thực hiện khởi tạo debug log
     * thực hiện tự động tạo ra trans_id, log_file_name nếu không được định nghĩa
     * thực hiện lưu lại $this->request
     * 
     * @return mixed
     */
    protected function debugInit() {

        if (!isset($this->debug_mode) || $this->debug_mode < 1) {

            return;
        }
        if (empty($this->trans_id)) {

            $this->trans_id = uniqid();
        }
        if (empty($this->log_file_name)) {

            $this->log_file_name = $this->name . '_' . $this->action;
        }

        $this->logAnyFile(__('Init transaction id %s', $this->trans_id), $this->log_file_name);
        $this->logAnyFile(__('Request Header data req_header_%s', $this->trans_id), $this->log_file_name);
        $this->logAnyFile($this->getallheaders(), $this->log_file_name);
        $this->logAnyFile(__('Request GET data req_get_%s', $this->trans_id), $this->log_file_name);
        $this->logAnyFile($this->request->query, $this->log_file_name);
        $this->logAnyFile(__('Request POST data req_post_%s', $this->trans_id), $this->log_file_name);
        $this->logAnyFile($this->request->data, $this->log_file_name);

        if ($this->debug_mode >= 2) {

            $this->logAnyFile(__('Request user agent ua_%s', $this->trans_id), $this->log_file_name);
            $this->logAnyFile($this->request->header('User-Agent'), $this->log_file_name);
        }

        if ($this->debug_mode >= 3) {

            // thực hiện khởi tạo lấy về output thực chất được trả về client, chứa cả các thông báo notice php nếu có
            ob_start();
        }
    }

    /**
     * debugResponse
     * thực hiện debug log trước khi response dữ liệu trả về
     * đối với trường hợp $this->debug_mode = 2, thì thực hiện lưu lại database query
     * 
     * @return mixed
     */
    protected function debugResponse() {

        if (!isset($this->debug_mode) || $this->debug_mode < 1) {

            return;
        }

        $this->logAnyFile(__('Response data res_%s', $this->trans_id), $this->log_file_name);

        if ($this->isJson($this->response)) {

            $response = json_encode(json_decode($this->response, true), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->logAnyFile($response, $this->log_file_name);
        } else {

            $this->logAnyFile($this->response, $this->log_file_name);
        }

        if ($this->debug_mode >= 2) {

            App::uses('ConnectionManager', 'Model');
            $db = ConnectionManager::getDataSource('default');
            $this->logAnyFile(__('Database query que_%s', $this->trans_id), $this->log_file_name);
            $this->logAnyFile($db->getLog(), $this->log_file_name);
        }

        if ($this->debug_mode >= 3) {

            // thực hiện khởi tạo lấy về output thực chất được trả về client, chứa cả các thông báo notice php nếu có
            $raw_response = ob_get_contents();
            $this->logAnyFile(__('Raw output o_%s response to client', $this->trans_id), $this->log_file_name);
            $this->logAnyFile($raw_response, $this->log_file_name);
        }
    }

    /**
     * isJson
     * kiểm tra xem chuỗi string có phải là json k?
     * 
     * @param string $string
     * @return bool
     */
    protected function isJson($string) {

        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function makeArrayValueToMongoObjectId(&$list) {
        foreach ($list AS $key => $value) {
            if (is_string($value) && strlen($value)) {
                $list[$key] = new MongoId($value);
            }
        }
    }

    public function resError($code, $options = array()) {

        $this->setJsonHeader();

        if (empty($options['config'])) {

            $config = $this->name;
        } else {

            $config = $options['config'];
        }


        if (empty($options['message'])) {

            $config_path = 'message_code.' . $config . '.' . $code;
            $message = Configure::read($config_path);
            if (empty($message)) {

                throw new NotImplementedException(__('%s config was not defined', $config_path));
            }
        } else {

            $message = $options['message'];
        }

        if (!empty($options['message_args'])) {

            $message_args = $options['message_args'];
            if (!is_array($message_args)) {

                $message_args = explode(',', $message_args);
            }
            array_unshift($message_args, $message);
            $message = call_user_func_array('__', $message_args);
        }

        $data = !empty($options['data']) ? $options['data'] : null;

        $res = array(
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'data' => $data,
        );
        $this->response = json_encode($res);

        echo $this->response;
        $this->debugResponse();
        exit();
    }

    public function resFail($message, $data = null) {

        $this->setJsonHeader();

        $res = array(
            'status' => 'fail',
            'message' => $message,
            'data' => $data,
        );
        $this->response = json_encode($res);

        echo $this->response;
        $this->debugResponse();
        exit();
    }

    public function resSuccess($res) {

        $this->setJsonHeader();

        $res['status'] = 'success';
        $this->response = json_encode($res);

        echo $this->response;
        $this->debugResponse();
        exit();
    }

    protected function getFileUris($data, $type, $multiply = false) {

        $file_uri_field = 'file_uris';
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');

        if (!isset($data[$file_uri_field][$type]) || !is_array($data[$file_uri_field][$type]) || empty($data[$file_uri_field][$type])) {

            return '';
        }
        if ($multiply) {

            $files = array_values($data[$file_uri_field][$type]);
            foreach ($files as $k => $v) {

                $files[$k] = $URL_BASE_FILE_MANAGER . $v;
            }

            return $files;
        } else {

            return $URL_BASE_FILE_MANAGER . array_values($data[$file_uri_field][$type])[0];
        }
    }

    protected function getRelativeFileUris($data, $type, $multiply = false) {

        $file_uri_field = 'file_uris';
        if (!isset($data[$file_uri_field][$type]) || !is_array($data[$file_uri_field][$type]) || empty($data[$file_uri_field][$type])) {

            return '';
        }
        if ($multiply) {

            $files = array_values($data[$file_uri_field][$type]);
            foreach ($files as $k => $v) {

                $files[$k] = $v;
            }

            return $files;
        } else {

            return array_values($data[$file_uri_field][$type])[0];
        }
    }

    protected function getPrices($data, $currency_code = null) {

        $DEFAULT_PRICE_CODE = Configure::read('sysconfig.ScreenTourHome.DEFAULT_PRICE_CODE');
        $prices_field = 'prices';
        if (empty($data[$prices_field]) || !is_array($data[$prices_field])) {

            return null;
        }

        $price = Hash::extract($data[$prices_field], '{n}[currency_code=/' . $currency_code . '/]');
        if (empty($price)) {

            return null;
        }
        foreach ($price as $v) {

            if ($v['price_code'] == $DEFAULT_PRICE_CODE) {

                return $v;
            }
        }

        return null;
    }

    protected function getRating($data) {

        $rating_field = 'rating';
        if (empty($data[$rating_field]) || !is_array($data[$rating_field])) {

            return array(
                'score' => '',
                'name' => '',
                'count' => '',
            );
        }

        return $data[$rating_field];
    }

    protected function getAvatarUrl($visitor_data) {

        $default_avatar_url = Configure::read('sysconfig.Visitor.default_avatar_url');
        $URL_BASE_FILE_MANAGER = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
        $avatar_url = !empty($visitor_data['visitor']['avatar_uri']) ?
                $URL_BASE_FILE_MANAGER . $visitor_data['visitor']['avatar_uri'] : $default_avatar_url;

        return $avatar_url;
    }

    /**
     * setInit
     * Khởi tạo chế độ debug, thực hiện set json header
     * Xử lý các tham số được dùng chung của các service
     */
    protected function setInit() {

        $this->debugInit();
//        $this->setJsonHeader();

        $currency_code = trim($this->getParam('currency_code'));
        if (empty($currency_code)) {

            $this->currency_code = Configure::read('sysconfig.App.constants.CURRENCY_CODE');
        } else {

            $this->currency_code = $currency_code;
        }

        $lang_code = trim($this->getParam('lang_code'));
        if (empty($lang_code)) {

            $this->lang_code = Configure::read('sysconfig.App.constants.LANG_CODE');
        } else {

            $this->lang_code = $lang_code;
        }

        $this->lat = floatval(trim($this->getParam('lat')));
        $this->lng = floatval(trim($this->getParam('lng')));

        // loại bỏ trường hợp truyền lên là -1
        if ($this->lat < 0 || $this->lng < 0) {

            $this->lat = $this->lng = null;
        }

        $this->os_name = trim($this->getParam('os_name'));
        $this->os_name = strtoupper($this->os_name);
        $this->os_version = trim($this->getParam('os_version'));

        $username = trim($this->getParam('user_id'));
        if (empty($username)) {

            $this->extractUserInfoFromToken();
        } else {

            $this->username = $username;
        }
    }

    protected function extractUserInfoFromToken() {

        $token = $this->request->header('token');
        if (empty($token)) {

            return false;
        }
        App::import('Vendor', 'JWT', array('file' => 'JWT' . DS . 'Authentication' . DS . 'JWT.php'));
        try {

            $token_config = Configure::read('App.token');
            $token_decode = JWT::decode($token, $token_config['secret']);

            $this->username = $token_decode->visitor->username;
            return $token_decode;
        } catch (Exception $ex) {

            return false;
        }
    }

    protected function setJsonHeader() {

        $this->autoRender = false;
        header('Content-Type: application/json');
    }

    protected function getRandArray($array) {

        $k = array_rand($array);
        $v = $array[$k];

        return $v;
    }

    /**
     * validateToken
     * thực hiện validate lại token của visitor, token được truyền vào header
     * 
     * @return object
     */
    protected function validateToken() {

        $token = $this->request->header('token');
        if (empty($token)) {

            $this->resError('#app004', array('config' => 'App'));
        }
        App::import('Vendor', 'JWT', array('file' => 'JWT' . DS . 'Authentication' . DS . 'JWT.php'));
        try {

            $token_config = Configure::read('App.token');
            $token_decode = JWT::decode($token, $token_config['secret']);

            // thực hiện check xem visitor có status = 2 hay không? tránh tình huống visitor bị ban vẫn có quyền thao tác
            $token_decode->Visitor = $this->validateVisitor($token_decode->visitor->id, $token);

            return $token_decode;
        } catch (Exception $ex) {

            // nếu token hết hạn
            if ($ex instanceof ExpiredException) {

                $this->resError('#vis008', array('config' => 'Visitors'));
            } else {

                $this->resError('#vis004', array('message_args' => $ex->getMessage(), 'config' => 'Visitors'));
            }
        }
    }

    /**
     * validateVisitor
     * thực hiện kiểm tra xem visitor có tồn tại trong hệ thống k? có trạng thái public k?
     * hay có bị thay đổi token k?
     * 
     * @param string $visitor_id
     * @param object $token
     */
    protected function validateVisitor($visitor_id, $token) {

        if (!isset($this->Visitor)) {

            $this->loadModel('Visitor');
        }

        $visitor = $this->Visitor->find('first', array(
            'conditions' => array(
                'id' => new MongoId($visitor_id),
            ),
        ));

        // check xem visitor có tồn tại hay không?
        if (empty($visitor)) {

            $this->resError('#app001', array('message_args' => $visitor_id, 'config' => 'App'));
        }

        // check xem trạng thái của visitor có đang là công khai hay không?
        if ($visitor['Visitor']['status'] != Configure::read('sysconfig.App.constants.STATUS_APPROVED')) {

            $this->resError('#app002', array('message_args' => $visitor_id, 'config' => 'App'));
        }

        // check lại luôn xem token có thay đổi không?
        if ($visitor['Visitor']['token'] != $token) {

            $this->resError('#app003', array('message_args' => $visitor_id, 'config' => 'App'));
        }

        return $visitor;
    }

    /**
     * detectGeoByVMSLSB
     * định vị trí của số điện thoại thông qua LSB của VMS
     * 
     * @param string $msisdn
     * @return \SimpleXMLElement
     */
    protected function detectGeoByVMSLSB($msisdn = null) {

        // nếu không truyền số điện thoại, tự động detect số điện thoại
        if (empty($msisdn)) {

            $msisdn = $this->detectMobile();
        }
        // nếu detect mà k được thì trả về false
        if (empty($msisdn)) {

            return false;
        }
        $vms_lsb = Configure::read('sysconfig.App.VMS.LSB');
        $xml_data = '<?xml version = "1.0" ?>
                <svc_init ver= "3.2.0">
                  <hdr ver="3.2.0">
                    <client>
                      <id>' . $vms_lsb['cp_id'] . '</id>
                      <serviceid>' . $vms_lsb['service_id'] . '</serviceid>
                    </client>
                    <requestor type= "MSISDN">
                      <id>' . $msisdn . '</id>
                    </requestor>
                  </hdr> 
                  <slir ver= "3.2.0" res_type= "SYNC">
                    <msid type= "MSISDN" enc= "ASC">' . $msisdn . '</msid>
                    <trans_id>20071123010203</trans_id>
                    <loc_type type= "CURRENT_OR_LAST" />
                    <eqop>
                      <resp_req type= "LOW_DELAY" />
                      <max_loc_age>5</max_loc_age>
                    </eqop>
                    <prio type= "NORMAL" />
                  </slir>
                </svc_init>';

//        App::uses('HttpSocket', 'Network/Http');
//        $HttpSocket = new HttpSocket(array(
//            'timeout' => 600,
//            'ssl_verify_peer' => false,
//            'ssl_verify_host' => false,
//            'ssl_allow_self_signed' => false,
//            'redirect' => true,
//        ));
//        $query_string = array(
//            'api_key' => $vms_lsb['api_key'],
//        );
//
//        $response = $HttpSocket->post($vms_lsb['api_url'] . '?' . http_build_query($query_string), $xml_data);
//        if (!$response->isOk()) {
//
//            $this->log('VMS LSB service was failed', 'vms_lsb_service');
//            $this->log($response, 'vms_lsb_service');
//            return false;
//        }
//
//        $this->log($response->body, 'vms_lsb_service');
//        die;

        App::import('Vendor', 'MyCurl', array('file' => 'MyCurl' . DS . 'MyCurl.php'));
        $ch = new MyCurl(array('api_key' => $vms_lsb['api_key']));

        // thiết lập timeout
        $ch->options['CURLOPT_CONNECTTIMEOUT'] = $vms_lsb['connect_timeout'];
        $ch->options['CURLOPT_TIMEOUT'] = $vms_lsb['timeout'];

        $response = $ch->post($vms_lsb['api_url'], $xml_data);

        if (!$response) {

            $this->log('VMS LSB service was failed', 'vms_lsb_service');
            $this->log('Xml data:', 'vms_lsb_service');
            $this->log($xml_data, 'vms_lsb_service');
            $this->log('Response:', 'vms_lsb_service');
            $this->log($response, 'vms_lsb_service');
            return false;
        }

        try {

            $geo = new SimpleXMLElement($response->body);
            $this->log($response->body, 'vms_lsb_service');
            $this->log($geo, 'vms_lsb_service');
        } catch (Exception $ex) {

            $this->log('The VMS LSB response is invalid XML format', 'vms_lsb_service');
            $this->log('raw response body:', 'vms_lsb_service');
            $this->log($response, 'vms_lsb_service');
            $this->log('error detail:', 'vms_lsb_service');
            $this->log($ex, 'vms_lsb_service');
            return false;
        }

        if (
                empty($geo->slia->pos->pd->shape->CircularArea->coord->Y) ||
                empty($geo->slia->pos->pd->shape->CircularArea->coord->X)
        ) {

            $this->log('Can not extract lng and lat from VMS LSB response', 'vms_lsb_service');
            $this->log($geo, 'vms_lsb_service');
            return false;
        }

        $results = array(
            'lng' => (float) $geo->slia->pos->pd->shape->CircularArea->coord->X,
            'lat' => (float) $geo->slia->pos->pd->shape->CircularArea->coord->Y,
        );

        return $results;
    }

    /**
     * getallheaders
     * lấy ra các thông số trong header request, chuyển thành cấu trúc array
     * 
     * @return array
     */
    protected function getallheaders() {

        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * standardizeMobile
     * chuyển số 0 ở đầu nếu có thành 84
     * 
     * @param string $raw_mobile
     * @return string
     */
    protected function standardizeMobile($raw_mobile) {

        $mobile = preg_replace('/^0/', '84', $raw_mobile);
        return $mobile;
    }

    /**
     * prettyMobile
     * làm đẹp số điện thoại, phù hợp với cách đọc của visitor
     * 
     * @param string $raw_mobile
     * @return string
     */
    protected function prettyMobile($raw_mobile) {

        $mobile = preg_replace('/^84/', '0', $raw_mobile);
        return $mobile;
    }

    protected function detectMobile() {

        $raw_headers = $this->getallheaders();
        $headers = array_change_key_case($raw_headers, CASE_LOWER);
        $msisdn = isset($headers['msisdn']) ? $headers['msisdn'] : null;

        return $msisdn;
    }

    /**
     * getParam
     * thực hiện lấy về param từ request bất chấp kiểu GET và POST
     * 
     * @param string $name
     * @return mixed
     */
    protected function getParam($name) {

        return isset($this->request->query[$name]) ?
                $this->request->query[$name] : (isset($this->request->data[$name]) ? $this->request->data[$name] : null);
    }

    // Begin TrungNQ
    /**
     * generateRandomLetters
     * thực tạo ra các kí tự ngẫu nhiên
     * 
     * @param int $length
     * @return string
     */
    public function generateRandomLetters($length) {

        $random = '';

        for ($i = 0; $i < $length; $i++) {

            $random .= chr(rand(ord('a'), ord('z')));
        }

        return $random;
    }

    public function validateMobile($mobileNumber) {

        $valid_number = false;
        if (preg_match('/^(84|0)(90|93|120|121|122|126|128)\d{7}$/', $mobileNumber, $matches)) {

            if ($matches[1] == 0) {

                $valid_number = preg_replace('/^0/', '84', $mobileNumber);
            } else {

                $valid_number = $mobileNumber;
            }
        }

        return $valid_number;
    }

    public function isAuthorized($user = null) {

        if (empty($user)) {

            return false;
        }

        return true;
    }

    /**
     * _flash
     * sử dụng cho việc hiện thị nhiều message trong Session setflash
     * @param string $message
     * @param string $type
     */
    public function _flash($message, $type = 'message') {
        $messages = (array) $this->Session->read('Message.multiFlash');
        $messages[] = array(
            'message' => $message,
            'element' => 'default',
            'params' => array('class' => $type),
        );
        $this->Session->write('Message.multiFlash', $messages);
    }

    // END TrungNQ
    // Begin Hoangnn
    // ================= WAP CHARGING =====================
    // Hàm nhúng
    public function aes128_ecb_encrypt($key, $data, $iv) {
        if (16 !== strlen($key))
            $key = hash('MD5', $key, true);
        if (16 !== strlen($iv))
            $iv = hash('MD5', $iv, true);
        $padding = 16 - (strlen($data) % 16);
        $data .= str_repeat(chr($padding), $padding);
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB, $iv);
    }

    public function aes256_ecb_encrypt($key, $data, $iv) {
        if (32 !== strlen($key))
            $key = hash('SHA256', $key, true);
        if (16 !== strlen($iv))
            $iv = hash('MD5', $iv, true);
        $padding = 16 - (strlen($data) % 16);
        $data .= str_repeat(chr($padding), $padding);
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB, $iv);
    }

    public function aes128_ecb_decrypt($key, $data, $iv) {
        if (16 !== strlen($key))
            $key = hash('MD5', $key, true);
        if (16 !== strlen($iv))
            $iv = hash('MD5', $iv, true);
        $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB, $iv);
        $padding = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padding);
    }

    public function aes256_ecb_decrypt($key, $data, $iv) {
        if (32 !== strlen($key))
            $key = hash('SHA256', $key, true);
        if (16 !== strlen($iv))
            $iv = hash('MD5', $iv, true);
        $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB, $iv);
        $padding = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padding);
    }

    // END ================= WAP CHARGING =====================
    // 
    // 

    /**
     * Lấy số giây từ thời điểm $mongoDate đến hiện tại
     * HoàngNN
     */
    protected function getSecondFromNowNMongoDate($mongoDate) {

        $time_start = $mongoDate->sec;
        $time_now = strtotime(date('Y-m-d H:i:s'));
        $time_remain = $time_now - $time_start;
        return $time_remain;
    }

    protected function getSecondCountDown($mongoDate) {

        $time_start = $mongoDate->sec;
        $time_now = strtotime(date('Y-m-d H:i:s'));
        $time_remain = ($time_start + 180) - $time_now;
        return $time_remain;
    }

    /**
     * Gửi MT
     * HoàngNN
     */
    protected function sendmt($phone, $content) {

        if (empty($this->service_sendmt)) {
            $this->service_sendmt = Configure::read('sysconfig.SmsSender.service_url');
        }
        $service_sendmt_url = $this->service_sendmt . "?to=$phone&text=" . urlencode($content);
        $this->logAnyFile("URL SEND MT: $service_sendmt_url", __CLASS__ . '_' . __FUNCTION__);

        $result = file_get_contents($service_sendmt_url);

        $this->logAnyFile("SEND MT: phone($phone), content: $content", __CLASS__ . '_' . __FUNCTION__);

        return $result;
    }

    public $CHARGE_OK = "CPS-0000";
    public $CHARGE_NOK_NO_MORE_CREDIT_AVAILABLE = "CPS-1001";

    /**
     * Gửi MT
     * HoàngNN
     */
    protected function charge($phone, $amount) {

        if (empty($this->arr_charge_info)) {
            $this->arr_charge_info = Configure::read('sysconfig.ChargingVMS');
        }
        $charge_url = sprintf($this->arr_charge_info['diameter'], $phone, $amount);
        $result = file_get_contents($charge_url);

        return ['charge_url' => $charge_url, 'result' => trim($result)];
    }

    protected function initResponseText() {

        $this->layout = null;
        $this->autoRender = false;
        header('Content-Type: text/html');
    }

    public function getWeek($year, $mon, $day) {
        $date_string = $year . "-" . $mon . "-" . $day;
        $date = new DateTime($date_string);

        return ((int) $date->format("W"));
    }

    function isWeekend($date) {
        $weekDay = date('w', strtotime($date));
        return ($weekDay == 0 || $weekDay == 6);
    }

    function isNewWeek() {
        $weekDay = date('w', strtotime(date()));
        return ($weekDay == 1);
    }

    /**
     * Kiểm tra xem có phải ngày mới không, 
     * nếu là ngày mới thì reset Player trước khi xử lý cú pháp
     */
    protected function checkNewDayAndResetPlayer(&$player) {

        $arr_player_update = [
            'id' => $player['id'],
            'time_last_action' => new MongoDate(),
        ];
        if (date('Ymd', $player['time_last_action']->sec) < date('Ymd')) {
            $arr_player_update['score_day'] = $player['score_day'] = ["score" => 0, "time" => 0];
            $arr_player_update['num_questions'] = $player['num_questions'] = [];
            $arr_player_update['count_group_aday'] = $player['count_group_aday'] = 0;
            $arr_player_update['question_group'] = $player['question_group'] = null;
            $arr_player_update['num_questions_pending'] = $player['num_questions_pending'] = null;

            $new_week = (date("w") == 1);
            $new_month = (date("d") == 1);

            if ($new_week) {
                $player['score_week'] = $arr_player_update['score_week'] = ["score" => 0, "time" => 0];
            }
            if ($new_month) {
                $player['score_month'] = $arr_player_update['score_month'] = ["score" => 0, "time" => 0];
            }
        }

        return $arr_player_update;
    }

    // END Hoangnn
    //Ungnv
    public function beforeFilter() {
        parent::beforeFilter();

        $key = $this->request->query('key');
        if (!empty($key)) {

            $this->Session->write('agency_code', $key);
        }

        // thực hiện tự động lưu vết
        if ($this->auto_trace) {

            $options = array(
                'service_code' => 'GAMEQUIZ',
            );
            $this->TrackingAccessCommon->setWapOpts($options);

            $save_data = array();
            $this->TrackingAccessCommon->trace($save_data, $options);
        }
    }

    public function unicodeToNCRDecimal($str) {

        $convmap = array(
            0xe1, 0xe1, 0x0, 0xe1, 0xc1, 0xc1, 0x0, 0xc1, 0xe0, 0xe0, 0x0, 0xe0,
            0xc0, 0xc0, 0x0, 0xc0, 0x1ea3, 0x1ea3, 0x0, 0x1ea3, 0x1ea2, 0x1ea2, 0x0, 0x1ea2,
            0xe3, 0xe3, 0x0, 0xe3, 0xc3, 0xc3, 0x0, 0xc3, 0x1ea1, 0x1ea1, 0x0, 0x1ea1,
            0x1ea0, 0x1ea0, 0x0, 0x1ea0, 0x103, 0x103, 0x0, 0x103, 0x102, 0x102, 0x0, 0x102,
            0x1eaf, 0x1eaf, 0x0, 0x1eaf, 0x1eae, 0x1eae, 0x0, 0x1eae, 0x1eb1, 0x1eb1, 0x0, 0x1eb1,
            0x1eb0, 0x1eb0, 0x0, 0x1eb0, 0x1eb3, 0x1eb3, 0x0, 0x1eb3, 0x1eb2, 0x1eb2, 0x0, 0x1eb2,
            0x1eb5, 0x1eb5, 0x0, 0x1eb5, 0x1eb4, 0x1eb4, 0x0, 0x1eb4, 0x1eb7, 0x1eb7, 0x0, 0x1eb7,
            0x1eb6, 0x1eb6, 0x0, 0x1eb6, 0xe2, 0xe2, 0x0, 0xe2, 0xc2, 0xc2, 0x0, 0xc2,
            0x1ea5, 0x1ea5, 0x0, 0x1ea5, 0x1ea4, 0x1ea4, 0x0, 0x1ea4, 0x1ea7, 0x1ea7, 0x0, 0x1ea7,
            0x1ea6, 0x1ea6, 0x0, 0x1ea6, 0x1ea9, 0x1ea9, 0x0, 0x1ea9, 0x1ea8, 0x1ea8, 0x0, 0x1ea8,
            0x1eab, 0x1eab, 0x0, 0x1eab, 0x1eaa, 0x1eaa, 0x0, 0x1eaa, 0x1ead, 0x1ead, 0x0, 0x1ead,
            0x1eac, 0x1eac, 0x0, 0x1eac, 0xe9, 0xe9, 0x0, 0xe9, 0xc9, 0xc9, 0x0, 0xc9,
            0xe8, 0xe8, 0x0, 0xe8, 0xc8, 0xc8, 0x0, 0xc8, 0x1ebb, 0x1ebb, 0x0, 0x1ebb,
            0x1eba, 0x1eba, 0x0, 0x1eba, 0x1ebd, 0x1ebd, 0x0, 0x1ebd, 0x1ebc, 0x1ebc, 0x0, 0x1ebc,
            0x1eb9, 0x1eb9, 0x0, 0x1eb9, 0x1eb8, 0x1eb8, 0x0, 0x1eb8, 0xea, 0xea, 0x0, 0xea,
            0xca, 0xca, 0x0, 0xca, 0x1ebf, 0x1ebf, 0x0, 0x1ebf, 0x1ebe, 0x1ebe, 0x0, 0x1ebe,
            0x1ec1, 0x1ec1, 0x0, 0x1ec1, 0x1ec0, 0x1ec0, 0x0, 0x1ec0, 0x1ec3, 0x1ec3, 0x0, 0x1ec3,
            0x1ec2, 0x1ec2, 0x0, 0x1ec2, 0x1ec5, 0x1ec5, 0x0, 0x1ec5, 0x1ec4, 0x1ec4, 0x0, 0x1ec4,
            0x1ec7, 0x1ec7, 0x0, 0x1ec7, 0x1ec6, 0x1ec6, 0x0, 0x1ec6, 0xed, 0xed, 0x0, 0xed,
            0xcd, 0xcd, 0x0, 0xcd, 0xec, 0xec, 0x0, 0xec, 0xcc, 0xcc, 0x0, 0xcc,
            0x1ec9, 0x1ec9, 0x0, 0x1ec9, 0x1ec8, 0x1ec8, 0x0, 0x1ec8, 0x128, 0x128, 0x0, 0x128,
            0x129, 0x129, 0x0, 0x129, 0x1ecb, 0x1ecb, 0x0, 0x1ecb, 0x1eca, 0x1eca, 0x0, 0x1eca,
            0xf3, 0xf3, 0x0, 0xf3, 0xd3, 0xd3, 0x0, 0xd3, 0xf2, 0xf2, 0x0, 0xf2,
            0xd2, 0xd2, 0x0, 0xd2, 0x1ecf, 0x1ecf, 0x0, 0x1ecf, 0x1ece, 0x1ece, 0x0, 0x1ece,
            0xf5, 0xf5, 0x0, 0xf5, 0xd5, 0xd5, 0x0, 0xd5, 0x1ecd, 0x1ecd, 0x0, 0x1ecd,
            0x1ecc, 0x1ecc, 0x0, 0x1ecc, 0x1a1, 0x1a1, 0x0, 0x1a1, 0x1a0, 0x1a0, 0x0, 0x1a0,
            0x1edb, 0x1edb, 0x0, 0x1edb, 0x1eda, 0x1eda, 0x0, 0x1eda, 0x1edd, 0x1edd, 0x0, 0x1edd,
            0x1edc, 0x1edc, 0x0, 0x1edc, 0x1edf, 0x1edf, 0x0, 0x1edf, 0x1ede, 0x1ede, 0x0, 0x1ede,
            0x1ee1, 0x1ee1, 0x0, 0x1ee1, 0x1ee0, 0x1ee0, 0x0, 0x1ee0, 0x1ee3, 0x1ee3, 0x0, 0x1ee3,
            0x1ee2, 0x1ee2, 0x0, 0x1ee2, 0xf4, 0xf4, 0x0, 0xf4, 0xd4, 0xd4, 0x0, 0xd4,
            0x1ed1, 0x1ed1, 0x0, 0x1ed1, 0x1ed0, 0x1ed0, 0x0, 0x1ed0, 0x1ed3, 0x1ed3, 0x0, 0x1ed3,
            0x1ed2, 0x1ed2, 0x0, 0x1ed2, 0x1ed5, 0x1ed5, 0x0, 0x1ed5, 0x1ed4, 0x1ed4, 0x0, 0x1ed4,
            0x1ed7, 0x1ed7, 0x0, 0x1ed7, 0x1ed6, 0x1ed6, 0x0, 0x1ed6, 0x1ed9, 0x1ed9, 0x0, 0x1ed9,
            0x1ed8, 0x1ed8, 0x0, 0x1ed8, 0xfa, 0xfa, 0x0, 0xfa, 0xda, 0xda, 0x0, 0xda,
            0xf9, 0xf9, 0x0, 0xf9, 0xd9, 0xd9, 0x0, 0xd9, 0x1ee7, 0x1ee7, 0x0, 0x1ee7,
            0x1ee6, 0x1ee6, 0x0, 0x1ee6, 0x169, 0x169, 0x0, 0x169, 0x168, 0x168, 0x0, 0x168,
            0x1ee5, 0x1ee5, 0x0, 0x1ee5, 0x1ee4, 0x1ee4, 0x0, 0x1ee4, 0x1b0, 0x1b0, 0x0, 0x1b0,
            0x1af, 0x1af, 0x0, 0x1af, 0x1ee9, 0x1ee9, 0x0, 0x1ee9, 0x1ee8, 0x1ee8, 0x0, 0x1ee8,
            0x1eeb, 0x1eeb, 0x0, 0x1eeb, 0x1eea, 0x1eea, 0x0, 0x1eea, 0x1eed, 0x1eed, 0x0, 0x1eed,
            0x1eec, 0x1eec, 0x0, 0x1eec, 0x1eef, 0x1eef, 0x0, 0x1eef, 0x1eee, 0x1eee, 0x0, 0x1eee,
            0x1ef1, 0x1ef1, 0x0, 0x1ef1, 0x1ef0, 0x1ef0, 0x0, 0x1ef0, 0xfd, 0xfd, 0x0, 0xfd,
            0xdd, 0xdd, 0x0, 0xdd, 0x1ef3, 0x1ef3, 0x0, 0x1ef3, 0x1ef2, 0x1ef2, 0x0, 0x1ef2,
            0x1ef7, 0x1ef7, 0x0, 0x1ef7, 0x1ef6, 0x1ef6, 0x0, 0x1ef6, 0x1ef9, 0x1ef9, 0x0, 0x1ef9,
            0x1ef8, 0x1ef8, 0x0, 0x1ef8, 0x1ef5, 0x1ef5, 0x0, 0x1ef5, 0x1ef4, 0x1ef4, 0x0, 0x1ef4,
            0x111, 0x111, 0x0, 0x111, 0x110, 0x110, 0x0, 0x110,
        );

        return mb_encode_numericentity($str, $convmap, "UTF-8");
    }

    public function unicodeToMobiNCRDecimal($raw_str) {

        $str = $this->unicodeToNCRDecimal($raw_str);
        return str_replace('&#', '##', $str);
    }

    //end Ungnv
}
