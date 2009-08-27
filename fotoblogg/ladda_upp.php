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
	}
?>