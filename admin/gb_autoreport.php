<?php
  try
	{
		require('../include/core/common.php');
		require(PATHS_INCLUDE . 'libraries/admin.lib.php');
		
		if(!is_privilegied('gb_autoreport'))
		{
			jscript_alert('Denna sida kräver privilegiet: gb_autoreport');
			jscript_location('/');
			die('inte för dig...');
		}
		
		$ui_options['stylesheets'][] = 'gb_autoreport.css';
		$ui_options['stylesheets'][] = 'forms.css';
		$ui_options['stylesheets'][] = 'rounded_corners_tabs.css';
		$ui_options['javascripts'][] = 'gb_autoreport.js';
		$ui_options['title'] = 'Automatiskt rapporterade gästboksinlägg - Hamsterpaj.net';
		
		$action = $_GET['action'];
		
		switch($action)
		{
			case 'string_add':
				if(!is_numeric($_POST['priority']))
				{
					throw new Exception('Priority not numeric');
				}
				
				if(strlen($_POST['string']) < 3)
				{
					throw new Exception('String must be longer than 3 letters');
				}

				$query = 'INSERT INTO gb_autoreport_strings SET creator = ' . $_SESSION['login']['id'] . ', string = "' . $_POST['string'] . '", information = "' . $_POST['information'] . '", priority = ' . $_POST['priority'];
				mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
				header('Location: /admin/gb_autoreport.php?action=strings');
			break;
				
			case 'string_remove':
				if(!is_numeric($_GET['id']))
				{
					throw new Exception('ID not numeric');
				}
				$query = 'DELETE FROM gb_autoreport_strings WHERE id = ' . $_GET['id'] . ' LIMIT 1';
				mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
				header('Location: /admin/gb_autoreport.php?action=strings');
			break;
			
			case 'strings':
				$rounded_corners_tabs_options['tabs'][] = array('href' => '/admin/gb_autoreport.php', 'label' => 'Rapporter', 'current' => false);
				$rounded_corners_tabs_options['tabs'][] = array('href' => '/admin/gb_autoreport.php?action=strings', 'label' => 'Strängar som ska rapporteras', 'current' => true);
			
				$out .= '<fieldset>' . "\n";
				$out .= '<legend>Lägg till strängar</legend>' . "\n";
				$out .= '<form method="post" action="/admin/gb_autoreport.php?action=string_add">' . "\n";
				$out .= '<table class="form">' . "\n";
				$out .= '<tr>' . "\n";
				$out .= '<td><label for="string">Sträng:</label></td>' . "\n";
				$out .= '<td><input type="text" name="string" /></td>' . "\n";
				$out .= '<td><label for="priority">Prioritet:</label></td>' . "\n";
				$out .= '<td>' . "\n";
				$out .= '<select name="priority">' . "\n";
				$out .= '<option value="3">Hög</option>' . "\n";
				$out .= '<option value="2">Normal</option>' . "\n";
				$out .= '<option value="1">Låg</option>' . "\n";
				$out .= '</select>' . "\n";
				$out .= '</td>' . "\n";
				$out .= '</tr>' . "\n";
				$out .= '<tr>' . "\n";
				$out .= '<td><label for="information">Info</label></td>' . "\n";
				$out .= '<td colspan="3">' . "\n";
				$out .= '<textarea style="width: 468px;" name="information"></textarea>' . "\n";
				$out .= '</td>' . "\n";
				$out .= '</tr>' . "\n";
				$out .= '</table>' . "\n";
				$out .= '<input type="submit" value="Spara" />' . "\n";
				$out .= '</form>' . "\n";
				$out .= '</fieldset>' . "\n";
				
				$query = 'SELECT gars.string, gars.priority, gars.id, gars.information, gars.creator, l.username, gars.priority, gars.id';
				$query .= ' FROM gb_autoreport_strings AS gars';
				$query .= ' JOIN login AS l ON l.id = gars.creator AND l.is_removed = 0';
				$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);

				$out .= '<h2>Här visas en lista på alla strängar som automagiskt ska rapporteras</h2>' . "\n";
				$out .= '<table class="form" id="gb_autoreport_strings">' . "\n";
				$out .= '<tr>' . "\n";
				$out .= '<th>Prioritet</th>' . "\n";
				$out .= '<th>Sträng</th>' . "\n";
				$out .= '<th>Information</th>' . "\n";
				$out .= '<th>Skapare</th>' . "\n";
				$out .= '<th>Radera</th>' . "\n";
				$out .= '</tr>' . "\n";
				
				while($string = mysql_fetch_assoc($result))
				{
					$out .= '<tr>' . "\n";
					$out .= '<td>' . str_replace(array(3, 2, 1), array('Hög', 'Normal', 'Låg'), $string['priority']) . '</td>' . "\n";
					$out .= '<td>' . $string['string'] . '</td>' . "\n";
					$out .= '<td>' . $string['information'] . '</td>' . "\n";
					$out .= '<td class="username"><a href="/traffa/profile.php?user_id=' . $string['creator'] . '">' . $string['username'] . '</a></td>' . "\n";
					$out .= '<td><a href="/admin/gb_autoreport.php?action=string_remove&id=' . $string['id'] . '">Radera</a></td>' . "\n";
					$out .= '</tr>' . "\n";
				}
				$out .= '</table>' . "\n";
			break;
			
			default:
				$rounded_corners_tabs_options['tabs'][] = array('href' => '/admin/gb_autoreport.php', 'label' => 'Rapporter', 'current' => true);
				$rounded_corners_tabs_options['tabs'][] = array('href' => '/admin/gb_autoreport.php?action=strings', 'label' => 'Strängar som ska rapporteras', 'current' => false);
				
				$out .= '<br />' . "\n";
				$out .= '<h2>Här visas en lista på alla strängar som automatiskt har flaggats av systemeet</h2>' . "\n";
				$out .= '<table class="form" id="gb_autoreport_posts">' . "\n";
				$out .= '<tr>' . "\n";
				$out .= '<th>GB-hack diskussion</th>' . "\n";
				$out .= '<th>GB-hack användare</th>' . "\n";
				$out .= '<th>Avsändare</th>' . "\n";
				$out .= '<th>Mottagare</th>' . "\n";
				$out .= '<th>Validerad</th>' . "\n";
				$out .= '</tr>' . "\n";
				
				$query = 'SELECT l.username, gars.priority, gb.sender, gb.message, garp.checked, gb.recipient, l.id AS user_id, garp.id, ls.id AS recipient_id, ls.username AS recipient_username';
				$query .= ' FROM gb_autoreport_posts AS garp';
				$query .= ' JOIN gb_autoreport_strings AS gars ON gars.id = garp.string_id ';
				$query .= ' JOIN traffa_guestbooks AS gb ON gb.id = garp.gb_id';
				$query .= ' JOIN login AS l ON l.id = gb.sender AND l.is_removed = 0';
				$query .= ' JOIN login AS ls ON ls.id = gb.recipient';
				$query .= ' WHERE garp.checked = 0';
				$query .= ' GROUP BY garp.id ';
				$query .= ' ORDER BY gars.priority DESC';
				$query .= ' LIMIT 100';
				$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);

				while($report = mysql_fetch_assoc($result))
				{
					$out .= '<tr' . ($report['id']&1 ? ' class="gb_autoreport_post_odd"' : '') . ' id="gb_autoreport_post_message_' . $report['id'] . '">' . "\n";
					$out .= '<th>Inlägg:</th>' . "\n";
					$out .= '<td colspan="6" class="gb_autoreport_message">' . nl2br($report['message']) . '</td>' . "\n";
					$out .= '</tr>' . "\n";
					$out .= '<tr id="gb_autoreport_post_info_' . $report['id'] . '" class="gb_autoreport_post_info' . ($report['id']&1 ? ' gb_autoreport_post_odd' : '') . '">' . "\n";
					$out .= '<td>' . (is_privilegied('use_ghosting_tools') ? '<a href="/admin/guestbook_hack.php?id_1=' . $report['sender'] . '&id_2=' . $report['recipient'] . '">Läs diskussion</a>' : 'Kräver gb-hack') . '</td>' . "\n";
					$out .= '<td>' . (is_privilegied('use_ghosting_tools') ? '<a href="/admin/guestbook_hack.php?id_1=' . $report['sender'] . '">Läs alla inlägg</a>' : 'Kräver gb-hack') . '</td>' . "\n";
					$out .= '<td class="username"><a href="/traffa/profile.php?user_id=' . $report['user_id'] . '">' . $report['username'] . '</a></td>' . "\n";
					$out .= '<td class="username"><a href="/traffa/profile.php?user_id=' . $report['recipient_id'] . '">' . $report['recipient_username'] . '</a></td>' . "\n";
					$out .= '<td><a id="' . $report['id'] . '" class="gb_autoreport_validate" href="/ajax_gateways/gb_autoreport.php?action=post_validate&id=' . $report['id'] . '&return=true" style="color: green;">Validera</a></td>' . "\n";
					$out .= '</tr>' . "\n";
				}
				$out .= '</table>' . "\n";
			break;
		}
		
		ui_top($ui_options);
		echo rounded_corners_tabs_top($rounded_corners_tabs_options, true);
		echo $out;
		echo rounded_corners_tabs_bottom($rounded_corners_tabs_options, true);
		ui_bottom();
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