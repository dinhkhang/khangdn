<?php
echo $this->element('top-banner');
echo $this->element('recent_history', array(
    'recent_type' => 'places',
));
?>

<?php if (!empty($res['data']['arr_cate'])): ?>
    <?php foreach ($res['data']['arr_cate'] as $cate): ?>
        <?php
        if (empty($cate['arr_object'])) {

            continue;
        }
        ?>
        <section>
            <div>
                <h3 class="section-title">
                    <?php echo $cate['name'] ?>
                    <a href="<?php
                    echo Router::url(array(
                        'controller' => ucfirst($cate['type']),
                        'action' => 'index',
                    ))
                    ?>" class="see-more pull-right extra_param">+ Thêm</a>
                </h3>
            </div>
            <div class="clearfix"></div>
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <?php foreach ($cate['arr_object'] as $item): ?>
                        <div class="swiper-slide">
                            <a href="<?php
                            echo Router::url(array(
                                'controller' => ucfirst($cate['type']),
                                'action' => 'info',
                                '?' => array(
                                    'id' => $item['id'],
                                ),
                            ))
                            ?>" class="swiper-slide">
                                <div class="slide-img">
                                    <img src="<?php echo $item['banner'] ?>">
                                    <?php if (!empty($item['star'])): ?>
                                        <div class="sticky sticky-orange"><span class="rate-number"><?php echo $item['star'] ?></span><span class="star-rate"></span></div>
                                    <?php endif; ?>
                                </div>
                                <div class="slide-text">
                                    <span class="object-title"><?php echo $item['name'] ?></span>
                                    <?php if (in_array($cate['type'], array('places'))): ?>
                                        <span class="slide-detail tour-price object-address"><?php echo $item['address'] ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($item['price'])): ?>
                                        <span class="slide-detail tour-price"><?php echo number_format($item['price']['amount']) ?> <?php echo $item['price']['currency_code'] ?></span>
                                    <?php endif; ?>
                                    <?php if (in_array($cate['type'], array('hotels', 'restaurants'))): ?>
                                        <span class="slide-detail">
                                            <span class="num-rate"><?php echo!empty($item['score']) ? $item['score'] : 0 ?></span><?php echo!empty($item['rate_count']) ? $item['rate_count'] : 0 ?> Đánh giá
                                        </span>
                                    <?php endif; ?>
                                </div> 
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
<footer>
    <div class="footer-logo text-center">
        <a href=""><img src="<?php echo Router::url('/') ?>css/assets/img/logo-mobifone.jpg"></a>
    </div>
    <p class="footer-text text-center">
        Copyright &copy; 2015 by mobifone.</br>
        All Rights Reserved.
    </p>
</footer>