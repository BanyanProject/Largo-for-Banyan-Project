var $ = jQuery.noConflict();

$(document).ready(function() {	

	$("#btn-submit").click(function() {
		$(this).prop("disabled",true);
		$("#founding-membership-pledge-form").submit();
	});

});

