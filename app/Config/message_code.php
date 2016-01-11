<?php

$config['message_code'] = array(
    'Common' => array(
        "STATUS_SUCCESS" => "success",
        "STATUS_FAIL" => "fail",
        "SYSTEM_ERROR" => "Hệ thống đang quá tải, xin vui lòng quay lại sau!"
    ),
    //BEGIN HOANGNN
    'Visitors' => array(
        'EXISTED' => "Tài khoản này đã tồn tại trong hệ thống! Bạn hãy đăng nhập hoặc dùng chức năng Quên Mật Khẩu!",
        'USERNAME_REQUIRE' => "Tên Đăng Nhập không được phép trống!",
        'PASSWORD_REQUIRE' => "Mật Khẩu không được phép trống!",
        'FIELDS_REQUIRE' => "Bạn phải nhập đầy đủ thông tin!",
        'ACCOUNT_INVALID' => "Tên đăng nhập hoặc mật khẩu chưa chính xác!",
        'NOT_LOGIN' => "Bạn chưa đăng nhập!",
        'REGISTER_SUCCESS' => 'Đăng ký thành công',
    ),
    //END HOANGNN
    //
    //
    //BEGIN TRAN PHU
    'Transports' => array(
        '#tra001' => 'Region due to id=%s does not exist',
        '#tra002' => 'Missing type id',
        '#tra003' => 'type id is not valid, or not exist from db',
    ),
    'Banks' => array(
        '#ban001' => 'Region due to id=%s does not exist',
    ),
    'Atms' => array(
        '#atm001' => 'Region due to id=%s does not exist',
    ),
    'Hospitals' => array(
        '#hos001' => 'Region due to id=%s does not exist',
    ),
    'Events' => array(
        '#eve001' => 'Region due to id=%s does not exist',
    ),
    'BusStations' => array(
        '#bus001' => 'Region due to id=%s does not exist',
        '#bus002' => 'Id is missing!',
        '#bus003' => 'System cant find your id!',
    ),
    'Tags' => array(
        '#tag001' => 'TagClientVersion does not exist',
        '#tag002' => 'File does not exist',
    ),
    'Notifications' => array(
        '#not001' => 'Visitor id is missing! please check again!',
        '#not002' => 'Notification id is missing! please check again!',
        '#not003' => 'Notification id is not exists! please check again!',
    ),
    //END TRAN PHU
    //
    //
    //BEGIN TRUNGNQ
    'Weather' => array(
        '#wea001' => 'Region does not exist',
    ),
    'Home' => array(
        '#hom001' => 'Categories was empty in Setting',
        '#hom002' => 'Categories due to lang_code %s was empty in sysconfig.Home.index.categories',
        '#hom003' => 'object_id is empty',
        '#hom004' => '%s due to id=%s does not exist',
        '#hom005' => '%s due to id=%s has not public status',
        '#hom006' => 'Can not search in region, because region object_id is empty',
    ),
    'Visitors' => array(
        '#vis001' => 'Can not detect msisdn',
        '#vis002' => 'Can not create new visitor',
        '#vis003' => 'Can not generate a token for visitor with id=%s',
        '#vis004' => 'The token is invalid, error detail: "%s"',
        '#vis005' => 'Login failed for user',
        '#vis006' => 'The HTTP request is not POST request',
        '#vis007' => 'username or password is empty',
        '#vis008' => 'Expired token',
        '#vis009' => 'user_id is empty',
        '#vis010' => 'User due to id=%s does not exist',
        '#vis011' => 'Can not logout for user due to id=%s',
        '#vis012' => 'Can not detect the authorized method',
    ),
    'Comments' => array(
        '#com001' => 'discussion_type is empty',
        '#com002' => 'The HTTP request is not POST request',
        '#com003' => 'User due to id=%s can not edit %s due to id=%s',
        '#com004' => 'content is empty',
        '#com005' => 'Can not create new %s for visitor with id=%s',
        '#com006' => 'id is empty',
        '#com007' => 'The HTTP request is not DELETE request',
        '#com008' => 'discussion_id is empty',
        '#com009' => 'Can not delete a %s due to id=%s for visitor with id=%s',
        '#com010' => '%s discussion due to id=%s does not exist',
        '#com011' => 'discussion_id or discussion_type is empty',
        '#com012' => 'Thread comment due to id=%s does not exist',
        '#com013' => 'Thread comment due to id=%s has not public status',
        '#com014' => 'Thread comment due to id=%s can not add any subcomment because it is a subcomment',
        '#com015' => 'thread_id is empty',
        '#com016' => 'discussion_type is empty',
    ),
    'Bookmarks' => array(
        '#boo001' => 'type or object_id is empty',
        '#boo002' => '%s due to id=%s does not exist',
        '#boo003' => '%s due to id=%s has not public status',
        '#boo004' => 'The HTTP request is not POST request',
        '#boo005' => 'action is empty',
        '#boo006' => 'action is invalid',
        '#boo007' => 'Can not create a new %s for visitor with id=%s',
        '#boo008' => 'Can not extract %s for visitor with id=%s',
        '#boo009' => 'Can not unset %s due id=%s for visitor with id=%s',
    ),
    'Favorites' => array(
        '#fav001' => 'type or object_id is empty',
        '#fav002' => '%s due to id=%s does not exist',
        '#fav003' => '%s due to id=%s has not public status',
        '#fav004' => 'The HTTP request is not POST request',
        '#fav005' => 'action is empty',
        '#fav006' => 'action is invalid',
        '#fav007' => 'Can not create a new %s for visitor with id=%s',
        '#fav008' => 'Can not extract %s for visitor with id=%s',
        '#fav009' => 'Can not unset %s due id=%s for visitor with id=%s',
    ),
    'Ratings' => array(
        '#rat001' => 'type or object_id is empty',
        '#rat002' => '%s due to id=%s does not exist',
        '#rat003' => '%s due to id=%s has not public status',
        '#rat004' => 'The HTTP request is not POST request',
        '#rat005' => 'score is invalid',
        '#rat006' => 'Can not create a new %s for visitor with id=%s',
        '#rat007' => 'Can not update rating_statistics %s due to %s for visitor with id=%s',
        '#rat008' => 'Can not update rating_statistics %s due to %s for visitor with id=%s',
    ),
    'RatingComment' => array(
        '#rac001' => 'type or object_id is empty',
        '#rac002' => '%s due to id=%s does not exist',
        '#rac003' => '%s due to id=%s has not public status',
    ),
    'App' => array(
        '#app001' => 'Visitor due to id=%s does not exist',
        '#app002' => 'Visitor due to id=%s has not public status',
        '#app003' => 'Visitor due to id=%s had an old token because the new token was created',
        '#app004' => 'token is empty',
    ),
    'Tests' => array(
        '#tes001' => 'token is empty',
    ),
    'SmsSender' => array(
        '#sms001' => 'api_key is empty',
        '#sms002' => 'The HTTP request is not POST request',
        '#sms003' => 'mobile is empty',
        '#sms004' => 'mobile number %s is invalid mobifone number',
        '#sms005' => 'api_key %s is invalid',
        '#sms006' => 'Can not create new visitor with mobile number=%s',
        '#sms007' => 'Visitor due to id=%s has not public status',
        '#sms008' => 'Can not regenerate new password for visitor due to id=%s',
        '#sms009' => 'Can not call mobifone sms service, error detail: %s',
        '#sms010' => 'Send sms to %s was failed, sms content: %s',
        '#sms011' => 'Send sms to %s was unsucessful, sms content: %s',
        '#sms012' => 'Visitor due to mobile=%s have not register any package in a Player',
        '#sms013' => 'Visitor due to mobile=%s does not exist',
        '#sms014' => 'mobile=%s is in blacklist',
    ),
        //END TRUNGNQ
        //
    //
    //
    //
    //
    //BEGIN 
        //END 
        //BEGIN 
        //END 
);

