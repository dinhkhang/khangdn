<style>
    .caption{
        overflow: hidden;
    }
</style>
<?php
echo $this->element('tag_autocomplete');
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
    if (!empty($region_type) && !empty($region_object_id)) {

        echo $this->Form->hidden('type', array(
            'value' => $region_type,
        ));
        echo $this->Form->hidden('object_id', array(
            'value' => $region_object_id,
        ));
    }
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
<?php if (!empty($res['data']['arr_type'])): ?>
    <?php foreach ($res['data']['arr_type'] as $type): ?>
        <?php
        if (empty($type['total'])) {

            continue;
        }
        ?>
        <?php if (!in_array($type['type'], array('events'))): ?>
            <section>
                <div>
                    <h3 class="section-title">
                        <span class="pull-left"><?php echo $type['name'] ?></span>
                        <span href="" class="count-post pull-left"><?php echo $type['total'] ?></span>
                        <?php if ($type['total'] > $limit): ?>
                            <a href="<?php
                            echo Router::url(array(
                                'controller' => 'Home',
                                'action' => 'search',
                                '?' => array(
                                    'type' => $type['type'],
                                    'keyword' => $keyword,
                                    'region_id' => $region_id,
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
        <?php else: ?>
            <section>
                <h3 class="section-title">
                    <span class="pull-left"><?php echo $type['name'] ?></span>
                    <?php if ($type['total'] > $limit): ?>
                        <a href="<?php
                        echo Router::url(array(
                            'controller' => 'Home',
                            'action' => 'search',
                            '?' => array(
                                'type' => $type['type'],
                                'keyword' => $keyword,
                            ),
                        ))
                        ?>" class="see-more pull-right">+ Thêm</a>
                       <?php endif; ?>
                </h3>
                <ul class="event-list event-list-fix-height">
                    <?php foreach ($type['arr_object'] as $item): ?>
                        <li class="col-sm-6 col-md-6">
                            <div>
                                <div class="row">
                                    <div class="thumbnail event-thumbnail">
                                        <a href="<?php
                                        echo Router::url(array(
                                            'controller' => ucfirst($item['type']),
                                            'action' => 'info',
                                            '?' => array(
                                                'id' => $item['id'],
                                            ),
                                        ))
                                        ?>">
                                            <img src="<?php echo $item['banner'] ?>" class="img-responsive">
                                        </a>
                                        <div class="caption">
                                            <a href="<?php
                                            echo Router::url(array(
                                                'controller' => ucfirst($item['type']),
                                                'action' => 'info',
                                                '?' => array(
                                                    'id' => $item['id'],
                                                ),
                                            ))
                                            ?>">
                                                <h3 class="object-title"><?php echo $item['name'] ?></h3>
                                            </a>
                                            <p class="post-date"><?php echo date('H:i d/m/Y', $item['release_date']->sec) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="clearfix"></div>
            </section>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
<?php
echo $this->element('Home/empty');
?>


