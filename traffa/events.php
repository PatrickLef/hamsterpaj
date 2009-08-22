<?php
	require('../include/core/common.php');
	
	require_once(PATHS_LIBRARIES . 'photoblog.lib.php');
	require_once(PATHS_LIBRARIES . 'comments.lib.php');
	require_once(PATHS_LIBRARIES . 'guestbook.lib.php');
	
	
	$ui_options['stylesheets'][] = 'photos.css';
	$ui_options['javascripts'][] = 'photos.js';
	
	$ui_options['stylesheets'][] = 'comments.css';
	$ui_options['javascripts'][] = 'comments.js';
	
	$ui_options['title'] = 'Dina nya händelser - Hamsterpaj.net';
	$ui_options['menu_path'] = array('traeffa', 'haendelser');
	
	ui_top($ui_options);
	
	if(login_checklogin())
	{
		echo '<h1>Nya händelser</h1>' . "\n";
		
		echo '<h2>Nya fotokommentarer</h2>' . "\n";
		$photos = photoblog_photos_fetch(array('user' => $_SESSION['login']['id'], 'force_unread_comments' => true));
		if(count($photos) > 0)
		{
			echo 'Wosch! Nya kommentarer att besvara!<br />' . "\n";
			echo '<ul class="photos_list">' . "\n";
			foreach($photos AS $photo)
			{
				$photo['description'] = (mb_strlen($photo['description'], 'UTF8') > 19) ? mb_substr($photo['description'], 0, 17, 'UTF8') . '...' : $photo['description'];
				echo '<li>' . "\n";
				echo '<a href="/fotoblogg/' . $photo['username'] . '/' . $photo['id'] . '"><img src="' . IMAGE_URL . 'photos/thumb/' . floor($photo['id']/5000) . '/' . $photo['id'] . '.jpg" title="' . $photo['username'] . '" /></a>';
				echo '<p><a href="/fotoblogg/' . $photo['username'] . '/' . $photo['id'] . '">' . $photo['description'] . '</a>';
				echo ($photo['user'] == $_SESSION['login']['id'] && $photo['unread_comments'] > 0) ? '<strong>(' . $photo['unread_comments'] . ')</strong>' : '';
				echo '</p>' . "\n";
				echo '</li>' . "\n";
			}
			echo '</ul>' . "\n";
		}
		else
		{
			echo '<italic>Du har inga oläsa fotokommentarer.</italic>';
		}
	}
	else
	{
		echo '<h1>Du måste vara inloggad</h1>' . "\n";
		echo 'Du måste vara inloggad för att komma åt den här sidan.' . "\n";
	}
	
	ui_bottom();
?>