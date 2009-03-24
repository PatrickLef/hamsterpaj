$(function() {
	$('.remove_user').click(function() {
		var userid = $(this).attr('id');
		var removal_message = prompt('Varför togs användaren bort?\n(Visas för användaren)', 'Här MÅSTE du lämna en beskrivning om varför användaren togs bort!');
		if(!removal_message || removal_message == 'Här MÅSTE du lämna en beskrivning om varför användaren togs bort!')
		{
			alert('Åtgärd avbruten.\nDu måste skriva ett meddelande och tryck OK.');
			return false;
		}
		else
		{
			removal_message = encodeURIComponent(removal_message);
		}
		document.location.href = '/admin/remove_user.php?userid=' + userid + '&removal_message=' + removal_message;
		return false;
	});
});