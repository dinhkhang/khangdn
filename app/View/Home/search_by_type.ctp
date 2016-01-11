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
echo $this->element('tag_autocomplete', array(
    'object_type' => $type[0],
));
?>
<?php echo $this->start('top-banner'); ?>
<div class="search-form col-md-12">
    <h2>Tìm kiếm</h2>
    <?php
    echo $this->Form->create('Search', array(
        'type' => 'get',
        'class' => 'navbar-form keyword-form',
        'role' => 'search',
    ));
    ?>
    <?php
    echo $this->Form->hidden('type', array(
        'value' => $type[0],
    ));
    if (!empty($region_id)) {

        echo $this->Form->hidden('region_id', array(
            'value' => $region_id,
        ));
    }
    ?>
    <div class="input-group">
        <?php
        echo $this->Form->input('keyword', array(
            'class' => 'form-control input-border tag-autocomplete',
            'placeholder' => 'Search',
            'type' => 'text',
            'div' => false,
            'label' => false,
            'default' => $this->request->query('keyword'),
            'required' => true,
        ));
        ?>
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
<?php echo $this->end(); ?>
<?php
$empty_count = 0;
?>
<?php if (!empty($res['data']['arr_type'])): ?>
    <?php foreach ($res['data']['arr_type'] as $type): ?>
        <?php
        if (empty($type['total'])) {

            $empty_count++;
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
            <div class="clearfix"></div>
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
            <div class="clearfix"></div>
        </section>
    <?php endforeach; ?>
<?php endif;
?>
<?php
echo $this->element('Home/empty');
?>

