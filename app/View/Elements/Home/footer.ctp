<footer>
    <div class="footer-logo text-center">
        <a href=""><img src="<?php echo Router::url('/') ?>css/assets/img/logo-mobifone.jpg"></a>
    </div>
    <p class="footer-text text-center">
        Copyright &copy; 2015 by mobifone.</br>
        All Rights Reserved.
    </p>
    <div class="footer-menu">
        <ul>
            <li class="col-md-20 active">
                <a href="<?php echo Router::url(array('controller' => 'Home', 'action' => 'index')) ?>">
                    <img src="<?php echo Router::url('/') ?>css/assets/img/home-icon.png">
                    <span>Trang chủ</span>
                </a>
            </li>
            <li class="col-md-20">
                <a href="<?php echo Router::url(array('controller' => 'Places', 'action' => 'home')) ?>">
                    <img src="<?php echo Router::url('/') ?>css/assets/img/local-icon.png">
                    <span>Địa điểm</span>
                </a>
            </li>
            <li class="col-md-20">
                <a href="<?php echo Router::url(array('controller' => 'Hotels', 'action' => 'home')) ?>">
                    <img src="<?php echo Router::url('/') ?>css/assets/img/hotel-icon.png">
                    <span>Khách sạn</span>
                </a>
            </li>
            <li class="col-md-20">
                <a href="<?php echo Router::url(array('controller' => 'Restaurants', 'action' => 'home')) ?>">
                    <img src="<?php echo Router::url('/') ?>css/assets/img/restaurant-icon.png">
                    <span>Nhà hàng</span>
                </a>
            </li>
            <li class="col-md-20">
                <a href="">
                    <img src="<?php echo Router::url('/') ?>css/assets/img/plus-icon.png">
                    <span>Mở rộng</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="overlay-sidebar hide"></div>
</footer>
