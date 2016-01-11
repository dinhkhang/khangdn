<?php

$config['sysconfig'] = array(
    'App' => array(
        'status' => array(
            -1 => __('status_rejected'),
            0 => __('status_hidden'),
            1 => __('status_wait_review'),
            2 => __('status_approved'),
        ),
        'constants' => array(
            'STATUS_APPROVED' => 2,
            'STATUS_ACTIVE' => 1,
            'STATUS_WAIT_REVIEW' => 1,
            'LANG_CODE' => 'vi',
            'CURRENCY_CODE' => 'VND',
            'DISTANCE' => 2, // 2 km
            'STATUS_HIDDEN' => 0,
            'STATUS_REGISTER' => 0,
        ),
        'GeoJSON_type' => 'Point',
        'token' => array(
            'ttl' => array(
                'short' => '+1 month',
                'long' => '+2 months',
            ),
            'secret' => 'trungpolit',
            'iss' => 'hallovn',
            'aud' => 'hallovn',
        ),
        'authorize' => array(
            'username_fields' => array(// các trường fields dùng để đăng nhập tương đương username
                'username',
                'email',
                'mobile',
            ),
        ),
        'VMS' => array(
            'LSB' => array(
                'api_key' => '123456',
                'api_url' => 'http://10.151.22.94:5000/',
                'cp_id' => 10014,
                'service_id' => 1000211,
                'timeout' => 3,
                'connect_timeout' => 3,
            ),
        ),
        'name' => 'GameQuiz',
    ),
    'Console' => array(
        'FORCE_READ_ALL_TAG' => 0,
    ),
    'Common' => array(
//        'URL_BASE_FILE_MANAGER' => 'http://124.158.5.134:8082/', //mã id của PLACES_CATEGORY để lấy data cho slide màn hình ACTIVITY
//        'URL_THUMB_VIDEO_DEFAULT' => 'http://124.158.5.134:8082/img/play.png', //mã id của PLACES_CATEGORY để lấy data cho slide màn hình ACTIVITY
        'URL_BASE_FILE_MANAGER' => 'http://cms.hallovietnam.vn:8080/', //mã id của PLACES_CATEGORY để lấy data cho slide màn hình ACTIVITY
        'URL_THUMB_VIDEO_DEFAULT' => 'http://cms.hallovietnam.vn:8080/img/play.png', //mã id của PLACES_CATEGORY để lấy data cho slide màn hình ACTIVITY
        'ACTIVITY_SLIDE_NUMBER' => 4,
        'STATUS_APPROVED' => 2,
        'SUCCESS_CODE' => 200,
        'FAIL_CODE' => 403,
        'WRONG_CODE' => 404,
        'TOKEN_KEY' => 'http://hivietnam.vn',
        'TOKEN_ISS' => 'http://hivietnam.vn',
        'TOKEN_AUD' => 'http://hivietnam.vn',
        'STATUS_SUCCESS_MSG' => 'Thực hiện thành công!',
        'STATUS_FAIL_MSG' => 'Dữ liệu không tồn tại hoặc đã bị xóa!',
        'STATUS_SUCCESS' => 'success',
        'STATUS_FAIL' => 'fail',
        'SCREEN_CODE' => [
            "home" => "home",
            "nearby" => "nearby",
            "search" => "search",
            "region_home" => "region_home",
            "region_search" => "region_search",
            "region_list" => "region_list",
            "region_listbycate" => "region_listbycate",
            "region_info" => "region_info",
            "place_home" => "place_home",
            "place_search" => "place_search",
            "place_list" => "place_list",
            "place_listbycate" => "place_listbycate",
            "place_info" => "place_info",
            "place_top100" => "place_top100",
            "hotel_home" => "hotel_home",
            "hotel_search" => "hotel_search",
            "hotel_list" => "hotel_list",
            "hotel_listbycate" => "hotel_listbycate",
            "hotel_info" => "hotel_info",
            "restaurant_home" => "restaurant_home",
            "restaurant_search" => "restaurant_search",
            "restaurant_list" => "restaurant_list",
            "restaurant_listbycate" => "restaurant_listbycate",
            "restaurant_info" => "restaurant_info",
            "tour_home" => "tour_home",
            "tour_search" => "tour_search",
            "tour_list" => "tour_list",
            "tour_listbycate" => "tour_listbycate",
            "tour_info" => "tour_info",
            "event_home" => "event_home",
            "event_search" => "event_search",
            "event_list" => "event_list",
            "event_listbycate" => "event_listbycate",
            "event_info" => "event_info",
            "transport_list" => "transport_list",
            "taxi_list" => "taxi_list",
            "utility_list" => "utility_list",
            "bank_list" => "bank_list",
            "hospital_list" => "hospital_list",
            "emergency_list" => "emergency_list",
            "traffic_home" => "traffic_home",
            "traffic_list" => "traffic_list",
            "traffic_info" => "traffic_info",
            "environment_home" => "environment_home",
            "environment_list" => "environment_list",
            "environment_info" => "environment_info",
            "music_home" => "music_home",
            "music_list" => "music_list",
            "music_info" => "music_info",
            "quiz_home" => "quiz_home",
            "quiz_play" => "quiz_play",
        ],
    ),
    // HoangNN
//PLACE
    'ScreenPlaceHome' => array(
        'LIST_CATEGORY_ID' => [
            ['id' => '557ea3cd887b66de54112fab', 'name' => 'Địa điểm đề xuất'],
        ],
        'LIMIT_LIST' => 5,
    ),
    'ScreenPlaceList' => array(
        'PLACE_OBJECT_TYPE_ID' => '5550246fa37d73a40b000029',
    ),
    'ScreenPlaceInfo' => array(
        'PLACE_OBJECT_TYPE_ID' => '5550246fa37d73a40b000029',
        'LIST_OBJECT_TYPE' => [
            ['type' => 'places', 'name' => 'Địa điểm xung quanh'],
        ],
        'DISTANCE_AROUND' => 10000, //xung quanh(mét) 
    ),
    //HOTEL
    'ScreenHotelHome' => array(
        'LIST_CATEGORY_ID' => [
            ['id' => '557ea4b1887b66da54112fa4', 'name' => 'Khách sạn đề xuất'],
        ],
        'LIMIT_LIST' => 5,
    ),
    'ScreenHotelList' => array(
        'HOTEL_OBJECT_TYPE_ID' => '55502527a37d739c0b000029',
    ),
    'ScreenHotelInfo' => array(
        'HOTEL_OBJECT_TYPE_ID' => '55502527a37d739c0b000029',
        'DISTANCE_AROUND' => 2000, //xung quanh(mét) 
    ),
    //RESTAURANT
    'ScreenRestaurantHome' => array(
        'LIST_CATEGORY_ID' => [
//['id' => '555d57dfc4a1608d6e9a1ac2', 'name' => 'Ethnic restaurant'],
            ['id' => '557f90d0887b66eb54112fbe', 'name' => 'Nhà hàng đề xuất'],
        ],
        'LIMIT_LIST' => 5,
    ),
    'ScreenRestaurantList' => array(
        'RESTAURANT_OBJECT_TYPE_ID' => '555024fca37d738c0b000029',
    ),
    'ScreenRestaurantInfo' => array(
        'RESTAURANT_OBJECT_TYPE_ID' => '555024fca37d738c0b000029',
        'DISTANCE_AROUND' => 2000, //xung quanh(mét) 
    ),
    //REGION 
    'ScreenRegionList' => array(
        'REGION_OBJECT_TYPE_ID' => '55502645a37d739c0b00002b', // 
    ),
    'ScreenRegionHome' => array(//Địa danh Home
        'LIST_CATEGORY_ID' => [
            ['id' => '557e4782189dc030646cb6ec', 'name' => 'Địa điểm đề xuất'],
        ],
        'LIMIT_LIST' => 5,
    ),
    'ScreenRegionInfo' => array(
        'REGION_OBJECT_TYPE_ID' => '55502645a37d739c0b00002b',
        'DISTANCE_AROUND' => 10000, //xung quanh(mét) 
    ),
    //Tour
    'ScreenTourHome' => array(
        'LIST_CATEGORY_ID' => [
            ['id' => '5562cc2f189dc00880da13e5', 'name' => 'Tour đề xuất'], //tour đề xuất
        ],
        'LIMIT_LIST' => 5,
        'DEFAULT_PRICE_CODE' => 'package_default', //mã gói tour mặc định
    ),
    'ScreenTourList' => array(
        'TOUR_OBJECT_TYPE_ID' => '5554123bc4a160c896cf05f0',
    ),
    'ScreenTourInfo' => array(
        'TOUR_OBJECT_TYPE_ID' => '5554123bc4a160c896cf05f0',
        'DISTANCE_AROUND' => 2000, //xung quanh(mét) 
    ),
    //Event
    'ScreenEventHome' => array(
        'LIST_CATEGORY_ID' => [
            ['id' => '557fa803887b66eb54112fd4', 'name' => 'Sự kiện đề xuất'], //tour hot
        ],
        'LIMIT_LIST' => 5,
    ),
    'ScreenEventList' => array(
        'EVENT_OBJECT_TYPE_ID' => '5554125fc4a1604296cf05ef',
    ),
    'ScreenEventInfo' => array(
        'EVENT_OBJECT_TYPE_ID' => '5554125fc4a1604296cf05ef',
        'DISTANCE_AROUND' => 2000, //xung quanh(mét) 
    ),
    //STREAMING_SERVER
    'STREAMING_SERVER' => array(
        'HTTP' => 'http://streaming.mplace.vn',
        'RTSP' => 'rtsp://streaming.mplace.vn:1935',
        'HLS' => 'http://streaming.mplace.vn:1935',
    ),
    'OS' => array(
        'ANDROID' => 'ANDROID',
        'IOS' => 'IOS',
        'WINDOWPHONE' => 'WINDOWPHONE',
        'SYMBIAN' => 'SYMBIAN',
    ),
    // End HoangNN
// Begin TrungNQ
    'ScreenWeather' => array(
        'DEFAULT_REGION_ID' => '5555bee2c4a1608e6e9a1610',
    ),
    'Favorites' => array(
        'default_status' => 2,
        'default_count' => 1,
        'actions' => array(
            'set', 'unset',
        ),
    ),
    'Tags' => array(
        'tags_client_file_uri' => 'http://cms.hallovietnam.vn:8080/data_files/tags_client_files/',
        'tags_client_file_name' => 'tags_client.db',
        'tags_client_file_zip' => 'tags_client.zip',
        'tags_client_force_download' => 0,
        'tags_client_max_change' => 1000,
    ),
    'SmsSender' => array(
        'api_key' => '5dc2eb7f9d609aeedb6f302560f48cc820649c1a',
        'service_url' => 'http://10.54.3.181:9091/cgi-bin/sendsms',
        'password_length' => 4,
        'status' => 1,
        'get_password_content' => 'Mat khau de su dung dich vu HaloVietNam la %s. Ban su dung mat khau nay cung so dien thoai %s de dang nhap va su dung dich vu.',
        'get_password_action' => 'GET_PASSWORD',
        'reset_password_content' => 'Mat khau de su dung dich vu HaloVietNam la %s. Ban su dung mat khau nay cung so dien thoai %s de dang nhap va su dung dich vu.',
        'reset_password_action' => 'RESET_PASSWORD',
        'sms_hello' => 'Xin chao ',
        'sms_conten_2' => '. Ban dang co ',
    ),
    'SERVICE_CODE' => array(
        'GAME_QUIZ' => 'GAME_QUIZ'
    ),
    'Players' => array(
        'STATUS' => [
            'CANCEL' => 0,
            'REGISTER' => 1,
            'NOT_CHARGE' => 2,
        ],
        'ACTION' => [
            'DANG_KY' => 'DANG_KY',
            'HUY' => 'HUY',
            'MUA' => 'MUA',
            'TRA_LOI' => 'TRA_LOI',
            'CHOI' => 'CHOI',
            'BO_QUA' => 'BO_QUA', //TIẾP
            'CHUYEN' => 'CHUYEN',
            'HUONG_DAN' => 'HUONG_DAN',
            'XEM_KET_QUA' => 'XEM_KET_QUA',
            'GIA_HAN' => 'GIA_HAN',
            'KHAC' => 'KHAC',
            'CANH_BAO_BLACKLIST' => 'CANH_BAO_BLACKLIST',
        ],
        'TTL_ANSWER' => '+3 minutes',
    ),
    'SEQ_TBL_KEY' => array(
        'seq_tbl_charge' => 'seq_tbl_charge',
    ),
    'ChargingVMS' => array(
        'key' => '9ABxlwWpn6mGzdlU',
        'sp_id' => '035',
        //ungnv 27/11 set giá = 0
        'MUA_price' => 2000,
        'G1_price' => 2000,
        'G7_price' => 9000,
        /* 'MUA_price' => 2000,
          'G1_price' => 2000,
          'G7_price' => 9000, */
        //end ungnv 27/11 set giá = 0
        'RESULT_CHARGE_OK' => "CPS-0000",
        'RESULT_CHARGE_NOK' => "CPS-1001",
        'url_charge' => "http://dangky.mobifone.com.vn/wap/html/sp/confirm.jsp",
        'url_return' => "http://124.158.5.134:8083/Players/registresult",
        'diameter' => 'http://10.54.3.181:8002/diameter/charge?msisdn=%s&amount=%s',
//        'url_charge' => "http://localhost/2vietnam_wap/Tests/fakeMobifoneGateWay?trans_id=%s&msisdn=%s&status=%s",
        'diameter_test' => 'http://localhost/2vietnam_wap/Tests/fakeMobifoneCharge?code=%s',
        'G1_free1day_information' => '2,000đ/1 ngày||Miễn phí 1 ngày', // nội dung hiện thị trang wap của mobi với trường hợp thuê bao đăng ký lần đầu tiên
        'G7_free1day_information' => '9,000đ/7 ngày||Miễn phí 1 ngày', // nội dung hiện thị trang wap của mobi với trường hợp thuê bao đăng ký lần đầu tiên
        'G1_information' => '1 ngày', // nội dung hiện thị trang wap của mobi với trường hợp thuê bao đăng ký lần 2 trở đi
        'G7_information' => '7 ngày', // nội dung hiện thị trang wap của mobi với trường hợp thuê bao đăng ký lần 2 trở đi
    ),
    'Visitors' => array(
        'time_lock_reset_password' => 5,
        'default_notification_group_id' => '558ab9dc189dc030646cb6f1',
        'wrong_password_title' => 'SAI MẬT KHẨU',
        'wrong_password_content' => 'Nhập số điện thoại và chọn Nhận mật khẩu qua tin nhắn để lấy lại mật khẩu',
//        'wrong_mobile_title' => 'Số điện thoại không phải là Mobifone hoặc không đúng định dạng',
//        'wrong_mobile_content' => 'Dich vu chi danh cho thue bao Mobifone. De duoc huong dan su dung dich vu Vuot Dinh Pan Xi Phang va tham gia tranh THUONG.... DONG moi tuan, soan HD PAN gui 9144, DT ho tro 9090 (200d/phut)',
        'wrong_mobile_title' => '',
        //'wrong_mobile_content' => 'Sai định dạng thuê bao Mobifone',
        'wrong_mobile_content' => 'Dich vu chi danh cho thue bao Mobifone. De duoc huong dan su dung dich vu Vuot Dinh Pan Xi Phang va tham gia tranh THUONG 10.000.000 DONG moi thang, soan HD PAN gui 9144, DT ho tro 9090 (200d/phut)',
//        'unregister_title' => 'Số điện thoại là Mobifone nhưng chưa đăng ký dịch vụ',
//        'unregister_content' => 'Ban chua dang ky dich vu Vuot Dinh Pan Xi Phang. De dang ky soan DK<Tengoi> gui 9144 (Tengoi : G1, G7) Ban co co hoi tranh giai thuong len den... dong. Chi tiet vui long soan HD PAN gui 9144 hoac LH 9090 (200d/phut). Tran trong.',
        'unregister_title' => '',
        'unregister_content' => 'Bạn chưa đăng ký dịch vụ <strong>Vượt Đỉnh Pan Xi Phang</strong>. Để đăng ký soạn <strong>DK<Tengoi></strong> gửi <strong>9144</strong> (Tengoi : G1, G7). Bạn có cô hội tranh giải thưởng lên đến ... đồng. Chi tiết vui lòng soạn <strong>HD PAN</strong> gửi <strong>9144</strong> hoặc LH 9090 (200đ/phút). Trân trọng.',
        'not_yet_register_title' => 'BẠN CHƯA ĐĂNG KÝ DỊCH VỤ',
        'not_yet_register_content' => 'Để đăng ký soạn DK<Tengoi> gửi 9144 (Tengoi : G1, G7). Chi tiết vui lòng soạn HD PAN gửi 9144 hoặc LH 9090 (200d/phút).',
        'error_system_title' => 'Hệ thống lỗi',
        'error_system_content' => 'Hiện tại hệ thống đang gặp sự cố.',
        'empty_mobile_title' => '',
        'empty_mobile_content' => 'Hãy nhập vào số điện thoại',
        'empty_password_title' => '',
        'empty_password_content' => 'Hãy nhập vào mật khẩu',
        'blacklist_title' => '',
        'blacklist_content' => 'Thuê bao của bạn hiện không thể sử dụng dịch vụ Gamequiz Halovietnam.',
    ),
    'Packages' => array(
        'reward_first_register' => array(
            'question_group' => 3,
            'score' => 100,
        ),
        'extra' => '2.000d',
        'day' => '2.000d',
        'week' => '9.000d',
        //ungnv 27/11 set giá = 0
        'buy_amount' => 0,
        //'buy_amount' => 2000,
        //end ungnv 27/11 set giá = 0
        'buy_question_group' => 1,
        'G1_question_group' => 3,
        'G7_question_group' => 3,
        'NOK_NO_MORE_CREDIT_AVAILABLE_TITLE' => 'THUÊ BAO KHÔNG ĐỦ SỐ DƯ',
        'NOK_NO_MORE_CREDIT_AVAILABLE_CONTENT' => 'Số dư tài khoản không đủ tiền. Vui lòng nạp thêm và đăng ký lại.',
        'error_system_title' => 'Hệ thống lỗi',
        'error_system_content' => 'Hiện tại hệ thống đang gặp sự cố.',
        'empty_question_daily_title' => 'Bạn đã sử dụng hết 15 câu hỏi của gói %s trong ngày hôm nay',
        'empty_question_daily_content' => 'Bạn có thể <strong>CHỌN</strong> mua thêm gói <strong>%s</strong> hoặc gói <strong>MUA THÊM</strong> câu hỏi sau đó <strong>ĐỒNG Ý</strong> để xác nhận',
        'empty1_question_daily_title' => 'Bạn đã sử dụng hết 15 câu hỏi của gói %s trong ngày hôm nay',
        'empty1_question_daily_content' => 'Bạn có thể <strong>CHỌN</strong> mua thêm gói <strong>MUA THÊM</strong> câu hỏi sau đó <strong>ĐỒNG Ý</strong> để xác nhận',
        'unregister_title' => 'Bạn chưa đăng ký dịch vụ <strong>Vượt Đỉnh Pan Xi Phang</strong>',
        'unregister_content' => 'Bạn có thể <strong>CHỌN</strong> mua gói <strong>G1</strong>, <strong>G7</strong> sau đó <strong>ĐỒNG Ý</strong> để xác nhận',
        'cancel_register_title' => 'Hủy bỏ',
        'cancel_register_content' => 'Bạn chưa đăng ký dịch vụ <strong>Vượt Đỉnh Pan Xi Phang</strong> thành công',
        'usage_question' => 'Bạn đã sử dụng %s câu hỏi trong ngày hôm nay. Bạn còn %s câu hỏi. Bạn có thể chọn mua thêm gói cước bằng cách ấn vào nút “Chọn” và ấn “Đồng ý” để xác nhận',
    ),
    'Rating' => array(
        'limit' => 2,
        'status' => 1,
        'page' => 1,
        'startYear' => 2015,
        'mon_week' => array(
            '0' => 'Tuần',
            '1' => 'Tháng',
        ),
        'error_msg' => 'Không tìm thấy kết quả phù hợp',
        'replacestr' => 'XXX',
    ),
    'RatingComment' => array(
        'score_levels' => array(
            5, 4, 3, 2, 1
        ),
    ),
    'Visitor' => array(
        'default_avatar_url' => 'http://113.187.31.115:6970/img/icon-user-default.png',
        'default_notification_group_id' => '558ab9dc189dc030646cb6f1',
    ),
    // End TrungNQ
// Begin Tổng bí thư Trần Phú
// Màn hình transport list
    'ScreenTransportList' => array(
        'LIST_TRANSPORT_ID' => [
            'airline' => '55668250c4a160ff90840dd6',
            'bus' => '55668698c4a160fd90840dbd',
            'taxi' => '55541212c4a1604e96cf05f3',
            'train' => '55667e57c4a160fc90840de3',
            'ship' => '55668a47c4a1605a9b840d09',
        ],
    ),
    // Màn hình Utility list
    'ScreenUtilityList' => array(
        'LIST_UTILITY_ID' => [
            'bank' => '55652508c4a160fe90840d96',
            'hospital' => '556911d3c4a1605a9b840d60',
//            'atm' => '55644dcfc4a160ff90840d8c',
            'emergency' => '558526b4887b66eb541134da',
        ],
    ),
    'ScreenTop100Place' => [
        'PLACE_CATEGORY_ID' => '557ea3cd887b66de54112fab',
        'PLACE_COLLECTION_ID' => '558a8236887b66844e944155'
    ],
    // End Trần Phú
// Màn hình Transport
    'ScreenTransport' => array(
        'LIST_OBJECT_TYPE_ID' => [
            '55541212c4a1604e96cf05f3', //Taxi
        ], // OBJECT_TYPE_ID của Tour
        'TAXI_LIMIT' => 0 //giới hạn lấy DINE, = 0 lấy ALL, 
    ),
    'ScreenProvince' => array(
        'ACTIVITY_CATEGORY_ID' => '555d5884c4a16080649a1698', //mã id của CATEGORY, khi nhập chú ý là phải thuộc object_type là ACTIVITY
        'PLACE_CATEGORY_ID' => '5556bc10c4a16067709a160f', //mã id của CATEGORY, khi nhập chú ý là phải thuộc object_type là PLACE
        'HOTEL_CATEGORY_ID' => '555d5a4ac4a160ef6e9a16e1', //mã id của CATEGORY, khi nhập chú ý là phải thuộc object_type là HOTEL
        'LIMIT_SLIDE_IMAGE' => 5, //mã id của CATEGORY, khi nhập chú ý là phải thuộc object_type là HOTEL
        'LIST_CATEGORY_ID' => [
            'activity' => '555d5884c4a16080649a1698',
            'place' => '5556bc10c4a16067709a160f',
            'hotel' => '555d5a4ac4a160ef6e9a16e1',
            'tour' => '5562cc2f189dc00880da13e5',
        ],
    ),
    'Topics' => array(
        'data_file_root' => 'topics_files',
        'status' => array(
            'NOT_APPROVE' => -1,
            'NOT_DISPLAY' => 0,
            'PENDING' => 1,
            'APPROVED' => 2,
        ),
    ),
    'Locations' => array(
//HiVN: 
//'GOOGLE_API_GEO_URL' => 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyBYL6bsagh4vb_YqmGdCUu9X0dzVDp_0xA',
//HaloVN: 
        'GOOGLE_API_GEO_URL' => 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyBsRrp9Rcjnsf98AO4x_HjIe-aQ0f1G_nM',
        'GOOGLE_API_DISTANCE_URL' => "https://maps.googleapis.com/maps/api/distancematrix/json?key=AIzaSyA6-uZnKtpMLssHYdu4b-s4S0OTvl86LiM",
        'GOOGLE_API_STATICMAP_URL' => 'https://maps.googleapis.com/maps/api/staticmap?zoom=13&amp;size=600x480&amp;center=',
    ),
    'Home' => array(
        'index' => array(
            'categories' => array(
                array(
                    'id' => '5562cc4f189dc00880da13e6', // Hot Tours
                    'code' => 'tours',
                    'max_item' => 5,
                    'lang_code' => 'vi',
                    'user_region_filter' => 1,
                ),
                array(
                    'id' => '557ea330887b6671541130d1', // Region quan tâm
                    'code' => 'regions',
                    'max_item' => 5,
                    'lang_code' => 'vi',
                    'user_region_filter' => 0,
                ),
                array(
//                    'id' => '555d5a4ac4a160ef6e9a16e1', // Hotel
                    'id' => '557ea943887b66dc54112fa8', // Hotel nổi bật
                    'code' => 'hotels',
                    'max_item' => 5,
                    'lang_code' => 'vi',
                    'user_region_filter' => 1,
                ),
                array(
                    'id' => '555d5805c4a1608b6e9a19bc', // Restaurant nổi bật
                    'code' => 'restaurants',
                    'max_item' => 5,
                    'lang_code' => 'vi',
                    'user_region_filter' => 1,
                ),
                array(
                    'id' => '55656755189dc00880da13ea', // Event
                    'code' => 'events',
                    'max_item' => 5,
                    'lang_code' => 'vi',
                    'user_region_filter' => 1,
                ),
                // dành cho en
                array(
                    'id' => '5562cc4f189dc00880da13e6', // Tour nổi bật
                    'code' => 'tours',
                    'max_item' => 5,
                    'lang_code' => 'en',
                    'user_region_filter' => 1,
                ),
                array(
                    'id' => '557ea330887b6671541130d1', // Region quan tâm
                    'code' => 'regions',
                    'max_item' => 5,
                    'lang_code' => 'en',
                    'user_region_filter' => 0,
                ),
                array(
                    'id' => '557ea943887b66dc54112fa8', // Hotel nổi bật
                    'code' => 'hotels',
                    'max_item' => 5,
                    'lang_code' => 'en',
                    'user_region_filter' => 1,
                ),
                array(
                    'id' => '555d5805c4a1608b6e9a19bc', // Restaurant nổi bật
                    'code' => 'restaurants',
                    'max_item' => 5,
                    'lang_code' => 'en',
                    'user_region_filter' => 1,
                ),
                array(
                    'id' => '55656755189dc00880da13ea', // Event
                    'code' => 'events',
                    'max_item' => 5,
                    'lang_code' => 'en',
                    'user_region_filter' => 1,
                ),
            ),
            'limit' => 5,
        ),
        'search' => array(
            'limit' => 5,
            'types' => array(
                'tours',
                'places',
                'hotels',
                'restaurants',
                'events',
            ),
            'search_in_region' => array(
                'types' => array(
                    'tours',
                    'places',
                    'hotels',
                    'restaurants',
                    'events',
                ),
            ),
        ),
        'nearby' => array(
            'limit' => 5,
            'types' => array(
                'places',
                'hotels',
                'restaurants',
            ),
            'DISTANCE' => 2000,
            'DISTANCE_REGION' => 10000, // khoảng cách mặc định tìm kiếm xung quan region
        ),
        'type_alias' => array(
            'places' => 'Địa điểm',
            'restaurants' => 'Nhà hàng',
            'hotels' => 'Khách sạn',
            'tours' => 'Tour',
            'events' => 'Sự kiện',
        ),
    ),
    'Weather' => array(
        'DEFAULT_REGION_ID' => '5555bee2c4a1608e6e9a1610',
    ),
    'Comments' => array(
        'default_status' => 2,
        'limit' => 5,
    ),
    'Bookmarks' => array(
        'default_status' => 2,
        'default_count' => 1,
        'actions' => array(
            'set', 'unset',
        ),
    ),
    'Restaurants' => array(
        'distance' => 2,
        'max_leng' => 30,
        'max_leng_faci' => 20,
        'defaultRegionId' => '5555bee2c4a1608e6e9a1610',
    ),
    'Hotels' => array(
        'distance' => 2,
        'max_leng' => 30,
        'max_leng_faci' => 20,
        'defaultRegionId' => '5555bee2c4a1608e6e9a1610',
    ),
);

