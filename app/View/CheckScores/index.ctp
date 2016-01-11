<?php $base = $this->request->base; ?> 
<script type="text/javascript">
    function scoresearch()
    {
        $.ajax(
                {
                    type: "POST",
                    url: "<?php echo $base; ?>/CheckScores/index",
                    data: $("#score_search_form").serialize(),
                    dataType: "json",
                    success: function (data) {
                        console.log(data);
                        if (data.error == 0) {
                            $("#tbody_result_score").html(data.error_msg);
                            $("#total_score").html(null);
                        } else {
                            var arr_tr = new Array();
                            var kq;
//                            console.log(data.data.arr_topPlayer.length);
                            for (kq = 0; kq < data.data.listPointByDay.length; kq++)
                            {
                                var tr = '<tr><td>' + data.data.listPointByDay[kq].stt + '</td><td>' + data.data.listPointByDay[kq].day + '</td><td>' + data.data.listPointByDay[kq].score + '</td></tr>';
                                arr_tr[kq] = tr;
                            }
                            var total_c = '<tr><td colspan="2"><strong>Tổng điểm tích lũy</strong></td><td><strong style="font-size:1.4rem;">' + data.data.total_score + '</strong></td></tr>'
                            $("#tbody_result_score").html(arr_tr);
                            $("#total_score").html(total_c);
                        }
                    }
                }
        );
    }
    function changeTile()
    {
        var mon_week = document.getElementById('mon_week').value;
        var arr_mw = new Array();
        var total = 52;
        if (mon_week == 1) {
            total = 12;
        }

        var i;
        for (i = 1; i <= total; i++) {
            var option = '<option value="' + i + '">' + i + '</option>';
            arr_mw[i - 1] = option;
        }

        $("#num_count").html(arr_mw);
    }
</script>
<div class="">                		
    <div class="col s12">
        <div class="rows">                      
            <div class="intabs">
                <div class="top-search">
                    <form id="score_search_form">
                        <select name ="year" class="browser-default slectsss-year">
                            <?php if (!empty($listYear)): ?>
                                <?php foreach ($listYear as $item): ?>
                                    <option value="<?php echo $item ?>"<?php if ($item == (int) $year): ?>selected="selected" <?php endif; ?>><?php echo $item ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (empty($listYear)): ?>
                                <option value="2015" selected="selected">2015</option>
                                <option value="2016" selected="">2016</option>
                                <option value="2017" selected="">2017</option>
                                <option value="2018" selected="">2018</option>
                            <?php endif; ?>
                        </select>
                        <select id="mon_week" name="mon_week" class="browser-default slectsss-month" onchange="changeTile()">
                            <?php if (!empty($listMonthWeek)): ?>
                                <?php echo $i = 0; ?>
                                <?php foreach ($listMonthWeek as $itemm): ?>                                        
                                    <option value="<?php echo $i; ?>"><?php echo $itemm ?></option>
                                    <?php echo $i +=1; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (empty($listMonthWeek)): ?>
                                <option value="0" selected="selected">Tuần</option>
                                <option value="1" selected="">Tháng</option>
                            <?php endif; ?>
                        </select>
                        <select id="num_count" name="num_count" class="browser-default slectsss-day">
                            <?php for ($y = 1; $y <= 52; $y++): ?>
                                <option value="<?php echo $y; ?>"<?php if ($y == (int) $week): ?>selected="selected" <?php endif; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                    <button onclick="scoresearch()" class="waves-effect waves-light btn tim-kiem">Tìm kiếm</button>
                </div>
                <table class="centered striped bordered ">
                    <thead>
                        <tr>
                            <th data-field="id" >STT</th>
                            <th data-field="name" >Ngày tháng</th>								
                            <th data-field="price" >Điểm số trong ngày</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_result_score">
                        <?php if (!empty($listPoint) && empty($listPoint['error_msg'])): ?>
                            <?php foreach ($listPoint['data']['listPointByDay'] as $item): ?>
                                <tr>
                                    <td><?php echo $item['stt'] ?></td>
                                    <td><?php echo $item['day'] ?></td>
                                    <td><?php echo $item['score'] ?></td>
                                </tr>                            
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($listPoint['error_msg'])): ?>
                            <?php echo $listPoint['error_msg'] ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($listPoint['data']['total_score'])): ?>
                        <tbody id="total_score">
                            <tr>
                                <td colspan="2"><strong>Tổng điểm tích lũy</strong></td>
                                <td ><strong style="font-size:1.4rem;"><?php if (!empty($listPoint)): ?><?php echo $listPoint['data']['total_score'] ?><?php endif; ?></strong></td>
                            </tr>
                        </tbody>
                    <?php endif; ?>
                </table>                
            </div>
        </div>
    </div>                
</div>
