<?php
$user = CakeSession::read('Auth.User');
if (!empty($user)) {

    $raw_mobile = $user['mobile'];
} else {

    $raw_mobile = $this->Common->detectMobile();
}
if (!empty($raw_mobile)) {

    $mobile = $this->Common->prettyMobile($raw_mobile);
}
?>
<?php if (!empty($mobile)): ?>
    <div class="col s12" style="text-align:center;background-color:#ff6666;color:#fff;padding:5px;font-size:14px;"> 
        Xin chào thuê bao <a href="<?php echo Router::url(array('controller' => 'Packages', 'action' => 'about')) ?>" style="color: white"><?php echo $mobile ?>. Dịch vụ đang được trải nghiệm miễn phí.</a>
    </div>
<?php else: ?>
    <div class="col s12" style="text-align:center;background-color:#ff6666;color:#fff;padding:5px;font-size:14px;"> 
        Không nhận diện được thuê bao, vui lòng kết nối GPRS hoặc truy cập wifi <a href="<?php echo Router::url(array('controller' => 'Visitors', 'action' => 'login')) ?>">tại đây</a>
    </div>
<?php endif; ?>

