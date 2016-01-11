<?php
if (isset($this->Paginator) && $this->Paginator->counter('{:pages}') > 1):
    ?>
    <?php
    $ajax_target = !empty($ajax_target) ? $ajax_target : '';
    ?>
    <div class="text-center pagination-container" data-ajax_target="<?php echo $ajax_target ?>">
        <div class="btn-group">
            <ul class="pagination" style="text-align:center;">
                <?php echo $this->Paginator->prev('<i class="material-icons waves-effect">chevron_left</i>', array('tag' => 'li', 'escape' => false,), null, array('class' => 'disabled', 'tag' => 'li', 'escape' => false, 'disabledTag' => '')); ?>
                <?php
                echo $this->Paginator->numbers_1(array(
                    'first' => 2,
                    'last' => 2,
                    'currentClass' => 'active',
                    'separator' => false,
                    'currentTag' => 'a',
                    'class' => 'waves-effect',
                ));
                ?> 
                <?php echo $this->Paginator->next('<i class="material-icons waves-effect">chevron_right</i>', array('tag' => 'li', 'escape' => false,), null, array('class' => 'disabled', 'tag' => 'li', 'escape' => false, 'disabledTag' => '')); ?>
            </ul>
        </div>
    </div>
    <?php
endif;
?>

