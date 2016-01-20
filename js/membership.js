var $ = jQuery.noConflict();

$(document).ready(function() {	
	
	$("#recurring-annual").click(function() {
		if (isChecked(this)) {
			writeReceipt();
			toggleMembership();
		} 
	});

	$("#recurring-monthly").click(function() {
		if (isChecked(this)) {
			writeReceipt();
			toggleMembership();
		} 
	});

	$("#non-recurring").click(function() {
		if (isChecked(this)) {
			writeReceipt();
			toggleMembership();
		} 
	});

	$("#type-500").click(function() {
		if (isChecked(this)) {writeReceipt();} 
	});

	$("#type-250").click(function() {
		if (isChecked(this)) {writeReceipt();} 
	});

	$("#type-125").click(function() {
		if (isChecked(this)) {writeReceipt();} 
	});

	$("#type-60").click(function() {
		if (isChecked(this)) {writeReceipt();} 
	});

	$("#type-36").click(function() {
		if (isChecked(this)) {writeReceipt();} 
	});


	$("#additional-donation-yes").click(function() {
		if (isChecked(this)) {
			$("#wrap-additional-amount").slideDown();
		} else {
			$("#additional-amount").val('');
			writeReceipt();			
			$("#wrap-additional-amount").slideUp();
		}
	});

	$("#additional-donation-no").click(function() {
		if (isChecked(this)) {
			$("#additional-amount").val('');
			writeReceipt();
			$("#wrap-additional-amount").slideUp();
		} else {
			$("#wrap-additional-amount").slideDown();
		}
	});

	$("#additional-amount").blur(function() {writeReceipt();});

	$("#btn-submit").click(function() {
		$(this).prop("disabled",true);
		$("#membership-form").submit();
	});

});

function writeReceipt() {	

	var total;	
	if (isChecked($("#type-500"))) {
		total = 500;
	} else if (isChecked($("#type-250"))) {
		total = 250;
	} else if (isChecked($("#type-125"))) {
		total = 125;
	} else if (isChecked($("#type-60")))	{
		total = 60;
	} else if (isChecked($("#type-36")))	{
		total = 36;
	} else {
		$("#form-receipt").slideUp();		
		$("#form-receipt").html('');
		return;
	}	

	if (isChecked($("#recurring-annual"))) {
			
		if ($.isNumeric($("#additional-amount").val()) && $("#additional-amount").val() > 0 && $("#additional-amount").val() < 10001 ) {
			var amount = Number($("#additional-amount").val());
			total = total + amount;
		}
			
		$("#form-receipt").html('Your credit card will be charged $' + total + ' today, with a recurring annual charge of $' + total + ' until you decide to cancel. You may cancel at any time.');

	} else if (isChecked($("#recurring-monthly"))) {
		
		total = Math.round(total * 100 / 12) / 100;
		
		if ($.isNumeric($("#additional-amount").val()) && $("#additional-amount").val() > 0 && $("#additional-amount").val() < 10001 ) {
			var amount = Number($("#additional-amount").val());
			total = total + amount;
		}		
		
		$("#form-receipt").html('Your credit card will be charged $' + total + ' today, with a recurring monthly charge of $' + total + ' until you decide to cancel. You may cancel at any time.');

	} else {
		
		if ($.isNumeric($("#additional-amount").val()) && $("#additional-amount").val() > 0 && $("#additional-amount").val() < 10001 ) {
			var amount = Number($("#additional-amount").val());
			total = total + amount;
		}
		
		$("#form-receipt").html('Your credit card will be charged $' + total + '.');
	}

	$("#form-receipt").slideDown();		

}

function toggleMembership() {
	if (isChecked($("#recurring-monthly"))) {
		$("#500-amount").html('$41.67/month');
		$("#250-amount").html('$20.83/month');
		$("#125-amount").html('$10.42/month');
		$("#60-amount").html('$5.00/month');
		$("#36-amount").html('$3.00/month');
	} else {
		$("#500-amount").html('$500/year');
		$("#250-amount").html('$250/year');
		$("#125-amount").html('$125/year');
		$("#60-amount").html('$60/year');
		$("#36-amount").html('$36/year');
	}
}

function isChecked(el){
	if ($(el).is(':checked'))
		return 1;
	return 0;
}

