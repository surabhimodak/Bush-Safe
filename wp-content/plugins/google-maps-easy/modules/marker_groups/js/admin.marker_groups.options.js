var g_gmpMarkerGroupsOptsFormChanged = false;

jQuery(document).ready(function() {
	var form = jQuery('#gmpMgrForm');

	form.find('#mgrFontSizeLevelsShell').sortable({
		items: '.mgrFontSizeLevel',
		axis: 'y'
	});
	form.find('.mgrFontSizeLevelAddBtn').on('click', function(e) {
		_gmpAddFontSizeLevelOpt();
		return false;
	});
	form.find('.mgrFontSizeLevelClearBtn').on('click', function(e) {
		_gmpClearFontSizeLevelOpt();
		return false;
	});
	jQuery(document).on('click', '.mgrFontSizeLevelRemoveBtn', function() {
		jQuery(this).parents('.mgrFontSizeLevel:first').fadeOut(300,function() {
			jQuery(this).remove();
		});
	});
	jQuery('#gmpMgrSaveBtn').click(function(e) {
		jQuery('#gmpMgrForm').submit();
		return false;
	});
	form.submit(function(e) {
		e.stopPropagation();
		e.preventDefault();
		jQuery(this).sendFormGmp({
			btn: '#gmpMgrSaveBtn'
		,	onSuccess: function (res) {
				if(!res.error) {
					_gmpUnchangeMarkerGroupsOptsForm();
				}
			}
		});
		return false;
	});
	form.find('input').change(function(){
		_gmpChangeMarkerGroupsOptsForm();
	});
});
function _gmpAddFontSizeLevelOpt(value) {
	value = value || '';

	var $optExample = jQuery('#mgrFontSizeLevelExample');

	if($optExample.length) {
		var $newOpt = $optExample.clone().removeAttr('id');

		$newOpt.appendTo(jQuery('#mgrFontSizeLevelsShell'));
		$newOpt.find('[name="source[]"]').val(value);
		$newOpt.find('input').removeAttr('disabled');
		$newOpt.show();
	}
}
function _gmpClearFontSizeLevelOpt() {
	var $optShell = jQuery('#mgrFontSizeLevelsShell');

	if($optShell.length) {
		$optShell.html('');
	}
}
// Marker Groups Opts form check change actions
function _gmpIsMarkerGroupsOptsFormChanged() {
	return g_gmpMarkerGroupsOptsFormChanged;
}
function _gmpChangeMarkerGroupsOptsForm() {
	g_gmpMarkerGroupsOptsFormChanged = true;
}
function _gmpUnchangeMarkerGroupsOptsForm() {
	g_gmpMarkerGroupsOptsFormChanged = false;
}