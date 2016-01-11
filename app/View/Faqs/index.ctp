<?php
if (isset($arr_faq, $arr_faq['arr_faq']) && count($arr_faq['arr_faq']) > 0) {
    echo '<section class="top-section">';
    foreach ($arr_faq['arr_faq'] AS $k => $item) {

        ?>
        <div class="col-md-12 faq">
                <p class="question"><?php echo $item['name']; ?></p>
                <p class="question-text">
                        <?php echo $item['question']; ?>
                </p>
                <p class="question-answer">
                        <strong>Đáp:</strong> <?php echo $item['answer']; ?>
                </p>
        </div>
        <div class="clearfix"></div>
        <div class="break-section"></div>
        <?php
    }
    echo '</section>';
} else {

    ?>
    <section class="top-section">
            <div style="color: red">
                    <?php echo __('No data.'); ?>
            </div>
    </section>
    <?php
}