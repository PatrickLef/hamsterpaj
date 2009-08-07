<?php
    $ui_options['ui_modules_hide'] = false;
    $ui_options['javascripts'][] = 'jquery.uploadify.js';
    
    if(isset($_POST['PHPSESSID']) && preg_match('/^([a-z0-9]+)$/', $_POST['PHPSESSID']) && $_POST['PHPSESSID'] != session_id())
    {
        session_destroy();
        session_id($_POST['PHPSESSID']);
        session_start();
    }
    
    if(!login_checklogin())
    {
	throw new Exception('Du måste vara inloggad för att kunna ladda upp bilder.');
    }
    
    if($_SESSION['photoblog_preferences']['upload_forbidden'] > time())
    {
	$out .= '<h2>Du är avstängd från att ladda upp bilder fram tills: ' . date('Y:m:d H:i:s', $_SESSION['photoblog_preferences']['upload_forbidden']) . '</h2>' . "\n";
	break;
    }
    
    switch(isset($uri_parts[3]) ? $uri_parts[3] : '')
    {
	case 'ladda_upp':
	    if ( ! isset($_FILES['image']) )
	    {
		throw new Exception('Ingen bild följde med uppladdningen!');
	    }
	    
	    $is_ajax = isset($uri_parts[4]) && $uri_parts[4] == 'ajax';
	    
	    // Date
	    if ( isset($_POST['use_exif_date']) && $_POST['use_exif_data'] )
	    {
		$exif_date = exif_read_data ( $_FILES['image']['tmp_name'] ,'IFD0' ,0 );
		$exif_date = strtotime($exif_date['DateTime']);
		
		if ( ! $exif_date )
		{
		    $date = date('Y-m-d');
		}
	    }
	    else
	    {
		if ( checkdate($_POST['month'], $_POST['day'], $_POST['year']) )
		{
		    $date = sprintf('%04d-%02d-%02d', $_POST['year'], $_POST['month'], $_POST['day']);
		}
		else
		{
		    $date = date('Y-m-d');
		}
	    }
	    
	    // Album
	    $category = $_POST['album'];
	    
	    // Description
	    $description = $_POST['description'];
	    
	    $options = array();
	    $options['file_temp_path'] = $_FILES['image']['tmp_name'];
	    $options['user'] = $_SESSION['login']['id'];
	    $options['category'] = $category;
	    $options['description'] = $description;
	    $options['date'] = $date;
	    
	    $_SESSION['photoblog_upload_id'] = photoblog_upload_upload($options);
	    
	    if ( $is_ajax )
	    {
		$options = array();
		$options['type'] = 'notification';
		$options['title'] = 'Vi äger!';
		$options['message'] = 'Bilden (eller bilderna) har laddats upp och allt är frid och fröjd! Seså! Gå ut och lek i fotobloggen nu.';
		$mess = ui_server_message($options);
		
		unset($_SESSION['photoblog_upload_id']);
		
		echo $mess;
		die();
	    }
	    else
	    {
		header('location: /fotoblogg/ladda_upp_enkel');
		exit;
	    }
	break;
	
	default:
	    
	    $out .= <<<EOD
		<h1>Ladda upp bilder!</h1>
		
		<p><label>Välj dina bilder här (välj fler med control eller shift) <input type="file" id="images" name="images" /></label></p>

		<div id="photoblog_queue"></div>
		<div style="clear: both;"></div>
		
		<div style="display: none" class="photoblog_photo_info photoblog_submit_info"><input value="Ladda upp!" type="submit" id="photoblog_upload_submit" /></div>
EOD;
	    $content .= '<h2 id="photoblog_upload_simple">En enklare uppladdning</h2>';
	    //$content .= '<p><a href="/fotoblogg/ladda_upp">Tillbaka till den andra, fräsigare(, buggigare) fotoblogguppladdningen.</a> Alla med en webbläsare som inte är textbaserad bör kunna använda den här uppladdaren.</p>';
	    
	    if ( isset($_SESSION['photoblog_upload_id']) )
	    {
		$options = array();
		$options['type'] = 'notification';
		$options['title'] = 'Vi äger!';
		$options['message'] = 'Bilden laddades upp och allt är frid och fröjd. Ladda upp en till här nedan, eller så kan du ';
		$options['message'] .= sprintf('<a href="/fotoblogg/%s/%d">klicka på den här länken och komma direkt till din rykande färska bild.</a>', $_SESSION['login']['username'], $_SESSION['photoblog_upload_id']);
		$out .= ui_server_message($options);
		
		unset($_SESSION['photoblog_upload_id']);
	    }
	    
	    $options = array('user' => $_SESSION['login']['id']);
	    $albums = photoblog_categories_fetch($options);
	    
	    $albums_jsarray = '[';
	    $albums_array = array();
	    foreach ( $albums as $album )
	    {
		$albums_array[] = '"' . $album['name'] . '"';
	    }
	    $albums_jsarray .= implode(', ', $albums_array) . ']';
	    
	    $out .= '<script type="text/javascript">
		hp.photoblog.categories = ' . $albums_jsarray . '
	    </script>';
	    
	    $options = '';
	    foreach ( $albums as $album )
	    {
		if ( $album['name'] == 'Övriga Bilder' )
		    $selected = 'selected="selected"';
		else
		    $selected = '';
		
		$options .= sprintf('<option %s value="%s">%s</option>', $selected, $album['name'], $album['name']);
	    }
	    
	    if ( ! strlen($options) )
	    {
		$options .= '<option value="">Övriga bilder</option>';
	    }
	    
	    $options .= '<option class="photoblog_upload_new_album">Nytt album</option>';
	    
	    $content .= '<div id="uploadify_queue"></div>';
	    $content .= '
	    <div class="photoblog_upload_container">
		<div>
		    <form action="/fotoblogg/ladda_upp_enkel/ladda_upp" method="post" enctype="multipart/form-data">
			<h3>Bild</h3>
			<p><label><strong>Fil: </strong> <input type="file" id="image" name="image" /></label> <label>Album: <select class="photoblog_upload_album" name="album">' . $options . '</select></label></p>
			<p><label><strong>Beskrivning: </strong><br />
			    <textarea rows="5" cols="50" name="description"></textarea></p>
			<p><label>Hämta datumet automagiskt: <input type="checkbox" checked="checked" name="use_exif_date" /></label> <label><span class="photoblog_upload_or">Eller...</span> År: <input type="text" maxlength="4" name="year" value="' . date('Y') . '" /></label> <label>Månad: <input type="text" name="month" maxlength="2" value="' . date('m') . '" /></label> <label>Dag: <input type="text" name="day" maxlength="2" value="' . date('d') . '" /></label></p>
			<p><input type="submit" value="Ladda upp skönheten!" /></p>
		    </form>
		</div>
	    </div>';
	    
	    $out .= photoblog_upload_messages($content);
	break;
    }
