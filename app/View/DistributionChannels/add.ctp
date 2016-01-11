<?php
// sử dụng upload file
echo $this->element('JqueryFileUpload/basic_plus_ui_assets');
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
                                $code_err = $this->Form->error($model_name . '.distributor_code');
                                $code_err_class = !empty($code_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_distributor_code') ?> <?php echo $this->element('required') ?></label>
                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.distributor_code', array(
                                                    'class' => 'form-control',
                                                    'div' => false,
                                                    'label' => false,
                                                    'default' => '',
                                                    'options' => $distributorList,
                                                    'empty' => '---',
                                                    'required' => true,
                                                ));
                                                ?>
                                        </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <?php
                                $name_err = $this->Form->error($model_name . '.name');
                                $name_err_class = !empty($name_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $name_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_name') ?> <?php echo $this->element('required') ?></label>

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
                                $name_err = $this->Form->error($model_name . '.code');
                                $name_err_class = !empty($name_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $name_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_code') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.code', array(
                                                    'class' => 'form-control',
                                                    'div' => false,
                                                    'label' => false,
                                                    'required' => true,
                                                    'disabled' => (bool) $this->Form->value($model_name . '.code'),
                                                ));
                                                ?>
                                        </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <?php
                                $name_err = $this->Form->error($model_name . '.sologan');
                                $name_err_class = !empty($name_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $name_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_sologan') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.sologan', array(
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
                                $name_err = $this->Form->error($model_name . '.private_key');
                                $name_err_class = !empty($name_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $name_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_private_key') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.private_key', array(
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
                                $name_err = $this->Form->error($model_name . '.server_ip');
                                $name_err_class = !empty($name_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $name_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_server_ip') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.server_ip', array(
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
                                $name_err = $this->Form->error($model_name . '.mobile');
                                $name_err_class = !empty($name_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $name_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_mobile') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.mobile', array(
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
                                $name_err = $this->Form->error($model_name . '.email');
                                $name_err_class = !empty($name_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $name_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_email') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.email', array(
                                                    'type' => 'email',
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
                                $dial_code_err = $this->Form->error($model_name . '.website');
                                $dial_code_err_class = !empty($dial_code_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $dial_code_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_website') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.website', array(
                                                    'type' => 'url',
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
                                $dial_code_err = $this->Form->error($model_name . '.wap_domain');
                                $dial_code_err_class = !empty($dial_code_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $dial_code_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_wap_domain') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.wap_domain', array(
                                                    'type' => 'url',
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
                                $code_err = $this->Form->error($model_name . '.description');
                                $code_err_class = !empty($code_err) ? 'has-error' : '';
                                ?>
                                <div class="form-group <?php echo $code_err_class ?>">
                                        <label class="col-sm-2 control-label"><?php echo __('distribution_channel_description') ?> <?php echo $this->element('required') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->Form->input($model_name . '.description', array(
                                                    'type' => 'textarea',
                                                    'class' => 'form-control editor',
                                                    'div' => false,
                                                    'label' => false,
//                                                    'required' => true,
                                                ));
                                                ?>
                                        </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo __('Icon file') ?></label>

                                        <div class="col-sm-10">
                                                <?php
                                                echo $this->element('JqueryFileUpload/basic_plus_ui', array(
                                                    'name' => $model_name . '.files.icon',
                                                    'options' => array(
                                                        'id' => 'icon',
                                                    ),
                                                    'upload_options' => array(
                                                        'maxNumberOfFiles' => 1,
                                                    ),
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
                                                <label class="col-sm-2 control-label"><?php echo __('distribution_channel_status') ?></label>

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