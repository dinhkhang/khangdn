<?php

class WeatherController extends AppController {

    public $uses = array(
        'Weather',
        'WeatherDescription',
        'Region',
        'SplashScreen',
        'Slogan',
    );
    public $components = array(
        'TrackingLogCommon',
    );
    public $debug_mode = 3;

    public function getByRegion() {

        $this->setInit();

//        $this->TrackingLogCommon->logAction('access', 'splash');

        $region = $this->detectRegionId(array(
            'object_return' => 1,
        ));
        $res = array(
            'status' => 'success',
            'data' => array(),
        );

        // nếu không xác định được $region_id thì trả về $region_id mặc định
        if (empty($region)) {

            // thực hiện gọi LBS để lấy về vị trí của visitor
            $visitor_geo = $this->detectGeoByVMSLSB();
            // nếu vẫn k xác định được vị trí của visitor mặc dù đã detect qua LSB
            if (empty($visitor_geo)) {

                $get_region = $this->getDefaultRegion();
                $region = $get_region['Region'];
                $res['data']['user_region_id'] = null;
                $res['data']['user_lng'] = null;
                $res['data']['user_lat'] = null;
                $region_id = $region['id'];
            }
            // nếu detect được vị trí của visitor, thực gọi google service để lấy về user_region)id
            else {

                $get_region = $this->findUserRegion($visitor_geo['lat'], $visitor_geo['lng']);
                // nếu không lấy được user_region_id
                if (empty($get_region)) {

                    $get_region = $this->getDefaultRegion();
                    $region = $get_region['Region'];
                    $res['data']['user_region_id'] = null;
                    $res['data']['user_lng'] = $visitor_geo['lng'];
                    $res['data']['user_lat'] = $visitor_geo['lat'];
                    $region_id = $region['id'];
                }
                // nếu lấy được user_region_id
                else {

                    $region = $get_region['Region'];
                    $res['data']['user_region_id'] = $region['id'];
                    $res['data']['user_lng'] = $visitor_geo['lng'];
                    $res['data']['user_lat'] = $visitor_geo['lat'];
                    $region_id = $region['id'];
                }
            }
        }
        // nếu đã xác định được region thì gán user_region_id chính bằng region id này
        else {

            $region_id = $region['id'];
            $res['data']['user_region_id'] = $region_id;
            $res['data']['user_lng'] = $this->lng;
            $res['data']['user_lat'] = $this->lat;
        }

        $res['data']['region'] = array(
            'id' => $region['id'],
            'name' => $region['name'],
            'loc' => $region['loc'],
        );

        $this->Session->write('Visitor.location', array(
            'lat' => $this->lat,
            'lng' => $this->lng,
            'user_region_id' => $res['data']['user_region_id'],
            'region' => $res['data']['region'],
            'region_id' => $res['data']['region']['id'],
        ));

        // lấy ra arr_img_bg (ảnh splash screen)
        $splash_screens = $this->SplashScreen->find('all', array(
            'conditions' => array(
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
            ),
            'order' => array(
                'modified' => 'DESC',
            ),
        ));
        $res['data']['arr_img_bg'] = array();
        if (!empty($splash_screens)) {

            foreach ($splash_screens as $splash) {

                $splash_img = $this->getFileUris($splash['SplashScreen'], 'splash');
                if (empty($splash_img)) {

                    continue;
                }
                $res['data']['arr_img_bg'][] = $splash_img;
            }

            // lấy arr_img_bg_version để client so sánh xem có cần update arr_img_bg mới về không?
            $res['data']['arr_img_bg_version'] = $splash_screens[0]['SplashScreen']['modified']->sec;
        }

        // lấy về slogan
        $slogan = $this->Slogan->find('first', array(
            'conditions' => array(
                'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
            ),
            'order' => array(
                'modified' => 'DESC',
            ),
        ));
        $res['data']['slogan_img'] = "";
        $res['data']['slogan_text'] = "";
        $res['data']['slogan_version'] = "";
        if (!empty($slogan)) {

            $res['data']['slogan_img'] = $this->getFileUris($slogan['Slogan'], 'slogan');
            $res['data']['slogan_text'] = $slogan['Slogan']['name'];
            $res['data']['slogan_version'] = $slogan['Slogan']['modified']->sec;
        }

        $options = array(
            'conditions' => array(
                'region' => new MongoId($region_id),
            ),
        );

        // lấy ra thông tin weather
        $get_weather = $this->Weather->find('first', $options);
        if (empty($get_weather) || empty($get_weather['Weather']['informations'])) {

            $res['data']['arr_weather'] = array();
            $res['data']['current'] = array();
            $this->resSuccess($res);
        }

        $weather_infos = $get_weather['Weather']['informations'];
        $weather_descriptions = array();
        foreach ($weather_infos as $v) {

            $code = $v['weather_description_code'];
            if (empty($weather_descriptions[$code])) {

                $weather_descriptions[$code] = $this->getWeatherDescription($code);
            }
            $info = array(
                'temperature_max' => $v['temperature_max'],
                'temperature_min' => $v['temperature_min'],
                'weather_description_code' => $code,
                'date_affected' => $v['date_affected'],
                'icon' => $weather_descriptions[$code]['icon'],
                'icon_d' => $weather_descriptions[$code]['icon_d'],
                'content' => $weather_descriptions[$code]['content'],
            );
            $res['data']['arr_weather'][] = $info;
        }

        $res['data']['current'] = array();
        // lấy ra current weather
        if (
                !empty($get_weather['Weather']['current']['temperature']) &&
                !empty($get_weather['Weather']['current']['weather_description_code'])
        ) {

            $res['data']['current']['temperature'] = $get_weather['Weather']['current']['temperature'];
            $code = $get_weather['Weather']['current']['weather_description_code'];
            if (empty($weather_descriptions[$code])) {

                $weather_descriptions[$code] = $this->getWeatherDescription($code);
            }
            $res['data']['current']['weather_description_code'] = $code;
            $res['data']['current']['icon'] = $weather_descriptions[$code]['icon'];
            $res['data']['current']['icon_d'] = $weather_descriptions[$code]['icon_d'];
            $res['data']['current']['content'] = $weather_descriptions[$code]['content'];
        }

        $this->resSuccess($res);
    }

    protected function getDefaultRegion() {

        $region_id = Configure::read('sysconfig.Weather.DEFAULT_REGION_ID');
        $get_region = $this->Region->find('first', array(
            'conditions' => array(
                'id' => new MongoId($region_id),
            ),
        ));

        if (empty($get_region)) {

            $this->resError('#wea001', array('message_args' => array($region_id)));
        }

        return $get_region;
    }

    protected function getWeatherDescription($code, $lang_code = null) {

        $weather_description = array(
            'icon' => '',
            'icon_d' => '',
            'content' => '',
        );
        $get_weather_description = $this->WeatherDescription->find('first', array(
            'conditions' => array(
                'code' => new MongoRegex("/^" . $code . "$/"),
            ),
        ));
        if (empty($get_weather_description) || empty($get_weather_description['WeatherDescription']['datas'])) {

            return $weather_description;
        }
        $datas = $get_weather_description['WeatherDescription']['datas'];
        if (empty($lang_code)) {

            $data = $datas[0];
        } else {

            foreach ($datas as $v) {

                if ($v['lang_code'] == $lang_code) {

                    $data = $v;
                    break;
                }
            }
        }
        if (empty($data)) {

            return $weather_description;
        }
        $weather_description['content'] = $data['content'];
        $weather_description['icon'] = $this->getFileUris($get_weather_description['WeatherDescription'], 'icon');
        $weather_description['icon_d'] = $this->getFileUris($get_weather_description['WeatherDescription'], 'icon_d');

        return $weather_description;
    }

    //END API HoangNN
}
