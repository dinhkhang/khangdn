<div>
	<div>
		<h1 class="logo-name">AS+</h1>
	</div>
	<h3>Welcome to AS+</h3>
	<p>Login in. To see it in action.</p>
	<?php
	echo $this->Form->create($model_name, array(
		'class' => 'm-t',
		'role' => 'form',
	));
	?>
	<div class="form-group">
		<?php
		echo $this->Form->input('username', array(
			'placeholder' => __('username_placeholder'),
			'required' => true,
			'div' => false,
			'label' => false,
			'class' => 'form-control',
		));
		?>
	</div>
	<div class="form-group">
		<?php
		echo $this->Form->input('password', array(
			'placeholder' => __('password_placeholder'),
			'required' => true,
			'div' => false,
			'label' => false,
			'class' => 'form-control',
			'type' => 'password',
		));
		?>
	</div>
	<?php
	echo $this->Form->button(__('log_in'), array(
		'class' => 'btn btn-primary block full-width m-b',
	));
	?>
	<?php echo $this->Form->end() ?>
</div>