
    <?php 
    if (!empty($arr_result['data']['arr_cate']))
    {
        $index = 0;
        foreach ($arr_result['data']['arr_cate'] as $key => $cate) {
         
    if (!empty($cate["arr_place"]))
    {    
        if ($index == 0)
        {        
        ?>
        <section class="top-section"> 
        <?php  
        } else {
        ?>  
        <section> 
        <?php  
        }
        $index++;
        ?> 
    <div>
        <h3 class="section-title"><?php echo $cate['name'] ?><a href="/places/listplace?cate_id=<?php echo $cate['id'] ?>" class="see-more pull-right">+ Thêm</a></h3>
    </div>
    <div class="clearfix"></div>
    <div class="swiper-container">
        <div class="swiper-wrapper"> 
                
    <?php 
        foreach ($cate["arr_place"] as $key => $place) {
             
    ?>
            
                <div class="swiper-slide">
                    <a href="/places/info?id=<?php echo $place['id'] ?>" class="swiper-slide">
                        <div class="slide-img">
                            <img src="<?php echo $place['banner'] ?>">
                        </div>
                        <div class="slide-text">
                            <span class="object-title"><?php echo $place['name'] ?></span>
                            <span class="slide-detail"><?php  if(count($place['address'] > 30)){ echo mb_substr($place['address'], 0,27, "UTF-8")." ...";
 }  else {
     echo $place['address'];
 }  ?></span>
                        </div> 
                    </a>
                </div>  
    <?php  
        }
    ?> 
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </section>
    <?php  
    }
        }
    }
    ?>
    <!--ĐỊA ĐIỂM HOT-->