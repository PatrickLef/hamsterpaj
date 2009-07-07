<?php
	try
	{
		require('../include/core/common.php');
		require_once(PATHS_LIBRARIES . 'photoblog.lib.php');
		
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
			case 'photo_fetch':
				if(!isset($_GET['id']) || !is_numeric($_GET['id']))
				{
					throw new Exception('No ID or faulty ID recieved');
				}
				
				$options['order-by'] = 'up.date';
				// fetch a single image
				if(!isset($_GET['month']) )
				{
					$options['id'] = $_GET['id'];
					friends_notices_set_read(array('action' => 'photos', 'item_id' => $_GET['id']));
					
					$photo = photoblog_photos_fetch($options);
					
					$ret['photo'] = $photo;
					
					// fetch comments
					$options = array();
					$options['photo_id'] = $_GET['id'];
					$comments = photoblog_comments_fetch($options);
					$options['use_container'] = false;
					$options['my_blog'] = login_checklogin() && $_SESSION['login']['id'] == $photo[0]['user'];
					$comments = photoblog_comments_list($comments, $options);
					
					$ret['comments'] = htmlentities($comments, ENT_QUOTES, 'UTF-8');
				}
				// fetch an entire month
				else
				{
					if(!is_numeric($_GET['month']))
					{
						throw new Exception('Month not numerical.');
					}
				
					$options['user'] = $_GET['id'];
					$options['month'] = $_GET['month'];
					$ret = photoblog_photos_fetch($options);
				}
				echo json_encode($ret);
			break;
			
			case 'photo_edit':
				if ( ! ctype_digit($_POST['edit_id']) )
				{
				    throw new Exception('Felaktig edit_id');
				}
				
				$options = array('id' => $_POST['edit_id']);
				$photo_info = photoblog_photos_fetch($options);
				$photo_info = end($photo_info);
				
				if ( $photo_info['user'] != $_SESSION['login']['id'] )
				{
				    throw new Exception('Endast medlemmar har rättighet att ändra bilder');
				}
				
				if ( isset($_POST['edit_submit']) )
				{
				    $data = array();
				    $data['id'] = $photo_info['id'];
				    $data['description'] = $_POST['edit_description'];
				    $data['date'] = $_POST['edit_date'];
				    photoblog_photos_update($data);
				}
				elseif ( isset($_POST['edit_delete']) && is_privilegied('photoblog_photo_remove') )
				{
				    $data = array('deleted' => 1, 'id' => $photo_info['id']);
				    photoblog_photos_update($data);
				}
				
				echo 'Du har sedermera uppdaterat ditt photo.';
			break;
			
			case 'photos_remove':
				if ( ! isset($_GET['photos']) )
				{
					throw new Exception('No input');	
				}
				
				$photos = explode('|', trim($_GET['photos'], '|'));
				
				foreach ( $photos as $photo )
				{
					if ( ! is_numeric($photo) )
					{
						throw new Exception('Bad input');	
					}
					
					$photo_options = array('id' => $photo);
					$photo_info = photoblog_photos_fetch($photo_options);
					
					if ( ! count($photo_info) )
					{
						throw new Exception('One of removed photos did not exist.');	
					}
					
					$photo_info = $photo_info[0];
					
					if ( $photo_info['user'] != $_SESSION['login']['id'] && !is_privilegied('photoblog_photo_remove') )
					{
						throw new Exception('Removing photo without the right rights.');	
					}					
					
					$data = array('deleted' => 1, 'id' => $photo);
					photoblog_photos_update($data);
				}
			break;
			
			case 'comments_fetch':
				if(!isset($_GET['id']) || !is_numeric($_GET['id']))
			    {
			    	throw new Exception('No Photo-ID or faulty ID recieved');
			    }
				
				$options['photo_id'] = $_GET['id'];
	            $comments = photoblog_comments_fetch($options);
	            $options['use_container'] = false;
	            $options['my_blog'] = login_checklogin() && $_SESSION['login']['id'] === @$_GET['blog_id'];
	            echo photoblog_comments_list($comments, $options);
			break;
			
			case 'comments_post':
				if(!isset($_GET['id']) || !is_numeric($_GET['id']))
			    {
			    	throw new Exception('No Photo-ID or faulty ID recieved');
			    }
				if(!login_checklogin())
	            {
	            	throw new Exception('Only users can post comments.');
	            }
				
	            $options['photo_id'] = $_GET['id'];
				$options['comment'] = $_POST['comment'];
				$options['author'] = $_SESSION['login']['id'];
	            photoblog_comments_add($options);
			break;
			
			case 'comments_reply':
				if (!isset($_GET['id']) || ! is_numeric($_GET['id']))
				{
					throw new Exception('No input');	
				}
				
				if (!login_checklogin())
				{
					throw new Exception('Only users can reply to comments.');	
				}
				
				$options['comment_id'] = $_GET['id'];
				$options['reply'] = $_POST['reply'];
				$options['author'] = $_SESSION['login']['id'];
				photoblog_comments_reply($options);
			break;
			
			case 'comments_remove':
				if ( ! isset($_GET['id']) || ! is_numeric($_GET['id']) )
				{
					throw new Exception('No input');	
				}
				
				$photo_options['id'] = $_GET['id'];
				$photo = photoblog_comments_fetch($photo_options);
				
				if ( ! count($photo) )
				{
					throw new Exception('No comment');
				}
				
				$options['image_owner_id'] = $photo[0]['user'];
				$options['comment_id'] = $_GET['id'];
				photoblog_comments_remove($options);
			break;
			
			case 'calendar_render':
				if (!isset($_GET['user_id'], $_GET['month'], $_GET['year']))
			    {
			        throw new Exception('No input.');
			    }
			    if (!is_numeric($_GET['user_id']) || !is_numeric($_GET['month']) || !is_numeric($_GET['year']))
			    {
			        throw new Exception('Not numerical input.');
			    }
			    
			    echo photoblog_calendar($_GET['user_id'], $_GET['month'], $_GET['year']);
			break;
                    
                        case 'sort_save':
                            photoblog_sort_save($_OLD_POST['data']);
                        break;
			
			default:
				throw new Exception('Action not found');
			break;
		}
	}
	catch (Exception $error)
	{
		$options['type'] = 'error';
    	$options['title'] = 'Nu blev det fel här';
   		$options['message'] = $error -> getMessage();
    	$options['collapse_link'] = 'Visa felsökningsinformation';
   		$options['collapse_information'] = preint_r($error, true);
    	$out .= ui_server_message($options);
		preint_r($error);
	}
?>