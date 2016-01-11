<?php
echo $this->Form->create('Search', array(
    'type' => 'get',
    'url' => array(
        'controller' => 'Home',
        'action' => 'nearby',
    ),
));

echo $this->Form->hidden('lat', array(
    'value' => $lat,
));
echo $this->Form->hidden('lng', array(
    'value' => $lng,
));
?>
<section class="option-display-restaurant">
    <h3 class="section-title-nobg">
        <span style="line-height:44px;">Danh mục</span>
    </h3>
    <div class="clearfix"></div>
    <?php
    $checked_place = $checked_hotel = $checked_restaurant = '';
    if (in_array('places', $type)) {

        $checked_place = 'checked';
    }
    if (in_array('hotels', $type)) {

        $checked_hotel = 'checked';
    }
    if (in_array('restaurants', $type)) {

        $checked_restaurant = 'checked';
    }
    ?>
    <div class="option-gr">
        <div class="col-xs-4 col-sm-4 col-md-4">
            <input type="checkbox" name="type[]" id="ck1" class="css-checkbox nearby-type" <?php echo $checked_place ?> value="places" />
            <label for="ck1" class="checkbox-label">Địa điểm</label>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4">
            <input type="checkbox" name="type[]" id="ck2" class="css-checkbox nearby-type" <?php echo $checked_hotel ?> value="hotels" />
            <label for="ck2" class="checkbox-label">Khách sạn</label>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4">
            <input type="checkbox" name="type[]" id="ck3" class="css-checkbox nearby-type" <?php echo $checked_restaurant ?> value="restaurants" />
            <label for="ck3" class="checkbox-label">Nhà hàng</label>
        </div>
    </div>
    <div class="clearfix"></div>
</section>
<section class="option-display-restaurant no-margin-top">
    <h3 class="section-title-nobg">
        <span style="line-height:44px;">Khoảng cách</span>
    </h3>
    <div class="clearfix"></div>
    <?php
    $checked_2km = $checked_5km = $checked_10km = '';
    switch ($distance):
        case '2000':
            $checked_2km = 'checked';
            break;
        case '5000':
            $checked_5km = 'checked';
            break;
        case '10000':
            $checked_10km = 'checked';
            break;
        default :
            $checked_2km = 'checked';
    endswitch;
    ?>
    <div class="option-gr">
        <div class="col-xs-4 col-sm-4 col-md-4">
            <input type="radio" name="distance" id="radio1" <?php echo $checked_2km ?> class="css-checkbox" value="2000" />
            <label for="radio1" class="css-label radGroup2">2 KM</label>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4">
            <input type="radio" name="distance" id="radio2" <?php echo $checked_5km ?> class="css-checkbox" value="5000" />
            <label for="radio2" class="css-label radGroup2">5 KM</label>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4">
            <input type="radio" name="distance" id="radio3" <?php echo $checked_10km ?> class="css-checkbox" value="10000" />
            <label for="radio3" class="css-label radGroup2">10 KM</label>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-12">
        <button class="cus-btn cus-btn-2">OK</button>
    </div>
    <div class="clearfix"></div>
</section>
<?php
echo $this->Form->end();
?>

<?php if (!empty($res['data']['arr_type'])): ?>
    <?php foreach ($res['data']['arr_type'] as $type): ?>
        <?php
        if (empty($type['total'])) {

            continue;
        }
        ?>
        <section>
            <div>
                <h3 class="section-title">
                    <span class="pull-left"><?php echo $type['name'] ?></span>
                    <span href="" class="count-post pull-left"><?php echo $type['total'] ?></span>
                    <?php if ($type['total'] > $limit): ?>
                        <?php
                        if (in_array($type['type'], array('places', 'regions'))) {

                            $distance = ($distance < 10000) ? 10000 : $distance;
                        }
                        ?>
                        <a href="<?php
                        echo Router::url(array(
                            'controller' => 'Home',
                            'action' => 'nearby',
                            '?' => array(
                                'type' => $type['type'],
                                'distance' => $distance,
                                'lat' => $lat,
                                'lng' => $lng,
                            ),
                        ))
                        ?>" class="see-more pull-right">+ Thêm</a>
                       <?php endif; ?>
                </h3>
            </div>
            <div class="clearfix"></div>
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <?php foreach ($type['arr_object'] as $item): ?>
                        <div class="swiper-slide">
                            <a href="<?php
                            echo Router::url(array(
                                'controller' => ucfirst($item['type']),
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
                                    <?php if (in_array($item['type'], array('places'))): ?>
                                        <span class="slide-detail tour-price object-address"><?php echo $item['address'] ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($item['price'])): ?>
                                        <span class="slide-detail tour-price"><?php echo number_format($item['price']['amount']) ?> <?php echo $item['price']['currency_code'] ?></span>
                                    <?php endif; ?>
                                    <?php if (in_array($item['type'], array('hotels', 'restaurants'))): ?>
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
<?php
echo $this->element('Home/empty');
?>
<script>
    $(function () {

        $('.nearby-type').on('change', function () {

            if (!$(this).prop('checked')) {

                var checked = validate();
                if (checked < 1) {

                    $(this).prop('checked', true);
                    return false;
                }
            }
        });

        function validate() {

            return $('.nearby-type:checked').length;
        }
    });
</script>

