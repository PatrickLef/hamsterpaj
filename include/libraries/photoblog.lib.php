<?php	function photoblog_fetch_active_user_data($options)	{		if(!isset($options['user_id']) && isset($options['username']) && preg_match('/^[a-zA-Z0-9-_]+$/', $options['username']) && strtolower($options['username']) != 'borttagen')		{			$query = 'SELECT id AS user_id FROM login WHERE username LIKE "' . str_replace('_', '\\_', $options['username']) . '" LIMIT 1';			$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);						if(mysql_num_rows($result) == 1)			{				$data = mysql_fetch_assoc($result);				$options['user_id'] = $data['user_id'];			}			else			{				throw new Exception('The username cannot be found!');			}		}				if(!isset($options['user_id']))		{			throw new Exception('No user_id passed to photoblog_fetch_active_user_data function.');		}				if(!is_numeric($options['user_id']))		{			throw new Exception('Die die die! Die hard!');		}				$query = 'SELECT pp.*, l.id, l.username';		$query .= ' FROM login AS l, photoblog_preferences AS pp';		$query .= ' WHERE pp.userid = l.id AND l.id = "' . $options['user_id'] . '"';		$query .= ' LIMIT 1';				$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);		if(mysql_num_rows($result) == 0)		{					$insert_query = 'INSERT INTO photoblog_preferences (userid, color_main, color_detail, members_only, friends_only, copy_protection)';			$insert_query .= ' VALUES(' . $options['user_id'] . ', "333333", "FF8040", 0, 0, 0)';			mysql_query($insert_query) or report_sql_error($insert_query, __FILE__, __LINE__);		}				// Do it again to find out if it works now when the default values are filled in...		$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);				if(mysql_num_rows($result) == 1)		{			return mysql_fetch_assoc($result);		}		else		{			throw new Exception('Fatal error: No entries found, expected 1! (Or something like that, I suppose we are fetching SOMETHING).');		}	}		function photoblog_upload_upload( $options )	{		if(mktime($_SESSION['photoblog_preferences']['upload_forbidden']) > time())		{			throw new Exception('You\'re forbidden to upload photos');		}		if( !isset( $options['user'] ) )		{			throw new Exception('You must specify an user id.');		}				if( !isset(  $options['file_temp_path'] ) )		{			throw new Exception('Missing parameter: file_temp_path');		}				$options['category'] = isset($options['category']) ? $options['category'] : 'Övriga Bilder';		$category = photoblog_categories_fetch(array('user' => $options['user'], 'name' => $options['category'], 'create_if_not_found' => true));		$category = array_pop($category);
		
		$options['date'] = isset($options['date']) ? $options['date'] : date('Y-m-d');		$options['description'] = isset($options['description']) ? $options['description'] : '';
				$query = 'INSERT INTO user_photos (user, upload_complete, date, category, description)';		$query .= ' VALUES("' . $options['user'] . '", 0, "' . $options['date'] . '", "' . $category['id'] . '", "' . $options['description'] . '")';		if( ! mysql_query($query) )		{			report_sql_error($query, __FILE__, __LINE__);			throw new Exception('Query failed');		}				$photo_id = mysql_insert_id();				$folder = floor($photo_id / 5000);						// Check if folders exists, otherwise, create it		foreach(array('mini', 'thumb', 'full') AS $format)		{			if(!is_dir(PHOTOS_PATH . $format . '/' . $folder))			{				mkdir(PHOTOS_PATH . $format . '/' . $folder);			}		}		$image_size = getimagesize($options['file_temp_path']);				$square = min($image_size[0], $image_size[1]);		$width = round($square * 0.9);		$height = ($width / 4) * 3;				$mini = 'convert ' . $options['file_temp_path'] . ' -gravity center -crop ' . $width . 'x' . $height . '+0+0 -resize 50x38! ' . PHOTOS_PATH . 'mini/' . $folder . '/' . $photo_id . '.jpg';		$thumb = 'convert ' . $options['file_temp_path'] . ' -gravity center -crop ' . $width . 'x' . $height . '+0+0 -resize 150x112! ' . PHOTOS_PATH . 'thumb/' . $folder . '/' . $photo_id . '.jpg';		$full = 'convert -resize "630x630>" ' . $options['file_temp_path'] . ' ' . PHOTOS_PATH . 'full/' . $folder . '/' . $photo_id . '.jpg';		system($mini);		system($thumb);		system($full);				return $photo_id;	}	
	function photoblog_upload_messages($content)
	{
		$ret .= $content;
		
		$options['type'] = 'warning';
		$options['id'] = 'photoblog_upload_rules';
		$options['title'] = 'Att tänka på innan du laddar upp bilder';
		$options['message'] = '<h3>Du förlorar kontrollen över bilder du laddar upp!</h3>' . "\n";
		$options['message'] .= '<p>Bilder som en gång laddats upp till Internet kan kopieras och skickas vidare i all evighet. Det gäller på Hamsterpaj såväl som på alla andra webbsajter.</p>' . "\n";
		$options['message'] .= '<h3>Är du en blond tjej med stora tuttar eller kille med brunt hår och slingor? </h3>' . "\n";
		$options['message'] .= '<p>Den där blyge typen med fula glasögon och som luktade äckligt i din klass i mellanstadiet kommer förr eller senare stjäla din bild för att ragga på Lunarstorm, Hamsterpaj, PlayAhead och andra communities. När det händer så kontaktar du en ordningsvakt så löser vi det!</p>' . "\n";
		$options['message'] .= '<h3>Hamsterpaj är ingen porrsajt, Goatse är äckligt och hitlerhälsningar olagliga</h3>' . "\n";
		$options['message'] .= '<p>Snälla låt bli porr och goatse här, tänk på att barn besöker den här sajten!</p>' . "\n";
		$options['message'] .= '<em>Brottsbalkens sextonde kapitel, paragraf åtta</em><br />' . "\n";
		$options['message'] .= '<p>8 § Den som i uttalande eller i annat meddelande som sprids hotar eller uttrycker missaktning för folkgrupp eller annan sådan grupp av personer med anspelning på ras, hudfärg, nationellt eller etniskt ursprung, trosbekännelse eller sexuell läggning, döms för hets mot folkgrupp till fängelse i högst två år eller om brottet är ringa, till böter.</p>' . "\n";
		$ret .= ui_server_message($options);
		
		return $ret;
	}
		function photoblog_sort_module($photos, $options = array())	{		if(!isset($options['user']))		{			if(login_checklogin())			{				$options['user'] = $_SESSION['login']['id'];			}			else			{				throw new Exception('No user specified and not logged in.');			}		}				$options['save_path'] = isset($options['save_path']) ? $options['save_path'] : '/fotoblogg/sortera/spara_sortering';		$out = '<div class="photoblog_sort_module">' . "\n";		$out .= 'Save path: ' . $options['save_path'];				$out .= '<ul class="albums">' . "\n";				$categories = photoblog_categories_fetch(array('user' => $options['user']));		foreach($categories as $category)		{			$out .= '<li id="photoblog_sort_album_' . $category['id'] . '"><pre>' . print_r($category, true) . '</pre></li>' . "\n";		}				$out .= '</ul>' . "\n";				$out .= '<ul class="photos">';		foreach($photos as $photo)		{			$out .= '<li id="photoblog_sort_' . $photo['id'] . '"><img src="' . IMAGE_URL . 'photos/mini/' . floor($photo['id'] / 5000) . '/' . $photo['id'] . '.jpg" alt="Dra till en kategori..." /></li>' . "\n";		}		$out .= '</ul>' . "\n";				$out .= '</div>' . "\n";				return $out;	}		function photoblog_photos_fetch($options)	{		if(isset($options['id']))		{			$options['id'] = (is_array($options['id'])) ? $options['id'] : array($options['id']);		}				if(isset($options['category']))		{			$options['category'] = (is_array($options['category'])) ? $options['category'] : array($options['category']);		}				if(isset($options['date']))		{			$options['date'] = (is_array($options['date'])) ? $options['date'] : array($options['date']);		}				$options['order-by'] = (in_array($options['order-by'], array('up.id', 'up.date', 'up.sort_index'))) ? $options['order-by'] : 'up.id';		$options['order-direction'] = (in_array($options['order-direction'], array('ASC', 'DESC'))) ? $options['order-direction'] : 'ASC';		$options['offset'] = (isset($options['offset']) && is_numeric($options['offset'])) ? $options['offset'] : 0;		$options['limit'] = (isset($options['limit']) && is_numeric($options['limit'])) ? $options['limit'] : 9999;
		$options['limit'] = (isset($options['category_limit']) && $options['category_limit']) ? count($options['category']) + 10 : $options['limit'];
			$query = 'SELECT up.*, l.username';		$query .= ' FROM user_photos AS up, login AS l';		$query .= ' WHERE l.id = up.user';
		$query .= (isset($options['include_removed_photos']) && $options['include_removed_photos'] == true) ? '' : ' AND up.deleted = 0';
		$query .= (isset($options['include_removed_users']) && $options['include_removed_users'] == true) ? '' : ' AND l.is_removed = 0';		$query .= (isset($options['id'])) ? ' AND up.id IN("' . implode('", "', $options['id']) . '")' : '';		$query .= (isset($options['user'])) ? ' AND up.user  = "' . $options['user'] . '"' : '';		$query .= (isset($options['month'])) ? ' AND DATE_FORMAT(up.date, "%Y%m") = "' . $options['month'] . '"' : '';		$query .= (isset($options['date'])) ? ' AND up.date IN("' . implode('", "', $options['date']) . '")' : '';		$query .= (isset($options['category'])) ? ' AND up.category IN("' . implode('", "', $options['category']) . '")' : '';		$query .= (isset($options['force_unread_comments']) && $options['force_unread_comments'] == true) ? ' AND up.unread_comments > 0' : '';		$query .= (isset($options['category_limit']) && $options['category_limit']) ? ' AND up.sort_index = 0' : ''; 
		$query .= ' ORDER BY ' . $options['order-by'] . ' ' . $options['order-direction'] . ' LIMIT ' . $options['offset'] . ', ' . $options['limit'];				$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
				$photos = array();		while($data = mysql_fetch_assoc($result))		{
			$data['description'] = (strlen($data['description']) > 0) ? $data['description'] : 'Ingen beskrivning';
			if ( isset($options['index_by_category']) && $options['index_by_category'] )
			{
				$photos[$data['category']][] = $data;
			}
			else
			{				$photos[] = $data;
			}		}				return $photos;	}		function photoblog_photos_update($data, $options = array())	{		if(isset($data['id']))		{			$options['id'] = (isset($options['id']) && is_numeric($options['id'])) ? $options['id'] : $data['id'];			unset($data['id']);		}				if(isset($options['old_data']))		{			foreach($options['old_data'] as $key => $value)			{				if(isset($data[$key]) && $data['key'] == $value)				{					unset($data[$key]);				}			}		}				if(!isset($options['id']) || !is_numeric($options['id']))		{			throw new Exception('Could not find a numeric ID in the $options nor the $data array.');		}				if(!empty($data))		{			$update_data = array();			foreach($data as $key => $value)			{				$update_data[] = $key . ' = "' . $value . '"';				if($key = 'deleted' && $value == 1)				{					//make ghostcomments go away					$query = 'UPDATE user_photos SET unread_comments = 0 WHERE id = '. $options['id'] .' LIMIT 1';					mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);				} 			}						$query = 'UPDATE user_photos SET ' . implode(', ', $update_data);			$query .= ' WHERE id = "' . $options['id'] . '"';			$query .= ' LIMIT 1';// Note: LIMIT 1 is used!						mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);		}				// Add more code for replacing photos etc. later...	}		function photoblog_categories_fetch($options)	{		$query = 'SELECT id, name, handle, photo_count, sorted_photos, user, (SELECT GROUP_CONCAT(id) FROM user_photos WHERE user = upc.user AND deleted = 0 AND category = upc.id LIMIT 9) AS photos';		$query .= ' FROM user_photo_categories AS upc';		$query .= ' WHERE is_removed = 0';		$query .= (isset($options['user'])) ? ' AND user = "' . $options['user'] . '"' : '';		$query .= (isset($options['name'])) ? ' AND name LIKE "' . $options['name'] . '"' : '';		$query .= (isset($options['handle'])) ? ' AND handle = "' . $options['handle'] . '"' : '';		$query .= (isset($options['id'])) ? ' AND id = "' . $options['id'] . '"' : '';		$query .= ' ORDER BY name ASC';		$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);		if(mysql_num_rows($result) == 0 && $options['create_if_not_found'] == true)		{			$query = 'INSERT INTO user_photo_categories (user, name) VALUES("' . $options['user'] . '", "' . $options['name'] . '")';			mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);			if(mysql_insert_id() > 0)			{				$category['id'] = mysql_insert_id();				$category['name'] = stripslashes($options['name']);				$category['user'] = $options['user'];				$category['photo_count'] = 0;				$categories[] = $category;			}			else			{				return false;			}		}		else		{			while($data = mysql_fetch_assoc($result))			{				// If they have no handle, create one				if(strlen($data['handle']) < 1)				{					$query = 'UPDATE user_photo_categories';					$query .= ' SET handle = "' . photoblog_categories_handle($data['name']) . '"';					$query .= ' WHERE id = ' . $data['id'];					$query .= ' LIMIT 1';					mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);					$data['handle'] = photoblog_categories_handle($data['name']);				}								if ( isset($options['id_index']) && $options['id_index'] == true )				{					$categories[$data['id']] = $data;				}				else				{					$categories[] = $data;				}			}		}					return $categories;	}		function photoblog_photos_fetch_sorted($options)	{
		$options['order-by'] = 'up.sort_index';		$photos = photoblog_photos_fetch($options);				$options['id_index'] = true;  		$options['create_if_not_found'] = false;				if ( isset($options['category']) )		{			$options['id'] = $options['category'];		}				$categories = photoblog_categories_fetch($options);				$albums_sorted = array();
		$uncategorized = array();
		
		foreach ( $categories as $category )
		{
			$albums_sorted[$category['id']] = array();
		}
				foreach ( $photos as $photo )		{			$cat = $photo['category'];
				
			if ( ! $cat )
			{
				$uncategorized[] = $photo;
			}
			else
			{				$albums_sorted[$cat][] = $photo;
			}		}
		
		$albums_sorted[0] = $uncategorized;
		krsort($albums_sorted);		 		 	return array($albums_sorted, $categories);	}		function photoblog_categories_new($options)	{		$query = 'INSERT INTO user_photo_categories (user, name, handle) VALUES("' . $options['user'] . '", "' . $options['name'] . '", "' . photoblog_categories_handle($options['name']) . '")';			mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);		return (mysql_insert_id() > 0);	}		function photoblog_categories_edit($options)	{		$query = 'UPDATE user_photo_categories';		$query .= ' SET name = "' . $options['name'] . '"';		$query .= ', handle = "' . photoblog_categories_handle($options['name']) . '"';		$query .= ' WHERE user = ' . (int)$options['user'] . ' AND id = ' . (int)$options['id'];		$query .= ' LIMIT 1';		mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);	}
	
	function photoblog_categories_remove($options)
	{
		$query = 'UPDATE user_photo_categories';
		$query .= ' SET is_removed = 1';
		$query .= sprintf(' WHERE id = %d AND user = %d', $options['id'], $options['user']);
		$query .= ' LIMIT 1';
		mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
		if ( mysql_affected_rows() == 1 )
		{
			$query = 'UPDATE user_photos';
			$query .= ' SET category = 0';
			$query .= ' WHERE category = ' . $options['id'];	
			mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
		}
	}		function photoblog_categories_handle($name)	{		$name = strtolower($name);		$name = str_replace(array('å', 'ä', 'ö', 'Å', 'Ä', 'Ö'), array('a', 'a', 'o', 'a', 'a', 'o'), $name);		$name = preg_replace('/\W|\s/', '-', $name);		$name = preg_replace('/\-\-{1,}/', '-', $name);		$name = trim($name, '-');		return $name;	}		function photoblog_comments_fetch($options = array())	{		$options['order_by_field'] = isset($options['order_by_field']) ? $options['order_by_field'] : 'c.timestamp';		$options['order_by_order'] = (isset($options['order_by_order']) && in_array($options['order_by_order'], array('ASC', 'DESC'))) ? $options['order_by_order'] : 'DESC';				$options['limit_start'] = (isset($options['limit_start']) && is_numeric($options['limit_start'])) ? $options['limit_start'] : 0;		$options['limit_end'] = (isset($options['limit_end']) && is_numeric($options['limit_end'])) ? $options['limit_end'] : 100;				$query  = 'SELECT c.*, l.username, p.unread_comments, p.user';		$query .= ' FROM photoblog_comments AS c, login AS l, user_photos AS p';		$query .= ' WHERE l.id = c.author';		$query .= ' AND l.is_removed = 0';		$query .= ' AND c.is_removed = 0';		$query .= ' AND p.id = c.photo_id';		$query .= (isset($options['photo_id']) && is_numeric($options['photo_id'])) ? ' AND c.photo_id = ' . $options['photo_id'] : '';		$query .= (isset($options['author']) && is_numeric($options['author'])) ? ' AND c.author = ' . $options['author'] : '';		$query .= (isset($options['id']) && is_numeric($options['id'])) ? ' AND c.comment_id = ' . $options['id'] : '';
		$query .= ' ORDER BY ' . $options['order_by_field'] . ' ' . $options['order_by_order'];		$query .= ' LIMIT ' . $options['limit_start'] . ', ' . $options['limit_end'];						$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);				$comments = array();		while($data = mysql_fetch_assoc($result))		{			$comments[] = $data;						if($_SESSION['login']['id'] == $data['user'] && $data['unread_comments'] != 0)			{				$query = 'UPDATE user_photos SET unread_comments = 0 WHERE id = ' . $data['photo_id'] . ' LIMIT 1';				mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);			}		}				return $comments;	}		function photoblog_comments_add($comment)	{		if(!isset($comment['comment']))		{			throw new Exception('Server error: No comment passed to function in options array. Terminating!');// Because "terminating" is such a cool word *NOT*		}		if(empty($comment['comment']))		{			throw new Exception('User error: Comment was empty, aborting.');		}				if(!login_checklogin() && !(isset($comment['author']) && is_numeric($comment['author'])))		{			throw new Exception('Server error: No author specified and user not logged on. Cannot post - aborting.');		}				if(!(isset($comment['photo_id']) && is_numeric($comment['photo_id'])))		{			throw new Exception('Server error: No photo_id specified, aborting.');		}				$query =  'INSERT INTO photoblog_comments(photo_id, author, comment, timestamp)';		$query .= ' VALUES ' . '(' . $comment['photo_id'] . ', ' . $comment['author'] . ' , "' . $comment['comment'] . '", UNIX_TIMESTAMP())';		mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);				$query = 'UPDATE user_photos SET unread_comments = unread_comments + 1 WHERE id = "' . $comment['photo_id'] . '" LIMIT 1';		mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);				return mysql_insert_id();	}		function photoblog_comments_reply($options)	{		if(!isset($options['reply']) || empty($options['reply']))		{			return; // no need to throw an error, but no need to update the reply either.		}		if(!login_checklogin() || !isset($options['author']) || !is_numeric($options['author']))		{			throw new Exception('Author needs to be set and numeric');		}		if (!isset($options['comment_id']) || !is_numeric($options['comment_id']))		{			throw new Exception('Comment_id needs to set and numeric.');		}				$query = 'SELECT p.id, c.comment, c.author, p.description, l.username FROM user_photos AS p, login AS l, photoblog_comments AS c';		$query .= sprintf(' WHERE c.comment_id = %d AND p.user = %d', $options['comment_id'], $options['author']);		$query .= ' AND l.id = p.user AND p.id = c.photo_id';		$query .= ' LIMIT 1';		$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);		$data = mysql_fetch_assoc($result);				if(mysql_num_rows($result) == 0)		{			throw new Exception('Cannot reply to a comment that isn\'t yours');		}				$query = 'UPDATE photoblog_comments';		$query .= sprintf(' SET reply = "%s"', $options['reply']);		$query .= sprintf(' WHERE comment_id = %d', $options['comment_id']);		mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);				$entry['sender'] = $_SESSION['login']['id'];		$message = $_SESSION['login']['username'] . ' svarade precis p&aring; din kommentar till fotot: <br /><a href="/fotoblogg/' . $data['username'] . '#image-' . $data['id'] . '">' . ((strlen($data['description']) > 1) ? $data['description'] : 'namnl&ouml;s') . '</a>' . "\n\n";		$message .= '<strong>Din kommentar:</strong>' . "\n";		$message .= $data['comment'] . "\n\n";		$message .= '<strong>' . $_SESSION['login']['username'] . '\'s svar:</strong>' . "\n";		$message .= $options['reply'] . "\n";		$entry['message'] = mysql_real_escape_string($message);				$entry['recipient'] = $data['author'];		guestbook_insert($entry);		}		function photoblog_comments_list($comments, $options)	{
		global $photoblog_user;		$options['use_container'] = (isset($options['use_container']) ? $options['use_container'] : true);		$out .= ($options['use_container']) ? '<div id="photoblog_comments_list">' . "\n" : '';		$out .= '<ul>' . "\n";		foreach ($comments as $comment)		{			$out .= '<li class="photoblog_comment">' . "\n";								$out .= '<div class="photoblog_comment_userinfo">' . "\n";			$out .= ui_avatar($comment['author']);			$out .= '<a href="/traffa/profile.php?user_id=' . $comment['author'] . '">' . $comment['username'] . '</a>' . "\n";			$out .= '<span>' . $comment['date'] . '</span>' . "\n"; // 31 December			$out .= '</div>' . "\n";									$out .= '<div class="photoblog_comment_bubble_pointer">' . "\n";			$out .= '<div class="photoblog_comment_text">' . "\n";			$out .= '<p>' . nl2br($comment['comment']) . '<br /><a class="report_abuse" href="/hamsterpaj/abuse.php?report_type=photo_comment&reference_id=' . $comment['comment_id'] . '">Rapportera</a></p>' . "\n";			if(strlen($comment['reply']) > 0)			{				$out .= '<div class="photoblog_comment_answer">' . "\n";				$out .= '<p>Svar:' . "\n";				$out .= nl2br($comment['reply']) . '</p>' . "\n";				$out .= '</div>' . "\n";			}
			$out .= '<ul class="photoblog_comment_actions">';			if ( isset($options['my_blog']) && $options['my_blog'] )			{				$out .= '<li><a class="photoblog_comment_reply" href="#reply-' . $comment['comment_id'] . '">Svara</a></li>' . "\n";			}
			if( login_checklogin() && is_privilegied('photoblog_comment_remove') || (isset($options['my_blog']) && $options['my_blog']) )
			{
				$out .= '<li><a class="photoblog_comment_remove" href="/ajax_gateways/photoblog.json.php?action=comments_remove&amp;id=' . $comment['comment_id'] . '">Ta bort kommentar</a></li>';
			}
			$out .= '</ul>';			$out .= '</div' . "\n";			$out .= '</div>' . "\n";			$out .= '<div style="clear: both;"></div>' . "\n";			$out .= '</li>' . "\n";		}				$out .= '</ul>';
		$out .= '<div style="clear: both;"></div>' . "\n";		$out .= ($options['use_container']) ? '</div>' . "\n" : '';					return $out;	}		function photoblog_comments_form($options)	{		$out .= '<div id="photoblog_comments_form">' . "\n";		$out .= '<ul>' . "\n";		$out .= '<li class="photoblog_comment">' . "\n";							$out .= '<div class="photoblog_comment_userinfo">' . "\n";		$avatar_options['show_nothing'] = true;		$out .= ui_avatar($_SESSION['login']['id'], $avatar_options);		$out .= '</div>' . "\n";			$out .= '<div class="photoblog_comment_bubble_pointer">' . "\n";		$out .= '<div class="photoblog_comment_text">' . "\n";		$out .= '<form action="#" method="post">' . "\n";		$out .= '<p>' . "\n";		$out .= '<textarea name="comment">Kommentar</textarea>' . "\n";		$out .= '<br />' . "\n";		$out .= '<input class="submit" type="submit" value="Skicka" />' . "\n";		$out .= '</p>' . "\n";		$out .= '</form>' . "\n";		$out .= '</div>' . "\n";		$out .= '</div>' . "\n";		$out .= '<div style="clear: both;"></div>' . "\n";		$out .= '</li>' . "\n";			$out .= '</ul>';		$out .= '</div>' . "\n";					return $out;	}		function photoblog_comments_remove($options)	{		if($options['image_owner_id'] != $_SESSION['login']['id'] && !is_privilegied('photoblog_comment_remove'))		{			throw new Exception('You need privilegies for this');		}
				if(!isset($options['comment_id']) && !is_numeric($options['comment_id']))		{			throw new Exception('Comment id must be set');		}				$query = 'UPDATE photoblog_comments SET is_removed = 1 WHERE comment_id = ' . $options['comment_id'] . ' LIMIT 1';		mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
	}		function photoblog_dates_fetch($options)	{				$photo_options = array(			'user' => $options['user']		);		$photos = photoblog_photos_fetch($photo_options);		$return = array();		foreach ( $photos as $photo )		{			$time = strtotime($photo['date']);			list($year, $month, $day) = explode('-', date('Y-m-d', $time));			$return[$year][$month][$day] = true;		}		natsort($return);		return $return;	}		function photoblog_access($options)	{		$members_only = $options['members_only'];		$friends_only = $options['friends_only'];		$action = $options['action'];		$owner_id = $options['owner_id'];				switch($action)		{			case visit:				if(userblock_checkblock($owner_id))				{					throw new Exception('Du är blockerad av användaren och kan därför inte besöka dess fotoblogg');				}				if($members_only == 1 && !login_checklogin())				{					throw new Exception('Du måste vara inloggad för att se den här personens fotoblogg. Varför inte besöka profilen istället? <a href="/traffa/profile.php?user_id=' . $owner_id . '">Gå till profil</a>');				}				if($friends_only == 1 && !friends_is_friends(array('user_id' => $_SESSION['login']['id'], 'friend_id' => $owner_id)))				{					throw new Exception('Du måste vara vän med personen för att se dess fotoblogg. Varför inte besöka profilen istället? <a href="/traffa/profile.php?user_id=' . $owner_id . '">Gå till profil</a>');				}			break;						default:			throw new Exception('No action was set');			break;		}	}	function photoblog_calendar($user_id, $month, $year)	{		$format_month = sprintf('%02s', $month);				$options = array('user' => $user_id);		$dates = photoblog_dates_fetch($options);		$used_dates = $dates[$year][$format_month];				$date = mktime(12, 0, 0, $month, 1, $year);		$daysInMonth = date('t', $date);				$dates_first = reset(end($dates));		$dates_last = end(reset($dates));				$prev_month = ($month == 1) ? 12 : $month - 1;		$prev_year = ($prev_month == 12) ? $year -1 : $year;		$prev_has = $used_dates != $dates_first;				$next_month = ($month == 12) ? 1 : $month + 1;		$next_year = ($next_month == 1) ? $year + 1 : $year;		$next_has = $used_dates != $dates_last;				$prev_year_has = isset($dates[$year - 1]);		$next_year_has = isset($dates[$year + 1]);				$prev_year_month = $prev_year_has ? end(array_keys($dates[$year - 1])) : false;		$next_year_month = $next_year_has ? reset(array_keys($dates[$year + 1])) : false;				$offset = date('N', $date);		$rows = 1;		$out .= '<div id="photoblog_calendar_month" class="date-' . $year . $format_month . '">' . "\n";			$out.= $prev_has ? sprintf('<a class="photoblog_calendar_date" href="#month-%s%02s">&laquo;</a>', $prev_year, $prev_month) . "\n" : '';				$out .= '<span>' . date('F', $date) . ', ' . $year . '</span>' . "\n";			$out.= $next_has ? sprintf('<a class="photoblog_calendar_date" href="#month-%s%02s">&raquo;</a>', $next_year, $next_month) . "\n" : '';		$out .= '</div>' . "\n";		$out .= '<table>' . "\n";		$out .= '<tr><th>M</th><th>T</th><th>O</th><th>T</th><th>F</th><th>L</th><th>S</th></tr>' . "\n";		$out .= '<tr>';		for($i = 1; $i < $offset; $i++)		{			$out .= '<td></td>' . "\n";		}		for($day = 1; $day <= $daysInMonth; $day++)		{			$format_day = sprintf('%02s', $day);						if( ($day + $offset - 2) % 7 == 0 && $day != 1)			{				$out .= '</tr><tr>' . "\n";				$rows++;			}			$out .= '<td>' . (isset($used_dates[$format_day]) ? '<a href="#day-' . $year . $format_month . $format_day . '">' . $day . '</a>' : $day) . '</td>' . "\n";		}		while( ($day + $offset) <= $rows * 7)		{			$out .= '<td></td>' . "\n";			$day++;		}		$out .= '</tr>' . "\n";		$out .= '</table>' . "\n";		$out .= '<div id="photoblog_calendar_year">' . "\n";		$out .= '<span class="photoblog_calendar_year_pre">' . (($prev_year_has) ? sprintf('<a class="photoblog_calendar_date" href="#month-%s%s">%s</a>', ($year - 1), $prev_year_month, ($year - 1)) : '') . '</span>';		$out .= '<span class="photoblog_calendar_year_after">' . (($next_year_has) ? sprintf('<a class="photoblog_calendar_date" href="#month-%s%s">%s</a>', ($year + 1), $next_year_month, ($year + 1)) : '')  . '</span>' . "\n";		$out .= '</div>' . "\n";		return $out;	}		function photoblog_sort_save($data)	{		$sort_arrays = array();		$sql_parts = array();		foreach ( $data as $category_id => $photos )		{			if ( ! is_numeric($category_id) )			{				throw new Exception('Erronous category ID:s, aborting.');			}						foreach ( $photos as $index => $photo_id )			{				if ( ! is_numeric($index) || ! is_numeric($photo_id) )				{					throw new Exception('Erronous photo ID:s, aborting.');				}				$sort_arrays[$category_id][$index] = $photo_id;				$sql_parts[$category_id][] = $photo_id;			}		}				foreach ( $sort_arrays as $id => $arr )		{			// $id == 0 == no category, no order?			if ( $id == 0 )			{				continue;			}			
			$i = 0;
			foreach ( $arr as $photo )
			{				$query = 'UPDATE user_photos';
				$query .= ' SET sort_index = ' . $i;
				$query .= ' WHERE id = ' . (int)$photo;
				$query .= ' LIMIT 1';							mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);				
				$i++;
			}
						if ( count($sql_parts[$id]) )			{				$or_ids = implode(' OR id = ', $sql_parts[$id]);				$query = 'UPDATE user_photos';				$query .= ' SET category = ' . $id;				$query .= ' WHERE id = ' . $or_ids;			}						mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);		}	}		function photoblog_viewer($options)	{		$ret = '';				$options['include_dates'] = (isset($options['include_dates'])) ? $options['include_dates'] : true;		$options['load_first'] = (isset($options['load_first']) ? $options['load_first'] : false);
		$options['album_view'] = (bool)$options['album_view'];
				$user_id = $options['user_id'];		$photo_options = array(			'user' => $user_id		);				if ( isset($options['date']) ) $photo_options['month'] = $options['date'];		if ( isset($options['category']) ) $photo_options['category'] = $options['category'];				$date = (isset($options['date'])) ? $options['date'] : date('Ym', time());				define('PHOTOBLOG_CURRENT_YEAR', substr($date, 0, 4));		define('PHOTOBLOG_CURRENT_MONTH', substr($date, 4, 2));		define('PHOTOBLOG_CURRENT_USER', $user_id);				$photos = (! isset($options['photos']) ) ? photoblog_photos_fetch($photo_options) : $options['photos'];		
		$ret .= '<!--[if lte IE 7]>';
		$ret .= '<div class="photoblog_ie_warning">';
		$ret .= '<p>Tjena! Som du kanske har märkt så fungerar fotobloggen inte så överdrivet bra med den versionen av Internet Explorer du kör nu! :( Det beror på att det är en dålig webbläsare för oss som gör hemsidor. Men! Du kan alltid uppgradera till en bättre webbläsare, till exempel <a href="http://www.firefox.com/">Firefox</a>, <a href="http://www.apple.com/safari/">Safari</a>, <a href="http://www.google.com/chrome">Google Chrome</a>, <a href="http://www.opera.com/">Opera</a> eller så kan du <a href="http://www.microsoft.com/windows/internet-explorer/">uppgradera till senaste versionen av Internet Explorer</a>. Om du gör något av detta så vinner du en internet!</p>';
		$ret .= '</div>';
		$ret .= '<![endif]-->';
				$ret .= '<div id="photoblog_thumbs">';			$ret .= '<div id="photoblog_thumbs_container">';				$ret .= '<div id="photoblog_thumbs_inner">';					$ret .= '<dl>';
					
					if ( isset($options['is_album']) && $options['is_album'] )
					{
						list($html, $last_id) = photoblog_viewer_albums_list($options);
						$ret .= $html;
						
						$current_photo = photoblog_photos_fetch(array('id' => $last_id, 'user' => $user_id));
						$current_photo = $current_photo[0];
					}
					else
					{						$ret .= '<dt id="photoblog_prevmonth"><a id="prevmonth" title="F&ouml;reg&aring;ende m&aring;nad" href="#prev-month">F&ouml;reg&aring;ende m&aring;nad</a></dt>';												$is_first = true;						$last_day = array('date' => null, 'formatted' => null);						if ( ! count($photos) )						{							$ret .= '<dt>Här var det tomt...</dt>';						}												$photos_last_index = count($photos) - 1;												foreach ( $photos as $key => $photo )						{							if ( $options['include_dates'] && $last_day['date'] != $photo['date'] )							{								$last_day['date'] = $photo['date'];								$last_day['formatted'] = date('j/n', strtotime($photo['date']));								$ret .= '<dt>' . $last_day['formatted'] . '</dt>';							}							$class = ' class="';							if ( $key == 0 ) $class .= 'first-image ';							if ( $key == $photos_last_index ) $class .= 'last-image ';							$class .= '"';
							$is_current = ($options['load_first']) ? $key == 0: $key == $photos_last_index;							$ret .= '<dd' . $class . '><a title="' . $photo['date'] . '" ' . ($is_current ? 'class="photoblog_active"' : '') . ' href="#image-' . $photo['id'] . '"><img src="' . IMAGE_URL . 'photos/mini/' . floor($photo['id']/5000) . '/' . $photo['id'] . '.jpg" title="' . $photo['username'] . '" /></a></dd>';						}
						
						$current_photo = ($options['load_first']) ? $photos[0] : $photos[$photos_last_index];												$ret .= '<dt id="photoblog_nextmonth"><a id="nextmonth" title="N&auml;sta m&aring;nad" href="#next-month">N&auml;sta m&aring;nad</a></dt>';					}
					$ret .= '</dl>';				$ret .= '</div>';			$ret .= '</div>';		$ret .= '</div>';		$ret .= '<div id="photoblog_image">';		$ret .= '<p><img src="' . IMAGE_URL . 'photos/full/' . floor($current_photo['id'] / 5000) . '/' . $current_photo['id'] . '.jpg" alt="" /></p>';		$ret .= '</div>';		$ret .= '<div id="photoblog_description">';
			if ( $current_photo )
			{
				$ret .= '<div id="photoblog_description_report">';
					$ret .= sprintf('<a class="report_abuse" href="/hamsterpaj/abuse.php?report_type=photo&reference_id=%d">Rapportera bilden</a>', $current_photo['id']);
				$ret .= '</div>';			}
			$ret .= '<div id="photoblog_description_text">';				$ret .= $current_photo['description'];			$ret .= '</div>';
			if ( login_checklogin() && $_SESSION['login']['id'] == $user_id )
			{
				$ret .= '<div id="photoblog_edit">';
				$ret .= '<form action="/ajax_gateways/photoblog.json.php?action=photo_edit" method="post">';
					$ret .= '<a href="#photoblog_edit_actions">Ändra din söta bild?</a>';
					$ret .= '<div style="display: none" id="photoblog_edit_do">';
						$ret .= '<h3>Ändringar <small><a href="/fotoblogg/ordna">(Du kanske vill sortera dina bilder?)</a></small></h3>';
						$ret .= '<input type="hidden" value="' . $current_photo['id'] . '" name="edit_id" />';
						$ret .= '<p id="photoblog_edit_description"><textarea rows="5" cols="50" name="edit_description">' . $current_photo['description'] . '</textarea></p>';
						$ret .= '<div id="photoblog_edit_date"><h4>Datum</h4>';
						$ret .= '<p><input type="text" name="edit_date" value="' . $current_photo['date'] . '" /></p></div>';
						$ret .= '<p id="photoblog_edit_save"><input type="submit" value="Spara dina ändringar" name="edit_submit" /> <input type="submit" name="edit_delete" value="Ta bort bilden" /></p>';
					$ret .= '</div>';
				$ret .= '</form>';
				$ret .= '</div>';
			}		$ret .= '</div>';				$ret .= '<script type="text/javascript">';			$ret .= 'hp.photoblog.current_user = {';				$ret .= 'id: ' . $user_id;				$ret .= ', date: ' . $date;
				$ret .= ', album_view: ' . (int)($options['album_view'] || $options['is_album']);			$ret .= '};';			$ret .= 'hp.photoblog.view.current_id = ' . ($current_photo['id'] ? $current_photo['id'] : '0') . ';';		$ret .= '</script>';		
		if ( $current_photo )
		{
			$comments = photoblog_comments_fetch(array('photo_id' => $current_photo['id']));						$comment_options = array(				'my_blog' => $_SESSION['login']['id'] == $photoblog_user['id'],			);						$ret .= photoblog_comments_form($photo_options);			$ret .= photoblog_comments_list($comments, $comment_options);		}
		else
		{
			$ret .= '<p>Här var det jättetomt! Vänd tillbaka!</p>';
		}
				return $ret;	}	
	function photoblog_viewer_albums($options)
	{
		$options['is_album'] = true;
		return photoblog_viewer($options);
	}
	
	function photoblog_viewer_albums_list($options)
	{
		global $photoblog_user;
		
		$photo_options = array('user' => $options['user_id']);
		$albums = photoblog_categories_fetch($photo_options);
		
		$album_ids = array();
		foreach ( $albums as $album )
		{
			$album_ids[] = $album['id'];
		}
		
		$photo_options['index_by_category'] = true;
		$photo_options['category'] = $album_ids;
		// this should be better
		//$photo_options['category_limit'] = true;
		$photos = photoblog_photos_fetch($photo_options);
		
		// remove albums with no photos
		foreach ( $albums as $key => $album )
		{
			if ( ! count($photos[$album['id']]) )
			{
				unset($albums[$key]);
			}
		}
		
		$ret = '<dt id="photoblog_prevmonth"><a id="prevmonth" title="F&ouml;reg&aring;ende m&aring;nad" href="#prev-month">F&ouml;reg&aring;ende m&aring;nad</a></dt>';
		$last = count($albums) - 1;
		$current = 0;
		foreach ( $albums as $album )
		{
			$class = ' class="';
			if ( $current == 0 )
				$class .= 'first-image';
			if ( $current == $last )
				$class .= 'last-image';
			$class .= '"';
			
			$album_url = '/fotoblogg/' . $photoblog_user['username'] . '/album/' . $album['handle'];
			$ret .= sprintf('<dt><a href="%s">%s</a></dt>', $album_url, $album['name']);
			
			$id = $photos[$album['id']][0]['id'];
			$dir = floor($id / 5000);
			$photo_class = ($current == 0) ? 'class="photoblog_active"' : ''; 
			$ret .= sprintf('<dd %s><a %s href="#image-%d"><img src="%sphotos/mini/%d/%d.jpg" alt="" /></a></dd>', $class, $photo_class, $id, IMAGE_URL, $dir, $id);
			$current++;
		}
		$ret .= '<dt id="photoblog_nextmonth"><a id="nextmonth" title="N&auml;sta m&aring;nad" href="#next-month">N&auml;sta m&aring;nad</a></dt>';
		
		return array($ret, $id);
	}
		function photoblog_forbid_upload($options)	{		if(!is_privilegied('photoblog_upload_forbid'))		{			throw new Exception('You need privilegies for this');		}				if(!isset($options['user_id']) && !is_numeric($options['user_id']))		{			throw new Exception('User id must be set');		}				if(!isset($options['days']) && !is_numeric($options['days']))		{			throw new Exception('number of days must be set');		}				$query = 'UPDATE photoblog_preferences SET upload_forbidden = ' . strtotime('+' . $options['days'] . ' day', time()) . ' WHERE userid = ' . $options['user_id'] . ' LIMIT 1';		mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);				if($_SESSION['login']['id'] == $options['user_id'])		{			$_SESSION['photoblog_preferences']['upload_forbidden'] = strtotime('+' . $options['days'] . ' day', time());		}		else		{			$query = 'SELECT session_id FROM login WHERE id = ' . $options['user_id'] . ' LIMIT 1';			$result = mysql_query($query) or report_sql_error($query);			if(mysql_num_rows($result) == 1)			{				$data = mysql_fetch_assoc($result);				if(strlen($data['session_id']) > 0)				{					$remote_session = session_load($data['session_id']);					$remote_session['photoblog_preferences']['upload_forbidden'] = strtotime('+' . $options['days'] . ' day', time());					session_save($data['session_id'], $remote_session);				}			}		}		log_admin_event('photoblog_upload_forbidden', 'Antal dagar: ' . $options['days'], $_SESSION['login']['id'], $options['user_id'], 0);	}
	function photoblog_migrate_sorting($options)
	{
		/*
			Note that this function does not care whether
			it already has been migrated.
		*/
		
		$user_id = $options['user'];
		if ( ! $user_id )
		{
			throw new Exception('Sorting order can only be migrated on one user at a time. Please specify a user ID.');
		}
		
		$log = '';
		
		$category_options = array('user' => $user_id);
		$categories = photoblog_categories_fetch($category_options);
		
		foreach ( $categories as $category )
		{
			$photos = unserialize($category['sorted_photos']);
			$log .= '<h3>Doing ' . $category['name'] . '</h3>';
			$index = 0;
			foreach ( $photos as $photo )
			{
				$query = 'UPDATE `user_photos`';
				$query .= ' SET `sort_index` = ' . $index;
				$query .= ' WHERE `id` = ' . (int)$photo;
				$query .= ' LIMIT 1';
				mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
				$log .= $query . '<br />';
				$index++;
			}
		}
	}?>