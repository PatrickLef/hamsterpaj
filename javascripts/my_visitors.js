﻿hp.my_visitors = {
	scrollstate: 0,
	picholder_offsetLeft: 0,
	picholder_offsetTop: 0,
	fetch_visitors_userid: 0,
	update_scrolling: function(){
		var picholder = document.getElementById('my_visitors_picholder');
				
		/* picholder_offset* is an IE-fix */
		if((hp.mouse.x > this.picholder_offsetLeft && hp.mouse.x < this.picholder_offsetLeft + picholder.offsetWidth) && (hp.mouse.y > this.picholder_offsetTop && hp.mouse.y < this.picholder_offsetTop + picholder.offsetHeight))
		{
			//picholder.scrollLeft += Math.ceil((hp.mouse.x - this.picholder_offsetLeft - (picholder.offsetWidth/2))/30);
			/* New sccrolling algoritm */
			if(hp.mouse.x < this.picholder_offsetLeft + 150){
				picholder.scrollLeft -= ((this.picholder_offsetLeft + 150) - hp.mouse.x) / 12;
			}
			else if(hp.mouse.x > this.picholder_offsetLeft + picholder.offsetWidth - 150)
			{
				picholder.scrollLeft += (hp.mouse.x - (this.picholder_offsetLeft + picholder.offsetWidth - 150)) / 12;
			}
		}

	},
	init: function(){
		var all_divs = document.getElementsByTagName('DIV');
		var match_id = 'my_visitors_showinfo_';
		var all_divs_width = 0;
		
		for(var div = 0; div < all_divs.length; div++)
		{
			if(typeof(all_divs[div].id) != "undefined" && all_divs[div].id.substring(0, match_id.length) == match_id)
			{
				all_divs[div].onclick=function(){
					hp.my_visitors.load_user_into_userinfo_pane(this.id.substring(match_id.length), true);
				}
				all_divs_width += all_divs[div].offsetWidth;
			}
		}
		
		var all_links = document.getElementsByTagName('A');
		var match_link_id_spot = 'my_visitors_spot_popup_';
		var match_link_id_username = 'my_visitors_search_showinfo_';
		
		for(var link = 0; link < all_links.length; link++)
		{
			if(typeof(all_links[link].id) != "undefined" && all_links[link].id.substring(0, match_link_id_spot.length) == match_link_id_spot)
			{
				all_links[link].onclick=function(){
					var info_string = this.id.substring(match_link_id_spot.length);
					var info_parts = info_string.split('_'); //[0]=point_x, [1]=point_y, [2]=userid.
					var parameters = {
						point_x: info_parts[0],
						point_y: info_parts[1],
						userid: info_parts[2]
					}
					
					hp.my_visitors.show_on_map(parameters);
				}
			}
			else if(typeof(all_links[link].id) != "undefined" && all_links[link].id.substring(0, match_link_id_username.length) == match_link_id_username)
			{
				all_links[link].onclick=function(){
					var info_string = this.id.substring(match_link_id_username.length);
					var info_parts = info_string.split('_'); //[0]=has_photo, [1]=userid.
					
					hp.my_visitors.load_user_into_userinfo_pane(info_parts[1], (info_parts[0] == 'true'));
					
					var userinfo_pane_object = document.getElementById('my_visitors_userinfo_pane');
					(navigator.appVersion.indexOf('MSIE')!=-1) ? userinfo_pane_object.style.position='absolute' : void(0);
					window.scrollTo(0, userinfo_pane_object.offsetTop);
					(navigator.appVersion.indexOf('MSIE')!=-1) ? userinfo_pane_object.style.position='static' : void(0);
					
					
					return false;
				}
			}
		}
		
		document.getElementById('my_visitors_picholder_expander').style.width= all_divs_width + 'px';
		
		// IE won't calculate offsetLeft and offsetTop properly without this.
		var picholder = document.getElementById('my_visitors_picholder');
		(navigator.appVersion.indexOf('MSIE')!=-1) ? picholder.style.position='absolute' : void(0);
		this.picholder_offsetTop  = picholder.offsetTop;
		this.picholder_offsetLeft = picholder.offsetLeft;
		(navigator.appVersion.indexOf('MSIE')!=-1) ? picholder.style.position='static' : void(0);

		this.fetch_visitors_userid = document.getElementById('my_visitors_show_user_id').value;

		try{
			document.getElementById('my_visitors_search_user').onfocus=function(){
				document.getElementById('my_visitors_search_type_by_user').checked = true;
			}
			document.getElementById('my_visitors_search_spot').onfocus=function(){
				document.getElementById('my_visitors_search_type_by_spot').checked = true;
			}
		}catch(e){ }

		setInterval('hp.my_visitors.update_scrolling()', 15);
	},
	load_user_into_userinfo_pane: function(userid, has_photo){
		var loader = hp.give_me_an_AJAX();
		loader.open('GET', '/ajax_gateways/my_visitors_load_userdata.php?id=' + userid + '&for_userid=' + this.fetch_visitors_userid);
		loader.onreadystatechange = function(){
			if(loader.readyState == 4 && loader.status == 200)
			{
				hp.my_visitors.load_user_into_userinfo_pane_draw(eval('(' + loader.responseText + ')'), userid, has_photo);
			}
		}
		loader.send(null);
	},
	load_user_into_userinfo_pane_draw: function(json_data, userid, has_photo){
		var output = '';
		var photo_path = 'http://images.hamsterpaj.net/';
		var photo_image_file = (has_photo ? photo_path + 'images/users/full/' + userid + '.jpg' : photo_path + '/images/noimage.png');
		
		output += '<img class="_user_picture" src="' + photo_image_file + '" alt="Bild på användare" />';
		output += '<div class="_text_area">';
		output += '<span class="_user_status">' + ((json_data.user_status == '') ? json_data.username : '&quot;' + json_data.user_status + '&quot;') + '</span>';
		output += '<div class="_details">';
		output += json_data.username + ',';
		output += ' ' + ((json_data.gender == 'u') ? '' : (json_data.gender == 'm') ? 'pojke' : 'flicka');
		output += ' ' + ((json_data.age == 0) ? '' : json_data.age + ' år');
		output += ' ' + ((json_data.location == '') ? '' : 'från ' + json_data.location);
		output += '<br />';
		output += json_data.username + ' har besökt ' + json_data.have_visited + ' ' + json_data.total_visits + ' gång' + ((json_data.total_visits == 1) ? '' : 'er') + ', senast ' + json_data.last_visit + '.';
		output += '<br />';
		for(var flag = 0; flag<json_data.user_flags.length; flag++)
		{
			output += '<img class="_flag" src="' + photo_path + 'user_flags/' + json_data.user_flags[flag] + '.png" alt="Flagga" />';
		}
		output += '<br /><br />';
		output += '<input type="button" value="Besök" class="button_60" onclick="hp.go_to_user.profile(' + userid + ')" />';
		if(json_data.x_rt90 != 0 && json_data.y_rt90 != 0){
			output += ' <input type="button" value="Visa på karta" class="button_120" onclick="hp.my_visitors.show_on_map({ userid: ' + userid + ', point_x: ' + json_data.y_rt90 + ', point_y: ' + json_data.x_rt90 + ' })" /><br />';
		}
		output += '<textarea id="my_visitors_compose_gb_' + userid + '" class="_compose_gb" onfocus="if(this.value == this.defaultValue){ this.value=' + "''" + '; }">Skriv ett gästboksinlägg här...</textarea><br />';
		output += '<input type="button" value="Skicka gästboksinlägg!" class="button_150" onclick="hp.my_visitors.compose_gb(' + userid + ', this)" />';
		output += '<span id="my_visitors_compose_gb_' + userid + '_ajax_status">&nbsp;</span>';
		output += '</div>';
		output += '</div>';
		
		document.getElementById('my_visitors_userinfo_pane').innerHTML = output;
	},
	show_on_map: function(params){
		if(!window.open('http://www.hitta.se/LargeMap.aspx?ShowSatellite=false&pointX=' + params.point_x + '&pointY=' + params.point_y + '&cx=' + params.point_x + '&cy=' + params.point_y + '&z=6&name=' + 'H%E4r%20n%E5gonstans%20bor%20anv%E4ndaren', 'user_map_' + params.userid, 'location=false, width=750, height=500'))
		{
			alert('Du måste tillåta popupfönster i din webbläsare för att kunna se kartan.');
		}
	},
	compose_gb: function(userid, button){
		button.style.display = 'none';
		
		var textbox = document.getElementById('my_visitors_compose_gb_' + userid);
		var statusbox = document.getElementById('my_visitors_compose_gb_' + userid + '_ajax_status');
		var loader = hp.give_me_an_AJAX();
		
		//loader.open('POST', '/traffa/gb-reply.php?action=send_reply&userid=' + userid, true);
		loader.open('POST', '/ajax_gateways/my_visitors_send_gb.php', true);
		loader.onreadystatechange = function(){
			if(loader.readyState == 4 && loader.status == 200){
				statusbox.innerHTML = loader.responseText;
			}
		}
		loader.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
		loader.send('recipient=' + userid + '&message=' + encodeURIComponent(textbox.value));
		
		textbox.style.display = 'none';
		statusbox.innerHTML = 'Skickar...';
	}
}

womAdd("hp.my_visitors.init()");
