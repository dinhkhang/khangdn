jQuery(document).ready(function() {
	jQuery('.menu-bar').click(function() {
		jQuery('.sub-menu').toggle();
	});
});

//dong ho dem nguoc
var clock;
		
jQuery(document).ready(function() {
	var clock;

	clock = jQuery('.clock').FlipClock({
        clockFace: 'HourlyCounter',
        autoStart: false,
        callbacks: {
        	stop: function() {
        		jQuery('.message').html('The clock has stopped!')
        	}
        }
    });
		    
    clock.setTime(18000);
    clock.setCountdown(true);
    clock.start();

});

//end dong ho dem nguoc

//date picker

jQuery(document).ready(function() {

    // assuming the controls you want to attach the plugin to 
    // have the "datepicker" class set
    jQuery('input#datepicker-soi-cau').Zebra_DatePicker();

 });

//back to top

jQuery(document).ready(function() {
    var offset = 220;
    var duration = 500;
    jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top').fadeIn(duration);
        } else {
            jQuery('.back-to-top').fadeOut(duration);
        }
    });
    jQuery(".back-to-top").click(function() {
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
        return false;
    });
}); 

