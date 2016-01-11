<section class="top-section" id="container-list">
        <ul class="recently-location-list vehicle-list">
                <?php foreach ($arr_transport['utility'] AS $item) : ?>
                    <li>
                            <a href="<?php echo $item['link']; ?>" class="extra_param">
                                    <img src="<?php echo $item['icon']; ?>" class="pull-left vehicle-icon">
                                    <h3 class="pull-left"><?php echo $item['name']; ?></h3>
                            </a>
                    </li>
                <?php endforeach; ?>
        </ul>
        <div class="clearfix"></div>
</section>
