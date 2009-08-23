<?php
	$ui_options['ui_modules']['photoblog_calendar'] = 'Fotoblogg Kalender';
	$ui_options['ui_modules']['photoblog_albums'] = $photoblog_user['username'] . 's album';

		if ( is_numeric($uri_parts[3]) && isset($front_access) )
		{
			$options['id'] = $uri_parts[3];
			$options['user'] = $photoblog_user['id'];
			$photo = end(photoblog_photos_fetch($options));
			if ( $photo )
			{
				$albumid = $photo['category'];
			}
		}

		if ( isset($albumid) || (isset($uri_parts[4]) && preg_match('/^[a-zA-Z0-9-_]+$/', $uri_parts[4])) )
		{
			$albumname = $uri_parts[4];
			global $photoblog_user;
			
			$options = array();
			
			if ( isset($albumid) )
			{
				$options['id'] = $albumid;
			}
			else
			{
				$options['handle'] = $albumname;
			}
			
			$options['user'] = $photoblog_user['id'];
			
			$photoblog_album = photoblog_categories_fetch($options);
			
			$options['category'] = $photoblog_album[0]['id'];
			
			unset($options['handle'], $options['id']);
			
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
			
			if ( isset($photo) )
			{
				unset($options['load_first']);
				$options['active_id'] = $photo['id'];
			}
			
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