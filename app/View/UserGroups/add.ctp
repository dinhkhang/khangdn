<?php
echo $this->element('js/chosen')
?>
<script>
	$(function () {

		$('.permissions').chosen();
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
				$use_gro_name_err = $this->Form->error($model_name . '.name');
				$use_gro_name_err_class = !empty($use_gro_name_err) ? 'has-error' : '';
				?>
				<div class="form-group <?php echo $use_gro_name_err_class ?>">
					<label class="col-sm-2 control-label"><?php echo __('user_group_name') ?> <?php echo $this->element('required') ?></label>

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
				<div class="form-group">
					<label class="col-sm-2 control-label"><?php echo __('user_group_permissions') ?> <?php echo $this->element('required') ?></label>

					<div class="col-sm-10">
						<?php
						echo $this->Form->input($model_name . '.permissions', array(
							'class' => 'form-control permissions',
							'div' => false,
							'label' => false,
							'required' => true,
							'options' => $permissions,
							'multiple' => true,
							'placeholder' => __('user_group_permissions_placeholder'),
						));
						?>
					</div>
				</div>
				<div class="hr-line-dashed"></div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?php echo __('user_group_status') ?></label>

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
					<label class="col-sm-2 control-label"><?php echo __('user_group_description') ?></label>

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