<section class="top-section" id="container-list">
        <ul class="event-list emegency-list">

                <?php foreach ($datas[$model_name] AS $item) : ?>
                    <li class="box col-xs-12 col-sm-6 col-md-4 emegency-item">
                            <div class="row">
                                    <div>
                                            <div class="thumbnail event-thumbnail emegency-thumbnail">
                                                    <a href="javascript:void(0)">
                                                            <img class="img-responsive" src="<?php echo $item['logo']; ?>">
                                                    </a>
                                                    <div class="caption">
                                                            <a href="javascript:void(0)" class="pull-left emg-name">
                                                                    <h3><?php echo $item['name']; ?></h3>
                                                            </a>
                                                            <?php if (isset($item['address']) && strlen($item['address']) > 1) : ?>
                                                                <p class="taxi-desc">Address: <?php echo $item['address']; ?></p>
                                                            <?php endif; ?>
                                                            <?php if (isset($item['tel']) && strlen($item['tel']) > 1) : ?>
                                                                <a class="taxi-desc pull-left"><img class="emg-call no-margin-top" src="<?php echo Router::url('/') ?>css/assets/img/ngan-hang-call.png" /> <?php echo $this->Common->parseTel($item['tel']); ?></a>
                                                            <?php endif; ?>
                                                            <?php if (isset($item['website']) && strlen($item['website']) > 1) : ?>
                                                                <p class="taxi-desc">Web: <?php echo $item['website']; ?></p>
                                                            <?php endif; ?>
                                                            <?php if (isset($item['tel']) && strlen($item['tel']) > 1) : ?>
                                                                <?php echo $this->Common->parseTel($item['tel'], 'call-taxi-btn call-emegency'); ?>
                                                            <?php endif; ?>
                                                    </div>
                                            </div>
                                    </div>
                            </div>
                            <!--<div class="clearfix"></div>-->
                    </li>
                <?php endforeach; ?>
        </ul>
        <div class="clearfix"></div>
</section>