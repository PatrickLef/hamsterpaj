<?php
	$ui_options['ui_modules']['photoblog_user'] = 'User';
	$ui_options['ui_modules']['photoblog_albums'] = 'Album';
	$ui_options['ui_modules']['photoblog_calendar'] = 'Kalender';
	
		if ( isset($uri_parts[4]) && preg_match('/^[a-zA-Z0-9-_]+$/', $uri_parts[4]) )
		{
			$albumname = $uri_parts[4];
			global $photoblog_user;
			
			$options = array();
			
			$options['handle'] = $albumname;
			$options['user'] = $photoblog_user['id'];
			
			$photoblog_album = photoblog_categories_fetch($options);
			
			$options['category'] = $photoblog_album[0]['id'];
			
			unset($options['handle']);
			
			list($photos_sorted, $category) = photoblog_photos_fetch_sorted($options);
		
			$out .= '<h2>' . $category[0]['name'] . '</h2>';
			
			$user_id = $photoblog_user['id'];
			$options = array(
				'photos' => reset($photos_sorted),
				'user_id' => $user_id,
				'include_dates' => false,
				'load_first' => true,
				'album_view' => true
			);
			
			$out .= photoblog_viewer($options);
		}
		else
		{		
				$out .= '<h2>' . $photoblog_user['username'] . 's album</h2>';
				global $photoblog_user;
				$options['user'] = $photoblog_user['id'];
				$photoblog_albums = photoblog_categories_fetch($options);
				
				foreach($photoblog_albums as $photoblog_album)
				{
					if(count($photoblog_album['photos']) >= 1)
					{
						$out .= '<a href="/fotoblogg/' . $photoblog_user['username'] . '/album/' . $photoblog_album['handle'] . '" />' . "\n";
						$out .= '<h3>' . $photoblog_album['name'] . '</h3>' . "\n";
						$out .= '<img src="' . IMAGE_URL . 'photos/full/' . floor($photoblog_album['photos'][0]/5000) . '/' . $photoblog_album['photos'][0] . '.jpg" />' . "\n";
						$out .= '</a>' . "\n";
					}
				}
		}
		
		
		
?>