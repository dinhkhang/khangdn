<?php
echo $this->element('recent_history_trigger', array(
    'recent_object' => !empty($arr_result['data']['region']) ?
            $arr_result['data']['region'] : array(),
    'recent_type' => 'places',
    'object_type' => 'regions',
));
echo $this->element('tag_autocomplete');
?>   
<?php
if (!empty($arr_result['data']['region'])) {
    $region = $arr_result['data']['region'];
    ?> 
    <header>
        <div class="top-banner">

            <div class="top-link">
                <a href="#" class="pull-left previous-page">
                    <img src="/css/assets/img/circle-back-arrow.png">
                </a>
                <a href="#" class="pull-right right-social-icon">
                    <img src="/css/assets/img/social_star.png">
                </a>
                <a href="#" class="pull-right right-social-icon">
                    <img src="/css/assets/img/social_send.png">
                </a>
                <a href="#" class="pull-right right-social-icon">
                    <img src="/css/assets/img/social_message.png">
                </a>
                <a href="#" class="pull-right right-social-icon">
                    <img src="/css/assets/img/social_heart.png">
                </a>
                <div class="clearfix"></div> 
            </div> 
            <div class="top-info top-info-restaurant">
                <div class="weather">
                    <?php if (!empty($region['weather']['current'])) {
                        ?>
                        <div class="weather-detail">
                            <img src="<?php echo $region['weather']['current']["icon"] ?>" class="pull-left">
                            <span class="celsius pull-left"><?php echo $region['weather']['current']["temperature"] ?>°</span>
                        </div>
                        <p class="weather-desc"><?php echo $region['weather']['current']["content"] ?></p>
                    <?php } ?>
                </div>
                <div class="top-restaurant-info">
                    <span class="top-restaurant-name "><?php echo $region['name'] ?></span>
                    <span class="top-restaurant-distance region-distance">
                        <?php if (!empty($region['distance'])) {
                            ?><?php echo $region['distance']['text'] ?>
                        <?php } ?></span>
                </div>
                <div class="clearfix"></div>
                <div class="top-restaurant-stats">
                    <!--<img src="/css/assets/img/star-rate.png">-->
                    <!--<span class="num-rate top-num-rate">8.3</span>-->
                    <!--<span class="top-res-count">12 đánh giá</span>-->
                    <!--<span class="count-view">69k lượt xem</span>-->
                </div>
            </div>
            <div class="overlay-banner"></div>
            <div class="swiper-container-top">
                <div class="swiper-wrapper">
                    <?php
                    if (!empty($arr_result['data']['region']['arr_slide_img'])) {
                        foreach ($arr_result['data']['region']['arr_slide_img'] as $slide_img) {
                            ?> 
                            <div class="swiper-slide top-slide-item">
                                <img src="<?php echo $slide_img ?>" class="banner-img">
                            </div> 
                            <?php
                        }
                    }
                    ?>  
                </div>
                <!-- Add Pagination -->
                <div class="swiper-paginationx"></div>
            </div>
        </div>
    </header>

    <!--LIÊN HỆ-->
    <section>
        <div>
            <h3 class="section-title-nobg">
                <span style="line-height:44px;" class="section-title-icon">Giới thiệu</span>
                <?php
                if (!empty($region['video'])) {
                    ?> 
                    <a class="province-video-link" href="#section-title-nobg" data-toggle="modal" data-target="#modal-video"><i class="fa fa-youtube-play fa-3"></i><span class="province-video-text">Video</span></a>

                    <!-- Modal video -->
                    <div class="modal fade" id="modal-video" active-slide='' tabindex="-1" role="dialog" >
                        <div class="modal-dialog media-popup" role="document">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <video controls class="video" poster="<?php echo $region['video']['thumb'] ?>">
                                                <source src="<?php echo $region['video']['url'] ?>" type="video/mp4">
                                            </video>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>  

            </h3>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-12">
            <article id="rmjs-1" aria-expanded="true" data-readmore="" style="max-height: none; height: 391px;" class="about-text">
                <?php echo $region['description'] ?>
            </article>

        </div>
    </section>
    <!--END LIÊN HỆ-->
    <div class="break-section"></div>

    <section>
        <div class="search-form no-padding-top col-md-12">
            <?php
            echo $this->Form->create('Search', array(
                'type' => 'get',
                'class' => 'navbar-form keyword-form',
                'role' => 'search',
                'url' => array(
                    'controller' => 'Home',
                    'action' => 'search',
                ),
            ));
            ?>
            <?php
            echo $this->Form->hidden('type', array(
                'value' => 'regions',
            ));
            echo $this->Form->hidden('object_id', array(
                'value' => $region['id'],
            ));
            ?>
            <div class="input-group">
                <input class="form-control input-border sm-text tag-autocomplete" placeholder="Tìm kiếm tại địa điểm đang được xem" name="keyword" type="text" required>
                <div class="input-group-btn">
                    <button class="btn btn-default search-btn cus-btn" type="submit">
                        <i class="glyphicon glyphicon-search"></i>
                    </button>
                </div>
            </div>
            <?php
            echo $this->Form->end();
            ?>
        </div>
        <div class="clearfix"></div>
    </section>

    <?php
    if (!empty($arr_result['data']['arr_guide'])) {
        ?> 
        <section>
            <div>
                <h3 class="section-title no-padding-top">
                    <span style="line-height:44px;" class="section-title-icon">Cẩm nang du lịch</span>
                </h3>
                <ul class="event-list handbook-list">

                    <?php
                    foreach ($arr_result['data']['arr_guide'] as $key => $guide) {
                        ?> 
                        <li class="col-sm-6 col-md-6">
                            <div class="row">
                                <div>
                                    <div class="thumbnail event-thumbnail handbook-thumbnail">
                                        <a href="/guides/info?id=<?php echo $guide['id'] ?>">
                                            <img src="<?php echo $guide['logo'] ?>" class="img-responsive">
                                        </a>
                                        <div class="caption">
                                            <a href="/guides/info?id=<?php echo $guide['id'] ?>">
                                                <h3><?php echo $guide['name'] ?></h3>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </li> 
                        <?php
                    }
                    ?> 
                </ul>
            </div>
            <div class="clearfix"></div>
        </section>

        <?php
    }
    ?>
    <div class="break-section"></div>
    <section>
        <ul class="recently-location-list cat-list"> 
            <?php
            if (!empty($arr_result['data']['arr_cate'])) {
                foreach ($arr_result['data']['arr_cate'] as $key => $cate) {
                    if ($cate['type'] == 'places') {
                        ?>
                        <li>
                            <a href="/places/index?region_id=<?php echo $region['id'] ?>">
                                <h3 class="icon-gray-location"><?php echo $cate['name'] . "(" . $cate['count'] . ")" ?></h3>
                            </a>
                        </li>
                        <?php
                    } else if ($cate['type'] == 'hotels') {
                        ?>
                        <li>
                            <a href="/hotels/index?region_id=<?php echo $region['id'] ?>">
                                <h3 class="icon-gray-hotel"><?php echo $cate['name'] . "(" . $cate['count'] . ")" ?></h3>
                            </a>
                        </li>
                        <?php
                    } else if ($cate['type'] == 'restaurants') {
                        ?>
                        <li>
                            <a href="/restaurants/index?region_id=<?php echo $region['id'] ?>">
                                <h3 class="icon-gray-restaurant"><?php echo $cate['name'] . "(" . $cate['count'] . ")" ?></h3>
                            </a>
                        </li>
                        <?php
                    } else if ($cate['type'] == 'tours') {
                        ?>
                        <li>
                            <a href="/tours/index?region_id=<?php echo $region['id'] ?>">
                                <h3 class="icon-gray-tour"><?php echo $cate['name'] . "(" . $cate['count'] . ")" ?></h3>
                            </a>
                        </li>
                        <?php
                    } else if ($cate['type'] == 'events') {
                        ?>
                        <li>
                            <a href="/events/index?region_id=<?php echo $region['id'] ?>">
                                <h3 class="icon-gray-event"><?php echo $cate['name'] . "(" . $cate['count'] . ")" ?></h3>
                            </a>
                        </li>
                        <?php
                    }
                    ?> 


                    <?php
                }
            }
            ?>

        </ul>
        <div class="clearfix"></div>
    </section>


    <?php
} else {
    ?> 
    <div style="color: red">Địa điểm này không tồn tại hoặc đã bị xóa!</div>
    <?php
} 
