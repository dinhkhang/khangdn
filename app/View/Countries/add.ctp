<?php
// sử dụng công cụ soạn thảo
echo $this->element('js/tinymce');
?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <?php
                echo $this->Form->create($model_name, array(
                    'class' => 'form-horizontal',
                ));
                ?>
                <?php
                if (!empty($this->request->data[$model_name]['id'])) {

                        echo $this->Form->hidden($model_name . '.id', array(
                            'value' => $this->request->data[$model_name]['id'],
                        ));
                }
                ?>
                <?php
                $name_err = $this->Form->error($model_name . '.name');
                $name_err_class = !empty($name_err) ? 'has-error' : '';
                ?>
                <div class="form-group <?php echo $name_err_class ?>">
                    <label class="col-sm-2 control-label"><?php echo __('country_name') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.name', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <?php
                $code_err = $this->Form->error($model_name . '.code');
                $code_err_class = !empty($code_err) ? 'has-error' : '';
                ?>
                <div class="form-group <?php echo $code_err_class ?>">
                    <label class="col-sm-2 control-label"><?php echo __('country_code') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.code', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <?php
                $code_err = $this->Form->error($model_name . '.order');
                $code_err_class = !empty($code_err) ? 'has-error' : '';
                ?>
                <div class="form-group <?php echo $code_err_class ?>">
                    <label class="col-sm-2 control-label"><?php echo __('country_order') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.order', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <?php
                $dial_code_err = $this->Form->error($model_name . '.dial_code');
                $dial_code_err_class = !empty($dial_code_err) ? 'has-error' : '';
                ?>
                <div class="form-group <?php echo $dial_code_err_class ?>">
                    <label class="col-sm-2 control-label"><?php echo __('country_dial_code') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.dial_code', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <?php
                $latitude_err = $this->Form->error($model_name . '.latitude');
                $latitude_err_class = !empty($latitude_err) ? 'has-error' : '';
                ?>
                <div class="form-group <?php echo $latitude_err_class ?>">
                    <label class="col-sm-2 control-label"><?php echo __('country_latitude') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.latitude', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <?php
                $longitude_err = $this->Form->error($model_name . '.longitude');
                $longitude_err_class = !empty($longitude_err) ? 'has-error' : '';
                ?>
                <div class="form-group <?php echo $longitude_err_class ?>">
                    <label class="col-sm-2 control-label"><?php echo __('country_longitude') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.longitude', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <?php
                $user = CakeSession::read('Auth.User');
                ?>
                <?php
                // ẩn edit status đối với user có type là CONTENT_PROVIDER
                if ($user['type'] !== 'CONTENT_PROVIDER'):
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?php echo __('country_status') ?></label>

                            <div class="col-sm-10">
                                <?php
                                echo $this->Form->input($model_name . '.status', array(
                                    'class' => 'form-control',
                                    'div' => false,
                                    'label' => false,
                                    'default' => 1,
                                    'options' => $status,
                                ));
                                ?>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <?php
                endif;
                ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('country_description') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->textarea($model_name . '.description', array(
                            'class' => 'form-control editor',
                            'div' => false,
                            'label' => false,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <a href="<?php echo Router::url(array('action' => 'index')) ?>" class="btn btn-white"><i class="fa fa-ban"></i> <span><?php echo __('cancel_btn') ?></span> </a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <span><?php echo __('save_btn') ?></span> </button>
                    </div>
                </div>
                <?php
                echo $this->Form->end();
                ?>
            </div>
        </div>
    </div>
</div>