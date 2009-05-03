<?php
try
{
	require('../include/core/common.php');
	$ui_options['title'] = 'Hamsterpajs diskussionsforum administration';
	$ui_options['stylesheets'][] = 'forms.css';
	$ui_options['stylesheets'][] = 'rounded_corners_tabs.css';

	if(!is_privilegied('forum_admin'))
	{
		die('SkräddarN säger NEJ!!!');
	}
	
	function forum_admin_category_recursive_list($categories, $depth)
	{
		foreach($categories AS $category)
		{
			$indent = '';
			for($i = 0; $i < $depth; $i++)
			{
				$indent .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			$style = ($depth == 0) ? ' style="font-weight: bold;"' : '';
			$outout .= '<tr>' . "\n";
			$output .= '<td' . $style . '>' . $indent . $category['title'] . '</td>' . "\n";
			$output .= '<td style="text-align: center;"><a href="?action=edit_category&id=' . $category['id'] . '">Ändra</a></td>' . "\n";
			$output .= '<td style="text-align: center;"><a href="?post=category_remove&id=' . $category['id'] . '">Ta bort</a></td>' . "\n";
			$output .= '</tr>' . "\n";

			$output .= forum_admin_category_recursive_list($category['children'], $depth+1);
		}				
		return $output;
	}
	
	function forum_admin_category_recursive_option_list($categories, $depth, $selected)
	{
		foreach($categories AS $category)
		{
			$indent = '';
			for($i = 0; $i < $depth; $i++)
			{
				$indent .= '&nbsp;&nbsp;';
			}
			$style = ($depth == 0) ? ' style="font-weight: bold;"' : '';
			$output .= '<option ' . ($category['id'] == $selected ? 'selected="selected"' : '') . ' value="' . $category['id'] . '"' . $style . '>' . $indent . $category['title'] . '</option>' . "\n";

			$output .= forum_admin_category_recursive_option_list($category['children'], $depth+1, $selected);
		}				
		return $output;
	}

	
	$get_fields = array('title', 'read_threads','create_post','create_thread','priority','description','quality_level');
	$ints = array('smallint','int','tinyint','bigint');
	
	$query = 'SHOW COLUMNS FROM public_forums';
	$result = mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
	while($data = mysql_fetch_assoc($result))
	{
		$type = explode('(', $data['Type']);
		$data['type'] = $type[0];
		if(in_array($data['Field'], $get_fields))
		{
			$fields[$data['Field']] = $data;
			if($data['type'] == 'enum')
			{
				$types = substr($data['Type'], 6, -2);
				$fields[$data['Field']]['values'] = explode("','", $types);
			}
		}
	}
	
	
	switch($_GET['post'])
	{
		case 'category_update':
			$numeric_fields = array('priority','quality_level','allow_anonymous');
			$allow_empty = array('parent');
			foreach($_POST as $input => $data)
			{
				if((empty($data) && $data != 0) || in_array($input, $allow_empty))
				{
					throw new Exception($input . ' is empty');
				}
				if(in_array($input, $numeric_fields) && !is_numeric($data))
				{
					throw new Exception($input . ' must be numeric');
				}
			}
			if(!isset($_GET['id']) && !is_numeric($_GET['id']))
			{
				throw new Exception('Inget ID kom med');
			}

			$query = 'UPDATE public_forums SET';
			$first = true;
			foreach($_POST as $column => $value)
			{
				if($first == true)
				{
					$query .= ' ' . $column . ' = "' . $value . '"';
				}
				else
				{
					$query .= ', ' . $column . ' = "' . $value . '"';
				}
				$first = false;
			}
			$query .= ' WHERE id= ' . $_GET['id'] . ' LIMIT 1';

			mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
			header('Location: /site_admin/discussion_forum_admin.php?action=edit_category&id=' . $_GET['id']);
		break;
			
		case 'category_remove':
			if(!isset($_GET['id']) && !is_numeric($_GET['id']))
			{
				throw new Exception('Inget ID kom med');
			}
			
			$query = 'UPDATE public_forums SET removed = 1 WHERE id = ' . $_GET['id'] . ' LIMIT 1';
			mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
			header('Location: /site_admin/discussion_forum_admin.php?action=categories_list');
		break;
		
		case 'category_add':
			
		break;
	}

	$rounded_corners_tabs_options['tabs'][] = array('href' => '?categories_list', 'label' => 'Kategorier');
	//$rounded_corners_tabs_options['tabs'][] = array('href' => '?category_add', 'label' => 'Skapa kategori');

	switch($_GET['action'])
	{
		case 'edit_category':
			$categories = discussion_forum_categories_fetch(array('disable_query_caching' => true, 'parent' => 0));
		
			$category_tree = discussion_forum_categories_fetch(array('id' => $_GET['id'], 'disable_query_caching' => true));
			$category = array_pop($category_tree);
			$out .= '<fieldset>' . "\n";
			$out .= '<legend>Ändra kategorin: ' . $category['title'] . '</legend>' . "\n";
			$out .= '<p><a href="?action=list_categories">&laquo; Tillbaka till kategorierna</a></p>' . "\n";
			$out .= '<form action="?post=category_update&id=' . $category['id'] . '" method="post">';
			$out .= '<table class="form">' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="title">Titel <strong>*</strong></label></th>' . "\n";
			$out .= '<td><input type="text" name="title" value="' . $category['title'] . '" /></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="parent">Förälder <strong>*</strong></label></th>' . "\n";
			$out .= '<td><select name="parent">' . "\n";
			$out .= '<option value="">Ingen</option>' . "\n";
			$out .= forum_admin_category_recursive_option_list($categories, 0, $category['parent']);
			$out .= '</select></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="priority">Prioritet <strong>*</strong></label></th>' . "\n";
			$out .= '<td><input type="text" style="width: 40px;" name="priority" value="' . $category['priority'] . '" /></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="description">Beskrivning <strong>*</strong></label></th>' . "\n";
			$out .= '<td><textarea name="description" style="width:450px; height:130px;">' . $category['description'] . '</textarea></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="quality_level">Quality Level <strong>*</strong></label></th>' . "\n";
			$out .= '<td><select name="quality_level">' . "\n";
			$out .= '<option ' . ($category['quality_level'] == '1' ? 'selected="selected"' : '') . ' value="1">1</option>' . "\n";
			$out .= '<option ' . ($category['quality_level'] == '2' ? 'selected="selected"' : '') . ' value="2">2</option>' . "\n";
			$out .= '<option ' . ($category['quality_level'] == '3' ? 'selected="selected"' : '') . ' value="3">3</option>' . "\n";
			$out .= '<option ' . ($category['quality_level'] == '4' ? 'selected="selected"' : '') . ' value="4">4</option>' . "\n";
			$out .= '<option ' . ($category['quality_level'] == '5' ? 'selected="selected"' : '') . ' value="5">5</option>' . "\n";
			$out .= '</select></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="allow_anonymous">Tillåt anonymitet <strong>*</strong></label></th>' . "\n";
			$out .= '<td><select name="allow_anonymous">' . "\n";
			$out .= '<option ' . ($category['allow_anonymous'] == '0' ? 'selected="selected"' : '') . ' value="0">Nej</option>' . "\n";
			$out .= '<option ' . ($category['allow_anonymous'] == '1' ? 'selected="selected"' : '') . ' value="1">Ja</option>' . "\n";
			$out .= '</select></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="read_threads">Läsa trådar <strong>*</strong></label></th>' . "\n";
			$out .= '<td><select name="read_threads">' . "\n";
			$out .= '<option ' . ($category['read_threads'] == 'everybody' ? 'selected="selected"' : '') . ' value="everybody">Alla</option>' . "\n";
			$out .= '<option ' . ($category['read_threads'] == 'no_one' ? 'selected="selected"' : '') . ' value="no_one">Ingen</option>' . "\n";
			$out .= '<option ' . ($category['read_threads'] == 'logged_in' ? 'selected="selected"' : '') . ' value="logged_in">Inloggade</option>' . "\n";
			$out .= '<option ' . ($category['read_threads'] == 'ov' ? 'selected="selected"' : '') . ' value="ov">Ordningsvakter</option>' . "\n";
			$out .= '<option ' . ($category['read_threads'] == 'joshua' ? 'selected="selected"' : '') . ' value="joshua">Joshua</option>' . "\n";
			$out .= '</select></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="create_thread">Skapa trådar <strong>*</strong></label></th>' . "\n";
			$out .= '<td><select name="create_thread">' . "\n";
			$out .= '<option ' . ($category['create_thread'] == 'no_one' ? 'selected="selected"' : '') . ' value="no_one">Ingen</option>' . "\n";
			$out .= '<option ' . ($category['create_thread'] == 'logged_in' ? 'selected="selected"' : '') . ' value="logged_in">Inloggade</option>' . "\n";
			$out .= '<option ' . ($category['create_thread'] == 'ov' ? 'selected="selected"' : '') . ' value="ov">Ordningsvakter</option>' . "\n";
			$out .= '<option ' . ($category['create_thread'] == 'joshua' ? 'selected="selected"' : '') . ' value="joshua">Joshua</option>' . "\n";
			$out .= '</select></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '<tr>' . "\n";
			$out .= '<th><label for="create_post">Svara i trådar <strong>*</strong></label></th>' . "\n";
			$out .= '<td><select name="create_post">' . "\n";
			$out .= '<option ' . ($category['create_post'] == 'no_one' ? 'selected="selected"' : '') . ' value="no_one">Ingen</option>' . "\n";
			$out .= '<option ' . ($category['create_post'] == 'logged_in' ? 'selected="selected"' : '') . ' value="logged_in">Inloggade</option>' . "\n";
			$out .= '<option ' . ($category['create_post'] == 'ov' ? 'selected="selected"' : '') . ' value="ov">Ordningsvakter</option>' . "\n";
			$out .= '<option ' . ($category['create_post'] == 'joshua' ? 'selected="selected"' : '') . ' value="joshua">Joshua</option>' . "\n";
			$out .= '</select></td>' . "\n";
			$out .= '</tr>' . "\n";
			
			$out .= '</table>' . "\n";
			$out .= '<input type="submit" value="Spara" />' . "\n";
			$out .= '</form>';
		break;
		
		case 'list_categories':
		default:
			$categories = discussion_forum_categories_fetch(array('disable_query_caching' => true, 'parent' => 0));
			
			$out .= '<fieldset>' . "\n";
			$out .= '<legend>Forumkategorier</legend>' . "\n";
			$out .= '<table class="form">' . "\n";
			$out .= '<tr>' . "\n";
			$out .= '<th>Titel</th>' . "\n";
			$out .= '<th>Ändra</th>' . "\n";
			$out .= '<th>Ta bort</th>' . "\n";
			$out .= '</tr>' . "\n";
			$out .= forum_admin_category_recursive_list($categories, 0);
			$out .= '</table>' . "\n";
			$out .= '</fieldset>' . "\n";
		break;
	}
	ui_top($ui_options);
	echo rounded_corners_tabs_top($rounded_corners_tabs_options, true);
	echo $out;
	echo rounded_corners_tabs_bottom($rounded_corners_tabs_options, true);
	ui_bottom();
}
catch (Exception $error)
{
 		echo $error -> getMessage();
}
?>