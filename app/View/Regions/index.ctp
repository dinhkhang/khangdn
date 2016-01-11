<?php $base = $this->request->base; ?>
<?php
if (!empty($arr_result['data']['arr_cate'])) {
    $index = 0;
    foreach ($arr_result['data']['arr_cate'] as $key => $cate) {

        if (!empty($cate["arr_region"])) {
            if ($index == 0) {
                ?>
                <section class="top-section"> 
                <?php
            } else {
                ?>  
                    <section> 
                    <?php
                }
                $index++;
                ?> 
                    <div>
                        <h3 class="section-title"><?php echo $cate['name'] ?><a href="/regions/listregion?cate_id=<?php echo $cate['id'] ?>" class="see-more pull-right">+ Thêm</a></h3>
                    </div>
                    <div class="clearfix"></div>
                    <div class="swiper-container">
                        <div class="swiper-wrapper"> 

            <?php
            foreach ($cate["arr_region"] as $key => $region) {
                ?>

                                <div class="swiper-slide">
                                    <a href="<?php echo$base . '/regions/info?id=' . $region['id'] ?>" class="swiper-slide">
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
            ?> 
                        </div>
                        <!-- Add Pagination -->
                        <div class="swiper-pagination"></div>
                    </div>
                </section>
            <?php
        }
    }
}
?>
    <!--ĐỊA ĐIỂM HOT-->