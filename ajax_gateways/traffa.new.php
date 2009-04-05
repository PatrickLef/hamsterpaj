<?php
	require_once('../include/core/common.php');
	require_once(PATHS_LIBRARIES . 'flags.lib.php');
	require_once(PATHS_LIBRARIES . 'guestbook.lib.php');
	
	//Gather data
	$birthdate = login_checklogin() ? split('-', $_SESSION['userinfo']['birthday']) : array(1992);
	$maxboxes = 9;
	$amount = is_numeric($_GET['amount']) ? $_GET['amount'] : 0;
	$existingboxes = split(',', $_GET['existingboxes']);
	$agemin = date('Y') - $_GET['agemin'];
	$agemax = date('Y') - $_GET['agemax'];
	$flags_list = user_flags_fetch();
	
	//Remove empty values
	foreach($existingboxes as $key => $value) {
		if($value == '') {
			unset($existingboxes[$key]);
		}
	}
	$existingboxes = array_values($existingboxes); 
	
	//Query
	$genderquery = $_GET['gender'] != 'all' ? (' AND u.gender = \'' . $_GET['gender'] . '\'') : '';
	$agemarginquery = ' AND YEAR(u.birthday) >= ' . $agemax . ' AND YEAR(u.birthday) <= ' . $agemin;
	foreach($existingboxes as $boxid)
	{
		if($boxid != '' && is_numeric($boxid)) $exceptions .= (' AND l.id != ' . $boxid);
	}
	$query = 'SELECT l.username, u.gender, u.userid, u.birthday, GROUP_CONCAT(uf.flag) AS flag, z.spot';
	$query .= ' FROM login AS l, userinfo AS u, user_flags AS uf, zip_codes AS z';
	$query .= ' WHERE l.id = u.userid AND uf.user = l.id AND l.username != "Borttagen" AND u.image > 0 AND z.zip_code = u.zip_code' . $agemarginquery . $exceptions . $genderquery;
	$query .= ' GROUP BY uf.user ORDER BY RAND() LIMIT ' . $amount;
	$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
	
	//Loop through collected users
	while($data = mysql_fetch_assoc($result))
	{
		//Gather data
		$userid = $data['userid'];
		$birthsplit = split('-', $data['birthday']);
		$flagsplit = split(',', $data['flag']);
		$flagsplit = user_flags_front($flagsplit);
		$limited_flags = $flagsplit;
		array_splice($limited_flags, 5);
		
		//Main card
		$o .= '<li id="' . $userid . '" class="userbox ' . ($data['gender'] == 'm' ? 'male' : ($data['gender'] == 'f' ? 'female' : 'female')) . ' birth' . $birthsplit[0] . ' ' . ' newbox" style="display: none;" onClick="displayuser(' . $userid . ', \'' . $data['gender'] . '\');">' . "\n";
		$o .= '<p class="username">' . $data['username'] . ' ' . ($data['gender'] == 'm' ? 'P' : 'F') . date_get_age($data['birthday']) . '</p><br />' . "\n";
		$o .= '<img src="' . IMAGE_URL . 'images/users/thumb/' . $userid . '.jpg" height="90" style="float: left; margin: 5px;" />';
		$o .= '<div class="flagcontainer">';
		foreach($limited_flags as $flag)
		{
			$o .= $flag != 0 ? ('<img src="' . IMAGE_URL . 'user_flags/' . $flags_list[$flag]['handle'] . '.png" /><br />') : '';
		}
		$o .= '</div>' . "\n";
		$o .= '</li>' . "\n";
		
		//Popup information
		$o .= '<div class="displayuser" id="wnd_' . $userid . '">';
		$genders = array('m' => 'kille', 'f' => 'tjej');
		$o .= '<p><a href="/traffa/profile.php?user_id=' . $userid . '">' . $data['username'] . '</a>';
		$o .= isset($genders[$data['gender']]) ? (', ' . $genders[$data['gender']]) : '';
		$o .= $data['birthday'] != '0000-00-00' ? (', ' . date_get_age($data['birthday']) . ' år') : '';
		$o .= !empty($data['spot']) ? (', ' . $data['spot']) : '';
		$o .= '</p>' . "\n";
		$o .= '<img src="' . IMAGE_URL . 'images/users/full/' . $userid . '.jpg" height="265" style="float: left; margin: 5px;" />' . "\n";
		$o .= '<div class="wndflagcontainer">';
		foreach($flagsplit as $flag)
		{
			$o .= $flag != 0 ? ('<img class="bigflag" src="' . IMAGE_URL . 'user_flags/' . $flags_list[$flag]['handle'] . '.png" alt="' . $flags_list[$flag]['title'] . '" title="' . $flags_list[$flag]['title'] . '" />') : '';
		}
		$o .= '</div>' . "\n";
		$o .= '<form name="kottmarknad_card" id="kottmarknad_card" class="gb_form">' . "\n";
		$o .= '<input type="hidden" name="recipient" value="' . $userid . '" />' . "\n";
		$o .= '<textarea name="message" id="kottmarknad_card_message"></textarea>' . "\n";
		$o .= '<input type="button" class="button_60" value="Skicka" onClick="sendmessage();" />' . "\n";
		$o .= '</form>' . "\n";
		
		$o .= '<a style="display: block; position: absolute; bottom: 10px; right: 10px;" href="/traffa/profile.php?user_id=' . $userid . '">Besök profil &raquo;</a>';
		$o .= '</div>' . "\n";
	}
	
	echo $o;
?>