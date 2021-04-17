/**
 * Javascript code needed to build tables in WordPress with jQuery DataTables.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */

if (typeof Object.assign != 'function') {
	// IE
	Object.assign = function(target, varArgs) { // .length of function is 2
		'use strict';
		var to = Object(target);
		for (var index = 1; index < arguments.length; index++) {
			var nextSource = arguments[index];
			if (nextSource != null) { // Skip over if undefined or null
				for (var nextKey in nextSource) {
					// Avoid bugs when hasOwnProperty is shadowed
					if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
						to[nextKey] = nextSource[nextKey];
					}
				}
			}
		}
		return to;
	};
}

function wpda_datatables_ajax_call(
	columnsvar, database, table_name, columns,
	responsive, responsive_popup_title, responsive_type, responsive_icon,
	language, sql_orderby,
	table_options_searching, table_options_ordering, table_options_paging, table_options_advanced,
	pub_id, pub_show_advanced_settings, modal_hyperlinks,
	filter_field_name, filter_field_value,
	nl2br, buttons, read_more, calc_estimate
) {
	/*
	* display possible values:
	* childrow = user toggled
	* childrowimmediate = show
	* modal = show details in modal window
	*/

	/*
	* type possible values:
	* column = no control element
	* inline = show control element
	*/

	var responsive_control_type = "inline";
	if (responsive_icon !== "yes") {
		responsive_control_type = "column";
	}

	var childrow = {
		details: {
			display: jQuery.fn.dataTable.Responsive.display.childRow,
			renderer: function (api, rowIdx, columns) {
				var data = jQuery.map(
					columns, function (col, i) {
						if (!col.hidden ) {
							return '';
						}
						if (columnsvar[i].className==='wpda_select') {
							return '';
						}
						if (pub_show_advanced_settings==='Never' && modal_hyperlinks.includes(i)) {
							return '';
						}
						if (pub_show_advanced_settings==='If not listed' && modal_hyperlinks.includes(i) && !col.hidden) {
							return '';
						}
						return '<tr class="' + columnsvar[i].className + '">' +
							'<td>' + columnsvar[i].label + '</td>' +
							'<td><strong>' + col.data + '</strong></td>' +
							'</tr>';
					}
				).join( '' );
				var datatable = '<table class="wpda-child-table display dataTable">' + data + '</table>';
				var table     = '<tr><td>' + datatable + '</td></tr>';

				return jQuery( '<table/>' ).append( table );
			},
			type: responsive_control_type
		}
	};

	var childrowimmediate = {
		details: {
			display: jQuery.fn.dataTable.Responsive.display.childRowImmediate,
			renderer: function (api, rowIdx, columns) {
				var data = jQuery.map(
					columns, function (col, i) {
						if (!col.hidden ) {
							return '';
						}
						if (columnsvar[i].className==='wpda_select') {
							return '';
						}
						if (pub_show_advanced_settings==='Never' && modal_hyperlinks.includes(i)) {
							return '';
						}
						if (pub_show_advanced_settings==='If not listed' && modal_hyperlinks.includes(i) && !col.hidden) {
							return '';
						}
						return '<tr class="' + columnsvar[i].className + '">' +
							'<td>' + columnsvar[i].label + '</td>' +
							'<td><strong>' + col.data + '</strong></td>' +
							'</tr>';
					}
				).join( '' );
				var datatable = '<table class="wpda-child-table display dataTable">' + data + '</table>';
				var table     = '<tr><td>' + datatable + '</td></tr>';

				return jQuery( '<table/>' ).append( table );
			},
			type: responsive_control_type
		}
	};

	var modal = {
		details: {
			display: jQuery.fn.dataTable.Responsive.display.modal(
				{
					header: function (row) {
						return responsive_popup_title;
					}
				}
			),
			renderer: function (api, rowIdx, columns) {
				var data = jQuery.map(
					columns, function (col, i) {
						if (columnsvar[i].className==='wpda_select') {
							return '';
						}
						if (pub_show_advanced_settings==='Never' && modal_hyperlinks.includes(i)) {
							return '';
						}
						if (pub_show_advanced_settings==='If not listed' && modal_hyperlinks.includes(i) && !col.hidden) {
							return '';
						}
						return '<tr class="' + columnsvar[i].className + '">' +
							'<td>' + columnsvar[i].label + '</td>' +
							'<td><strong>' + col.data + '</strong></td>' +
							'</tr>';
					}
				).join( '' );
				var datatable = '<table class="wpda-child-table display dataTable">' + data + '</table>';
				var footer    = '<tr><td style="padding-top:10px; text-align: center"><div>' +
					'<input type="button" value="Close" class="button dtr-modal-close" onclick="jQuery(\'.dtr-modal\').remove()"/>' +
					'</div></td></tr>';
				var table     = '<tr><td>' + datatable + '</td></tr>' + footer;

				return jQuery( '<table/>' ).append( table );
			},
			type: responsive_control_type
		}

	};

	var responsive_value = false;
	if (responsive === 'yes') {
		switch (responsive_type) {
			case "modal":
				responsive_value = modal;
				break;
			case "expanded":
				responsive_value = childrowimmediate;
				break;
			default:
				/* collapsed */
				responsive_value = childrow;
		}
	}

	if (language===undefined) {
		language = 'English';
	}

	orderby = [];
	if ( sql_orderby != '') {
		sql_orderby.split("|").forEach(function (item) {
			orderby_array = item.split(",");
			orderby.push(orderby_array);
		});
	}

	var jQueryDataTablesUserOptions = {
		searching: table_options_searching,
		ordering: table_options_ordering,
		paging: table_options_paging
	};
	if (!table_options_paging) {
		jQueryDataTablesUserOptions.serverSide = false;
	}

	var stateSave = true;
	if (orderby.length>0) {
		stateSave = false;
	}

	// Allow user to use more button to load additional rows
	var more_rows = [];
	var more_start = 0;
	var more_limit = 10;
	var more_new = true;

	if (table_options_advanced!==null && table_options_advanced.pageLength!==undefined && !isNaN(table_options_advanced.pageLength)) {
		more_limit = table_options_advanced.pageLength;
	}

	var jQueryDataTablesDefaultOptions = {
		responsive: responsive_value,
		processing: true,
		serverSide: true,
		stateSave: stateSave,
		bAutoWidth: false,
		columnDefs: columnsvar,
		order: orderby,
		buttons: buttons,
		ajax: {
			method: "POST",
			url: wpda_ajax.wpda_ajaxurl,
			data: function(data) {
				data.action ="wpda_datatables";
				data.database = database;
				data.table_name = table_name;
				data.columns = columns;
				data.pub_id = pub_id;
				data.filter_field_name = filter_field_name;
				data.filter_field_value = filter_field_value;
				data.nl2br = nl2br;
				if (read_more==="true") {
					if (more_new) {
						more_start = 0;
					} else {
						more_start += more_limit;
					}
					data.more_start = more_start;
					data.more_limit = more_limit;
				}
				jQuery.each(window.location.search.replace('?','').split('&'), function(index, val) {
					var urlparam = val.split('=');
					if (urlparam.length===2) {
						if (urlparam[0].substring(0, 19) === 'wpda_search_column_') {
							data[urlparam[0]] = urlparam[1];
						}
					}
				});
				var function_name = 'wpda_' + table_name + '_advanced_' + pub_id;
				if (typeof window[function_name] === "function") {
					var return_value = eval(function_name)();
					if (Array.isArray(return_value)) {
						for (var key in return_value) {
							data[key] = return_value[key];
						}
					}
				}
			},
			dataSrc: function(data) {
				if (read_more==="true") {
					if (data.data.length<more_limit) {
						jQuery("#" + table_name + pub_id + "_more_button").prop('disabled', true).html('END OF LIST');
					} else {
						jQuery("#" + table_name + pub_id + "_more_button").prop('disabled', false).html('SHOW MORE');
					}
					if (more_new) {
						more_rows = data.data;
					} else {
						more_rows = more_rows.concat(data.data);
					}
					more_new = true;

					return more_rows;
				}

				return data.data;
			}
		},
		language: {
			url: datatables_i18n_url + language + ".json"
		},
		infoCallback: function( settings, start, end, max, total, pre ) {
			if (read_more==="true") {
				if (jQueryDataTablesDefaultOptions.wpda_search_more_info) {
					return jQueryDataTablesDefaultOptions
							.wpda_search_more_info
							.replaceAll("_START_", start)
							.replaceAll("_END_", end)
							.replaceAll("_MAX_", max)
							.replaceAll("_TOTAL_", total);
				} else {
					return end + " rows selected (from " + total + " entries)";
				}
			}

			return (calc_estimate === 'true' ? '~' : '') +  pre;
		},
		initComplete: function(settings, json) {
			if (responsive === 'yes') {
				hiddenColumns = this.api().columns().responsiveHidden();
				for (i=0; i<hiddenColumns.length; i++) {
					if (!hiddenColumns[i]) {
						hide_header_and_footer_of_hidden_column(table_name, pub_id, i);
					}
				}
			}

			if (jQueryDataTablesDefaultOptions.userInitComplete) {
				jQueryDataTablesDefaultOptions.userInitComplete(settings, json);
			}
		},
		drawCallback: function(settings) {
			if (buttons.length > 0) {
				jQuery("#" + table_name + pub_id).find("td").on("click", function(e) {
					if (jQuery(this).hasClass("dtr-control")) {
						table = jQuery("#" + table_name + pub_id).DataTable();
						if (!table.responsive.hasHidden()) {
							// Overwrite default icon behaviour
							selectedRows = table.row({selected : true});
							if (selectedRows[0].includes(this._DT_CellIndex.row)) {
								table.row(':eq(' + this._DT_CellIndex.row + ')', { page: 'current' }).deselect();
							} else {
								table.row(':eq(' + this._DT_CellIndex.row + ')', { page: 'current' }).select();
							}
						}
					}
				});
			}

			if (jQueryDataTablesDefaultOptions.userDrawCallback) {
				jQueryDataTablesDefaultOptions.userDrawCallback(settings);
			}
		}
	};

	if ( typeof Object.assign != 'function' ) {
		var jQueryDataTablesOptions = jQueryDataTablesDefaultOptions;
	} else {
		var jQueryDataTablesOptions = Object.assign(jQueryDataTablesDefaultOptions, jQueryDataTablesUserOptions);
	}

	convert_string_to_function(table_options_advanced);
	jQueryDataTablesOptions = Object.assign(jQueryDataTablesOptions, table_options_advanced);

	jQuery("#" + table_name + pub_id).addClass('wpda-datatable');
	jQuery("#" + table_name + pub_id).DataTable(jQueryDataTablesOptions);

	if (jQuery("#" + table_name + pub_id + "_more_button").length>0) {
		// Add load more rows action
		jQuery("#" + table_name + pub_id + "_more_button").on("click", function() {
			more_new = false;
			jQuery("#" + table_name + pub_id).DataTable().draw("page");
			jQuery('html, body').animate({
				scrollTop: jQuery("#" + table_name + pub_id + "_more_container").offset().top
			}, 1000);
		});
	}
}

function convert_string_to_function(obj) {
	for (var prop in obj) {
		if (typeof obj[prop]=='string') {
			if (obj[prop].substr(0,8)=='function') {
				fnc = obj[prop];
				delete obj[prop];
				var f = new Function("return " + fnc);
				if (prop==="initComplete") {
					// Plugin users cannot overwrite initComplete
					prop = "userInitComplete";
				}
				if (prop==="drawCallback") {
					// Plugin users cannot overwrite initComplete
					prop = "userDrawCallback";
				}
				obj[prop] = f();
			}
		} else {
			convert_string_to_function(obj[prop]);
		}
	}
}

function hide_header_and_footer_of_hidden_column(table_name, pub_id, i) {
	// Hide labels of dynamic hyperlinks and double header rows
	var tr_head0 = jQuery("#" + table_name + pub_id).find('thead tr').eq(0);
	tr_head0.find('th').eq(i).hide();
	tr_head0.find('td').eq(i).hide();

	var tr_head1 = jQuery("#" + table_name + pub_id).find('thead tr').eq(1);
	tr_head1.find('th').eq(i).hide();
	tr_head1.find('td').eq(i).hide();

	var tr_foot  = jQuery("#" + table_name + pub_id).find('tfoot tr').eq(0);
	tr_foot.find('th').eq(i).hide().find('td').eq(i).hide();
}