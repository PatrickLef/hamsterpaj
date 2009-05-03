<?php
try
{
	require('../include/core/common.php');
	$ui_options['title'] = 'Hamsterpajs diskussionsforum administration';

	if(!is_privilegied('forum_admin'))
	{
		die('SkräddarN säger NEJ!!!');
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
		case 'update':
			foreach($_POST as $input => $data)
			{
				if(empty($data))
				{
					throw new Exception($input . ' is empty');
				}
				if(in_array($fields[$input]['type'], $ints) && !is_numeric($data))
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
			$query .= ' WHERE id = ' . $_GET['id'] . ' LIMIT 1';
			
			mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
			header('Location: /site_admin/discussion_forum_admin.php');
		break;
			
		case 'remove':
				
		break;
		
		case 'add':
			
		break;
	}

	switch($_GET['action'])
	{
		default:
			$categories = discussion_forum_categories_fetch(array('disable_query_caching' => true));
			$out .= '<table>' . "\n";
			$out .= '<tr>' . "\n";
			foreach($fields as $key => $field)
			{
				$out .= '<th>' . $key . '</th>' . "\n";
			}
			$out .= '</tr>' . "\n";
			foreach($categories as $category)
			{
				$out .= '<form method="post" action="?post=update&id=' . $category['id'] . '">' . "\n";
				foreach($fields as $key => $field)
				{
					$out .= '<td>' . "\n";
						
						switch($field['type'])
						{
							case 'enum':
								$out .= '<select name="' . $key . '">' . "\n";
								foreach($field['values'] as $value)
								{
									$out .= '<option ' . ($value == $category[$key] ? 'selected="selected"' : '') . ' value="' . $value . '">' . $value . '</option>' . "\n";
								}
								$out .= '</select>' . "\n";
							break;
							
							case 'varchar':
								$out .= '<input name="' . $key . '" value="' . $category[$key] . '" />' . "\n";
							break;
							
							case 'int':
							case 'smallint':
							case 'tinyint':
								$out .= '<input name="' . $key . '" value="' . $category[$key] . '" style="width: 50px;" />' . "\n";
							break;
							
							case 'text':
								$out .= '<textarea name="' . $key . '" style="width: 300px; height: 100px;">' . $category[$key] . '</textarea>' . "\n";
							break;
						}
					$out .= '</td>' . "\n";
				}
				$out .= '<td><input type="submit" value="Spara" /></td>' . "\n";
				$out .= '</tr>' . "\n";
				$out .= '</form>' . "\n";
			}
			$out .= '</table>' . "\n";
	}
	echo $out;
}
catch (Exception $error)
{
 		echo $error -> getMessage();
}
?>