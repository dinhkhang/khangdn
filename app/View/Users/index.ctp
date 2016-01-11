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
                echo $this->Form->input('username', array(
                    'div' => false,
                    'class' => 'form-control',
                    'label' => __('user_username'),
                    'default' => $this->request->query('username'),
                ));
                ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <?php
                echo $this->Form->input('user_group', array(
                    'div' => false,
                    'class' => 'form-control',
                    'label' => __('user_user_group'),
                    'default' => $this->request->query('user_group'),
                    'options' => $group,
                    'empty' => '-------',
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
                    'label' => __('user_status'),
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
                        <th><?php echo __('no') ?></th>
                        <th><?php echo __('user_username') ?></th>
                        <th><?php echo __('user_user_group') ?></th>
                        <th><?php echo __('user_description') ?></th>
                        <th><?php echo __('user_status') ?></th>
                        <th><?php echo __('operation') ?></th>
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
                                        <td><?php echo $item[$model_name]['username'] ?></td>
                                        <td>
                                            <?php
                                            $user_group = $this->Common->parseId($item[$model_name]['user_group']);
                                            echo!empty($group[$user_group]) ?
                                                    $group[$user_group] : __('unknown');
                                            ?>
                                        </td>
                                        <td><?php echo nl2br($item[$model_name]['description']) ?></td>
                                        <td>
                                            <?php
                                            echo!empty($status[$item[$model_name]['status']]) ?
                                                    $status[$item[$model_name]['status']] : __('unknown');
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo Router::url(array('action' => 'edit', $id)) ?>" class="btn btn-primary" title="<?php echo __('edit_btn') ?>">
                                                <i class="fa fa-edit"></i>
                                            </a>
                <!--									<a href="<?php echo Router::url(array('action' => 'reqDelete', $id)) ?>" class="btn btn-danger remove" title="<?php echo __('delete_btn') ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>-->
                                        </td>
                                    </tr>
                                    <?php $stt++; ?>
                            <?php endforeach; ?>
                    <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center"><?php echo __('no_result') ?></td>
                            </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->element('pagination'); ?>
    </div>
</div>

