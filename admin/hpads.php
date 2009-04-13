<?php
	require('../include/core/common.php');
	$ui_options['stylesheets'][] = 'hpads_admin.css';
	$ui_options['javascripts'][] = 'hpads_admin.js';
	$ui_options['title'] = 'Startsidan pÃƒÂ¥ Hamsterpaj';

	if(!is_privilegied('hp_ad_admin'))
	{
		jscript_alert('En skyddad sida, du är inte välkommen');
		jscript_location('/');
		die('Du måste ha privilegie för att nå den här sidan');
	}

	ui_top($ui_options);
	
	
	$_POST['html'] = html_entity_decode($_POST['html']);
	if($_POST['action'] == 'create')
	{
		$uniqid = md5(rand() . uniqid() . microtime());
		
		$query = 'INSERT INTO hp_ads (name, area, credits, expire, html, probability, uniqid) VALUES("';
		$query .= $_POST['name'] . '", "' . $_POST['area'] . '", "' . $_POST['credits'] . '", "' . strtotime($_POST['expire']) . '", "' . $_POST['html'];
		$query .= '", "' . $_POST['probability'] . '", "' . $uniqid . '")';
		
		mysql_query($query);
	}
	if($_POST['action'] == 'update')
	{
		$query = 'UPDATE hp_ads SET name = "' . $_POST['name'] . '", area = "' . $_POST['area'] . '", credits = "' . $_POST['credits'] . '"';
		$query .= ', expire = "' . strtotime($_POST['expire']) . '", html = "' . $_POST['html'] . '", probability = "' . $_POST['probability'] . '"';
		$query .= ' WHERE id = "' . $_POST['id'] . '" LIMIT 1';
		
		mysql_query($query);
	}

	echo hpads_form();

	ui_bottom();
	?>
