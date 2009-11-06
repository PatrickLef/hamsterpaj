hp.schedule_v2 = {
	admin: {
		fetch_slot_data_path:  '/admin/schemalagt_v2.php?page=ajax_fetch_slot&id=' + '$1',
		save_slot_data_path:   '/admin/schemalagt_v2.php?page=ajax_save_slot&' + '$parameters',
		schema_div:            'schedule_v2_admin_slot_schema',
		schema_item_classname: 'schedule_v2_admin_schema_item',
		slot_config:           'schedule_v2_admin_slot_config_$1',
		schema_day_width : 85,
		schema_day_add_left_pixels: -1,
		schema_scale: 6,
		
		init: function()
		{
			var all_clickables = document.getElementsByTagName('li');
			var match_clickable = 'schedule_v2_admin_select_slot_';
			for(var clickable in all_clickables)
			{
				if(typeof(all_clickables[clickable].id) != 'undefined' && all_clickables[clickable].id.substring(0, match_clickable.length) == match_clickable)
				{
					all_clickables[clickable].onclick = function()
					{
						hp.schedule_v2.admin.fetch_slot_data(this.id.substring(match_clickable.length));
					}
				}
			}
		},
		
		fetch_slot_data: function(slotid)
		{
			var loader = hp.give_me_an_AJAX();
			loader.onreadystatechange = function(){ hp.schedule_v2.admin.fetch_slot_data_onreadystatechange(loader); }
			loader.open('GET', this.fetch_slot_data_path.replace('$1', slotid), true);
			loader.send(null);
		},
		
		fetch_slot_data_onreadystatechange: function(ajax_response)
		{
			if(ajax_response.readyState == 4 && ajax_response.status == 200)
			{
				// Probably an 404 Error, do not parse data.
				var response_json = eval('(' + ajax_response.responseText + ')');
				this.parse_slot_data(response_json);
			}
		},
		
		parse_slot_data: function(json_data)
		{
			var one_hour = 60;
			var one_day = 1440; // Minutes on 24h
			var one_week = one_day *  7; // Minutes on one week
			
			var type = json_data.type;
			var id = json_data.id;
			var start = json_data.start;
			var end = json_data.end;
			
			var start_day = Math.ceil((start / one_week) * 7);
			var end_day = Math.ceil((end / one_week) * 7);
			
			var start_minute = start - (one_day * (start_day - 1));
			var end_minute = end - (one_day * (end_day - 1));
			
			var start_hour = Math.floor(start_minute / one_hour);
			var end_hour = Math.floor(end_minute / one_hour);
			
			var start_minute_on_hour = start_minute - (start_hour * one_hour);
			var end_minute_on_hour = end_minute - (end_hour * one_hour);
			
			
			var plot_day_len = (end_day - start_day) + 1;
			
			/*alert(
				  'Start: ' + start + '\n'
				+ 'End: ' + end + '\n'
				+ 'Start day: ' + start_day + '\n'
				+ 'Start minute: ' + start_minute + '\n'
				+ 'End day: ' + end_day + '\n'
				+ 'End minute: ' + end_minute + '\n'
				+ 'Plot days: ' + plot_day_len + '\n'
			);*/
			
			var plot_areas = new Array();
			
			for(var day_offset = 0; day_offset < plot_day_len; day_offset++)
			{
				var current_day = start_day + day_offset;
				if(current_day != start_day && current_day != end_day) // Plot whole day
				{
					var plot_minutes_top = 0;
					var plot_minutes_bottom = one_day;
				}
				else
				{
					if(start_day == end_day)
					{
						var plot_minutes_top = start_minute;
						var plot_minutes_bottom = end_minute;
					}
					else
					{
						if(current_day == start_day)
						{
							var plot_minutes_top = start_minute;
							var plot_minutes_bottom = one_day;
						}
						else if(current_day = end_day)
						{
							var plot_minutes_top = 0;
							var plot_minutes_bottom = end_minute;
						}
					}
				}
				var area = new Array();
				area['top'] = plot_minutes_top;
				area['bottom'] = plot_minutes_bottom;
				area['day'] = current_day;
				plot_areas[plot_areas.length] = area;
			}
			
			this.draw_slot_data(plot_areas);
			
			var moment_start = new Array();
			var moment_end = new Array();
			var config = new Array();
			
			moment_start['day'] = start_day + ((start_hour == 24) ? 1 : 0);
			moment_start['hour'] = ((start_hour == 24) ? 0 : start_hour);
			moment_start['minute'] = start_minute_on_hour;
			moment_start['week_minutes'] = start;
			
			moment_end['day'] = end_day + ((end_hour == 24) ? 1 : 0);
			moment_end['hour'] = ((end_hour == 24) ? 0 : end_hour);
			moment_end['minute'] = end_minute_on_hour;
			moment_end['week_minutes'] = end;
			
			config['type'] = type;
			config['id'] = id;
			config['start'] = moment_start;
			config['end'] = moment_end;
			
			this.draw_slot_config(config);
		},
		
		draw_slot_data: function(plot_areas)
		{
			var schema_div = document.getElementById(this.schema_div);
			schema_div.innerHTML = '';// Clear from previous content.
			
			(navigator.appVersion.indexOf('MSIE')!=-1) ? schema_div.style.position='absolute' : void(0);
			var schema_left = schema_div.offsetLeft;
			var schema_top = schema_div.offsetTop;
			(navigator.appVersion.indexOf('MSIE')!=-1) ? schema_div.style.position='static' : void(0);
			
			for(var plot_area = 0; plot_area < plot_areas.length; plot_area++)
			{		
				var current_area = plot_areas[plot_area];
				
				var rect = document.createElement('div');
				rect.className = this.schema_item_classname;
				
				rect.style.left = (schema_left + this.schema_day_add_left_pixels + ((current_area['day'] - 1) * this.schema_day_width)) + 'px';
				rect.style.top = (schema_top + Math.floor(current_area['top'] / this.schema_scale)) + 'px';
				rect.style.height = Math.floor((current_area['bottom'] / this.schema_scale) - (current_area['top'] / this.schema_scale)) + 'px';
				
				// alert('Plot\nDay: ' + current_area['day'] + '\nTop: ' + current_area['top'] + '\nBottom: ' + current_area['bottom']);
				
				schema_div.appendChild(rect);
			}
		},
		
		draw_slot_config: function(config)
		{
			document.getElementById(this.slot_config.replace('$1', 'container')).style.display = 'block';
			
			// Start
			document.getElementById(this.slot_config.replace('$1', 'start_day')).options[config['start']['day']-1].selected = true;
			document.getElementById(this.slot_config.replace('$1', 'start_hour')).options[config['start']['hour']].selected = true;
			document.getElementById(this.slot_config.replace('$1', 'start_minute')).options[config['start']['minute']].selected = true;
			
			// End
			document.getElementById(this.slot_config.replace('$1', 'end_day')).options[config['end']['day']-1].selected = true;
			document.getElementById(this.slot_config.replace('$1', 'end_hour')).options[config['end']['hour']].selected = true;
			document.getElementById(this.slot_config.replace('$1', 'end_minute')).options[config['end']['minute']].selected = true;
			
			// Other
			document.getElementById(this.slot_config.replace('$1', 'id')).innerHTML = config['id'];
			var type_selector = document.getElementById(this.slot_config.replace('$1', 'type'));
			for(var option = 0; option < type_selector.options.length; option++)
			{
				if(type_selector[option].value == config['type'])
				{
					type_selector[option].selected = true;
				}
			}
			document.getElementById(this.slot_config.replace('$1', 'start_week_minutes')).innerHTML = config['start']['week_minutes'];
			document.getElementById(this.slot_config.replace('$1', 'end_week_minutes')).innerHTML = config['end']['week_minutes'];
			
			document.getElementById(this.slot_config.replace('$1', 'save')).onclick = function()
			{
				hp.schedule_v2.admin.save_slot_config(config['id']);
			}
		},
		
		save_slot_config: function(slot_id)
		{
			var one_day = 1440;
			var one_hour = 60;
			
			var type_selector = document.getElementById(this.slot_config.replace('$1', 'type'));

			var start_day_selector = document.getElementById(this.slot_config.replace('$1', 'start_day'));
			var start_hour_selector = document.getElementById(this.slot_config.replace('$1', 'start_hour'));
			var start_minute_selector = document.getElementById(this.slot_config.replace('$1', 'start_minute'));
			
			var end_day_selector = document.getElementById(this.slot_config.replace('$1', 'end_day'));
			var end_hour_selector = document.getElementById(this.slot_config.replace('$1', 'end_hour'));
			var end_minute_selector = document.getElementById(this.slot_config.replace('$1', 'end_minute'));
			
			var start_day = parseInt(start_day_selector.options[start_day_selector.selectedIndex].value);
			var start_hour = parseInt(start_hour_selector.options[start_hour_selector.selectedIndex].value);
			var start_minute = parseInt(start_minute_selector.options[start_minute_selector.selectedIndex].value);
			
			var end_day = parseInt(end_day_selector.options[end_day_selector.selectedIndex].value);
			var end_hour = parseInt(end_hour_selector.options[end_hour_selector.selectedIndex].value);
			var end_minute = parseInt(end_minute_selector.options[end_minute_selector.selectedIndex].value);
			
			var start = (start_day * one_day) + (start_hour * one_hour) + start_minute;
			var end = (end_day * one_day) + (end_hour * one_hour) + end_minute;
			
			if(start >= end)
			{
				alert('Nej, du måste ju sätta ett slut som är före din början.\nVi kan ju inte ha saker som börjar innen de ska sluta i systemet!');
				return;
			}
			
			var type_selector = document.getElementById(this.slot_config.replace('$1', 'type'));
			var type = type_selector.options[type_selector.selectedIndex].value;
			
			var slot_data = 'id=' + slot_id
			              + '&start=' + start
			              + '&end=' + end
			              + '&type=' + type;
			window.location.href = this.save_slot_data_path.replace('$parameters', slot_data);
		}
	}
}

womAdd('hp.schedule_v2.admin.init()');
