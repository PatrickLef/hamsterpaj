<?php
	require('../include/core/common.php');

	$ui_options['menu_path'] = array('admin', 'ov_watch');
	$ui_options['stylesheets'][] = 'forms.css';
	$ui_options['stylesheets'][] = 'ov_watch.css';

	if(!is_privilegied('use_statistic_tools'))
	{
		jscript_alert('Denna sida kräver privilegiet: use_statistic_tools');
		jscript_location('/');
		die('inte för dig...');
	}
	
	// action types
	$action_types = array(
										'post removed' 	=> array('name' => 'Borttagna inlägg', 'privilegie' => 'discussion_forum_remove_posts'),
										'avatar validated'	=> array('name' => 'Validerade avatarer', 'privilegie' => 'avatar_admin'),
										'ghost'	=> array('name' => 'Ghostade användare', 'privilegie' => 'use_ghosting_tools'),
										'guestbook_hack'	=> array('name' => 'GB-hackade användare', 'privilegie' => 'use_ghosting_tools'),
										'ip banned'	=> array('name' => 'IP-bannade användare', 'privilegie' => 'ip_ban_admin'),
										'report event'	=> array('name' => 'Hanterade rapporter', 'privilegie' => 'abuse_report_handler'),
										'user blocked image upload'	=> array('name' => 'Avataruppladdningsblockeringar', 'privilegie' => 'avatar_admin'),
										'user kicked'	=> array('name' => 'Utloggningar av användare', 'privilegie' => 'logout_user'),
										'user recovered'	=> array('name' => 'Återskapande av användare', 'privilegie' => 'user_recover'),
										'user warned'	=> array('name' => 'Varnade användare', 'privilegie' => 'warnings_admin'),
										'user removed'	=> array('name' => 'Borttagna användare', 'privilegie' => 'user_remove')
									);

	$out .= '<fieldset>' . "\n";
	$out .= '<legend>Visa statistik från</legend>' . "\n";
	$out .= '<form method="get">' . "\n";
	$out .= '<table class="form">' . "\n";
	$out .= '<tr>' . "\n";
	$out .= '<td><label for="action">Typ av åtgärd:</label></td>' . "\n";
	$out .= '<td>' . "\n";
	$out .= '<select name="action">' . "\n";
	$out .= '<option value="">Alla</option>' . "\n";
	foreach($action_types AS  $action => $option)
	{
	$out .= '<option value="' . $action . '">' . $option['name'] . '</option>' . "\n";
	}
	$out .= '</select>' . "\n";
	$out .= '</td>' . "\n";
	$out .= '<td><label for="days">Antal dagar:</label></td>' . "\n";
	$out .= '<td><input type="text" name="days" /></td>' . "\n";
	$out .= '</tr>' . "\n";
	$out .= '</table>' . "\n";
	$out .= '<input type="submit" value="Filtrera" />' . "\n";
	$out .= '</form>' . "\n";
	$out .= '</fieldset>' . "\n";
	
	if(isset($_GET['days']) && is_numeric($_GET['days']))
	{
		$days = $_GET['days'];
	}
	if(isset($_GET['action']))
	{
		$action = $action_types[$_GET['action']];
	}
	else
	{
		unset($action);
	}

	$query = 'SELECT l.id, l.username, FROM_UNIXTIME(MIN(ae.timestamp)) AS first_action, COUNT(ae.event) AS count_actions, TIMESTAMPDIFF(DAY,FROM_UNIXTIME(MIN(ae.timestamp)), NOW()) AS total_days, ROUND(COUNT(ae.event) / TIMESTAMPDIFF(DAY,FROM_UNIXTIME(MIN(ae.timestamp)), NOW()), 0) AS average';
	$query .= ' FROM privilegies AS p ';
	$query .= ' JOIN login AS l ON l.id = p.user AND l.is_removed = 0';
	$query .= ' LEFT JOIN admin_event AS ae ON ae.admin_id = p.user ';
	$query .= !empty($_GET['action']) ? ' AND ae.event = "' . $_GET['action'] . '"' : '';
	$query .= isset($days) ? ' AND TIMESTAMPDIFF(DAY,FROM_UNIXTIME(ae.timestamp), NOW()) <= ' . $days  : '';
	$query .= isset($action['privilegie']) ? ' WHERE p.privilegie IN ("igotgodmode","' . $action['privilegie'] . '")' : '';
	$query .= ' GROUP BY p.user ';
	$query .= ' ORDER BY COUNT(ae.event) DESC';
	$query .= ' LIMIT 100';
	echo $query;
	
	$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
	$ovs = mysql_fetch_assoc($result);
	
	$out .= '<fieldset>' . "\n";
	$out .= '<legend>Ordningsvakter och deras förehavanden</legend>' . "\n";
	$out .= '<p>Här visas statistik baserat på ' . (isset($_GET['action']) ? $action['name'] : 'Allt') . ' under ' . (isset($days) ? $days : 'alla') . ' dagar</p>' . "\n";
	$out .= '<table class="form" id="ov_watch_table">' . "\n";
	$out .= '<tr>' . "\n";
	$out .= '<th>Namn</th>' . "\n";
	$out .= '<th>Första åtgärden</th>' . "\n";
	$out .= '<th>Antal åtgärder</th>' . "\n";
	$out .= '<th>Antal dagar</th>' . "\n";
	$out .= '<th>Snitt</th>' . "\n";
	$out .= '</tr>' . "\n";
	
	while($ov = mysql_fetch_assoc($result))
	{
		$out .= '<tr>' . "\n";
		$out .= '<td class="username"><a href="/traffa/profile.php?user_id=' . $ov['id'] . '">' . $ov['username'] . '</a></td>' . "\n";
		$out .= '<td>' . $ov['first_action'] . '</td>' . "\n";
		$out .= '<td>' . $ov['count_actions'] . '</td>' . "\n";
		$out .= '<td>' . $ov['total_days'] . '</td>' . "\n";
		$out .= '<td>' . $ov['average'] . '</td>' . "\n";
		$out .= '</tr>' . "\n";
	}
	$out .= '</table>' . "\n";
	$out .= '</fieldset>' . "\n";


ui_top($ui_options);
echo $out;
ui_bottom();

?>
