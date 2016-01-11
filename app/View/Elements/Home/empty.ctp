<?php

$is_emtpy = 0;
?>
<?php if (empty($res['data']['arr_type'])): ?>
    <?php $is_emtpy = 1 ?>
<?php else: ?>
    <?php

    $empty_count = 0;
    foreach ($res['data']['arr_type'] as $type) {

        if (empty($type['total'])) {

            $empty_count++;
        }
    }
    if (count($res['data']['arr_type']) == $empty_count) {

        $is_emtpy = 1;
    }
    ?>
<?php endif; ?>
<?php if (!empty($is_emtpy)): ?>
    <section>
        <div>
            <h3 class="section-title" style="text-align: center">
                <span >Không tìm thấy kết quả phù hợp</span>
            </h3>
        </div>
    </section>
<?php endif; ?>

