function wpdp_show_notice(value, msg) {
	if (value === 'bulk-delete') {
		return confirm(msg);
	}
}

function wpdp_action_button(msg) {
	action1 = jQuery("#wpda_main_form :input[name='action']").val();
	if (action1 !== "-1") {
		return wpdp_show_notice(action1, msg);
	}
	action2 = jQuery("#wpda_main_form :input[name='action2']").val();
	if (action2 !== "-1") {
		return wpdp_show_notice(action2, msg);
	}
	return true;
}
