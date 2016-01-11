jQuery(document).ready(function($) {
    $('.sidebar').css('top', $('.top-bar').height()+5);
    
  var swiper = new Swiper('.swiper-container', {
        pagination: '.swiper-pagination',
        slidesPerView: 'auto',
        paginationClickable: true,
        spaceBetween: 15,
//        loop:true,
    });

    var swiper = new Swiper('.swiper-container-top', {
        pagination: '.swiper-paginationx',
        paginationClickable: true,
        spaceBetween: 0,
        centeredSlides: true,
        autoplay: 3500,
        autoplayDisableOnInteraction: false
    });


    $(document).on('click', '.show-modal', function() {
      $("#modal-media").attr('active-slide',$(this).attr('imgcode'));
      $('#modal-media').modal();
    });
    $('#modal-media').on('shown.bs.modal', function (e) {
        var owl = $('.owl-carousel');
        owl.owlCarousel({
            loop: true,
            items: 1,
            thumbs: true,
            thumbImage: true,
            thumbContainerClass: 'owl-thumbs',
            thumbItemClass: 'owl-thumb-item'
        });
        var active_id = $(this).attr('active-slide');
        console.log(active_id);
        $(this).find('.owl-item').removeClass('active');
        $(this).find("#"+active_id).closest('.owl-item').addClass('active');

        var sefl = $(this).find('.media-popup');
//        sefl.css('top', '50%');
        sefl.css('margin-top', $(window).height()/2-sefl.height()/2);

        $('.overl').removeClass('hide');
    });
    $('#modal-video').on('shown.bs.modal', function (e) {
      $('.overl').removeClass('hide');
      var sefl = $(this).find('.media-popup');
      //sefl.css('top', '50%');
      sefl.css('margin-top', $(window).height()/2-sefl.height()/2);
    });
    $('#modal-video').on('hidden.bs.modal', function (e) {
        $('.video').get(0).pause();
    });
    $('#modal-media, #modal-video').on('hidden.bs.modal', function (e) {$('.overl').addClass('hide');});

  //top bar, sidebar
    var offset = 0,
  $top_bar = $('.top-bar');
  $top_notify = $('.alert-unidentified');
  $(window).scroll(function(){
    if( $(this).scrollTop() > offset ) {
      //$top_bar.addClass('hide');
            //$top_notify.css('top', '0px');
      $top_notify.addClass('hide');
            //$('.sidebar').addClass('no-top');
            $('.home-banner').addClass('no-margin-top');
            $('.banner-img').addClass('no-margin-top');
    } else{
      //$top_bar.removeClass('hide');
            //$top_notify.css('top', '85px');
      $top_notify.removeClass('hide');
            //$('.sidebar').removeClass('no-top');
            $('.home-banner').removeClass('no-margin-top');
            $('.banner-img').removeClass('no-margin-top');
    } 
  });

  var lastScrollTop = 0;
  $(window).scroll(function(event){
     var st = $(this).scrollTop();
     if (st > lastScrollTop){
        $top_bar.fadeOut();
     } else {
        $top_bar.fadeIn();
     }
     lastScrollTop = st;
  });


  $('article').readmore({
      moreLink: '<a class="more-less-link" href="#"><i class="fa fa-angle-down"></i></a>',
      lessLink: '<a class="more-less-link" href="#"><i class="fa fa-angle-up"></i></a>',
      collapsedHeight: 150,
      afterToggle: function(trigger, element, expanded) {
        if(! expanded) {
          $('html, body').animate({scrollTop: $(element).offset().top}, {duration: 100});
        }
      }
    });

    var mh = 0;
    var mw = 0;
    $('.thumbnail-item').each(function() {
      var temp = $(this).find('img');
      if(temp[0].getBoundingClientRect().width>mh) mh = temp[0].getBoundingClientRect().height;
      if(temp[0].getBoundingClientRect().width>mw) mw = temp[0].getBoundingClientRect().width;
    });
    $('.thumbnail-item').find('img').width(mw).height(mh);

    var browser_width = $(window).width();
    var scr_width_arr = [320,360,375,384,412,414,600,768, 480,568,640,667,736,1024];
    if($.inArray(browser_width, scr_width_arr) == -1 ) {
      var m_h = 0;
      var m_w = 0;
      $('.restaurant-item').each(function() {
        var temp = $(this).find('img');
        if(temp[0].getBoundingClientRect().width>m_h) m_h = temp[0].getBoundingClientRect().height;
        if(temp[0].getBoundingClientRect().width>m_w) m_w = temp[0].getBoundingClientRect().width;
      });
      $('.restaurant-item a').find('img').width(m_w).height(m_h);
    }

});