function formatMMd($datestr) {
	var d = new Date($datestr);
	d.setHours(d.getHours() + 8);
	return $.datepicker.formatDate("MM d", d);
}

