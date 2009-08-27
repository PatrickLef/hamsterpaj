<?php
	/*$ui_options['ui_modules']['photoblog_user'] = 'User';
	$ui_options['ui_modules']['photoblog_calendar'] = 'Kalender';
	$ui_options['ui_modules']['photoblog_albums'] = 'Album';*/
	$ui_options['ui_modules_hide'] = false;
	
	$ui_options['stylesheets'][] = 'colorpicker.css';
	$ui_options['stylesheets'][] = 'colorpicker_layout.css';
	$ui_options['stylesheets'][] = 'forms.css';
		
	$ui_options['javascripts'][] = 'colorpicker.js';
	$ui_options['javascripts'][] = 'photoblog_preferences.js';
	$ui_options['javascripts'][] = 'colorpicker_eye.js';
	$ui_options['javascripts'][] = 'colorpicker_layout.js';
	$ui_options['javascripts'][] = 'colorpicker_utils.js';
				
	$photoblog_preferences = new photoblog_preferences();
	$my_photoblog_preferences = $photoblog_preferences->fetch();
	
	$out .= '<form id="photoblog_preferences_form" action="/fotoblogg/instaellningar/post_settings.php" method="post">' . "\n";
	$out .= '<fieldset>' . "\n";
	$out .= '<legend>Inställningar</legend>' . "\n";
		$out .= '<table class="form" id="photoblog_preferences_color_table">' . "\n";
			$checked_blog = ($my_photoblog_preferences['album_or_blog'] == 'blog') ? ' checked="checked"' : '';
			$checked_album = ($my_photoblog_preferences['album_or_blog'] == 'album') ? ' checked="checked"' : '';
			$out .= <<<EOD
				<tr>
					<th>Min fotoblogg är...</th>
					<td>
						<table style="float: right; width: 150px;">
							<tr>
								<td style="padding: 0; border: none"><label>En blogg<br /><input name="photoblog_preferences_type" value="blog" {$checked_blog} type="radio" /></label></td>
								<td style="padding: 0; border: none"><label>Ett album<br /><input name="photoblog_preferences_type" value="album" {$checked_album} type="radio" /></label></td>
							</tr>
						</table>
					</td>
				</tr>
EOD;
			/* Members only */
			$out .= '<tr>' . "\n";
				$out .= '<th>' . "\n";
					$out .= '<label for="photoblog_members_only">Visa endast för inloggade medlemmar</label>' . "\n";
				$out .= '</th>' . "\n";
				$out .= '<td style="text-align: right">' . "\n";
					$out .= '<input type="checkbox" name="photoblog_preferences_members_only" id="photoblog_preferences_members_only"';
					$out .= ($my_photoblog_preferences['members_only'] == 1) ? ' checked="checked"' : '';
					$out .= ' value="1" />' . "\n";
				$out .= '</td>' . "\n";
			$out .= '</tr>' . "\n";
			/* Copy-protection */
			$out .= '<tr style="display: none">' . "\n";
				$out .= '<th>' . "\n";
					$out .= '<label for="photoblog_copy_protection">Kopieringskydda mina bilder</label>' . "\n";
					$out .= '<br /><span>(Fast det finns inget sätt att kopieringskydda bilder på webben, och vi tänker inte heller låta F11 tro det. Men vi har kvar kryssrutan och hoppas på någon slags "placebo-effekt".)</span>' . "\n";
				$out .= '</th>' . "\n";
				$out .= '<td style="text-align: right">' . "\n";
					$out .= '<input type="checkbox" name="photoblog_preferences_copy_protection" id="photoblog_preferences_copy_protectiony"';
					$out .= ($my_photoblog_preferences['copy_protection'] == 1) ? ' checked="checked"' : '';
					$out .= ' value="1" />' . "\n";
				$out .= '</td>' . "\n";
			$out .= '</tr>' . "\n";
			/* Detail color */
			$out .= '<tr>' . "\n";
				$out .= '<th>' . "\n";
					$out .= '<label for="photoblog_preferences_color_detail">Detaljfärg</label>' . "\n";
				$out .= '</th>' . "\n";
				$out .= '<td>' . "\n";
					$out .= '<div style="float: right" class="colorSelector" id="photoblog_preferences_color_detail_div"><div style="background-color: ' . $my_photoblog_preferences['color_detail'] . ';"/></div></div>' . "\n";
						$out .= '<input type="hidden" name="photoblog_preferences_color_detail" id="photoblog_preferences_color_detail" value="' . $my_photoblog_preferences['color_detail'] . '" />' . "\n";
				$out .= '</td>' . "\n";
			$out .= '</tr>' . "\n";
			/* background color */
			$out .= '<tr>' . "\n";
				$out .= '<th>' . "\n";
					$out .= '<label for="photoblog_preferences_color_main">Bakgrund på element</label>' . "\n";
				$out .= '</th>' . "\n";
				$out .= '<td>' . "\n";
					$out .= '<div style="float: right" class="colorSelector" id="photoblog_preferences_color_main_div"><div style="background-color: ' . $my_photoblog_preferences['color_main'] . ';"/></div></div>' . "\n";
					$out .= '<input type="hidden" name="photoblog_preferences_color_main" id="photoblog_preferences_color_main" value="' . $my_photoblog_preferences['color_main'] . '" />' . "\n";
				$out .= '</td>' . "\n";
			$out .= '</tr>' . "\n";
	$out .= '</table>' . "\n";
	$out .= '<input type="submit" value="Spara inställningar" />' . "\n";
	$out .= '</fieldset>' . "\n";
	$out .= '</form>' . "\n";
	switch ($uri_parts[3])
	{
		case 'post_settings.php':
		//$out .= preint_r($_POST);
		$options = array(
			'color_main' => strtoupper($_POST['photoblog_preferences_color_main']),
			'color_detail' => strtoupper($_POST['photoblog_preferences_color_detail']),
			'members_only' => $_POST['photoblog_preferences_members_only'],
			'friends_only' => $_POST['photoblog_preferences_friends_only'],
			'copy_protection' => $_POST['photoblog_preferences_copy_protection'],
			'album_or_blog' => (in_array($_POST['photoblog_preferences_type'], array('blog', 'album')) ? $_POST['photoblog_preferences_type'] : 'blog')
		);
		$options_check_strlen_len_6_array = array(
			'color_main',
			'color_detail'
		);
		foreach ($options_check_strlen_len_6_array as $key)
		{
			if (strlen($options[$key]) != 6)
			{
				throw new Exception('Fel i postfunktionen... klaga på <a href="/joar/gb">Joar</a>');
			}
		}
		$photoblog_preferences->save($options);
		header('Location: /fotoblogg/instaellningar/');
		break;
	}
?>