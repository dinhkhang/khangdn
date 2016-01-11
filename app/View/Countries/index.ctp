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
                                    'label' => __('country_name'),
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
                                    'label' => __('country_code'),
                                    'empty' => '-------',
                                    'default' => $this->request->query('code'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('order', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('country_order'),
                                    'empty' => '-------',
                                    'default' => $this->request->query('order'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('dial_code', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('country_dial_code'),
                                    'empty' => '-------',
                                    'default' => $this->request->query('dial_code'),
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
                                    'label' => __('country_status'),
                                    'options' => $status,
                                    'empty' => '-------',
                                    'default' => $this->request->query('status'),
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
                                                        <th>
                                                                <?php
                                                                echo $this->Paginator->sort('name', __('country_name'));
                                                                ?>
                                                        </th>
                                                        <th>
                                                                <?php
                                                                echo $this->Paginator->sort('code', __('country_code'));
                                                                ?>
                                                        </th>
                                                        <th>
                                                                <?php
                                                                echo $this->Paginator->sort('order', __('country_order'));
                                                                ?>
                                                        </th>
                                                        <th>
                                                                <?php
                                                                echo $this->Paginator->sort('dial_code', __('country_dial_code'));
                                                                ?>
                                                        </th>
                                                        <th>
                                                                <?php
                                                                echo $this->Paginator->sort('status', __('country_status'));
                                                                ?>
                                                        </th>
                                                        <th><?php echo __('operation') ?></th>
                                                <?php else: ?>
                                                        <th><?php echo __('no') ?></th>
                                                        <th><?php echo __('country_name') ?></th>
                                                        <th><?php echo __('country_code') ?></th>
                                                        <th><?php echo __('country_order') ?></th>
                                                        <th><?php echo __('country_dial_code') ?></th>
                                                        <th><?php echo __('country_status') ?></th>
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
                                                        <tr>
                                                                <td>
                                                                        <?php
                                                                        $id = $item[$model_name]['id'];
                                                                        echo $this->Form->create($model_name, array('url' => Router::url('edit/' . $id, true)
                                                                        ));
                                                                        echo $this->Form->hidden($model_name . '.id', array(
                                                                            'value' => $id,
                                                                        ));
                                                                        echo $stt;
                                                                        ?>
                                                                </td>
                                                                <td><?php echo $item[$model_name]['name'] ?></td>
                                                                <td><?php echo $item[$model_name]['code'] ?></td>
                                                                <td><?= isset($item[$model_name]['order']) && $item[$model_name]['order'] ? $item[$model_name]['order'] : ''; ?></td>
                                                                <td><?php echo $item[$model_name]['dial_code'] ?></td>
                                                                <td>
                                                                        <?php
                                                                        $user = CakeSession::read('Auth.User');
                                                                        // ẩn edit status đối với user có type là CONTENT_PROVIDER
                                                                        if ($user['type'] !== 'CONTENT_PROVIDER') {
                                                                                echo $this->Form->input('status', array(
                                                                                    'div' => false,
                                                                                    'class' => 'form-control',
                                                                                    'label' => false,
                                                                                    'options' => $status,
                                                                                    'default' => $item[$model_name]['status'],
                                                                                ));
                                                                        } else {
                                                                                echo $status[$item[$model_name]['status']];
                                                                        }
                                                                        ?>
                                                                </td>
                                                                <td>
                                                                        <button class="btn btn-primary" type="submit">
                                                                                <i class="fa fa-save"></i>
                                                                        </button>
                                                                        <a href="<?php echo Router::url(array('action' => 'edit', $id)) ?>" class="btn btn-primary" title="<?php echo __('edit_btn') ?>">
                                                                                <i class="fa fa-edit"></i>
                                                                        </a>
                                                                        <a href="<?php echo Router::url(array('action' => 'reqDelete', $id)) ?>" class="btn btn-danger remove" title="<?php echo __('delete_btn') ?>">
                                                                                <i class="fa fa-trash"></i>
                                                                        </a>
                                                                        <?php
                                                                        echo $this->Form->end();
                                                                        ?>
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

