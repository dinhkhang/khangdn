<?php
echo $this->element('js/nestable');
?>
<?php
echo $this->element('page-heading-with-add-action');
?>
<div class="ibox float-e-margins">
    <div class="ibox-content">
        <?php if (!empty($list_data)): ?>
                <div id="nestable-menu">
                    <button type="button" data-action="expand-all" class="btn btn-white btn-sm"><?php echo __('expand_all_btn') ?></button>
                    <button type="button" data-action="collapse-all" class="btn btn-white btn-sm"><?php echo __('collapse_all_btn') ?></button>
                </div>
                <div class="list-container">
                    <div class="dd" id="nestable2">
                        <?php
                        $ol_clss = 'dd-list';
                        $li_clss = 'dd-item';
                        $div_clss = 'dd-handle';
                        $key = 'name';
                        ?>
                        <?php
                        echo $this->element('TreeCommon/nested_list', array(
                            'list_data' => $list_data,
                            'ol_clss' => $ol_clss,
                            'li_clss' => $li_clss,
                            'div_clss' => $div_clss,
                            'key' => $key,
                        ));
                        ?>

                    </div>
                    <?php
                    echo $this->Form->create($model_name, array(
                        'class' => 'form-serialize',
                    ));
                    ?>
                    <?php
                    echo $this->Form->hidden('serialize', array(
                        'class' => 'serialize',
                    ));
                    ?>
                    <div class="row">
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary"><?php echo __('save_btn') ?></button>
                            </div>
                        </div>
                    </div>
                    <?php
                    echo $this->Form->end();
                    ?>
                </div>
        <?php else: ?>
                <div class="faq-item">
                    <div class="row">
                        <div class="text-center p-lg">
                            <p><?php echo __('no_result') ?></p>
                        </div>
                    </div>
                </div>
        <?php endif; ?>
    </div>
</div>