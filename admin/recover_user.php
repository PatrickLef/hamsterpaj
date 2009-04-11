<?php
	require('../include/core/common.php');
	$ui_options['current_menu'] = 'hamsterpaj';
	$ui_options['title'] = 'Användare borttagen - Hamsterpaj.net';
	
	// Check if user is privilegied
	if(!is_privilegied('recover_user'))
	{
		die('Den här sidan är endast för Hamsterpaj\'s moderatorer');
	}
	
	// Check input data
	if(!isset($_GET['userid']) || !is_numeric($_GET['userid']) || (strlen($_GET['username']) < 2))
	{
		die('Inputdatan validerar inte');
	}

	// Recover the user
	switch(login_recover_user($_GET['userid'], $_GET['username']))
	{
		case 'success':
			header('Location: /traffa/profile.php?user_id=' . $_GET['userid']);
		break;
		case 'no_user':
			$out .= 'Den här användaren har aldrig funnits' . "\n";
		break;
		case 'username_taken':
			$out .= 'Det användarnamn som den här användaren hade är upptaget, välj ett nytt' . "\n";
			$out .= '<form method="get">' . "\n";
			$out .= '<input type="text" name="username" />' . "\n";
			$out .= '<input type="text" name="userid" style="display: none;" value="' . $_GET['userid'] . '" />' . "\n";
			$out .= '<input type="submit" value="Återskapa" />' . "\n";
			$out .= '</form>' . "\n";
		break;
		default:
			$out .= 'Något skumt systemfel har inträffat' . "\n";
		break;
	}

	ui_top($ui_options);
	echo $out;
	ui_bottom();
?>
