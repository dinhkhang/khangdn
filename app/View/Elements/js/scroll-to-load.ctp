<?php
if (!$this->request->is('ajax')):
    ?>
    <?php
    echo $this->Html->script('plugins/infinite-scroll/jquery.masonry.min');
    if (empty($query_string)) {

        $query_string = $this->request->query;
    }
    ?>
    <script>
        halovn.scrollContainer = '#scroll-container';
        halovn.scrollItem = 'div.scroll-item';
        halovn.scrollFecth = '<?php echo $this->Html->url(array('action' => $this->action)) ?>';
        halovn.scrollOffset = 50;
        halovn.scrollQueryParam = <?php echo json_encode($query_string) ?>;
        if (!$.isPlainObject(halovn.scrollQueryParam)) {

            halovn.scrollQueryParam = {};
        }
        console.log(halovn.scrollQueryParam);
        $(function () {

            var lock = 0;
            $(window).scroll(function () {

                if ($(window).scrollTop() === ($(document).height() - $(window).height())) {

                    if (lock === 1) {

                        return false;
                    }

                    lock = 1;
                    // tăng page lên + 1
                    if (!halovn.scrollQueryParam.hasOwnProperty('page')) {

                        halovn.scrollQueryParam.page = 2;
                    }
                    var req = $.get(halovn.scrollFecth, halovn.scrollQueryParam, function (data) {

                        lock = 0;
                        var $scrollItem = $('<div>').html(data).find(halovn.scrollItem);
                        if (!$scrollItem.length) {

                            lock = 1;
                            return false;
                        }

                        // thực hiện append phần tử vào html
                        var append_html = '';
                        $.each($scrollItem, function () {

                            append_html += $(this)[0].outerHTML;
                        });
                        $(halovn.scrollContainer).append(append_html);

                        $('.object-title').quickfit({truncate: true, min: 20});
                        $('.object-address').quickfit({truncate: true, min: 17});
                        $('.slide-img').matchHeight();

                        halovn.scrollQueryParam.page += 1;
                    });

                    req.fail(function (jqXHR, textStatus, errorThrown) {

                        lock = 0;
                        console.log('Call api: ' + halovn.scrollFecth + ' was failed');
                        console.log(textStatus);
                        console.log(errorThrown);
                    });
                }
            });
        });
    </script>
    <?php
endif;
?>