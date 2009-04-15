<?php
	try
	{
		require('../include/core/common.php');
		
		if(!is_privilegied('gb_autoreport'))
		{
			jscript_alert('Denna sida kräver privilegiet: gb_autoreport');
			jscript_location('/');
			die('inte för dig...');
		}
		
		if(isset($_GET['action']))
		{
			$action = $_GET['action'];
		}
		else
		{
			throw new Exception('No action in get data recieved');
		}
		
		switch($action)
		{
			case 'post_validate':
				if(!is_numeric($_GET['id']))
				{
					throw new Exception('ID not numeric');
				}
				
				$query = 'UPDATE gb_autoreport_posts SET checked = 1 WHERE id = ' . $_GET['id'];
				mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
				
				if($_GET['return'] == true)
				{
					header('Location: /admin/gb_autoreport.php');
				}
			break;
			
			default:
				throw new Exception('Action not found');
			break;
		}
	}
	catch (Exception $error)
	{
		echo '<div class="form_notice_error">';
   		echo $error -> getMessage();
		echo '</div>';
	}
?>