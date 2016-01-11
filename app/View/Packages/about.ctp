<div id="page1" class="col s12" style="padding-top:2em;">	
    <?php if (!empty($player)): ?>
        <div class="col s12">
            <div class="rows">
                <div class="intabs">
                    <h3 class="h3-title">Tài khoản</h3>
                    <p>
                        - Gói cước sử dụng: <strong><?php echo implode(', ', $register_package); ?></strong><br>
                        - Thời gian sử dụng: <strong><?php echo implode(', ', $time_effective); ?></strong><br>
                        - Để hủy gói cước tham khảo <a href="<?php echo Router::url(array('controller' => 'Rules', 'action' => 'index')) ?>">tại đây</a>, gói cước của bạn sẽ là gói cước mặc định
                    </p>
                    <?php if (!empty($empty_question_daily_title)): ?>
                        <p><?php echo $empty_question_daily_title ?></p>
                    <?php endif; ?>
                    <?php if (!empty($empty_question_daily_content)): ?>
                        <p><?php echo $empty_question_daily_content ?></p>
                    <?php endif; ?>
                    <?php if (!empty($usage_question)): ?>
                        <p><?php echo $usage_question ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>	
    <?php endif; ?>

    <div class="col s12">
        <div class="row">
            <div class="col s12" style="text-align:center;">
                <?php if ($package_buy_display == 1): ?>
                    <div class="col s6">
                        <div class="card">
                            <span class="card-title">GÓI MUA THÊM</span>
                            <div class="card-content ">

                                <p class="pag-title">M<p>
                                    <small class="price-title">2000đ/5 câu hỏi</small>
                                <p>
                                    <a class="waves-effect waves-light btn white-text package-register" href="<?php echo Router::url(array('action' => 'buy', 'MUA')) ?>" title="MUA">
                                        Chọn
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($package_day_status == 0 && $package_week_status == 0): ?>
                    <div class="col s6">
                        <div class="card">
                            <span class="card-title">Gói ngày</span>
                            <div class="card-content ">

                                <p class="pag-title">G1<p>
                                    <small class="price-title">2000đ/Ngày</small>
                                <p>
                                    <a class="waves-effect waves-light btn white-text package-register" href="<?php echo Router::url(array('action' => 'register', 'G1')) ?>" title="G1">
                                        Chọn
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($package_week_status == 0 && $package_day_status == 0): ?>
                    <div class="col s6">
                        <div class="card">
                            <span class="card-title">Gói tuần</span>
                            <div class="card-content ">

                                <p class="pag-title">G7</p>
                                <small class="price-title">9000đ/Tuần</small>								
                                <p>
                                    <a class="waves-effect waves-light btn white-text package-register" href="<?php echo Router::url(array('action' => 'register', 'G7')) ?>" title="G7">
                                        Chọn
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$charge_failed_title = CakeSession::read('Notification.charge_failed_title');
$charge_failed_content = CakeSession::read('Notification.charge_failed_content');
?>
<a href="#charge-failed-modal" id="charge-failed" style="display:none" rel="leanModal" name="charge-failed">charge failed</a>
<div id="charge-failed-modal" class="modal" style="text-align: center;">
    <div class="modal-content">
        <h4 class="charge-failed-title" style="color: red">
            <?php
            echo!empty($charge_failed_title) ? $charge_failed_title : '';
            ?>
        </h4>
        <p class="charge-failed-content">
            <?php
            echo!empty($charge_failed_content) ? $charge_failed_content : '';
            ?>
        </p>
    </div>
    <div class="modal-footer-center">
        <!--<a href="#close-charge-failed-modal" class=" modal-action modal-close waves-effect waves-green btn-flat">Đóng</a>-->
        <p><a href="#close-charge-failed-modal" class="waves-effect waves-light btn-large orange modal-action modal-close">Đồng ý</a></p>
    </div>
</div>
<script>
    $(function () {

        $('a[rel*=leanModal]').leanModal();
<?php if (!empty($charge_failed_content)): ?>
            $('#charge-failed').trigger('click');
<?php endif; ?>
    });
</script>
<?php
// chỉ hiện thị notification đúng 1 lần, refresh lại thì xóa
echo CakeSession::delete('Notification');
?>