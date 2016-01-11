<section class="top-section">
        <ul class="bank-list" id="container-list">
                <?php foreach ($datas[$model_name] AS $item) : ?>
                    <li class="box col-xs-12 col-sm-6 col-md-4">
                            <div class="row">
                                    <div class="bank-item">
                                            <div class="thumbnail bank-thumbnail">
                                                    <a href="javascript:void(0)" class="bank-banner-link">
                                                            <img src="<?php echo $item['logo']; ?>" class="img-responsive">
                                                    </a>
                                                    <div class="caption bank-info">
                                                            <a href="javascript:void(0)">
                                                                    <h3 class="bank-name"><?php echo $item['name']; ?></h3>
                                                            </a>
                                                            <?php if (isset($item['address']) && strlen($item['address']) > 1) : ?>
                                                                <p class="bank-address-text">Address: <?php echo $item['address']; ?></p>
                                                            <?php endif; ?>
                                                    </div>
                                            </div>
                                            <?php if (isset($item['tel']) && strlen($item['tel']) > 1) : ?>
                                                <p class="bank-contact">Tel: <?php echo $this->Common->parseTel($item['tel']); ?></p>
                                            <?php endif; ?>
                                    </div>
                            </div>
                            <div class="clearfix"></div>
                    </li>
                <?php endforeach; ?>
        </ul>
        <div class="clearfix"></div>
</section>
<?php
echo $this->element('infinite-scroll', array('query_param' => $this->request->query));
