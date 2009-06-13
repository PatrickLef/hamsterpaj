<?php
	global $photoblog_user;
	$options['output'].= ui_avatar($photoblog_user['id']);
	$options['output'].= '<h3><a href="/traffa/profile.php?user_id=' . $photoblog_user['id'] . '">' . $photoblog_user['username'] . '</a></h3>' . "\n";
	$options['output'].= '<br /><span><a href="/traffa/profile.php?user_id=' . $photoblog_user['id'] . '">Gå till presentation &raquo;</a></span>' . "\n";
?>