<style>
    #infscr-loading {
        background: none repeat scroll 0 0 #000;
        border-radius: 10px;
        bottom: 40%;
        left: 25%;
        opacity: 0.8;
        position: fixed;
        text-align: center;
        width: 200px;
        z-index: 100;
    }
    #infscr-loading > div {
        color: #fff;
    }
    li {
        list-style: none;
        list-style-type: none;
    }
    #container-list {
        overflow: hidden;
    }
    #page-nav {
        display: none;
    }
</style>
<?php
echo $this->Html->script('plugins/infinite-scroll/jquery.masonry.min');
echo $this->Html->script('plugins/infinite-scroll/jquery.infinitescroll.min');
?>
<script>
    $(function () {
        var current_height = $('.restaurant-item').first().find('.slide-img').find('img').height();
        var current_width = $('.restaurant-item').first().find('.slide-img').find('img').width();
        var $container = $('#container-list');

        $container.imagesLoaded(function () {
            $container.masonry({
                itemSelector: '.box',
                columnWidth: 0
            });
            $('.box').removeAttr('style');
        });

        $container.infinitescroll({
            navSelector: '#page-nav', // selector for the paged navigation 
            nextSelector: '#page-nav a', // selector for the NEXT link (to page 2)
            itemSelector: '.box', // selector for all items you'll retrieve
            loading: {
                finished: function() {
                    $('.box').removeAttr('style');
                    $('.masonry-brick').removeAttr('style');
                    $('#infscr-loading').hide();
                },
                finishedMsg: '<?php echo __('No more pages to load.'); ?>',
                msgText: '<?php echo __('Loading'); ?>',
                img: '<?php echo Router::url('/img/loading.gif', true); ?>'
            }
        },
        // trigger Masonry as a callback
        function (newElements) {

            // hide new items while they are loading
            var $newElems = $(newElements).css({opacity: 0});
            // ensure that images load before adding to masonry layout
            $newElems.imagesLoaded(function () {
                $newElems.find('.slide-img').find('img').width(current_width);
                $newElems.find('.slide-img').find('img').height(current_height);
                
                // show elems now they're ready
                $newElems.animate({opacity: 1});
                $newElems.removeAttr('style');
                
                $container.masonry('appended', $newElems, true);
                $('.box').removeAttr('style');
            });
            $('.masonry-brick').removeAttr('style');
            $('.object-title').quickfit({truncate: true, min: 20});
            $('.object-address').quickfit({truncate: true, min: 17});
        }
        );

    });
</script>
<div id="page-nav">
    <?php
    $query_string = http_build_query($query_param);

    echo $this->Paginator->next(
            '<i class="fa fa-chevron-right"></i>', array(
        'tag' => 'span',
        'escape' => false,
        'class' => 'btn btn-white',
        'url' => array(
            '?' => $query_string
        ),
            ), null, array(
        'class' => 'disabled btn btn-white',
        'tag' => 'span',
        'escape' => false,
        'disabledTag' => '',
        'url' => array(),
            )
    );
    ?>
</div>
