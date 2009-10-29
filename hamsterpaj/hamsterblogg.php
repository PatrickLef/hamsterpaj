<?php
	require('../include/core/common.php');
	require(PATHS_LIBRARIES . 'hamsterblog.lib.php');
	require(PATHS_LIBRARIES . 'comments.lib.php');
	$ui_options['stylesheets'][] = 'abuse.css';
	$ui_options['javascripts'][] = 'start.js';
	$ui_options['javascripts'][] = 'comments.js';
	$ui_options['stylesheets'][] = 'comments.css';
	$ui_options['stylesheets'][] = 'groups.css';
	$ui_options['title'] = 'Hamsterpajs ledning skriver för brinnande livet! - Hamsterpaj.net';
	$ui_options['menu_active'] = 'hamsterpaj_Hamsternytt';
	
	switch($_GET['action'])
	{
		case 'compose':
		if (!is_privilegied('hamsterblog_admin'))
		{
			jscript_alert('nehedu, den gick inte');
			jscript_location('/');
			die('Den här sidan kräver privilegiet: hamsterblog_admin');
		}
			$out .= '<h2>Skriv nytt inlägg i Hamsternytten :)</h2>';
			$out .= rounded_corners_top();
			$out .= '<form action="' . $_SERVER['PHP_SELF'] . '?action=insert" method="post">' . "\n";
			$out .= '<label for="header">Rubrik</label><br />' . "\n";
			$out .= '<input type="text" name="header" /><br />' . "\n";
			$out .= '<label for="content">Text:</label><br />' . "\n";
			$out .= '<textarea name="content" style="width: 500px; height: 300px;">' . "\n";
			$out .= '</textarea><br />' . "\n";
			$out .= '<input type="submit" value="Skriv" class="button_60" />' . "\n";
			$out .= '' . "\n";
			$out .= '</form>' . "\n";
			$out .= rounded_corners_bottom();
		break;
		
		case 'insert':
			if (!is_privilegied('hamsterblog_admin'))
			{
				jscript_alert('nehedu, den gick inte');
				jscript_location('/');
				die('Den här sidan kräver privilegiet: hamsterblog_admin');
			}
		
			$query = 'INSERT INTO hamsterblog (timestamp, author, header, content) VALUES (' . time() . ', ' . $_SESSION['login']['id'] . ', "' . $_POST['header'] . '", "' . $_POST['content'] . '")';
			mysql_query($query) or die(report_sql_error($query, __FILE__, __LINE__));
			
			$query = 'SELECT id FROM hamsterblog ORDER BY timestamp DESC LIMIT 1';
			$result = mysql_query($query) or die(report_sql_error($query, __FILE__, __LINE__));
			$data = mysql_fetch_assoc($result);
			
			$blogpost_url = '/hamsterpaj/Hamsternytt.php?action=show&id=' . $data['id'];
			$query = 'INSERT INTO recent_updates (type, timestamp, url, label) VALUES ("blog_post", "' . time() . '", "' . $blogpost_url . '", "' . $_POST['header'] . '")';
			mysql_query($query) or die(report_sql_error($query, __FILE__, __LINE__));
			
			header('Location: ' . $blogpost_url);
		break;
		
		case 'show':
			if(!is_numeric($_GET['id']))
			{
				die('Sluta hacka, Joel kan ju pissa på sig av upphetsning');
			}
			$out .= '<h1>Hamsternytt</h1>' . "\n";
			$sql = 'SELECT d.*, d.author AS user_id, l.username';
			$sql .= ' FROM hamsterblog AS d, login AS l';
			$sql .= ' WHERE l.id = d.author AND d.id = ' . $_GET['id'] . '';
			$sql .= ' ORDER BY d.id DESC';
			$result = mysql_query($sql) or die(mysql_error());
			while ($data = mysql_fetch_assoc($result))
			{
				$entries[] = $data;
			}
			$out .= render_entries($entries, array("enable_comments" => true));
			
		break;
		
		default:
			
			$out .= '<h1>Hamsternytt</h1>' . "\n";
			$sql = 'SELECT d.*, d.author AS user_id, l.username';
			$sql .= ' FROM hamsterblog AS d, login AS l';
			$sql .= ' WHERE l.id = d.author';
			$sql .= ' ORDER BY d.id DESC';
			$result = mysql_query($sql) or die(mysql_error());
			while ($data = mysql_fetch_assoc($result))
			{
				$entries[] = $data;
			}
			$out .= render_entries($entries);
			$out .= (is_privilegied('hamsterblog_admin') && $_GET['action'] !== 'show') ? '<a href="?action=compose">Skriv ett nytt inlägg&raquo;</a>' : '';
		break;
	}
	ui_top($ui_options);
	echo $out;
	ui_bottom();
?>
