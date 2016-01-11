<?php
echo $this->start('top-banner');

if (empty($object_type)) {

    echo $this->element('tag_autocomplete');

    $nearby_url = Router::url(array(
                'controller' => 'Home',
                'action' => 'nearby',
                    ), true);
    $search_url = Router::url(array(
                'controller' => 'Home',
                'action' => 'search',
                    ), true);
} else {

    echo $this->element('tag_autocomplete', array(
        'object_type' => $object_type,
    ));

    $nearby_router = array(
        'controller' => 'Home',
        'action' => 'nearby',
        '?' => array(
            'type' => $object_type,
        ),
    );
    if (in_array($object_type, array('places', 'regions'))) {

        $nearby_router['?']['distance'] = 10000;
    }
    $nearby_url = Router::url($nearby_router, true);
    $search_url = Router::url(array(
                'controller' => 'Home',
                'action' => 'search',
                '?' => array(
                    'type' => $object_type,
                ),
                    ), true);
}
if (empty($search_placeholder)) {

    $search_placeholder = 'Địa điểm, khách sạn, nhà hàng';
}
?>
<div class="top-banner <?php echo ($this->params['controller'] == 'Home') ? "home-banner" : "" ?>">
    <img src="<?php echo Router::url('/') ?>css/assets/img/top-banner.png" class="banner-img">
    <div class="search-location">
        <?php if (empty($object_type) || !in_array($object_type, array('events', 'tours'))): ?>
            <a href="<?php echo $nearby_url ?>" class="extra_param nearby">

                <div class="search-intro text-center">
                    <img src="<?php echo Router::url('/') ?>css/assets/img/intro-icon.png" class="intro-icon">
                    <span class="intro-text">Xung quanh bạn</span>
                </div>
            </a>
        <?php endif; ?>
        <?php
        echo $this->Form->create('Search', array(
            'type' => 'get',
            'url' => $search_url,
            'class' => 'form-inline search-location-form keyword-form',
        ));
        ?>
        <?php
        if (!empty($object_type)) {

            echo $this->Form->hidden('type', array(
                'value' => $object_type,
            ));
        }
        ?>
        <div class="input-group">
            <input class="form-control input-border tag-autocomplete" placeholder="<?php echo $search_placeholder ?>" name="keyword" type="text" required>
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
</div>
<?php
echo $this->end();
?>