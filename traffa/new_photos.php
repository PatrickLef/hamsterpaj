<?php
	require('../include/core/common.php');
	require_once(PATHS_LIBRARIES . 'photoblog.lib.php');

	$ui_options['stylesheets'][] = 'photos.css';
	$ui_options['menu_path'] = array('traeffa', 'new_photos');
	$ui_options['title'] = 'Nya foton - Hamsterpaj.net';

	$out .= '<h1>Nya foton</h1>';

	//Get pagenumber
	$page = 1;
	if(isset($_GET['page']) && is_numeric($_GET['page']))
	{
		$page = intval($_GET['page']);
		if($page < 1 || $page > 999)
		{
			$page = 1;
		}
	}

	$offset = (($page - 1) * 32);
	
	$photos = photoblog_photos_fetch(array('order-direction' => 'DESC', 'offset' => $offset, 'limit' => 32));
	$out .= '<ul class="photos_list">' . "\n";
		foreach($photos AS $photo)
		{
			$photo['description'] = (mb_strlen($photo['description'], 'UTF8') > 19) ? mb_substr($photo['description'], 0, 17, 'UTF8') . '...' : $photo['description'];
			$out .= '<li>' . "\n";
			$out .= '<a href="/fotoblogg/' . $photo['username'] . '/' . $photo['id'] . '"><img src="' . IMAGE_URL . 'photos/thumb/' . floor($photo['id']/5000) . '/' . $photo['id'] . '.jpg" title="' . $photo['username'] . '" /></a>';
			$out .= '<p><a href="/fotoblogg/' . $photo['username'] . '/' . $photo['id'] . '">' . $photo['description'] . '</a>';
			$out .= ($photo['user'] == $_SESSION['login']['id'] && $photo['unread_comments'] > 0) ? '<strong>(' . $photo['unread_comments'] . ')</strong>' : '';
			$out .= '</p>' . "\n";
			$out .= '</li>' . "\n";
		}
		$out .= '</ul>' . "\n";

	//Create Pagination links
	if(isset($_GET['page']) && is_numeric($_GET['page']))
	{
		$page = intval($_GET['page']);
		if($page > 1)
		{
			$out .= ' <a href="' . $_SERVER['PHP_SELF'] . '?page=' . ($page - 1) . '">&laquo; Föregående</a> |';
		}
		
		if($page > 0)
		{
			$out .= ' ' . $page . ' | <a href="' . $_SERVER['PHP_SELF'] . '?page=' . ($page + 1) . '">Nästa &raquo;</a>';
		}
	}
	else
	{
		$out .= ' <a href="' . $_SERVER['PHP_SELF'] . '?page=2">Nästa &raquo;</a>';
	}

	ui_top($ui_options);
	echo $out;
	ui_bottom();
?>
