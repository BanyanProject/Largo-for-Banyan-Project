jQuery('document').ready(function($){
	
	// sharebuttons scrollover
    $('.wrap-sharetools-horizontal-small .sharetool img').mouseover( function () {
        $(this).attr('src', $(this).attr('src').replace(/gray-64\.png/, '64.png') );
    });
    $('.wrap-sharetools-horizontal-small .sharetool img').mouseout( function () {
        $(this).attr('src', $(this).attr('src').replace(/64\.png/, 'gray-64.png') );
    });
    
	// Search slide out for mobile
    (function() {
        var searchForm = $('.sticky-nav-holder-banyan .form-search');
        var toggle = searchForm.parent().find('.toggle');
        toggle.on('click', function() {
            searchForm.parent().toggleClass('show');
            return false;
        });
    })();
    
});	

function formatMMd($datestr) {
	var d = new Date($datestr);
	d.setHours(d.getHours() + 8);
	return $.datepicker.formatDate("MM d", d);
}

