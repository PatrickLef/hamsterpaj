<?php

try {
	include('../include/core/common.php');
	require_once(PATHS_LIBRARIES . 'photoblog.lib.php');
	
	
	if(isset($_GET['action']))
	{
		$action = $_GET['action'];
	}
	else
	{
		throw new Exception('No action in get data recieved');
	}
		
	if (!login_checklogin())
	{
		throw new Exception('You must be logged in to ordna');
	}
	
	switch ( $action )
	{
		case 'album_new':
			if ( empty($_GET['name']) )
			{
				throw new Exception('Name cannot be empty');
			}
			
			$options = array(
				'user' => $_SESSION['login']['id'],
				'name' => $_GET['name']
			);
			photoblog_categories_new($options);
		break;
		
		case 'album_edit':
			if ( ! isset($_GET['id']) || ! is_numeric($_GET['id']) )
			{
				throw new Exception('No ID provided');
			}
			
			if ( empty($_GET['name']) )
			{
				throw new Exception('Name cannot be empty');
			}
			
			$options = array(
				'id' => $_GET['id'],
				'user' => $_SESSION['login']['id'],
				'name' => $_GET['name']
			);
			photoblog_categories_edit($options);
		break;
	}
} catch (Exception $e) {
	echo $e->getMessage();
}