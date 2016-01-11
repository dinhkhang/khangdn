<?php
$messages = $this->Session->read('Message.multiFlash');
?>
<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $k => $v): ?>
        <?php if ($k == 0): ?>
            <div class="col s12" style="text-align:center;background-color:#ff6666;color:#fff;padding:20px;font-size:20px;">
                <?php echo $this->Session->flash('multiFlash.' . $k); ?>
            </div>
        <?php else: ?>
            <div class="col s12" style="text-align:center;padding:20px;font-size:20px;">
                <?php echo $this->Session->flash('multiFlash.' . $k); ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
<div id="page1" class="col s12" style="padding-top:2em;">			
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
                    <?php
                        $price_g1 = '2000';
                        $price_g7 = '9000';
                        if(isset($player)){
                            if(empty($player['Player']['time_register_first'])) {
                                $price_g1 = '0';
                                $price_g7 = '0';
                            }
                        }
                    ?>
                    <div class="col s6">
                        <div class="card">
                            <span class="card-title">Gói ngày</span>
                            <div class="card-content ">

                                <p class="pag-title">G1<p>
                                    <small class="price-title"><?php echo $price_g1 ?>đ/Ngày</small>
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
                                <small class="price-title"><?php echo $price_g7 ?>đ/Tuần</small>								
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
<div id="page2" class="col s12">
    <div class="rows">
        <div class="intabs">
            <h3 class="h3-title">Thể lệ</h3>
            <p>
                - Bạn đã trả lời chưa chính xác! Bạn không được cộng điểm. Chọn tiếp tục để trả lời câu hỏi tiếp theo. Bạn đã trả lời chưa chính xác! Bạn không được cộng điểm.<br><br>

                - Chọn tiếp tục để trả lời câu hỏi tiếp theo
                Bạn đã trả lời chưa chính xác! Bạn không được cộng điểm. Chọn tiếp tục để trả lời câu hỏi tiếp theo.
            </p>
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
