<?php
echo $this->element('page-heading-with-add-action');
?>
<div class="ibox-content m-b-sm border-bottom">
        <?php
        echo $this->Form->create('Search', array(
            'url' => array(
                'action' => $this->action,
                'controller' => Inflector::pluralize($model_name),
            ),
            'type' => 'get',
        ))
        ?>
        <div class="row">
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('name', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('service_provider_name'),
                                    'default' => $this->request->query('name'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('code', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('service_provider_code'),
                                    'empty' => '-------',
                                    'default' => $this->request->query('code'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('mobile', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('service_provider_mobile'),
                                    'empty' => '-------',
                                    'default' => $this->request->query('mobile'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('status', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('service_provider_status'),
                                    'options' => $status,
                                    'empty' => '-------',
                                    'default' => $this->request->query('status'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('platform_provider_code', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('service_provider_platform_provider_code'),
                                    'options' => $plastformProviderList,
                                    'empty' => '-------',
                                    'default' => $this->request->query('platform_provider_code'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div>
                                <label style="visibility: hidden"><?php echo __('search_btn') ?></label>
                        </div>
                        <?php echo $this->element('buttonSearchClear');?>
                </div>
        </div>
        <?php echo $this->Form->end(); ?>
</div>
<div class="ibox float-e-margins">
        <div class="ibox-content">
                <div class="table-responsive">
                        <table class="table table-striped">
                                <thead>
                                        <tr>
                                                <?php if (!empty($list_data)): ?>
                                                        <th><?php echo __('no') ?></th>
                                                        <th><?php echo $this->Paginator->sort('platform_provider_code', __('service_provider_platform_provider_code')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('code', __('service_provider_code')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('name', __('service_provider_name')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('cms_domain', __('service_provider_cms_domain')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('website', __('service_provider_website')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('sologan', __('service_provider_sologan')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('email', __('service_provider_email')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('mobile', __('service_provider_mobile')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('server_ip', __('service_provider_server_ip')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('private_key', __('service_provider_private_key')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('status', __('service_provider_status')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('modified', __('service_provider_modified')); ?></th>
                                                        <th><?php echo __('operation') ?></th>
                                                <?php else: ?>
                                                        <th><?php echo __('no') ?></th>
                                                        <th><?php echo __('service_provider_platform_provider_code'); ?></th>
                                                        <th><?php echo __('service_provider_code'); ?></th>
                                                        <th><?php echo __('service_provider_name'); ?></th>
                                                        <th><?php echo __('service_provider_cms_domain'); ?></th>
                                                        <th><?php echo __('service_provider_website'); ?></th>
                                                        <th><?php echo __('service_provider_sologan'); ?></th>
                                                        <th><?php echo __('service_provider_email'); ?></th>
                                                        <th><?php echo __('service_provider_mobile'); ?></th>
                                                        <th><?php echo __('service_provider_server_ip'); ?></th>
                                                        <th><?php echo __('service_provider_private_key'); ?></th>
                                                        <th><?php echo __('service_provider_status'); ?></th>
                                                        <th><?php echo __('service_provider_modified'); ?></th>
                                                        <th><?php echo __('operation') ?></th>
                                                <?php endif; ?>
                                        </tr>
                                </thead>
                                <tbody>
                                        <?php if (!empty($list_data)): ?>
                                                <?php
                                                $stt = $this->Paginator->counter('{:start}');
                                                ?>
                                                <?php foreach ($list_data as $item): ?>
                                                        <?php
                                                        $id = $item[$model_name]['id'];
                                                        ?>
                                                        <tr>
                                                                <td><?php echo $stt ?></td>
                                                                <td><?= $plastformProviderList[$item[$model_name]['platform_provider_code']]; ?></td>
                                                                <td><?= $item[$model_name]['code']; ?></td>
                                                                <td><?= $item[$model_name]['name']; ?></td>
                                                                <td><?= $item[$model_name]['cms_domain']; ?></td>
                                                                <td><?= $item[$model_name]['website']; ?></td>
                                                                <td><?= $item[$model_name]['sologan']; ?></td>
                                                                <td><?= $item[$model_name]['email']; ?></td>
                                                                <td><?= $item[$model_name]['mobile']; ?></td>
                                                                <td><?= $item[$model_name]['server_ip']; ?></td>
                                                                <td><?= $item[$model_name]['private_key']; ?></td>
                                                                <td><?= $status[$item[$model_name]['status']]; ?></td>
                                                                <td><?= date('d-m-Y ', $item[$model_name]['modified']->sec); ?></td>
                                                                <td>
                                                                        <a href="<?php echo Router::url(array('action' => 'edit', $id)) ?>" class="btn btn-primary" title="<?php echo __('edit_btn') ?>">
                                                                                <i class="fa fa-edit"></i>
                                                                        </a>
                                                                        <a href="<?php echo Router::url(array('action' => 'reqDelete', $id)) ?>" class="btn btn-danger remove" title="<?php echo __('delete_btn') ?>">
                                                                                <i class="fa fa-trash"></i>
                                                                        </a>
                                                                </td>
                                                        </tr>
                                                        <?php $stt++; ?>
                                                <?php endforeach; ?>
                                        <?php else: ?>
                                                <tr>
                                                        <td colspan="8" style="text-align: center"><?php echo __('no_result') ?></td>
                                                </tr>
                                        <?php endif; ?>
                                </tbody>
                        </table>
                </div>
                <?php echo $this->element('pagination'); ?>
        </div>
</div>

