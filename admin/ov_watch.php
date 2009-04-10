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
	
	$query = 'SELECT l.id, l.username, ac.avatars_denied, ac.avatars_approved, ac.posts_removed 
						FROM privilegies AS p 
						JOIN login AS l ON l.id = p.user AND l.is_removed = 0
						LEFT JOIN admin_counts AS ac ON ac.user_id = p.user
						WHERE p.privilegie IN ("igotgodmode","avatar_admin","discussion_forum_remove_posts")
						GROUP BY p.user ORDER BY l.username';
	
	$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
	$ovs = mysql_fetch_assoc($result);
	$out .= '<fieldset>' . "\n";
	$out .= '<legend>Ordningsvakter och deras förehavanden</legend>' . "\n";
	$out .= '<table class="form" id="ov_watch_table">' . "\n";
	$out .= '<tr>' . "\n";
	$out .= '<th>Namn</th>' . "\n";
	$out .= '<th>Borttagna inlägg</th>' . "\n";
	$out .= '<th>Nekade visningsbilder</th>' . "\n";
	$out .= '<th>Validerade visningsbilder</th>' . "\n";
	$out .= '<th>Andel nekade visningsbilder</th>' . "\n";
	$out .= '</tr>' . "\n";
	
	while($ov = mysql_fetch_assoc($result))
	{
		$out .= '<tr>' . "\n";
		$out .= '<td class="username"><a href="/profile.php?user_id=' . $ov['id'] . '">' . $ov['username'] . '</a></td>' . "\n";
		$out .= '<td>' . $ov['posts_removed'] . '</td>' . "\n";
		$out .= '<td>' . $ov['avatars_denied'] . '</td>' . "\n";
		$out .= '<td>' . $ov['avatars_approved'] . '</td>' . "\n";
		$out .= '<td>' . round($ov['avatars_denied'] / ($ov['avatars_denied'] + $ov['avatars_approved']) * 100, 2).' % </td>' . "\n";
		$out .= '</tr>' . "\n";
	}
	$out .= '</table>' . "\n";
	$out .= '</fieldset>' . "\n";


ui_top($ui_options);
echo $out;
ui_bottom();

?>
