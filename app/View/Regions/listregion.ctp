    <?php 
    if (!empty($arr_result['data']['arr_cate']))
    {
        foreach ($arr_result['data']['arr_cate'] as $key => $cate) {
             
    ?>
    <section class="top-section">
                
    <?php 
    if (!empty($cate["arr_region"]))
    {
        foreach ($cate["arr_region"] as $key => $region) {
             
    ?>
        <div class="col-xs-12 col-sm-6 col-md-4 restaurant-item box">
            <a href="/regions/info?id=<?php echo $region['id'] ?>" class="swiper-slide">
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
        <div class="clearfix"></div>
    </section>
    <?php echo $this->element('infinite-scroll', array('query_param' => array('cate_id' => $cate['id']))); ?>
    <?php  
        }
    }
    ?>
    <!--ĐỊA ĐIỂM HOT-->