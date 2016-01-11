<div class="top-banner">
    <img class="banner-img" src="/css/assets/img/top-banner.png">
    <div class="search-location">
        <div class="search-intro text-center">
            <img class="intro-icon" src="/css/assets/img/intro-icon.png">
            <span class="intro-text">Xung quanh bạn</span>
        </div>
        <form class="form-inline search-location-form" action="" method="post">
            <div class="input-group search-location-input">
                <input type="text" placeholder="Địa điểm, khách sạn, nhà hàng" class="form-control">
                <span class="input-group-addon"><img src="/css/assets/img/search-icon.png"></span>
            </div>
        </form>
    </div>
</div>
<!--ĐỊA ĐIỂM HOT--> 

<?php
if (!empty($arr_result['data']['arr_cate'])) {
    foreach ($arr_result['data']['arr_cate'] as $key => $cate) {
        ?>
        <section class="top-section"> 
            <div>
                <h3 class="section-title"><?php echo $cate['name'] ?><a href="/regions/index" class="see-more pull-right">+ Thêm</a></h3>
            </div>
            <div class="clearfix"></div>
            <div class="swiper-container">
                <div class="swiper-wrapper"> 

        <?php
        if (!empty($cate["arr_region"])) {
            foreach ($cate["arr_region"] as $key => $region) {
                ?>

                            <div class="swiper-slide">
                                <a href="/regions/listregion?cate_id=<?php echo $cate['id'] ?>" class="swiper-slide">
                                    <div class="slide-img">
                                        <img src="<?php echo $region['banner'] ?>">
                                    </div>
                                    <div class="slide-text">
                                        <h3><?php echo $region['name'] ?></h3>
                                        <span class="slide-detail"><?php echo $region['address'] ?></span>
                                    </div> 
                                </a>
                            </div>  
                <?php
            }
        }
        ?> 
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </section>
        <?php
    }
}
?>
<!--ĐỊA ĐIỂM HOT-->
