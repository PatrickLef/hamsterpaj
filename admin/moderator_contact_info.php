<?php
	include '../include/core/common.php';
	$ui_options['title'] = 'Moderatorkontaktinfo - Hamsterpaj.net';
	
	if (!login_checklogin() || !is_privilegied('discussion_forum_remove_posts')) {
		die('Tjockis :(<br /><a href="/">Hejdå</a>');
	}
	
	switch ($_GET['action']) {
		case 'edit_my_info':
			$ui_options['title'] = 'Ändra min kontaktinfo - Moderatorkontaktinfo - Hamsterpaj.net';
			$ui_options['stylesheets'][] = 'forms.css';
			$out .= '<a href="/admin/moderator_contact_info.php">&laquo; Tillbaka</a>' . "\n";
			$out .= '<fieldset>' . "\n";
			$out .= '<legend>Edit maj innfo!</legend>';
			$out .= '<p>Här slänger du in lite info om dig så att vi som sitter på siten kan ringa till dig om du varit stygg, eller om vi helt enkelt blivit dumpade och behöver vilt hamstersekz!</p>' . "\n";
			$sql = 'SELECT * FROM moderator_contact_info WHERE user_id = ' . $_SESSION['login']['id'] . ' LIMIT 1';
			$result = mysql_query($sql) or report_sql_error($sql, __FILE__, __LINE__);
			$data = mysql_fetch_assoc($result);
			$out .= '<form action="/admin/moderator_contact_info.php?action=submit" method="post">';
			$out .= '<table class="form">' . "\n";
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="full_name">För- och efternamn</label></th>';
			$out .= '<td><input type="text" name="full_name" value="' . $data['full_name'] . '" /></td>' . "\n";
			$out .= '</tr><tr>' . "\n";
			$out .= '<th><label for="street_address">Gatuadress</label></Th>' . "\n";
			$out .= '<td><input type="text" name="street_address" value="' . $data['street_address'] . '" /></td>' . "\n";
			$out .= '</tr><tr>' . "\n";
			$out .= '<th><label for="zip_code">Postnummer</label></th>' . "\n";
			$out .= '<td><input type="text" name="zip_code" value="' . $data['zip_code'] . '" /></td>' . "\n";
			$out .= '</tr><tr>' . "\n";
			$out .= '<th><label for="phone_number">Telefonnummer</label></th>' . "\n";
			$out .= '<td><input type="text" name="phone_number" value="' . $data['phone_number'] . '" /></td>' . "\n";
			$out .= '</tr><tr>' . "\n";
			$out .= '<th><label for="email">E-post</label></th>' . "\n";
			$out .= '<td><input type="text" name="email" value="' . $data['email'] . '" /></td>' . "\n";
			$out .= '</tr><tr>' . "\n";
			$out .= '<th><label for="msn_address">MSN-adress</label></th>' . "\n";
			$out .= '<td><input type="text" name="msn_address" value="' . $data['msn_address'] . '" /></td>' . "\n";
			$out .= '</tr><tr>' . "\n";
			$out .= '<th><label for="waist_size">Midjemått<strong>*</strong></label></th>' . "\n";
			$out .= '<td><input type="text" name="waist_size" maxlength="5" value="' . $data['waist_size'] . '" /></td>' . "\n";
			$out .= '</tr><tr>' . "\n";
			$out .= '<th><label for="cup_size">Kupstorlek<strong>*</strong></label></th>' . "\n";
			$out .= '<td>' . "\n";
			$cup_sizes = array('AA', 'A', 'B', 'C', 'D', 'DD', 'E', 'F', 'FF');
			$out .= '<select name="cup_size">' . "\n";
			foreach ($cup_sizes as $cup_size) {
				$out .= '<option value="' . $cup_size . '"' . ($data['cup_size'] == $cup_size ? ' selected="selected"' : NULL) . '>' . $cup_size . '</option>' . "\n";
			}
			$out .= '</select>' . "\n";
			$out .= '</td>' . "\n";
			$out .= '</tr><tr>' . "\n";
			$out .= '<th><label for="visibility_level">Visa info för</label></th>' . "\n";
			$visibility_levels = array(
				'ovs' => 'Ordningsvakter, Admins och Sysops',
				'admins' => 'Admins och Sysops',
				'sysops' => 'Sysops'
			);
			$out .= '<td><select name="visibility_level">' . "\n";
			foreach ($visibility_levels as $db_alias => $spoken_language_alias) {
				$out .= '<option value="' . $db_alias . '"' . ($data['visibility_level'] == $db_alias ? ' selected="selected"' : NULL) . '>' . $spoken_language_alias . '</option>' . "\n";
			}
			$out .= '</select></td>' . "\n";
			$out .= '</tr>' . "\n";
			$out .= '</table>' . "\n";
			$out .= '<p>Fält märkta med <strong style="color: red;">*</strong> är obligatoriska.</p>' . "\n";
			$out .= '<input type="submit" value="Spara" />' . "\n";
			$out .= '</form>';
			$out .= '</fieldset>' . "\n";
			break;
		case 'submit':
			$sql = 'SELECT id FROM moderator_contact_info WHERE user_id = ' . $_SESSION['login']['id'] . ' LIMIT 1';
			$result = mysql_query($sql);
			$result_num_rows = mysql_num_rows($result);
			if ($result_num_rows == 0) {
				$sql = 'INSERT INTO moderator_contact_info SET';
				$sql .= ' user_id = "' . $_SESSION['login']['id'] . '",';
				$sql .= ' visibility_level = "' . (in_array($_POST['visibility_level'], array('sysops', 'admins', 'ovs')) ? $_POST['visibility_level'] : 'ovs') . '",';
				$sql .= ' moderator_class = "' . (is_privilegied('igotgodmode') ? 'sysop' : (is_privilegied('ip_ban_admin') ? 'admin' : 'ov')) . '",';
				$sql .= ' full_name = "' . $_POST['full_name'] . '",';
				$sql .= ' street_address = "' . $_POST['street_address'] . '",';
				$sql .= ' zip_code = "' . $_POST['zip_code'] . '",';
				$sql .= ' phone_number = "' . $_POST['phone_number'] . '",';
				$sql .= ' email = "' . $_POST['email'] . '",';
				$sql .= ' msn_address = "' . $_POST['msn_address'] . '",';
				$sql .= ' waist_size = "' . $_POST['waist_size'] . '",';
				$sql .= ' cup_size = "' . $_POST['cup_size'] . '"';
				mysql_query($sql) or report_sql_error($sql, __FILE__, __LINE__);
			} else {
				$sql = 'UPDATE moderator_contact_info SET';
				$sql .= ' user_id = "' . $_SESSION['login']['id'] . '",';
				$sql .= ' visibility_level = "' . (in_array($_POST['visibility_level'], array('sysops', 'admins', 'ovs')) ? $_POST['visibility_level'] : 'ovs') . '",';
				$sql .= ' full_name = "' . $_POST['full_name'] . '",';
				$sql .= ' street_address = "' . $_POST['street_address'] . '",';
				$sql .= ' zip_code = "' . $_POST['zip_code'] . '",';
				$sql .= ' phone_number = "' . $_POST['phone_number'] . '",';
				$sql .= ' email = "' . $_POST['email'] . '",';
				$sql .= ' msn_address = "' . $_POST['msn_address'] . '",';
				$sql .= ' waist_size = "' . $_POST['waist_size'] . '",';
				$sql .= ' cup_size = "' . $_POST['cup_size'] . '"';
				$sql .= ' WHERE user_id = ' . $_SESSION['login']['id'];
				mysql_query($sql) or report_sql_error($sql, __FILE__, __LINE__);
			}
			jscript_alert('Ändrat, fixat och donat ;)');
			jscript_location('/admin/moderator_contact_info.php');
			break;
		default:
			$sql = 'SELECT id FROM moderator_contact_info WHERE user_id = ' . $_SESSION['login']['id'] . ' LIMIT 1';
			$result = mysql_query($sql) or report_sql_error($sql, __FILE__, __LINE__);
			if (mysql_num_rows($result) == 0) {
				$out .= '<p class="error">';
				$out .= 'Du verkar inte ha lagt in din information i databasen, var vänlig gör det <a href="/admin/moderator_contact_info.php?action=edit_my_info">här</a>!';
				$out .= '</p>';
			} else {
				$out .= '<a href="/admin/moderator_contact_info.php?action=edit_my_info">Ändra min info &raquo;</a>' . "\n";
			}
			$out .= '<h2>Moderatorkontaktinfo</h2>';
			$out .= '<table style="width: 100%">';
			$out .= '<tr>' . "\n";
			$out .= '<th>Nick</th>' . "\n";
			$out .= '<th>För- och efternamn</th>' . "\n";
			$out .= '<th>Address</th>' . "\n";
			$out .= '<th>Postnr</th>' . "\n";
			$out .= '<th>Tfnnummer</th>' . "\n";
			$out .= '<th>E-post</th>' . "\n";
			$out .= '<th>MSN</th>' . "\n";
			$out .= '<th>Midjem.</th>' . "\n";
			$out .= '<th>Kupa</th>' . "\n";
			$out .= '</tr>' . "\n";
			$sql = 'SELECT mci.*, l.username FROM moderator_contact_info mci, login l WHERE l.id = mci.user_id' . (is_privilegied('igotgodmode') ? NULL : (is_privilegied('ip_ban_admin') ? ' AND visibility_level != "sysops"' : ' AND visibility_level != "sysops" AND visibility_level != "admins"')) . ' ORDER BY mci.moderator_class DESC, l.username ASC';
			$result = mysql_query($sql) or report_sql_error($sql, __FILE__, __LINE__);
			$moderator_class_aliases = array(
				'sysop' => 'Sysops',
				'admin' => 'Administratörer',
				'ov' => 'Ordningsvakter'
			);
			while ($data = mysql_fetch_assoc($result)) {
				if ($moderator_class_current != $data['moderator_class']) {
					$moderator_class_current = $data['moderator_class'];
					$out .= '<tr>';
					$out .= '<th colspan="9" style="text-align: left;">';
					$out .= $moderator_class_aliases[$data['moderator_class']];
					$out .= '</th>';
					$out .= '</tr>';
				}
				$out .= '<tr>';
				$out .= '<td style="border-right: thin solid #aaa;">' . $data['username'] . '</td>';
				$out .= '<td style="background: rgb(253, 253, 253);">' . $data['full_name'] . '</td>';
				$out .= '<td style="background: rgb(251, 251, 251);">' . $data['street_address'] . '</td>';
				$out .= '<td style="background: rgb(249, 249, 249);">' . $data['zip_code'] . '</td>';
				$out .= '<td style="background: rgb(247, 247, 247);">' . $data['phone_number'] . '</td>';
				$out .= '<td style="background: rgb(245, 245, 245);">' . $data['email'] . '</td>';
				$out .= '<td style="background: rgb(243, 243, 243);">' . $data['msn_address'] . '</td>';
				$out .= '<td style="background: rgb(241, 241, 241);">' . $data['waist_size'] . '</td>';
				$out .= '<td style="background: rgb(239, 239, 239);">' . $data['cup_size'] . '</td>';
				$out .= '</tr>';
			}
			$out .= '</table>';
			$out .= '';
			$out .= '';
			break;
	}
	
	echo ui_top($ui_options);
	echo $out;
	echo ui_bottom();
?>