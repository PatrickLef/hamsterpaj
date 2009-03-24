<?php
	require('../include/core/common.php');
	$ui_options['current_menu'] = 'hamsterpaj';
	$ui_options['title'] = 'Användare borttagen - Hamsterpaj.net';
	
	// Check if user is privilegied
	if(!is_privilegied('remove_user'))
	{
		die('Den här sidan är endast för Hamsterpaj\'s moderatorer');
	}
	
	// Check input data
	if(!isset($_GET['userid']) && !is_numeric($_GET['userid']) && !isset($_GET['removal_message']))
	{
		throw New Exception('Inputdatan validerar inte');
	}

	// Remove the user
	if(login_remove_user($_GET['userid'], $_GET['removal_message']))
	{
		$out = 'Användaren var successfullt borttagen.';
	}
	else
	{
		$out = 'Nu gick något fel, troligtvis är användaren redan borttagen';
	}

	ui_top($ui_options);
	echo $out;
	ui_bottom();
?>


