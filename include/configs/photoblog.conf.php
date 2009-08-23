<?php
	$photoblog_preferences_default_values = array(
		'user_id' => isset($_SESSION['login']['id']) ? $_SESSION['login']['id'] : null,
		'color_main' => 'FFFF00',
		'color_detail' => 'FF00FF',
		'members_only' => '0',
		'friends_only' => '0',
		'copy_protection' => '0',
		'album_or_blog' => 'blog'
	);
?>