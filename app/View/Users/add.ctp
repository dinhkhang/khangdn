<?php
echo $this->element('js/chosen');
echo $this->element('js/validate');
?>
<script>
		$(function () {

			// khi user type là content provider hoặc distributor, thì thực hiện ẩn hiện content_provider hoặc distributor
			$('.type').on('change', function () {

				$('.cp-container').hide();
				$('.cp').prop('required', false);

				var type = $(this).val();
				if (type === 'CONTENT_PROVIDER') { // tương ứng với content provider

					$('.cp-container').show();
					$('.cp').prop('required', true);
				}
			});
			$('.type').trigger('change');

			$('form').validate();
			// thực hiện validate cho password và password confirm
			$("#password_confirm").rules("add", {
				equalTo: '#password',
				messages: {
					equalTo: "<?php echo __('password_confirm_invalid') ?>"
				}
			});
		});
</script>
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
                $user_name_err = $this->Form->error($model_name . '.username');
                $user_name_err_class = !empty($user_name_err) ? 'has-error' : '';
                ?>
                <div class="form-group <?php echo $user_name_err_class ?>">
                    <label class="col-sm-2 control-label"><?php echo __('user_username') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.username', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('user_password') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.password', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                            'type' => 'password',
                            'id' => 'password',
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('user_password_confirm') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.password_confirm', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                            'type' => 'password',
                            'id' => 'password_confirm',
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('user_email') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.email', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'required' => true,
                            'type' => 'email',
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('user_type') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.type', array(
                            'class' => 'form-control type',
                            'div' => false,
                            'label' => false,
                            'options' => $type,
                            'required' => true,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('user_user_group') ?> <?php echo $this->element('required') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->input($model_name . '.user_group', array(
                            'class' => 'form-control',
                            'div' => false,
                            'label' => false,
                            'options' => $group,
                            'required' => true,
                        ));
                        ?>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="cp-container">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('user_content_provider') ?> <?php echo $this->element('required') ?></label>

                        <div class="col-sm-10">
                            <?php
                            echo $this->Form->input($model_name . '.content_provider_code', array(
                                'class' => 'form-control cp',
                                'div' => false,
                                'label' => false,
                                'options' => $cp,
                                'empty' => '-------',
                            ));
                            ?>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('user_status') ?></label>

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
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('user_description') ?></label>

                    <div class="col-sm-10">
                        <?php
                        echo $this->Form->textarea($model_name . '.description', array(
                            'class' => 'form-control',
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