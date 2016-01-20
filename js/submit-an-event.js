var $ = jQuery.noConflict();

$(document).ready(function() {	

	var today = new Date(), oneYear = new Date();
	oneYear = oneYear.setFullYear(oneYear.getFullYear() + 1);

	$('#start-date').datetimepicker({
		format: 'YYYY-MM-DD'
		, minDate: today
	});
	$('#start-time').datetimepicker({
		format:	'LT'
		, stepping:	15
	});
	$('#end-date').datetimepicker({
		format: 'YYYY-MM-DD'		
		, minDate: today
	});
	$('#end-time').datetimepicker({
		format:	'LT'
		, stepping: 15
	});
	$('#recurs-until').datetimepicker({
		format: 'YYYY-MM-DD'		
		, minDate: today
		, maxDate: oneYear
	});

	$("#event-all-day").click(function() {
		if (isChecked(this)) {
			allDayEvent();
		} else {
			partialDayEvent();
		}
	});
	
	$('#event-recurrence').change(function() {
		if (this.options[this.selectedIndex].value == 'none') {
			$('#recurs-until input').val('');
			$('#wrap-recurs-until').slideUp();
		}
		else if (this.options[this.selectedIndex].value == 'weekly') {
			$('#wrap-recurs-until').slideDown();
		}		
	});
	
	$('#start-date input').val('');
	$('#end-date input').val('');
	$('#recurs-until input').val('');
});

function allDayEvent() {
	$('#start-time input').val('');
	$('#end-time input').val('');
	$('#wrap-start-time').slideUp();
	$('#wrap-end-time').slideUp();
}

function partialDayEvent() {
	$('#wrap-start-time').slideDown();
	$('#wrap-end-time').slideDown();
}

function isChecked(el){
	if ($(el).is(':checked'))
		return 1;
	return 0;
}

