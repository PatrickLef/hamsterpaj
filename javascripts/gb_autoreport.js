$(document).ready(function(){
	// Remove report
	$('.gb_autoreport_validate').click(function() {
		var id = $(this).attr('id');
		$.ajax({
			url: '/ajax_gateways/gb_autoreport.php?action=post_validate',
			type: 'GET',
			data: 'id=' + id,
			success: function(result) {
				$('#gb_autoreport_post_message_' + id).slideUp('500');
				$('#gb_autoreport_post_info_' + id).slideUp('500');
			}
		});
		return false;
	});
});