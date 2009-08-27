<?php

		$ui_options['ui_modules']['photoblog_calendar'] = 'Fotoblogg Kalender';
		$ui_options['ui_modules']['photoblog_albums'] = $photoblog_user['username'] . 's album';
		$ui_options['javascripts'][] = 'jquery.protect-image.js';
		
		if ( ! isset($highest_date) || $highest_date == 0 )
		{
			$date = date('Ym', time());
		}
		else
		{
			$date = $highest_date;
		}
		
		$options = array(
			'user_id' => $photoblog_user['id'],
			'date' => $date,
			'active_id' => (isset($uri_parts[3]) && is_numeric($uri_parts[3]) ? $uri_parts[3] : false)
		);
		
		$out .= photoblog_viewer($options);
?>