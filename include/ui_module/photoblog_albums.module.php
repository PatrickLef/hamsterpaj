<?php
	global $photoblog_user;
	$photo_options = array('user' => $photoblog_user['id']);
	$photoblog_albums = photoblog_categories_fetch($photo_options);
	$username = $photoblog_user['username'];
	$possessive = (substr($username, -1, 1) == 's') ? $username : $username . 's';
	$options['output'] .= sprintf('<h2><a href="/fotoblogg/%s/album">%s album</a></h2>', $username, $possessive);
	foreach($photoblog_albums as $photoblog_album)
	{
		if(strlen($photoblog_album['photos']) >= 1)
		{
			$photoblog_album['photos'] = explode(',', $photoblog_album['photos']);
			$options['output'].= '<a class="photoblog_album_module_link" href="/fotoblogg/' . $photoblog_user['username'] . '/album/' . $photoblog_album['handle'] . '">';
			$options['output'].= '<img src="http://images.hamsterpaj.net/photos/thumb/' . floor($photoblog_album['photos'][0]/5000) . '/' . $photoblog_album['photos'][0] . '.jpg" />';
			$options['output'].= '<h3>' . $photoblog_album['name'] . '</h3>';
			$options['output'].= '</a><br style="clear: both;" />';
		}
	}
?>