<?php
if (!empty($arr_result['data']['arr_cate'])) {
    foreach ($arr_result['data']['arr_cate'] as $key => $cate) {
        ?>
        <section class="top-section" id="container-list">
            <?php
            if (!empty($cate["arr_place"])) {
                foreach ($cate["arr_place"] as $key => $place) {
                    ?>
                    <div class="col-xs-12 col-sm-6 col-md-4 restaurant-item box">
                        <a href="/places/info?id=<?php echo $place['id'] ?>" class="swiper-slide">
                            <div class="slide-img">
                                <img src="<?php
                                if (isset($place['banner']) && $place['banner'] != NULL) {
                                    echo $place['banner'];
                                } else {
                                    echo "No image…";
                                }
                                ?>">
                            </div>
                            <div class="slide-text">
                                <span class="object-title"><?php echo $place['name'] ?></span>
                                <span class="slide-detail object-address"><?php echo $place['address']; ?></span>
                            </div> 
                        </a>
                    </div>
                    <?php
                }
            }
            ?> 
            <div class="clearfix"></div>
        </section>
        <?php echo $this->element('infinite-scroll', array('query_param' => array('cate_id' => $cate['id']))); ?>
        <?php
    }
}
?>
<!--ĐỊA ĐIỂM HOT-->