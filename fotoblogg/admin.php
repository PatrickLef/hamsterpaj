<?php
    $ui_options['ui_modules_hide'] = false;
    
    if (
	!is_privilegied('photoblog_photo_remove')
	|| ! is_privilegied('photoblog_upload_forbid') )
    {
	throw new Exception('Endast rätt människor har tillträde här');
    }
    
    if ( isset($uri_parts[4]) )
    {
	switch ( $uri_parts[4] )
	{
	    case 'ban':
		if ( ! isset($_POST['days']) || ! is_numeric($_POST['days']) )
		{
		    throw new Exception('Dagar kan ju endast beskrivas med siffror');
		}
		
		$options = array('user_id' => $photoblog_user['id'], 'days' => $_POST['days']);
		photoblog_forbid_upload($options);
		
		$out .= '<h2>Bannat! Hihihahahahohoho</h2>';
	    break;
	}
    }
    
    $out .= '<p>Endast admins här pl0x.</p>';
    $out .= '<h1>Let\'s administrera this sucker!</h1>';
    
    $photo_options = array('user' => $photoblog_user['id'], 'index_by_id' => true);
    $all_photos = photoblog_photos_fetch($photo_options);
    
    $photo_options['include_removed_photos'] = true;
    $deleted_photos = photoblog_photos_fetch($photo_options);
    
    if ( is_privilegied('photoblog_upload_forbid') )
    {
	$out .= '<form action="/fotoblogg/' . $photoblog_user['username'] . '/admin/ban" method="post">';
	$out .= '<ol>';
	$out .= '<li><strong>Blockera</strong> den här personen från att <strong>ladda upp bilder</strong> <input type="text" style="width: 40px" name="days" value="7" /> dagar <input type="submit" value="Banna!" /><br />Tips: för att ta bort en ban kan du skriva in -100 eller nåt.</li>';
	$out .= '</ol>';
	$out .= '</form>';
    }
    
    function photoblogadmin_photo($photo)
    {
	global $photoblog_user;
	
	$thumb_url = photoblog_photo_thumb_url($photo['id']);
	$full_url = photoblog_photo_full_url($photo['id']);
	
	$info = '<div class="photoblog_info">';
	$info .= '<form method="post" action="/ajax_gateways/photoblog.json.php?action=photo_edit">';
	$info .= '<input type="hidden" value="' . $photo['id'] . '" name="edit_id" />';
	$info .= '<p><label>Beskrivning:<br /><textarea name="edit_description" rows="2" cols="50">' . $photo['description'] . '</textarea></label></p>';
	$info .= '<p class="date"><label>Datum: <input type="text" name="edit_date" value="' . $photo['date'] . '" /></label> ';
	$info .= '<input type="submit" value="Spara" /></p>';
	$info .= '<p class="remove"><a href="/ajax_gateways/photoblog.json.php?action=photos_remove&photos=' . $photo['id'] . '&redirect">Ta bort bilden</a>';
	$info .= ' / <a href="/ajax_gateways/photoblog.json.php?action=photo_putback&photo=' . $photo['id'] . '&redirect">Lägg tillbaka</a></p>';
	$info .= '</form>';
	$info .= '</div>';
	
	$output = sprintf('<li><a href="/fotoblogg/%s/%s">#%d</a> <span class="info_toggle"></span><br /><a href="%s"><img src="%s" alt="*bilden*" /></a>%s</li>', $photoblog_user['username'], $photo['id'], $photo['id'], $full_url, $thumb_url, $info);
	return $output;
    }
    
    if ( is_privilegied('photoblog_photo_remove') )
    {
	$out .= '<div id="photoblog_admin_all">';
	if ( ! count($all_photos))
	{
	    $out .= '<p>Den här användaren har inga bilder.</p>';
	}
	else
	{
	    $out .= '<p>Den här användaren har totalt ' . count($all_photos) . ' ickeborttagna bilder i databasen.</p>';
	    $out .= '<ul>';
	    foreach ( $all_photos as $key => $photo )
	    {
		$out .= photoblogadmin_photo($photo);
		unset($deleted_photos[$key]);
	    }
	    $out .= '</ul>';
	}
	
	$out .= '<h1>Borttagna bilder</h1>';
	
	if ( ! count($deleted_photos) )
	{
	    $out .= '<p>Den här användaren inte tagit bort några bilder!<p>';
	}
	else
	{
	    $out .= '<p>Den här användaren har tagit bort ' . count($deleted_photos) . ' bilder.</p>';
	    $out .= '<ul>';
	    foreach ( $deleted_photos as $photo )
	    {
		$out .= photoblogadmin_photo($photo);
	    }
	    $out .= '</ul>';
	}
	
	$out .= '</div>'; // photoblog_admin_all
    }