  
<section class="top-section" id="container-list">

    <?php
    if (!empty($arr_result['data']['arr_place'])) {
        foreach ($arr_result['data']['arr_place'] as $key => $place) {
            ?>
            <div class="col-xs-12 col-sm-6 col-md-4 restaurant-item box">
                <a href="/places/info?id=<?php echo $place['id'] ?>" class="swiper-slide">
                    <div class="slide-img">
                        <img src="<?php echo $place['banner'] ?>" alt="<?php echo $place['name'] ?>">
                    </div>
                    <div class="slide-text">
                        <h3><?php echo $place['name'] ?></h3>
                        <span class="slide-detail"><?php
                            if (count($place['address'] > 30)) {
                                echo mb_substr($place['address'], 0, 27, "UTF-8") . " ...";
                            } else {
                                echo $place['address'];
                            }
                            ?></span>
                    </div> 
                </a>
            </div>
            <?php
        }
    }
    ?> 
    <div class="clearfix"></div>
</section> 
<?php echo $this->element('infinite-scroll', array('query_param' => $this->request->query)); ?>
<!--ĐỊA ĐIỂM HOT-->