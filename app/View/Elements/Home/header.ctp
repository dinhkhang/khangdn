
<header>
    <?php
    $top_bar_home_class = empty($page_title) ? 'top-bar-home' : '';
    ?>
    <div class="top-bar <?php echo $top_bar_home_class ?> text-center">
        <?php if (empty($page_title)): ?>
            <img src="<?php echo Router::url('/') ?>css/assets/img/logo.png" alt="">
        <?php else: ?>
            <span class="page-title"><?php echo $page_title ?></span>
        <?php endif; ?>
        <button type="button" class="navbar-toggle toggle-right " data-toggle="sidebar" data-target=".sidebar">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    
    <!--
    <?php if (empty(CakeSession::read('Auth.User'))): ?>
        <?php if (!empty($this->params['controller']) && strtolower($this->params['controller']) == 'home' && strtolower($this->params['action']) == 'index'): ?>
            <div class="alert alert-home alert-dismissible alert-unidentified" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true"><img src="<?php echo Router::url('/') ?>css/assets/img/close-alert.png"></span>
                </button>
                <a href="<?php echo Router::url(array('controller' => 'Visitors', 'action' => 'login')) ?>">Không nhận diện được thuê bao. Vui lòng đăng nhập wifi.</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    -->


    <div class="col-xs-9 col-sm-4 col-md-3 sidebar sidebar-right sidebar-animate sidebar-show-lg">
        <div class="row">
            <ul class="nav navbar-stacked push-menu">
                <li>
                    <div class="banner-menu">
                        <img src="<?php echo Router::url('/') ?>css/assets/img/207x130.jpg" class="banner-menu-bg">
                        <div class="absolute banner-menu-info">
                            <div class="banner-menu-province">
                                <p class="location-text">Hà Nội</p>
                                <a href="<?php echo Router::url(array('controller' => 'Regions', 'action' => 'choose')) ?>" class="no-padding extra_param">
                                    <p class="select-location-text">Vị trí của bạn</p>
                                    <i class="fa fa-angle-down location-arrow"></i>
                                </a>
                            </div>
                            <div class="banner-menu-weather">
                                <img src="<?php echo Router::url('/') ?>css/assets/img/cloud-icon.png" class="banner-menu-icon">
                                <span class="banner-menu-celsius">...</span>
                                <p class="banner-menu-weather-info">...</p>
                            </div>
                        </div>

                    </div>
                </li>
                <!--class="active"-->
                <li><a href="<?php echo Router::url(array('controller' => 'Home', 'action' => 'index')) ?>" class="pmenu-link pmenu-home extra_param">Trang chủ</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Places', 'action' => 'top100')) ?>" class="pmenu-link pmenu-top-100 extra_param">100 địa điểm HOT</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Home', 'action' => 'nearby')) ?>" class="pmenu-link pmenu-xung-quanh extra_param nearby">Xung quanh bạn</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Regions', 'action' => 'home')) ?>" class="pmenu-link pmenu-dia-diem extra_param">Địa điểm</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Hotels', 'action' => 'home')) ?>" class="pmenu-link pmenu-khach-san extra_param">Khách sạn</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Restaurants', 'action' => 'home')) ?>" class="pmenu-link pmenu-nha-hang extra_param">Nhà hàng</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Tours', 'action' => 'home')) ?>" class="pmenu-link pmenu-tour extra_param">Tours du lịch</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Events', 'action' => 'home')) ?>" class="pmenu-link pmenu-su-kien extra_param">Hoạt động - Sự kiện</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Transports'), true); ?>" class="pmenu-link pmenu-phuong-tien extra_param">Phương tiện</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Utilities'), true); ?>" class="pmenu-link pmenu-tien-ich extra_param">Tiện ích</a></li>
                <li><span class="pmenu-cat">Liên kết</span></li>
                <li><a href="#" class="pmenu-link pmenu-360-giao-thong">360 giao thông</a></li>
                <li><a href="#" class="pmenu-link pmenu-moi-truong">Môi trường</a></li>
                <li><a href="#" class="pmenu-link pmenu-am-nhac">Âm nhạc dân gian</a></li>
                <li><a href="/gamequiz" class="pmenu-link pmenu-game-quiz">Gam Quiz</a></li>
                <li><span class="pmenu-cat">Tài khoản</span></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Visitors', 'action' => 'login')) ?>" class="pmenu-link pmenu-wifi">Đăng nhập qua wifi</a></li>
                <li><a href="#" class="pmenu-link pmenu-trang-ca-nhan">Trang cá nhân</a></li>
                <li><a href="#" class="pmenu-link pmenu-yeu-thich">Yêu thích</a></li>
                <li><a href="#" class="pmenu-link pmenu-bookmark">Bookmark</a></li>
                <li><a href="#" class="pmenu-link pmenu-thong-bao">Thông báo</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Visitors', 'action' => 'logout')) ?>" class="pmenu-link pmenu-logout">Đăng xuất tài khoản</a></li>
                <li><span class="pmenu-cat">Hỗ trợ</span></li>
                <li><a href="<?php echo Router::url(array('controller' => 'AboutUs'), true); ?>" class="pmenu-link pmenu-gioi-thieu">Giới thiệu</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Faqs'), true); ?>" class="pmenu-link pmenu-hoi-dap">Hỏi đáp</a></li>
                <li><a href="<?php echo Router::url(array('controller' => 'Utilities', 'action' => 'detail', '?' => array('type' => 'emergencies')), true); ?>" class="pmenu-link pmenu-sos extra_param">Khẩn cấp</a></li>
                <li><a href="#" class="pmenu-link pmenu-cai-dat">Cài đặt</a></li>
            </ul>
        </div>
    </div>

    <div class="clearfix"></div>
</header>
<?php
echo $this->fetch('top-banner');
?>

