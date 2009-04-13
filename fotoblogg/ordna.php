<?php
    $ui_options['javascripts'][] = 'sorter.js';

    $out .= '<div id="photoblog_sort">';
    $out .= '<h1>Sortera dina bilder genom att dra och släppa bilderna dit du vill ha dem</h1>';
    
    $out .= '<h2>Skapa album</h2>';
    $out .= '<form action="/ajax_gateways/photoblog_ordna.php" method="get">';
    	$out .= '<input type="hidden" name="action" value="album_new" /><p><label><strng>Namn:</strong> <input type="text" name="name" /> <input type="submit" value="Skapa" /></p>';
    $out .= '</form>';
    
    $options = array(
    	'user' => $_SESSION['login']['id']
    );
    
    list($albums_sorted, $categories) = photoblog_photos_fetch_sorted($options);
    
    $albums = array();
    
   	foreach ( $albums_sorted as $album_id => $album )
   	{
   		foreach ( $album as $photo )
   		{
   			$albums[$album_id][] = '<li id="photo_' . $photo['id'] . '"><img src="' . IMAGE_URL . 'photos/mini/' . floor($photo['id']/5000) . '/' . $photo['id'] . '.jpg" title="' . $photo['username'] . '" /><br /><input type="checkbox" name="foo" value="' . $photo['id'] . '" /></li>';
   		}
   	}
    
    foreach ( $albums as $id => $album )
    {
    	$out .= '<form class="photoblog_album_edit" action="/ajax_gateways/photoblog_ordna.php" method="get">';
        $out .= '<h2><span>' . (! strlen($categories[$id]['name']) ? 'Inget namn' : $categories[$id]['name']) . '</span> <input type="text" name="name" value="' . $categories[$id]['name'] . '" /> <input type="submit" value="Spara" /></h2>';
        $out .= '<input type="hidden" name="action" value="album_edit" /><input type="hidden" name="id" value="' . $id . '" />';
        $out .= '</form>';
        $out .= '<ul id="album_' . $id . '">';
        $out .= implode('', $album);
        $out .= '</ul>';
    }
    $out .= '<p><a class="photoblog_sort_save" href="#">Spara ändringar</a> | <a class="photoblog_sort_remove" href="#">Ta bort markerade</a></p>';
    $out .= '</div>';
?>