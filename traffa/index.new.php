<?php
	require('../include/core/common.php');
	
	$ui_options['title'] = 'Köttmarknaden (beta)';
	$ui_options['stylesheets'][] = 'traffa.new.css';
	$ui_options['javascripts'][] = 'traffa.new.js';
	
	$birthsplit = split('-', $_SESSION['userinfo']['birthday']);
	
	$o .= rounded_corners_top(array('color' => 'white'));
	
	$o .= '<form name="userinfo">' . "\n";
	$o .= '<input type="hidden" name="userinfo_username" value="' . $_SESSION['login']['username'] . '" />' . "\n";
	$o .= '<input type="hidden" name="userinfo_gender" value="' . $_SESSION['userinfo']['gender'] . '" />' . "\n";
	$o .= '<input type="hidden" name="userinfo_birth" value="' . $birthsplit[0] . '" />' . "\n";
	$o .= '<input type="hidden" name="userinfo_avatar" value="' . $_SESSION['userinfo']['image'] . '" />' . "\n";
	$o .= '<input type="hidden" name="userinfo_environment" value="' . ENVIRONMENT . '" />' . "\n";
	$o .= '</form>' . "\n";
	$o .= '<div class="setupmaskot">' . "\n";
	$o .= '<input class="optionbutton optionbutton_hover" id="nav_back" type="button" title="Bakåt" value="&laquo; Bak" />' . "\n";
	$o .= '<input class="optionbutton optionbutton_hover" id="nav_adv" type="button" title="Avancerat" value="Avanc." />' . "\n";
	$o .= '<input class="optionbutton optionbutton_hover" id="nav_forward" type="button" title="Framåt" value="Fram &raquo;" />' . "\n";
	$o .= '</div>' . "\n";
	$o .= '<div id="setupcontainer">' . "\n";
	$o .= '<div class="top"></div>' . "\n";
	$o .= '<div class="middle"></div>' . "\n";
	$o .= '<div class="bottom"></div>' . "\n";
	$o .= '<div class="setupquestion"></div>' . "\n";
	$o .= '</div>' . "\n";
	$o .= '<br style="clear: both;" />' . "\n";
	$o .= '<input class="reloadbutton" type="button" value="Ladda om" />' . "\n";
	$o .= '<div id="search_desc"></div>' . "\n";
	$o .= '<ul class="userboxcontainer"></ul>' . "\n";
	$o .= '<br style="clear: both;" />' . "\n";

	$o .= rounded_corners_bottom();
	
	ui_top($ui_options);
	echo $o;
	ui_bottom();
?>