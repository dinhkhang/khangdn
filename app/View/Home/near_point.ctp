<?php echo $this->start('script') ?>
<script src="<?php echo Router::url('/') ?>css/assets/js/jquery.matchHeight-min.js"></script>
<script>
    $(function () {

        $('.slide-img').matchHeight();
    });
</script>
<?php echo $this->end(); ?>
<?php
echo $this->element('js/scroll-to-load');
?>
<?php
echo $this->Form->create('Search', array(
    'type' => 'get',
    'url' => array(
        'controller' => 'Home',
        'action' => 'nearPoint',
    ),
));

echo $this->Form->hidden('type', array(
    'value' => $type[0],
));
echo $this->Form->hidden('object_id', array(
    'value' => $object_id,
));
?>
<section class="option-display-restaurant top-section">
    <h3 class="section-title-nobg">
        <span style="line-height:44px;">Khoảng cách</span>
    </h3>
    <div class="clearfix"></div>
    <?php if (!in_array($type[0], array('regions', 'places'))): ?>
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
    <?php else: ?>
        <?php
        $checked_10km = $checked_20km = $checked_30km = '';
        switch ($distance):
            case '10000':
                $checked_10km = 'checked';
                break;
            case '20000':
                $checked_20km = 'checked';
                break;
            case '30000':
                $checked_30km = 'checked';
                break;
            default :
                $checked_10km = 'checked';
        endswitch;
        ?>
        <div class="option-gr">
            <div class="col-xs-4 col-sm-4 col-md-4">
                <input type="radio" name="distance" id="radio1" <?php echo $checked_10km ?> class="css-checkbox" value="10000" />
                <label for="radio1" class="css-label radGroup2">10 KM</label>
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <input type="radio" name="distance" id="radio2" <?php echo $checked_20km ?> class="css-checkbox" value="20000" />
                <label for="radio2" class="css-label radGroup2">20 KM</label>
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <input type="radio" name="distance" id="radio3" <?php echo $checked_30km ?> class="css-checkbox" value="30000" />
                <label for="radio3" class="css-label radGroup2">30 KM</label>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php endif; ?>
    <div class="col-md-12">
        <button class="cus-btn cus-btn-2">OK</button>
    </div>
    <div class="clearfix"></div>
</section>
<div class="break-section"></div>

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
                </h3>
            </div>
            <div id="scroll-container">
                <?php foreach ($type['arr_object'] as $item): ?>
                    <div class="col-xs-12 col-sm-6 col-md-4 restaurant-item scroll-item">
                        <a href="<?php
                        echo Router::url(array(
                            'controller' => ucfirst($item['type']),
                            'action' => 'info',
                            '?' => array(
                                'id' => $item['id'],
                            ),
                        ))
                        ?>" class="">
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
            <div class="clearfix"></div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
<?php
echo $this->element('Home/empty');
?>

