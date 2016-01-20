var $ = jQuery.noConflict();

$(document).ready(function() {	

	$("#btn-submit").click(function() {
		$(this).prop("disabled",true);
		$("#create-user-account-form").submit();
	});

});

