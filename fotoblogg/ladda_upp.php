<?php
	$ui_options['ui_modules_hide'] = false;
		
	if(!login_checklogin())
	{
		throw new Exception('Du måste vara inloggad för att kunna ladda upp bilder.');
	}
	
	switch(isset($uri_parts[3]) ? $uri_parts[3] : '')
	{
		default:
			if($_SESSION['photoblog_preferences']['upload_forbidden'] > time())
			{
				$out .= '<h1>Du är avstängd från att ladda upp bilder fram tills: ' . date('Y:m:d H:i:s', $_SESSION['photoblog_preferences']['upload_forbidden']) . '</h1>' . "\n";
				break;
			}
			$ui_options['stylesheets'][] = 'datepicker.css';
			
			$out .= '<h1>Välkommen att ladda upp bilder i din fotoblogg</h1>' . "\n";
			
			$options['type'] = 'notification';
			$options['title'] = '';
			$options['message'] = '<p>Du kan ladda upp flera bilder samtidigt genom att markera flera när du väljer bilder.<br /> Men tänk på att det tar en del tid att ladda upp bilderna, och du bör kanske inte ladda upp så många i taget.</p><p><a href="/fotoblogg/ladda_upp_enkel"><strong>Fungerar inte bilduppladdningen? Klicka här för att använda en enklare version av uppladdningen</strong></a></p>';
			$out .= ui_server_message($options);
			
			
			$content .= '<div id="photoblog_upload_wrapper">' . "\n";
			$content .= '<div id="photoblog_upload_upload_flash_objectarea">&nbsp;</div>' . "\n";
			$content .= '<script type="text/javascript">
				var so = new SWFObject("/swfs/photoblog_upload.swf", "photoblog_upload_flash_upload", "635", "200", "8", "#ffffff");
				so.addParam("wmode", "transparent");
				so.addParam("flashVars", "PHPSESSID=" + document.cookie.split("PHPSESSID=")[1].split("&")[0]);
				so.write("photoblog_upload_upload_flash_objectarea");
			</script>' . "\n";
			$content .= '<br style="clear: both" />';
			$content .= '</div>' . "\n";
			$content .= '<form action="/fotoblogg/ladda_upp/beskrivningar" method="post">' . "\n";
				$content .= '<div id="photoblog_photo_properties_container">&nbsp;</div>' . "\n";
				$content .= '<input type="submit" value="Vidare &raquo;" class="button_80" id="photoblog_photo_properties_save" />' . "\n";
			$content .= '</form>' . "\n";

			$out .= photoblog_upload_messages($content);
		break;
		case 'beskrivningar':
			$photo_ids = array();
			foreach($_POST as $key => $value)
			{
				if(preg_match('/^photoblog_photo_properties_(\d+)_description$/', $key, $matches))
				{
					$matches['photo_id'] = $matches[1];
					if(isset($_POST['photoblog_photo_properties_' . $matches['photo_id'] . '_autodate']))
					{
						$data['date'] = ('Y-m-d');
					}
					elseif(isset($_POST['photoblog_photo_properties_' . $matches['photo_id'] . '_date']) && strtolower($_POST['photoblog_photo_properties_' . $matches['photo_id'] . '_date']) == 'idag')
					{
						$data['date'] = date('Y-m-d');
					}
					elseif(isset($_POST['photoblog_photo_properties_' . $matches['photo_id'] . '_date']) && preg_match('/^20(\d{2})-(\d{1,2})-(\d{1,2})$/', $_POST['photoblog_photo_properties_' . $matches['photo_id'] . '_date']))
					{
						$data['date'] = $_POST['photoblog_photo_properties_' . $matches['photo_id'] . '_date'];
					}
					else
					{
						throw new Exception('Invalid date!');
					}
					
					if( is_numeric($matches['photo_id']) )
					{
						$data['id'] = $matches['photo_id'];
					}
					else
					{
						throw new Exception('No or invalid photo id!');
					}
					
					$data['description'] = $_POST['photoblog_photo_properties_' . $matches['photo_id'] . '_description'];
					
					photoblog_photos_update($data);
					
					$photo_ids[] = $data['id'];
				}
			}
			
			if(false && empty($photo_ids))
			{
				$out .= 'Något gick lite snett, vi hittade inga av dina foton du just laddade upp.';
				throw new Exception('No photos found when counting uploaded ids before fetching them.');
			}
			else
			{
				$out .= '<h2>Nu har dina foton laddats upp!</h2>';
				$out .= '<p><a href="/fotoblogg/ordna">Ordna dina foton</a>, <a href="/fotoblogg/ladda_upp">Ladda upp fler</a> eller <a href="/fotoblogg/">Spana in dina skönheterna</a>.</p>';
				/*$photos = photoblog_photos_fetch(array('id' => $photo_ids), array('save_path' => '/fotoblogg/ladda_upp/spara_sortering'));
				$out .= photoblog_sort_module($photos);*/
			}
		break;
	}
?>