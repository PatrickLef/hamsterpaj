<?php
	/* OPEN_SOURCE */

	require('../include/core/common.php');
	$ui_options['menu_active'] = 'forum';
	$ui_options['title'] = 'Hamsterpajs diskussionsforum';
	$ui_options['stylesheets'][] = 'discussion_forum.css';
	$ui_options['stylesheets'][] = 'abuse.css';
	$ui_options['javascripts'][] = 'discussion_forum.js';	
	$ui_options['javascripts'][] = 'forum_help_texts.js';	
	

	$request = discussion_forum_parse_request($_SERVER['REQUEST_URI']);

	unset($post);

	switch($request['action'])
	{
		case 'view_category':
			$all_categories_list = discussion_forum_categories_fetch(array('handle' => $request['category_handle']));
			$category = array_pop($all_categories_list);

			$forum_security = forum_security(array('action' => 'view_category', 'category' => $category, 'forum_id' => $category['id']));
			if($forum_security !== true)
			{
				$output .= $forum_security;
				break;
			}
			$ui_options['title'] = $category['title'] . ' - Hamsterpajs forum';
			$path_to_category = discussion_forum_path_to_category(array('id' => $category['id']));
			$locator_options['categories'] = $path_to_category;
			$output .= discussion_forum_locator($locator_options);
			$output .= discussion_forum_category_head($request);

			unset($options);
			$options['max_levels'] = 0;
			$options['parent'] = $category['id'];
			$categories = discussion_forum_categories_fetch($options);
			$output .= discussion_forum_categories_list($categories);

			$output .= '<h2>Trådar</h2>' . "\n";
			$post_options['forum_id'] = $request['category']['id'];
			$post_options['threads_only'] = true;
			$post_options['order_by_sticky'] = true;
			$post_options['page_offset'] = $request['page_offset'];
			$threads = discussion_forum_post_fetch($post_options);
			$output .= discussion_forum_thread_list($threads);
			
			$path_to_trailing_category = array_pop($path_to_category);
			$output .= '<a href="' . $path_to_trailing_category['url'] . 'traadsida_' . ($request['page_offset'] + 2) . '.php">Nästa sida &raquo;</a>';
			
			if(forum_security(array('action' => 'discussion_create', 'forum_id' => $category['id'])) === true)
			{
				$form_options['forum_id'] = $request['category']['id'];
				$form_options['mode'] = 'create_thread';
				$output .= discussion_forum_post_form($form_options);
			}
			else
			{
				$output .= '<h2>Du får inte starta diskussioner här</h2>' . "\n";
				$output .= 'I den här kategorin får du inte starta några diskussioner, men kanske finns det några underkategorier du får det i?';
			}
			
			forum_update_category_session(array('category' => $category, 'threads' => $threads));
								
		break;
		
		case 'move_thread':
			if(forum_security(array('action' => 'move_thread', 'thread' => $request['thread'])))
			{
				$query = 'UPDATE forum_posts SET forum_id = "' . $request['new_category']['id'] . '" WHERE id = "' . $request['thread']['id'] . '" LIMIT 1';
				mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
			
				$message  = 'Hej, din tråd i forumet med titeln "%TITLE%" har flyttats till %NEW_CATEGORY%.' . "\n";
				$message .= 'Har du några frågor om varför tråden flyttades så kan du ta dem med %MOVERS_USERNAME%';
				$message .= 'eller med någon annan ordningsvakt, du hittar sådana i modulen "Inloggade Ordningsvakter" till höger.' . "\n";
				$message .= '/Webmaster';
				$guestbook_message = array(
					'sender' => 2348,
					'recipient' => intval($request['thread']['author']),
					'message' => mysql_real_escape_string(str_replace(
						array('%TITLE%', '%NEW_CATEGORY%', '%MOVERS_USERNAME%'),
						array($request['thread']['title'], $request['new_category']['title'], $_SESSION['login']['username']),
						$message
					))
				);
				guestbook_insert($guestbook_message); 
				header('Location: ' . $request['new_category']['url']);
				exit;
			}
			break;
		
		case 'latest_threads':
			$ui_options['title'] = 'Senaste trådarna i Hamsterpajs forum';
			$ui_options['menu_active'] = 'forum_nya_tradar';
			$output .= '<h2>De 50 senaste trådarna i forumet</h2>' . "\n";
			$post_options['threads_only'] = true;
			$post_options['order-by'] = 'p.id';
			$post_options['order-direction'] = 'DESC';
			$post_options['limit'] = 50;
			$post_options['max_userlevel'] = (login_checklogin() ? $_SESSION['login']['userlevel'] : 0);
			$threads = discussion_forum_post_fetch($post_options);
			$output .= discussion_forum_thread_list($threads);
			break;
			
		case 'view_thread':
			$post_options['thread_handle'] = $request['thread_handle'];
			$post_options['mode'] = 'thread';
			$first_post = discussion_forum_post_fetch($post_options);
			
			// if the request doesn't end with .php (has no set page)
			if(substr($_SERVER['REQUEST_URI'], -4) != '.php' && login_checklogin())
			{
				discussion_forum_goto_latest_read($_SERVER['REQUEST_URI'], $first_post[0]['id']);
			}


			$forum_security = forum_security(array('action' => 'view_thread', 'forum_id' => $first_post[0]['forum_id']));
			if($forum_security !== true)
			{
				$output .= $forum_security;
				break;
			}
			
			$ui_options['title'] = $first_post[0]['title'] . ' - Hamsterpajs forum';

			if($first_post[0]['forum_id'] == 102)
			{
				$output .= '<a href="/sex_och_sinne/"><img style="margin-top: 4px;" src="http://images.hamsterpaj.net/sex_and_sense/sex_and_sense_top.png" /></a>' . "\n";
			}

			$locator_options['categories'] = discussion_forum_path_to_category(array('id' => $first_post[0]['forum_id']));
			$locator_options['thread_handle'] = $first_post[0]['handle'];
			$locator_options['thread_title'] = $first_post[0]['title'];
			$locator_options['post_count'] = $first_post[0]['child_count'];
			$locator_options['current_page'] = 'test'.$request['page_num'];
			$output .= discussion_forum_locator($locator_options);
			
			$output .= discussion_forum_thread_info($first_post[0]);
			
			unset($post_options);
			$post_options['thread_id'] = $first_post[0]['id'];
			$post_options['mode'] = 'thread';
			//$post_options['disable_forum_lookup'] = true;
			$post_options['offset'] = ($request['page_num']-1) * FORUM_POSTS_PER_PAGE;
			
			if ( isset($_SESSION['forum_sorting_order'][$first_post[0]['id']]) && $_SESSION['forum_sorting_order'][$first_post[0]['id']] == 'desc' )
			{
				$post_options['order-direction'] = 'desc';
			}

			if($_SESSION['login']['id'] == 774586)
			{
//				echo '<strong>Posts fetch</strong> ';
//				preint_r($request);
			}
			
			$posts = discussion_forum_post_fetch($post_options);
			
			if($first_post[0]['removed'] == 1)
			{
				$output .= '<h1>Tråd borttagen</h1>' . "\n";
				$output .= '<p>Hamsterpajs ordningsvakter har tagit bort den här tråden. Hade vi haft några regler hade det stått att tråden bryter mot reglerna, men eftersom Ace inte skrivit någre regler så kan vi väl bara gissa att det var en skräptråd...</p>' . "\n";
				$output .= '<p>Gå till <a href="/diskussionsforum/">forumets startsida</a> eller lyssna på lite <a href="/mattan/gratis_musik.php#dia_psalma">Dia Psalma</a> istället!</p>' . "\n";
				$output .= '<div class="forum_post_removed">Inlägget har tagits bort' . "\n";
				$output .= (forum_security(array('action' => 'unremove_post'))) ? '<button value="' . $post_options['thread_id'] . '" class="forum_unremove_button">trob At</button>, Borttaget av <a href="/traffa/profile.php?id=' . $first_post[0]['remover'] . '">' . $first_post[0]['remover'] . '</a>.' . "\n" : '';
				$output.= '</div>';
				if(strlen($first_post[0]['removal_comment']) > 0)
				{
					$output .= '<h2>Varför togs inlägget bort?</h2>' . "\n";
					$output .= '<p>Ordningsvakten som tog bort inlägget har lämnat en liten förklaring om varför här nedan:<br />' . $first_post[0]['removal_comment'] . '</p>' . "\n";
				}

				/* List posts without output, to make sure notices are removed */
				discussion_forum_post_list($posts);
				discussion_forum_count_views($first_post[0]);
				break;
			}
			
			$output .= discussion_forum_post_list($posts, $first_post[0]);
			
			$paging_options['current_page'] = $request['page_num'];
			$paging_options['post_count'] = $first_post[0]['child_count'];
			$paging_options['thread_handle'] = $first_post[0]['handle'];
			$paging_options['category_url'] = $locator_options['categories'][count($locator_options['categories'])-1]['url'];
			$paging_options['label'] = true;
			
			$output .= forum_thread_paging($paging_options);
			
			$forum_security = forum_security(array('action' => 'reply', 'post' => $first_post[0]));
			if($forum_security === true)
			{
				$form_options['forum_id'] = $first_post[0]['forum_id'];// The ghost notices father! $request['category']['id'];
				$form_options['thread_id'] = $first_post[0]['id'];
				$form_options['mode'] = 'post';
				$form_options['title'] = 'Sv: ' . $first_post[0]['title'];
				$output .= discussion_forum_post_form($form_options);				
			}
			else
			{
				$output .= $forum_security;
			}
			
			discussion_forum_count_views($first_post[0]);
			
		break;
		
		case 'settings':
		break;
		
		case 'search':
			$ui_options['title'] = 'Sök i Hamsterpajs forum';
			$ui_options['menu_active'] = 'forum_sok';
			$output .= discussion_forum_search_form();
			
			if(strlen($request['freetext']) > 0)
			{
				$fetch_options = array();
				$fetch_options['order-direction'] = 'DESC';
				$fetch_options['match']['against'] = $request['freetext'];
				$fetch_options['match']['in_columns'] = array('p.content');
				$posts = discussion_forum_post_fetch($fetch_options);
				if(count($posts) > 0)
				{
					$output .= discussion_forum_post_list_search($posts);
				}
				else
				{
					$output .= '<h1>Vi hittade inget</h1>' . "\n";
					$output .= '<p>Nedan har vi gjort en Google-sökning åt dig, klicka med scrollhjulet på länkarna.</p>' . "\n";
					$output .= '<iframe src="http://www.google.se/search?q=site%3Awww.hamsterpaj.net%2Fdiskussionsforum%2F+' . urlencode($request['freetext']) . '" style="width: 630px; height: 1000px;"></iframe>' . "\n";
				}
			}
			
			break;
		
		case 'new_post':
			$content_check = content_check($_POST['content']);
			if($content_check != 1)
			{
				$output .= '<h2>' . $content_check . '</h2>' . "\n";
				
				$output .= '<p>Ditt inlägg har inte sparats, eftersom vår server tror att du skickar spam.</p><ol><li>Markera och kopiera ditt inlägg nedan</li><li>Backa tillbaks webbläsaren</li><li>Klistra in ditt inlägg i skriv-rutan och plocka bort det som bryter mot våra regler</li></ol>' . "\n";
				$output .= '<pre>' . $_POST['content'] . '</pre>' . "\n";
				break;
			}
			if($_SESSION['forum']['last_post_timestamp'] > time() - FORUM_MIN_POST_DELAY)
			{
				$output .= '<h2>Max ett inlägg per ' . FORUM_MIN_POST_DELAY . ' sek</h2>' . "\n";
				
				$output .= '<p>Även om du tror att du är jättesmart och kör med flera flikar, så är det <strong>fortfarande</strong> så att våra antispamregler gäller... Här ser du ditt inlägg, om du vill kan du kopiera det.</p>' . "\n";
				$output .= '<pre>' . $_POST['content'] . '</pre>' . "\n";
				break;				
			}
			if($_POST['mode'] == 'create_thread')
			{
				$forum_security = forum_security(array('action' => 'discussion_create', 'forum_id' => $_POST['forum_id'], 'content' => $_POST['content']));
	
				if($forum_security !== true)
				{
					$output .= $forum_security;
					break;
				}
				
				$post['content'] = $_POST['content'];
				$post['forum_id'] = $_POST['forum_id'];
				$post['title'] = $_POST['title'];
				$post['mode'] = 'new_thread';
				$thread_id = discussion_forum_post_create($post);
				$redirect_url = forum_get_url_by_post($thread_id);

				if($_SESSION['preferences']['forum_subscribe_on_create'] == 1)
				{
					$query = 'INSERT INTO forum_read_posts (user_id, thread_id, subscribing, posts, has_voted) VALUES("' . $_SESSION['login']['id'] . '", "' . $thread_id;
					$query .= '", "true", 1, 0)';
					mysql_query($query) or report_sql_error($query, __FILE__, __LINE__);
		
					$thread = array_pop(discussion_forum_post_fetch(array('post_id' => $thread_id)));
					$_SESSION['forum']['subscriptions'][$thread_id] = $thread;
				}

				header('Location: ' . $redirect_url);
			}
			elseif($_POST['mode'] == 'sub_thread')
			{

			}
			else
			{			
				$forum_security = forum_security(array('action' => 'new_post', 'forum_id' => $_POST['forum_id'], 'parent_post' => $_POST['parent'], 'content' => $_POST['content']));
	
				if($forum_security !== true)
				{
					$output .= $forum_security;
					break;
				}
				
				$post['content'] = $_POST['content'];
				$post['parent_post'] = $_POST['parent'];
				$post['forum_id'] = $_POST['forum_id'];
				$post['mode'] = 'new_post';
				$post_id = discussion_forum_post_create($post);

				if($_SESSION['preferences']['forum_subscribe_on_post'] == 1)
				{
					$query = 'UPDATE forum_read_posts SET subscribing = "true" WHERE user_id = "' . $_SESSION['login']['id'] . '" AND thread_id = "' . $_POST['parent'] . '" LIMIT 1';
					mysql_query($query);
		
					$thread = array_pop(discussion_forum_post_fetch(array('post_id' => $_POST['parent'])));
					$_SESSION['forum']['subscriptions'][$_POST['parent']] = $thread;
				}
				
				$redirect_url = forum_get_url_by_post($post_id);
			}
			
			header('Location: ' . $redirect_url);
			break;

		case 'threads_by_user':
			$ui_options['menu_active'] = 'forum_dina_tradar';
			$output .= '<h2>Dina 50 senaste trådar</h2>' . "\n";
			$post_options['threads_only'] = true;
			$post_options['order-by'] = 'p.id';
			$post_options['order-direction'] = 'DESC';
			$post_options['limit'] = 50;
			$post_options['author'] = $request['user_id'];
			$post_options['max_userlevel'] = (login_checklogin() ? $_SESSION['login']['userlevel'] : 0);
			$threads = discussion_forum_post_fetch($post_options);
			$output .= discussion_forum_thread_list($threads);
			break;

		case 'index':
			$ui_options['menu_active'] = 'forum_kategorier';
			$output = '<h1>Hej, det här är Hamsterpajs diskussionsforum!</h1>' . "\n";
			if(login_checklogin())
			{
				if($_SESSION['userinfo']['forum_posts'] < 5 && false)
				{
					$output .= '<p>Här kan du ställa frågor, starta egna diskussioner och delta i andras diskussioner. Börja med att klicka in i en kategori, varför inte <a href="/diskussionsforum/allmaent/mellan_himmel_och_jord/">Mellan himmel och jord</a>?</p><p>Väl där kan du starta en ny diskussion (tråd) med formuläret längst ner på sidan, eller delta i en som redan finns.</p>' . "\n";
				}
			}
			else
			{
				$output .= '<p>Alla kan läsa och surfa runt i forumet, men vill du kunna skriva måste du <a href="/register.php">bli medlem</a>, det går snabbt och är gratis. Till skillnad från många andra frågar vi inte efter din e-postadress, inget spam med andra ord!<br />Välkommen in!</p>' . "\n";
				
			}
			
			unset($options);
			$options['max_levels'] = 1;
			$options['parent'] = 0;
			$options['viewers_userlevel'] = (login_checklogin() ? $_SESSION['login']['userlevel'] : 0);

			$categories = discussion_forum_categories_fetch($options);
			$output .= discussion_forum_categories_list($categories);
			
			break;
			
		case 'view_new_notices':
			discussion_forum_reload_all();
			$ui_options['menu_active'] = 'forum_dina_notiser';
			$ui_options['title'] = 'Bevakade trådar - Hamsterpaj.net';
			$output .= discussion_forum_locator(array('page' => 'notices'));
			$output .= discussion_forum_list_notices();

			$output .= '<h1>Bevakade trådar</h1>' . "\n";
			$output .= discussion_forum_thread_list($_SESSION['forum']['subscriptions']);
			

			foreach($_SESSION['forum']['categories'] AS $category)
			{
				if($category['subscribing'] == 1)
				{
					$subscribing_categories[] = $category['category_id'];
				}
			}
			if(count($subscribing_categories) > 0)
			{
				$output .= '<h1 style="margin-top: 20px;">Kategorier du prenumererar på</h1>' . "\n";
				
				$viewers_userlevel = login_checklogin() ? $_SESSION['login']['userlevel'] : 0;
				$categories = discussion_forum_categories_fetch(array('id' => $subscribing_categories, 'max_levels' => 0, 'viewers_userlevel' => $viewers_userlevel));
				
				$output .= discussion_forum_categories_list($categories);
			}
			break;
			case 'view_notices':
			discussion_forum_reload_all();
			$ui_options['menu_active'] = 'forum_dina_notiser';
			$ui_options['title'] = 'Bevakade trådar - Hamsterpaj.net';
			$output .= discussion_forum_locator(array('page' => 'notices'));
			$output .= discussion_forum_list_notices();

			$output .= '<h1>Bevakade trådar</h1>' . "\n";
			$options['notice_listing'] = true;
			$output .= discussion_forum_thread_list($_SESSION['forum']['subscriptions'], $options);
			

			foreach($_SESSION['forum']['categories'] AS $category)
			{
				if($category['subscribing'] == 1)
				{
					$subscribing_categories[] = $category['category_id'];
				}
			}
			if(count($subscribing_categories) > 0)
			{
				$output .= '<h1 style="margin-top: 20px;">Kategorier du prenumererar på</h1>' . "\n";
				
				$viewers_userlevel = login_checklogin() ? $_SESSION['login']['userlevel'] : 0;
				$categories = discussion_forum_categories_fetch(array('id' => $subscribing_categories, 'max_levels' => 0, 'viewers_userlevel' => $viewers_userlevel));
				
				foreach($categories as $category)
				{
					$options['show_new_threads'] = true;
					$options['forum_id'] = $category['id'];
					$threads = discussion_forum_post_fetch($options);
					
					$output .= '<h2><a href="' . $category['url'] . '">' . $category['title'] . '</a></h2>' . "\n";
					if(count($threads) > 0)
					{
						// next row gave lef a lot of spokenotices, so that has to be checked before release!
						// $output .= '<a href="/ajax_gateways/discussion_forum.php?action=set_category_read&category=' . $category['id'] . '&return=' . $_SERVER['REQUEST_URI'] . '">Markera kategori som läst</a>' . "\n";
						$options['notice_listing'] = false;
						$output .= discussion_forum_thread_list($threads, $options);
					}
					else
					{
						$output .= '<p>Inga nya trådar i kategorin :(</p>' . "\n";
					}
				}
			}
			break;
			
		default:
			$output = '<h1>Fel!</h1>' . "\n";
			$output .= '<p>Forumet kunde inte förstå din förfrågan, sidladdningen har avrbrutits!</p>' . "\n"; 
		break;
	}

	ui_top($ui_options);
	echo $output;
	ui_bottom();

?>