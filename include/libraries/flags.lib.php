<?php
function user_flags_fetch()
{
	$options['query'] = 'SELECT id, handle, title FROM user_flags_list ORDER BY id';
	$options['max_delay'] = 600;
	$result = query_cache($options);
	$flags_list = array();
	foreach($result as $flag)
	{
		$flags_list[$flag['id']]['handle'] = $flag['handle'];
		$flags_list[$flag['id']]['title'] = $flag['title'];
	}
	return $flags_list;
}

function user_flags_front($userflags = array())
{
	//Sort the flags in the following order:
	
	//singel, homosexual, grown_up, ...
	$flags_order = array(139, 95, 76);
	
	//country, ...
	$flags_order = array_merge($flags_order, range(55, 75), array(86), range(88, 94), range(96, 98));
	
	//religion, ...
	$flags_order = array_merge($flags_order, range(19, 23), range(25, 27), array(105));
	
	//sober, vegetarian, emo, fjortis, metal_rock, hip_hop, horseback_riding, weight_lifting, gamer, musician
	$flags_order = array_merge($flags_order, array(50, 28, 102, 33, 35, 34, 46, 118, 83, 128));
	
	$flags_order = array_reverse($flags_order);
	foreach($flags_order as $flag)
	{
		if(in_array($flag, $userflags))
		{
			array_unshift($userflags, $flag);
		}
	}
	return array_unique($userflags);
}
?>