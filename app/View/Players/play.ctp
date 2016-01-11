
<?php $base = $this->request->base;?>
<script type="text/javascript">  
        
        
        
        function choice(index)
        {
            if(index == 1)
            {
                $("#inp_answer").val(1);
                $("#btn_answer1").addClass( "orange" );
                $("#btn_answer2").removeClass("orange" );
            }
            else if(index == 2)
            {
                $("#inp_answer").val(2);
                $("#btn_answer2").addClass( "orange" );
                $("#btn_answer1").removeClass("orange" );
            }
            else
            {
                $("#inp_answer").val(0);
                $("#btn_answer2").removeClass( "orange" );
                $("#btn_answer1").removeClass("orange" );
            }
        } 
        
        function hideShowBtn(isHide)
        {
            $("#btn_answer1").removeClass("orange");
            $("#btn_answer2").removeClass("orange");
            if(isHide)
            {
                $('#btn_answer1').hide();
                $('#btn_answer2').hide();
            } 
            else
            {
                $('#btn_answer1').show();
                $('#btn_answer2').show();
            }
        }
        
        myVar = null;
        var time_remain = <?php echo $arr_data['time_remain'] ?>;
        var time_remain_m = 0;
        var time_remain_s = 0;
        
        isRunning = false;
        isFinish = false;
        function submitAnswer()
        { 
            if (isFinish)
            {
                $("#a_modal").click();
                return;
            }
            if (isRunning)
            {
                return;
            }
            else
            {
                isRunning = true;
                clearInterval(myVar);

                $.ajax(
                    {
                        type: "POST",
                        url: "<?php echo $base ;?>/players/answer",
                        data: $("#form_question").serialize(),
                        dataType: "json",
                        success: function (data) {
                            
                            if (data.status == -1) {
                                if (confirm(data.msg))
                                {
                                    window.location.href = data.url;
                                }
                            } else if (data.status != 0) {
                                alert(data.msg);
                                window.location.href = data.url;
                            } else {
                                $("#p_msg").html('<strong>' + data.data.msg + '</strong>');
                                
                                if (data.data.question_index >= 0)
                                { 
//                                    choice(0);
                                    $("#inp_answer").val(0);
//                                    $("#btn_answer").text("ĐỒNG Ý");
                                    hideShowBtn(false);
                                    $("#span_question_number").html("Câu số " + (data.data.question_index + 1) + '<small class="small-date"  id="small_time">00:00</small>');
                                    $("#p_question_content").html(data.data.question_content);
                                    time_remain_m = 0;
                                    time_remain_s = 0;
                                    time_remain = data.data.time_remain;
                                    //clearInterval(myVar);

                                    initTimeRemain();
                                }
                                else
                                {          
                                    $("#span_question_number").html("THÔNG BÁO");
                                    $("#p_question_content").html("");
                                    $("#p_result").html(data.data.msg);
                                    
//                                    $("#p_question_content").html("");
//                                    $("#span_question_number").html("Kết quả");                          
//                                    $('#div_result_group').html(data.mt_result_group);  
                                    hideShowBtn(true);
                                    
                                    $("#a_modal").click();
                                    isFinish = true;
                                }
                            }
                            
                            isRunning = false;
    //                        $('#load_image').css('display', 'none');
                        },
                        error: function () {
                            isRunning = false;
    //                        $('#load_image').css('display', 'none');
                        },
                        complete: function () {
                            isRunning = false;
//                            $('#load_image').css('display', 'none');
                        }
                    }
                );
            }
        }
        
    function initTimeRemain() { 
        
        if (time_remain > 0) {
            time_remain_m = Math.floor(time_remain/60);
            time_remain_s = time_remain%60;
            $("#small_time").html(formatTime(time_remain_m, + time_remain_s));
            myVar = setInterval(runTimeRemain, 1000);
        } else {
            $("#small_time").html("00:00");
        }
    }
    
    function runTimeRemain() { 
        
        time_remain_s--;
        if (time_remain_s < 0 && time_remain_m > 0) {
            time_remain_m--;
            time_remain_s = 59;
        } else if (time_remain_s < 0) {
            time_remain_s = 0;
            clearInterval(myVar);
            hideShowBtn(true);
            $("#span_question_number").html("THÔNG BÁO");
            $("#p_msg").text("Đã hết thời gian trả lời câu hỏi hiện tại, mời bạn nhấn TIẾP TỤC để trả lời câu hỏi tiếp theo!");
            $("#p_question_content").text("");
            $("#inp_answer").val(-1);
            $("#lost_time").val(1);
        } 
        
        if (time_remain_m < 0) {
            time_remain_m = 0;
        }
        
        $("#small_time").html(formatTime(time_remain_m, + time_remain_s));
    }    
    
    function formatTime(m, s) { 
        m = (m + "");
        s = (s + "");
        if (m.length == 1)
        {
            m = "0" + m;
        }
        if (s.length == 1)
        {
            s = "0" + s;
        }
        
        return m + ":" + s;
    }
</script>  

<div style="padding-top:2em;" class="col s12" id="page1">
    <?php if (empty($arr_data))
    {?>
    <span class="card-title-add" style="color: Red;">Hệ thống đang quá tải, bạn vui lòng quay lại sau!</span>
    <?php } else {?>
    
<form id="form_question" method="post"> 
    <input type="hidden" name="phone" value="84942291166">
    <input type="hidden" name="answer" value="0" id="inp_answer">
    <input type="hidden" name="lost_time" value="" id="lost_time">
    <input type="hidden" name="question_index" value="<?php echo $arr_data['question_index'] ?>" id="inp_question_index">
</form>
<script type="text/javascript">  
//    var time_start = <?php echo $arr_data['time_start'] ?>;
//    var time_now = <?php echo $arr_data['time_now'] ?>;

    $('document').ready(function() {
        initTimeRemain(); 
        $('.modal-trigger').leanModal();
        
        <?php if ($arr_data['status'] != 0)
        { ?>
        $("#a_modal").attr('href', '#myModalNotify');   
        $("#a_modal").click();
        hideShowBtn(true);
        $("#btn_answer").hide();
        <?php } else if ($arr_data['question_index'] < 0 || $arr_data['time_remain'] <= 0)
        { ?>
               
//        $("#span_question_number").html("THÔNG BÁO");
        $("#inp_answer").val(-1); 
        hideShowBtn(true);
        
        <?php } 
        if ($arr_data['question_index'] < 0)
        {
        ?>
        isFinish = true;
        <?php  
        }
        ?>   
    });
    
</script>        
        <div class="col s12">
                <div class="row">
                        <div class="col s12">						
                                <div class="card">
                                        <span class="card-title-add" id="span_question_number">
                                             <?php if ($arr_data['question_index'] < 0 || $arr_data['time_remain'] <= 0)
                                                { ?>
                                            Thông báo
                                                <?php } else {?>
                                            Câu số <?php echo ($arr_data['question_index'] + 1) ?> <small class="small-date"  id="small_time">00:00</small>
                                            
                                                <?php } ?>
                                        </span>
                                        <div class="card-content ">
                                                <p style="padding:20px 0px 20px 0px;">
                                                        </p><p  id="p_msg"><?php echo $arr_data['msg'] ?></p>
                                                        <blockquote>
                                                                <p id="p_question_content"><?php echo $arr_data['question_content'] ?></p>
                                                        </blockquote>
<!--                                                        <p>
                                                                <strong style="text-decoration: underline;">Đáp án:</strong><br>
                                                                <strong>1. Năm 1994</strong><br>
                                                                <strong>2. Năm 2000</strong>
                                                        </p>-->
                                                <p></p>

                                        </div>							
                                        <div class="card-action">
                                            <div style="margin-bottom:0px;" class="row">
                                                <a id="btn_answer1" href="javascript:choice(1);" class="waves-effect waves-light btn white-text">Đáp án 1</a>
                                                <a id="btn_answer2" href="javascript:choice(2);" class="waves-effect waves-light btn white-text">Đáp án 2</a>
                                            </div>
                                        </div>
                                </div>
                                <div style="text-align:center; font-size:1.3rem;font-weight:bold;">
                                        <a href="javascript:submitAnswer()" id="btn_answer" class="waves-effect waves-light btn-large orange">Tiếp tục</a>
                                        <a id="a_modal" class="waves-effect waves-light btn-large orange modal-trigger" href="#myModal" style="display:none;">Modal</a>
                                </div>
                        </div>
                </div>
        </div>
        <div id="myModal" style="text-align:center;" class="modal">
                <div class="modal-content">
                        <h4 class="mood-title"><i class="material-icons">mood</i></h4>
                        <p class="m-p-ti">THÔNG BÁO</p>
                        <p id="p_result"><?php echo $arr_data['msg'] ?></p>
                </div>
                <div class="modal-footer">
                        <a style="float: inherit;" class="modal-action modal-close waves-effect waves-green btn-flat orange" href="/players/buy">Tiếp tục</a>&nbsp;&nbsp;
                        <a style="float: inherit;" class="modal-action modal-close waves-effect waves-green btn-flat orange" href="/gamequiz">Dừng</a>
                </div>
        </div> 

        <div id="myModalNotify" style="text-align:center;" class="modal">
                <div class="modal-content">
                        <h4 class="mood-title"></h4>
                        <p class="m-p-ti">THÔNG BÁO</p>
                        <p id="p_result_notify"><?php echo $arr_data['msg'] ?></p>
                </div>
                <div class="modal-footer">
                        <a style="float: inherit;" class="modal-action modal-close waves-effect waves-green btn-flat orange" href="/players/play?channel_play=WAP">Tiếp tục</a>&nbsp;&nbsp;
                        <a style="float: inherit;" class="modal-action modal-close waves-effect waves-green btn-flat orange" href="/gamequiz">Không</a>
                </div>
        </div> 
     <?php } ?>
</div>