<?php
	require('../include/core/common.php');
	$ui_options['menu_active'] = 'hamsterpaj_om_hamsterpaj';
	$ui_options['title'] = 'Om ungdomssidan Hamsterpaj.net';
	
	require(PATHS_LIBRARIES . 'articles.lib.php');
	$ui_options['stylesheets'][] = 'articles.css';
	
	$article = articles_fetch(array('id' => '150'));
	$out .= render_full_article($article);
	
	ui_top($ui_options);
		echo $out;
	ui_bottom();
?>
