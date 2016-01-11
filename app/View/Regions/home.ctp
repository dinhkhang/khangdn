<?php
echo $this->element('top-banner', array(
    'object_type' => 'places',
    'search_placeholder' => 'Tìm kiếm địa điểm',
));
echo $this->element('recent_history', array(
    'recent_type' => 'places',
));
?>
<!--ĐỊA ĐIỂM HOT--> 

<?php
if (!empty($arr_result['data']['arr_cate'])) {
    foreach ($arr_result['data']['arr_cate'] as $key => $cate) {
        ?>   
        <div class="clearfix">
            <h3 class="section-title"><?php echo $cate['name'] ?><a href="/regions/index" class="see-more pull-right extra_param">+ Thêm</a></h3>
        </div>
        <section id="container-list">        
            <?php
            if (!empty($cate["arr_region"])) {
                foreach ($cate["arr_region"] as $key => $region) {
                    ?>
                    <div class="col-md-12 restaurant-item box">
                        <a href="<?php echo Router::url(array('controller' => 'Regions', 'action' => 'info', '?' => array('id' => $region['id']))) ?>" class="swiper-slide extra_param">
                            <div class="slide-img">
                                <img src="<?php echo $region['banner'] ?>">                               
                            </div>
                            <div class="slide-text">
                                <h3 class="object-title"><?php echo $region['name'] ?></h3>
                                <span class="slide-detail object-address"><?php echo $region['address'] ?></span>
                            </div>  
                        </a>
                    </div>
                    <?php
                }
            }
            ?> 
        </section>
        <?php
    }
}
?>
<!--ĐỊA ĐIỂM HOT-->
