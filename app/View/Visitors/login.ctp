<div class="col s12">
    <div class="rows">
        <div class="intabs">
            <p class="wifi-title"><i class="material-icons">wifi</i></p>
            <p style="text-align:center;font-size:1.05rem;">
                Bạn vui lòng đăng nhập để sử dụng <strong>GAME QUIZ!</strong>
                và cơ hội trúng nhiều phần thưởng có giá trị lớn.
            </p>
            <?php echo $this->Form->create($model_name) ?>
            <div class="input-field col s6" style="margin:30px 0px 30px 0px;text-align:center;">
                <i class="material-icons prefix">phone</i>
                <!--<input id="icon_telephone" type="tel" class="validate">-->
                <?php
                echo $this->Form->input('username', array(
                    'class' => 'validate',
                    'id' => 'icon_telephone',
                    'type' => 'tel',
                    'div' => false,
                    'required' => true,
                    'label' => false,
                ));
                ?>
                <label for="icon_telephone">Nhập số điện thoại</label>
                <a style="margin-top:20px;" class="waves-effect waves-light btn disabled" id="reset-password">Nhận mật khẩu qua tin nhắn</a>
            </div>
            <div class="input-field col s6" style="margin:30px 0px 30px 0px;text-align:center;">
                <i class="material-icons prefix">lock</i>
                <!--<input id="icon_telephone" type="tel" class="validate">-->
                <?php
                echo $this->Form->input('password', array(
                    'class' => 'validate',
                    'id' => 'icon_password',
                    'div' => false,
                    'required' => true,
                    'label' => false,
                ));
                ?>
                <label for="icon_password">Nhập mật khẩu</label>
                <!--<a style="margin-top:20px;" class="waves-effect waves-light btn">Đăng nhập</a>-->
                <button style="margin-top:20px;" class="waves-effect waves-light btn" id="submit">Đăng nhập</button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>	

<a href="#login-failed-modal" id="login-failed" style="display:none" rel="leanModal" name="login-failed">login failed</a>
<div id="login-failed-modal" class="modal" style="text-align: center">
    <div class="modal-content">
        <h4 class="login-failed-title" style="color: red">
            <?php
            echo!empty($login_failed_title) ? $login_failed_title : '';
            ?>
        </h4>
        <p class="login-failed-content">
            <?php
            echo!empty($login_failed_content) ? $login_failed_content : '';
            ?>
        </p>
    </div>
    <div class="modal-footer-center">
        <!--<a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Đóng</a>-->
        <p><a href="#close-login-failed-modal" class="waves-effect waves-light btn-large orange modal-action modal-close">Đồng ý</a></p>
    </div>
</div>

<a href="#validate-mobile-modal" id="validate-mobile" style="display:none" rel="leanModal" name="validate-mobile">validate mobile</a>
<div id="validate-mobile-modal" class="modal" style="text-align: center">
    <div class="modal-content">
        <h4 class="validate-mobile-title" style="color: red"></h4>
        <p class="validate-mobile-content"></p>
    </div>
    <div class="modal-footer-center">
        <!--<a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Đóng</a>-->
        <p><a href="#close-validate-mobile-modal" class="waves-effect waves-light btn-large orange modal-action modal-close">Đồng ý</a></p>
    </div>
</div>
<script>
    $(function () {
        $('a[rel*=leanModal]').leanModal({
            ready: function () {

            },
            complete: function () {

<?php if (!empty($login_failed_redirect)): ?>
                    window.location.href = '<?php echo $login_failed_redirect ?>';
<?php endif; ?>
            }
        });
<?php if (!empty($login_failed_content)): ?>
            $('#login-failed').trigger('click');
<?php endif; ?>

        $('a#reset-password').on('click', function () {

            var mobile = $.trim($('#icon_telephone').val());
            var $self = $(this);
            if ($(this).hasClass('disabled')) {

                return false;
            }

            // thực hiện khóa tính năng gửi sms
            $(this).addClass('disabled');
            var sms_sevice = '<?php echo $this->Html->url(array('action' => 'reqResetPassword', 'controller' => 'SmsSender')) ?>';
            var req = $.post(sms_sevice, {mobile: mobile}, function (data) {

                if (data.status === 'success') {


                } else {

                    console.log('Validate failed:');
                    console.log(data);

                    $('.validate-mobile-title').html(data.data.client_title);
                    $('.validate-mobile-content').html(data.data.client_message);
                    $('#validate-mobile').trigger('click');
                    $self.removeClass('disabled');
                }
            }, 'json');

            req.error(function (xhr, status, error) {

                alert("An AJAX error occured: " + status + "\nError: " + error + "\nError detail: " + xhr.responseText);
            });

            setTimeout(function () {

                $self.removeClass('disabled');
            }, <?php echo $time_lock_reset_password ?> * 1000);
        });

//        $('#submit').on('click', function () {
//
//            if ($(this).hasClass('disabled')) {
//
//                return false;
//            }
//        });

        // nếu chưa nhập vào phone, thì ẩn đi nút "Nhận mật khẩu qua tin nhắn"
        $('#icon_telephone').on('keyup change', function () {

            var value = $.trim($(this).val());
            var password = $('#icon_password').val();
            if (value.length && password.length) {

                $('#reset-password').addClass('disabled');
//                $('#submit').removeClass('disabled');
            }
            else if (value.length && !password.length) {

//                $('#submit').addClass('disabled');
                $('#reset-password').removeClass('disabled');
            }
            else {

                $('#reset-password').addClass('disabled');
//                $('#submit').addClass('disabled');
            }
        });
        $('#icon_telephone').trigger('change');

        $('#icon_password').on('keyup change', function () {

            var value = $(this).val();
            var phone = $.trim($('#icon_telephone').val());
            if (!phone.length) {

                $('#reset-password').addClass('disabled');
//                $('#submit').addClass('disabled');
            } else if (value.length && phone.length) {

                $('#reset-password').addClass('disabled');
//                $('#submit').removeClass('disabled');
            } else if (!value.length && phone.length) {

                $('#reset-password').removeClass('disabled');
//                $('#submit').addClass('disabled');
            } else {

                $('#reset-password').addClass('disabled');
//                $('#submit').addClass('disabled');
            }

        });
        $('#icon_password').trigger('change');
    });
</script>