<?php

	$threads = cache_load('latest_forum_posts');
	
	$options['output'] .= '<ul>' . "\n";
	foreach($threads as $thread)
	{
		if(forum_category_access($thread['read_threads']))
		{
			$shortened_title = (mb_strlen($thread['title'], 'UTF8') > 22) ? mb_substr($thread['title'], 0, 19, 'UTF8') . '...' : $thread['title'];
			$info = 'I ' . $thread['category_title'] . ' av ' . $thread['username'] . ': ' . $thread['title'];
			$options['output'] .= '<li><span class="ui_module_latest_posts_written">' . date('H:i', $thread['last_post_timestamp']) . '</span> <a title="' . $info . '" href="' . $thread['url'] . '"' . ($thread['read_threads'] == 'joshua' ? 'style="color: orange;"' : '') . '>' . $shortened_title . '</a></li>' . "\n";
		}
	}
	$options['output'] .= '</ul>' . "\n";

?>