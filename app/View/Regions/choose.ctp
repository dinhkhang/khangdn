<script>
    $(function () {

        $('#basic').on('change', function () {

            var region_id = $(this).val();
            localforage.getItem('halovn_location', function (err, value) {

                console.log('Check whether location was set or not?');
                if (!value) {

                    console.log('Get back location again.');
                    halovn.redirect_location = '<?php echo $this->here ?>';
                    halovn.getRegion({lat: '', lng: '', expried: new Date().getTime() + halovn.location_expried, user_region_id: region_id});
                    return false;
                }

                value.user_region_id = region_id;
                value.expried = new Date().getTime() + halovn.region_expried;

                var form_action = $('.region-choose-form').attr('action');
                var params = {user_region_id: region_id};
                var param_string = halovn.serializeQueryString(params);
                var redirect_url = form_action + ((form_action.indexOf('?') != -1) ? '&' : '?') + param_string;
                console.log('redirect_url: ' + redirect_url);

                halovn.redirect_location = redirect_url;
                halovn.getRegion(value);
            });
        });

        localforage.getItem('halovn_location', function (err, value) {

            console.log('Set value into region selectbox: ' + value.user_region_id);
            $('#basic').selectpicker('val', value.user_region_id);
        });
    });
</script>
<section class="top-section">
    <div class="select-block">
        <div class="col-md-12">
            <form class="form-horizontal region-choose-form" role="form" action="<?php echo $referer ?>">
                <div class="form-group">
                    <select id="basic" class="selectpicker form-control" name="select">
                        <?php if (!empty($regions)): ?>
                            <?php foreach ($regions as $item): ?>
                                <option value="<?php echo $item['Region']['id'] ?>"><?php echo $item['Region']['name'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="clearfix"></div>
    </div>
</section>