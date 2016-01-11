
<?php $base = $this->request->base; ?>
<script type="text/javascript">

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
                    <!--<form id="rating_search_form">-->
                    <form id="rating_search_form" action="<?php echo $base; ?>/GameRatings/index" method="get">
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
                                    <option value="<?php echo $i; ?>"<?php if ($i == (int) $week_num): ?>selected="selected" <?php endif; ?>><?php echo $itemm ?></option>
                                    <?php echo $i +=1; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (empty($listYear)): ?>
                                <option value="0" selected="selected">Tuần</option>
                                <option value="1" selected="">Tháng</option>
                            <?php endif; ?>
                        </select>
                        <select id="num_count" name="num_count" class="browser-default slectsss-day">
                            <?php $num = !empty($total_num) ? $total_num : 52 ?>
                            <?php for ($y = 1; $y <= $num; $y++): ?>
                                <option value="<?php echo $y; ?>"<?php if ($y == (int) $week_count): ?>selected="selected" <?php endif; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                        <button class="waves-effect waves-light btn tim-kiem" type="submit">Tìm kiếm</button>
                    </form>
                    <!--                    <button onclick="ratingsearch()" class="waves-effect waves-light btn tim-kiem">Tìm kiếm</button>-->
                </div>
                <table class="centered striped bordered">
                    <thead>
                        <tr>
                            <th data-field="stt" >Hạng</th>
                            <th data-field="sdt" >Tài khoản</th>
                            <th data-field="sc" >Điểm</th>
                            <th data-field="t" >Thời gian</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_result_rating">
                        <?php if (!empty($listTopPlayer) && empty($listTopPlayer['error_msg'])): ?>
                            <?php foreach ($listTopPlayer['data']['arr_topPlayer'] as $item): ?>
                                <tr>
                                    <td><?php echo $item['stt'] ?></td>
                                    <td><?php echo $item['phone'] ?></td>
                                    <td><?php echo $item['score'] ?></td>
                                    <td><?php echo $item['time'] ?></td>
                                </tr>                            
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($listTopPlayer['error_msg'])): ?>
                            <?php echo $listTopPlayer['error_msg'] ?>
                        <?php endif; ?>
                    </tbody>
                    <tbody >
                        <tr>
                            <td colspan="4" style="text-align:center;" ><?php echo $this->element('pagination_gameRating'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>        
    </div>                
</div>
