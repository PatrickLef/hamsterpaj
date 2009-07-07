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
			
			$category = end($category);
		
			$out .= '<h2>' . $category['name'] . '</h2>';
			
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
			$name = $photoblog_user['username'];
			$name = $name . (substr($name, -1, 1) == 's' ? '' : 's');
			$out .= '<h2>' . $name . ' album</h2>';
			global $photoblog_user;
			
			$photo_options = array(
				'user_id' => $photoblog_user['id']
			);
		
			$out .= photoblog_viewer_albums($photo_options);
		}
		
		
		
?>