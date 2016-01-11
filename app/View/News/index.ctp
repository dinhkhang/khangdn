<?php
echo $this->element('js/chosen');
echo $this->Html->script('location');
echo $this->element('page-heading-with-add-action');
echo $this->element('js/datetimepicker');
echo $this->Html->script('search');
?>
<div class="ibox-content m-b-sm border-bottom">
        <?php
        echo $this->Form->create('Search', array(
            'url' => array(
                'action' => $this->action,
                'controller' => 'news',
            ),
            'type' => 'get',
        ))
        ?>
        <div class="row">
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('location', array(
                                    'div' => false,
                                    'class' => 'form-control select-ajax',
                                    'label' => __('activity_location'),
                                    'type' => 'select',
                                    'id' => 'Location',
                                    'data-ajax--url' => Router::url('/activities/search/Location/1'),
                                    'default' => $this->request->query('location'),
                                    'options' => isset($locationInfo, $locationInfo['location']) ? $locationInfo['location'] : [],
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('name', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('new_name'),
                                    'default' => $this->request->query('name'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('order', array(
                                    'type' => 'number',
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('place_order'),
                                    'default' => $this->request->query('order'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('source', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('new_source'),
                                    'empty' => '-------',
                                    'default' => $this->request->query('source'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('news_categories', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('category'),
                                    'options' => $categories,
                                    'empty' => '-------',
                                    'default' => $this->request->query('news_categories'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                                <?php
                                echo $this->Form->input('news_collections', array(
                                    'div' => false,
                                    'class' => 'form-control',
                                    'label' => __('collection'),
                                    'options' => $collections,
                                    'empty' => '-------',
                                    'default' => $this->request->query('news_collections'),
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
                                    'label' => __('new_status'),
                                    'options' => $status,
                                    'empty' => '-------',
                                    'default' => $this->request->query('status'),
                                ));
                                ?>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group date">
                                <label><?= __('new_modified'); ?></label>
                                <div id="datepicker" class="input-daterange input-group">
                                        <span class="input-group-addon"><?= __('date_from'); ?></span>
                                        <input type="text" name="modified_start" class="text-center input-sm form-control datepicker showDate" value="<?= $this->request->query('modified_start'); ?>">
                                        <span class="input-group-addon"><?= __('date_to'); ?></span>
                                        <input type="text" name="modified_end" class="text-center input-sm form-control datepicker showDate" value="<?= $this->request->query('modified_end'); ?>">
                                </div>
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
                                                        <th><?php echo __('new_location') ?></th>
                                                        <th><?php echo $this->Paginator->sort('name', __('new_name')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('order', __('new_order')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('source', __('new_source')); ?></th>
                                                        <th><?php echo $this->Paginator->sort('status', __('new_status')); ?></th>
                                                        <th><?php echo __('category') ?></th>
                                                        <th><?php echo __('collection') ?></th>
                                                        <th><?php echo $this->Paginator->sort('modified', __('new_modified')); ?></th>
                                                        <th><?php echo __('operation') ?></th>
                                                <?php else: ?>
                                                        <th><?php echo __('no') ?></th>
                                                        <th><?php echo __('new_location') ?></th>
                                                        <th><?php echo __('new_name') ?></th>
                                                        <th><?php echo __('new_order') ?></th>
                                                        <th><?php echo __('new_source') ?></th>
                                                        <th><?php echo __('new_status') ?></th>
                                                        <th><?php echo __('category') ?></th>
                                                        <th><?php echo __('collection') ?></th>
                                                        <th><?php echo __('new_modified') ?></th>
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
                                                                <td><?= isset($item[$model_name]['location']) ? $listLocation[$item[$model_name]['location']->{'$id'}] : ''; ?></td>
                                                                <td><?= $item[$model_name]['name']; ?></td>
                                                                <td><?= isset($item[$model_name]['order']) && $item[$model_name]['order'] ? $item[$model_name]['order'] : ''; ?></td>
                                                                <td><?= $item[$model_name]['source']; ?></td>
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
                                                                        <?php
                                                                        echo $this->Form->input('news_categories', array(
                                                                            'div' => false,
                                                                            'multiple' => true,
                                                                            'class' => 'form-control chosen-select',
                                                                            'label' => false,
                                                                            'options' => $categories,
                                                                            'data-placeholder' => ' ',
                                                                            'default' => isset($item[$model_name]['news_categories']) ? $item[$model_name]['news_categories'] : '',
                                                                        ));
                                                                        ?>
                                                                </td>
                                                                <td>
                                                                        <?php
                                                                        echo $this->Form->input('news_collections', array(
                                                                            'div' => false,
                                                                            'multiple' => true,
                                                                            'class' => 'form-control chosen-select',
                                                                            'label' => false,
                                                                            'options' => $collections,
                                                                            'data-placeholder' => ' ',
                                                                            'default' => isset($item[$model_name]['news_collections']) ? $item[$model_name]['news_collections'] : '',
                                                                        ));
                                                                        ?>
                                                                </td>
                                                                <td><?= date('d-m-Y ', $item[$model_name]['modified']->sec); ?></td>
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

