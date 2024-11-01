jQuery(document).ready(function($) {
	$("select#appinsights_subscription").change(function() {
		$("#appinsights-update-options").attr("disabled", "disabled");
		$("#appinsights_component_response").html('<td colspan="2"><div class="appinsights-spinner"><img src="images/wpspin_light.gif" alt="Loading..." /></div></td>');
		var data = {
			'action': 'display_appinsights_components',
			'subscription_id': $("#appinsights_subscription option:selected").val(),
			'next_nonce' : AppInsights_Ajax.next_nonce
		};
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(AppInsights_Ajax.ajax_url, data, function(response) {
			$("#appinsights_component_response").html(response);
			$("#appinsights-update-options").removeAttr("disabled");
		});
	});
});