<?php
echo $this->start('recent_history');
?>
<script>
    halovn.getRecentHistory('<?php echo $recent_type ?>');
</script>
<section class="no-padding-bottom" id="recent_history">
    <div>
        <h3 class="section-title">Xem gần đây</h3>
    </div>
    <div class="clearfix"></div>
    <ul class="recently-location-list recently-fixed-list">
    </ul>
</section>
<?php
echo $this->end();
?>