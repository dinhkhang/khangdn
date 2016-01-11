<?php
echo $this->element('recent_history_trigger', array(
    'recent_object' => !empty($arr_result['data']['place']) ?
            $arr_result['data']['place'] : array(),
    'recent_type' => 'places',
));
?> 
<?php
if (!empty($arr_result['data']['place'])) {
    $place = $arr_result['data']['place'];
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
                    <?php if (!empty($place['weather']['current'])) {
                        ?>
                        <div class="weather-detail">
                            <img src="<?php echo $place['weather']['current']["icon"] ?>" class="pull-left">
                            <span class="celsius pull-left"><?php echo $place['weather']['current']["temperature"] ?>°</span>
                        </div>
                        <p class="weather-desc"><?php echo $place['weather']['current']["content"] ?></p>
                    <?php } ?> 
                </div>
                <div class="top-restaurant-info">
                    <span class="top-restaurant-name"><?php echo $place['name'] ?></span>
                    <?php if (!empty($place['distance'])) {
                        ?>
                        <span class="top-restaurant-distance"><?php echo $place['distance']['text'] ?></span>
                    <?php } ?> 
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
                    if (!empty($place['arr_slide_img'])) {
                        foreach ($place['arr_slide_img'] as $slide_img) {
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
                <span class="section-title-icon" style="line-height:44px;">Địa chỉ</span>
                <?php
                if (!empty($place['video'])) {
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
                                            <video controls class="video" poster="<?php echo $place['video']['thumb'] ?>">
                                                <source src="<?php echo $place['video']['url'] ?>" type="video/mp4">
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

                <a href="#sec_map"><span class="contact-button"><i class="contact-icon icon-map"></i>Bản đồ</span></a>
            </h3>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-12">
            <div class="row">
                <p class="res-contact-detail"><?php echo $place['address'] ?></p>
            </div>
        </div>
    </section>
    <!--END LIÊN HỆ-->
    <div class="break-section"></div>
    <!--LIÊN HỆ-->
    <section>
        <h3 class="section-title-nobg">
            <span style="line-height:44px;" class="section-title-icon">Giới thiệu</span>
            <!--<span class="about-button"><i class="about-icon icon-play-video"></i>Video</span>
            <span class="about-button"><i class="about-icon icon-headphone"></i>Audio</span>-->
        </h3>
        <div class="col-md-12 clearfix" >
            <article class="about-restaurant">
                <?php echo $place['description'] ?>
            </article>
        </div>
    </section>
    <!--END LIÊN HỆ--> 

    <!--THƯ VIỆN ẢNH, VIDEO-->
    <?php
    if (!empty($place['arr_img'])) {
        $max_img = 4;
        $i = 1;
        ?> 
        <section>
            <h3 class="section-title">Thư viện ảnh, video<a href="#section-title" class="see-more pull-right show-modal">+ Thêm</a></h3>
            <div class="thumbnail-box">
                <?php
                if (!empty($place['video'])) {
                    $max_img = 3;
                    ?> 
                    <div class="col-xs-6 col-md-3 thumbnail-item">
                        <a href="#section-title" class="thumbnail" data-toggle="modal" data-target="#modal-video">
                            <img src="<?php echo $place['video']['thumb'] ?>">                            
                        </a>
                    </div>
                    <?php
                }
                ?> 

                <?php
                $i = 1;
                foreach ($place['arr_img'] as $img) {
                    if ($i > $max_img) {
                        break;
                    }

                    $i++;
                    ?> 
                    <div class="col-xs-6 col-md-3 thumbnail-item">
                        <a href="#section-title" class="thumbnail show-modal" imgcode="carousel-img<?php echo $i ?>">
                            <img src="<?php echo $img ?>">
                        </a>
                    </div> 
                    <?php
                }
                ?>   
            </div>
            <div class="clearfix"></div>
        </section>
        <!-- Modal image -->
        <div class="modal fade" id="modal-media" active-slide='' tabindex="-1" role="dialog" >
            <div class="modal-dialog media-popup" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="owl-carousel">
                            <?php foreach ($place['arr_img'] as $img) {
                                ?> 
                                <div class="item" id="carousel-img1">
                                    <img src="<?php echo $img ?>" alt="">
                                </div>
                                <?php
                            }
                            ?>    
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
    ?>   

    <!--END THƯ VIỆN ẢNH, VIDEO-->
    <!-- MAP -->
    <section id="sec_map">
        <h3 class="section-title">
            <span class="pull-left">Bản đồ</span>
        </h3>
        <div class="col-md-12 map-box" id="map" style="width: 100%; height: 300px">        
            <script async defer
                    src="https://maps.googleapis.com/maps/api/js?signed_in=true&callback=initMap">
            </script>
            <script type="text/javascript">
                function initMap()
                {
                    var myLatLng = {lat: <?php echo $place['loc']['coordinates']['1'] ?>, lng: <?php echo $place['loc']['coordinates']['0'] ?>};
                    var map = new google.maps.Map(document.getElementById('map'), {
                        zoom: 17,
                        center: myLatLng
                    });

                    var marker = new google.maps.Marker({
                        position: myLatLng,
                        map: map,
                        title: "<?php echo $place['name']; ?>"
                    });
                }
            </script>
        </div>

    </section>    
    <!-- END MAP -->
    <div class="break-section"></div>
    <!-- NHÀ HÀNG XUNG QUANH -->
    <section>
        <h3 class="section-title-nobg">
            <a href="<?php
            echo Router::url(array(
                'controller' => 'Home',
                'action' => 'nearPoint',
                '?' => array(
                    'type' => 'places',
                    'object_id' => $place['id'],
                    'distance' => 10000,
                ),
            ))
            ?>" class="pull-left more-hotel"><img src="<?php echo Router::url('/') ?>css/assets/img/local-gray-icon.png">Địa điểm xung quanh (<?php echo $arr_result['data']['count_around'] ?>)</a>
        </h3>
        <div class="clearfix"></div>
    </section>
    <?php
}
?>  
<!-- END NHÀ HÀNG XUNG QUANH -->
