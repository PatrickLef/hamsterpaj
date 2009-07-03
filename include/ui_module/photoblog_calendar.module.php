<?php
    if ( ! defined('PHOTOBLOG_CURRENT_USER') )
    {
	define('PHOTOBLOG_CURRENT_USER', $_SESSION['login']['id']);
	define('PHOTOBLOG_CURRENT_MONTH', date('m'));
	define('PHOTOBLOG_CURRENT_YEAR', date('Y'));
    }
    $options['output'].= photoblog_calendar(PHOTOBLOG_CURRENT_USER, (int)PHOTOBLOG_CURRENT_MONTH, (int)PHOTOBLOG_CURRENT_YEAR); 
?>