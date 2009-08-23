hp.photoblog = {
	upload:
	{
		flash_upload:
		{
			new_file: function(raw_json_data)
			{
				json_data = eval('(' + raw_json_data + ')');
				if(parseInt(json_data.server_id, 10) > 0)
				{
					hp.photoblog.upload.photo_properties.photo_create({
						photo_id: json_data.server_id,
						photo_filename: json_data.filename
					});
				}
				else
				{
					alert('Något gick snett :/ Fotot ' + json_data.filename + ' kunde inte laddas upp (Debug: Ogiltigt ServerID returnerat från Flash). ' + json_data.server_id);
				}
			}
		},
		
		photo_properties:
		{
			photos: new Array(),
			photo_create: function(params)
			{
				hp.photoblog.upload.photo_properties.photos.push(params);
				
				var properties_div = $(document.createElement('div')).appendTo('#photoblog_photo_properties_container');
				$(properties_div)
					.attr('id', 'photoblog_photo_properties_' + params.photo_id)
					.attr('className', 'photoblog_photo_properties')
					.html(''
						+ '<div class="properties">'
							+ '<input class="photoblog_photo_properties_date" type="text" name="photoblog_photo_properties_' + params.photo_id + '_date" id="photoblog_photo_properties_' + params.photo_id + '_date" value="Idag" />'
							+ '<textarea class="photoblog_photo_properties_description" name="photoblog_photo_properties_' + params.photo_id + '_description"></textarea>'
							+ '<input type="hidden" name="photoblog_photo_properties_' + params.photo_id + '_photo_id" value="' +  params.photo_id + '" />'
						+ '</div>'
						
						+ '<div class="float">'
							+ '<div class="thumbnail_wrapper">'
								+ '<img src="' + hp.photoblog.make_thumbname(params.photo_id) + '" class="thumbnail" />'
							+ '</div>'
							
							+ '<div class="rotate">'
								+ '<img src="http://images.hamsterpaj.net/photoblog/rotate_left.png" class="rotate_left" />'
								+ '<img src="http://images.hamsterpaj.net/photoblog/rotate_right.png" class="rotate_right" />'
							+ '</div>'
						+ '</div>'
					);
				
				$('#photoblog_photo_properties_' + params.photo_id + '_date').datepicker({
					showWeeks: true,
					dateFormat: 'yy-mm-dd'
				});
					
				$('#photoblog_upload_rules').hide();
				$('#photoblog_photo_properties_save').show();
			}
		}
	},
	
	mousepos: {x: 0, y: 0},
	
	view: {
	failthumburl: 'http://images.hamsterpaj.net/photos/mini/50/253244.jpg',
	failimageurl: 'http://images.hamsterpaj.net/photos/full/50/253244.jpg',
	
	// Do indent of hp.photoblog.view when we are done, I'm fed up of having to scroll sideways
	init: function() {
		hp.photoblog.year_month.init();
		hp.photoblog.calendar.init();
		hp.photoblog.edit.init();
		
		if ( hp.photoblog.current_user.date ) {
			hp.photoblog.year_month.set_date(hp.photoblog.current_user.date);
		}
		
		this.thumbsContainer = $('#photoblog_thumbs_container');
		this.thumbs = $('#photoblog_thumbs');
		this.thumbsList = $('dl', this.thumbsContainer);
		
		this.imageContainer = $('#photoblog_image');
		this.image = $('img', this.imageContainer);
		
		this.prev_month = $('#photoblog_prevmonth a');
		this.next_month = $('#photoblog_nextmonth a');
		this.prevnext_month = $('#photoblog_nextmonth a, #photoblog_prevmonth a');
		
		this.can_keyboard_nav = true;
		
		this.make_scroller();
		this.make_nextprev();
		this.make_ajax();
		this.make_keyboard();
		this.make_comments();
		this.make_global_click();
		
		// we have to select the latest month
		if ( ! this.load_from_hash() ) {
			if ( hp.photoblog.year_month.current_month_select[0].options.length > 1 ) {
				var latest;
				do {
					latest = hp.photoblog.year_month.get_next_date();
				} while (latest === false);
				hp.photoblog.year_month.select_month(latest.substr(4));
			}
		}
		
		var active_id = hp.photoblog.view.get_active();
		if ( active_id.length ) {
			active_id = hp.photoblog.image_id(active_id);
			this.set_prevnext(active_id);
		}
		
		this.make_month();
		
		if ( hp.photoblog.current_user.album_view ) {
			this.in_album_view();
		}
		
		// Create the image used when an image fails loading
		this.failthumb = $('<img />').attr('src', this.failthumburl);
		this.failimage = $('<img />').attr('src', this.failimageurl);
	},
	
	make_scroller: function() {
		var self = this;
		
		this.thumbs.append('<a id="photoblog_scroller_toggle" href="#">Visa allt</a>');
		$('#photoblog_scroller_toggle').click(function() {
			self.thumbs.toggleClass('hide_scroller');
			if ( self.thumbs.hasClass('hide_scroller') )
				hp.photoblog.view.reset_scroller();
			else
				hp.photoblog.view.centralize_active();
			return false;
		});
		
		// create scroller elements		
		this.thumbs.removeClass('hide_scroller');
		this.scroller = $('#photoblog_thumbs_scroller');
		this.scroller_outer = $('#photoblog_thumbs_scroller_outer');
		this.handle = this.scroller.find('#photoblog_thumbs_handle');
		
		// this has to be dynamically set because it's (probably) extremely slow
		this.thumbsContainer.sWidth = this.thumbsContainer.container_width();
		this.thumbsContainer.real_width = this.thumbsContainer.width();
		
		// Simple scroller
		var callback = function() {
			var percent = Math.max(self.handle.position().left, 0);
			percent = percent / (self.scroller.width() - self.handle.width());
			self.thumbsContainer.scrollLeft((self.thumbsContainer.cwidth - self.thumbsContainer.real_width) * percent);// - self.thumbsContainer.real_width);
		};
		
		this.scroller.scroller(callback);
		// \Simple scroller
		
		/*this.scroller.slider({
			//animate: true,
			slide: function(e, ui) {
				// calculate our own percentage, n / 100 is not precise enough
				var percent = Math.max(self.handle.position().left, 0);
				console.log(percent);
				percent = percent / self.scroller.width();
				self.thumbsContainer.scrollLeft(self.thumbsContainer.sWidth * percent);// - self.thumbsContainer.real_width);
				//self.thumbsContainer.scrollLeft(ui.value);
			}
		});*/
		
		this.scroller.find('.ui-slider-handle').attr('id', 'photoblog_thumbs_handle');
		
		this.set_scroller_width();
		this.centralize_active();
		
		this.scroller.pWidth = this.scroller.width() - this.handle.width();
	},
	
	make_nextprev: function() {
		var timer;
		var self = this;
		
		// create next prev elements
		var html = '<a href="#" id="photoblog_prev">F&ouml;reg&aring;ende</a><a href="#" id="photoblog_next">N&auml;sta</a>';
		this.imageContainer.append(html);
		
		this.prev = $('#photoblog_prev').hide();
		this.next = $('#photoblog_next').hide();
		
		this.prevnext = $('#photoblog_prev, #photoblog_next');
		
		var prev = this.prev;
		var next = this.next;
		
		this.imageContainer.mousemove(function(e) {
			e = e || window.event;
			var xPos = e.clientX - self.imageContainer.position().left;
			var half = self.imageContainer.width() / 2;
			
			// right (next)
			if ( xPos > half + 30 && next.css('display') == 'none' ) {
				next.css('display', 'block').animate({opacity: .6});
				prev.fadeOut();
			// left (prev)
			} else if ( xPos < half - 30 && prev.css('display') == 'none' ) {
				prev.css('display', 'block').animate({opacity: .6});
				next.fadeOut();
			} else if ( xPos > half - 30 && xPos < half + 30 ){
				next.fadeOut();
				prev.fadeOut();
			}
			clearTimeout(timer);
		});
		
		$(document).mousemove(function(e) {
			e = jQuery.event.fix(e || window.event);
			hp.photoblog.mousepos = {
				x: e.pageX,
				y: e.pageY
			};
		});
		
		this.imageContainer.mouseout(function(e) {
			timer = setTimeout(function() {
				var pos = hp.photoblog.mousepos;
				var el = self.imageContainer;
				var elPos = el.position();
				if (
					(pos.x < elPos.left || pos.x > elPos.left + el.width())
					&& (pos.y < elPos.top || pos.y > elPos.top + el.height())
				) {
					next.fadeOut();
					prev.fadeOut();
				}
			}, 300)
		});
	},
	
	make_ajax: function(thumbsOnly) {
		var self = this;
		
		var click_callback = function(e) {
			var t = $(this);
			document.location.hash = t.attr('href').split('#')[1];
			if ( t.attr('href').indexOf('#month-') != -1 ) {
				var date_month = hp.photoblog.get_month(t);
				hp.photoblog.year_month.load_date(date_month, {
					useLastIndex: (t.attr('id').indexOf('prev') != -1)
				});
			} else {
				var id = hp.photoblog.image_id(t);
				self.load_image(id);
			}
			return false;
		};
		
		var thumbs = $('#photoblog_thumbs a[href*=image-]');
		thumbs.click(click_callback);
		
		if ( ! thumbsOnly) {
			this.prevnext.click(click_callback);
			this.prevnext_month.click(click_callback);
		}
	},
	
	make_ajax_thumbs: function() {
		this.make_ajax(true);
	},
	
	make_keyboard: function() {
		var self = this;
		
		var table = {
			37: 'left',
			38: 'up',
			39: 'right',
			40: 'down',
			27: 'esc'
		};
		
		$(document).keydown(function(e) {
			e = e || window.event;
			var key = table[e.keyCode];
			
			if ( ! self.can_keyboard_nav ) return true;
			
			switch (key) {
				case 'left':
					self.prev.click();
					return false;
				break;
				case 'right':
					self.next.click();
					return false;
				break;
				case 'esc':
					$('.__htmlpopup').fadeOut(function() {
						$(this).remove();
					});
					$(document).click(); // this calls the .__htmlpopup byeFunc
				break;
			}
		});
	},
	
	make_comments: function() {
		var self = this;
		
		var textarea = $('.photoblog_comment_text textarea').focus(function() {
			if ( ! hp.login_checklogin() ) {
				tiny_reg_form_show();
			}
			
			if ( ! this.has_changed ) {
				this.orig_value = this.value;
				this.value = '';
				this.has_changed = true;
			}
			
			if ( this.value == this.orig_value ) {
				this.value = '';
			}
			
			self.can_keyboard_nav = false;
		}).blur(function() {
			if ( this.value == '' ) {
				this.value = this.orig_value;
				this.is_orig = true;
			}
			self.can_keyboard_nav = true;
		});
		
		this.comment_form = $('.photoblog_comment_text form').submit(function() {
			if ( $('.photoblog_comment_text textarea')[0].has_changed )
				self.post_comment(self.current_id, textarea.val());			
			return false;
		});
		
		$(document).click(function(event) {
			var t = $(event.target);
			if ( t.is('.photoblog_comment_reply') || t.parents('.photoblog_comment_reply').length ) {
				var t = $(this);
				var wrap = $('<div class="__htmlpopup"><form><h3>Svara</h3><textarea cols="50" rows="5"></textarea><br /><br /><input type="submit" value="Svara" /></form></div>')
					.css('position', 'absolute')
					.hide()
					.appendTo(document.body);
				var target = $(event.target);
				
				var tar_pos = target.position();
				wrap.css({
					left: $(window).width() / 2 - wrap.width() / 2,
					top: $(window).height() / 2 - wrap.height() / 2 + $(document).scrollTop()
				}).fadeIn();
				
				hp.photoblog.view.can_keyboard_nav = false;
				var byeFunc = function(e) {
					hp.photoblog.view.can_keyboard_nav = true;
					if ( e && $(e.target).is('.__htmlpopup') || $(e.target).parents('.__htmlpopup').length ) return null;
					wrap.fadeOut(function() {
						wrap.remove();
					});
					$(document).unbind('click', byeFunc);
					return false;
				};
				setTimeout(function() {
					$(document).bind('click', byeFunc);
				}, 20);
				
				wrap.children('form').submit(function() {
					hp.photoblog.view.save_reply(wrap.find('textarea').val(), target.attr('href').split('#')[1].replace('reply-', ''));
					byeFunc(false);
					return false;
				});
				
				wrap.find('textarea').focus();
				
				return false;
			} else if ( t.is('.photoblog_comment_remove') ) {
				$.get(t.attr('href'));
				t.parents('.photoblog_comment').slideUp(function() {
					$(this).remove();	
				});
				return false;
			}
		});
	},
	
	make_month: function(all) {
		var prev_date = hp.photoblog.year_month.get_prev_date();
		if ( prev_date === false ) {
			this.prev_month.hide();
		} else {
			this.prev_month.show();
			this.prev_month.attr('href', '#month-' + prev_date);
		}
		
		var next_date = hp.photoblog.year_month.get_next_date();
		if ( next_date === false ) {
			this.next_month.hide();
		} else {
			this.next_month.show();
			this.next_month.attr('href', '#month-' + next_date);
		}
	},
	
	make_global_click: function() {
		$(document).click(function(e) {
			e = e || window.event;
			var target = $(e.target);
			
			if ( target.is('a') && target.attr('href').indexOf('#') != -1 ) {
				hp.photoblog.view.load_hash(target.attr('href').split('#')[1], target);
			}
		});
	},
	
	load_hash: function(hash, target) {
		var keyword = hash.split('-')[0];
		var date = hash.split('-')[1];
		
		switch ( keyword ) {
			case 'image':
				//alert('load image: ' + date);
			break;
		
			case 'month':
				hp.photoblog.year_month.load_date(date);
			break;
		
			case 'day':
				var options = {'useDay': true};
				if ( target.parents('#ui_module_photoblog_calendar table') ) {
					$('.photoblog_calendar_active').removeClass('photoblog_calendar_active');
					target.parent().addClass('photoblog_calendar_active');
				}
				hp.photoblog.year_month.load_date(date, options);						
			break;
		}
	},
	
	// import future
	make_cache: function() {
		this.cache = $('<div style="display: none" id="photoblog_cache"></div>').appendTo(document.body);
	},
	
	add_to_cache: function(id, image, description) {
		var cacheElement = $('<div id="photoblog_cache_' + id + '"></div>').appendTo(this.cache);
		image.clone().appendTo(cacheElement);
		description.clone().appendTo(cacheElement);
	},
	
	in_cache: function(id) {
		var cache = $('#photoblog_cache_' + id);
		if ( ! cache.length ) return false;
		return {
			'image': $('img', cache),
			'description': $('div', cache)
		};
	},
	// end future
	
	set_active: function(active) {
		active = $(active);
		hp.photoblog.view.get_active().removeClass('photoblog_active');
		if ( ! active.length ) {
			return false;
		} else {
			active.addClass('photoblog_active');
			return true;
		}
	},
	
	set_scroller_width: function() {
		var outerWidth = this.thumbsContainer.width();
		var innerWidth = this.thumbsContainer.container_width();
		if ( innerWidth <= outerWidth ) {
			this.handle.css('width', '100%');
		} else {
			var w = Math.max(outerWidth / innerWidth * outerWidth, 40);
			this.handle.css('width', w);
		}
		this.thumbsContainer.cwidth = innerWidth;
	},
	
	reset_scroller: function() {
		this.scroller.slide_slider(0);
	},
	
	centralize_active: function() {
		var thumbsContainer = this.thumbsContainer;
		var active = hp.photoblog.view.get_active();
		if ( ! active.length ) {
			this.scroller.slide_slider(0);
			return;
		}
		var position = (active.position().left + (active.width() / 2)) / (thumbsContainer.sWidth - thumbsContainer.real_width / 2);
		position = position * this.scroller.width() - this.handle.width();
		this.scroller.slide_slider(position);
	},
	
	set_data: function(options) {
		var description = $('#photoblog_description');
		var text = $('#photoblog_description_text');
		
		$('#photoblog_description_report a').attr('href', '/hamsterpaj/abuse.php?report_type=photo&reference_id=' + options.id);

		text.html(options.description);
		if ( options.description.toLowerCase() == 'ingen beskrivning' || options.description == '' || options.description.toLowerCase() == 'namnlös' ) {
			text.css('display', 'none');
		} else {
			text.css('display', 'block');
		}
		
		hp.photoblog.edit.uptoDate(options);
	},
	
	set_image: function(id) {
		var src = hp.photoblog.make_name(id);
		var self = this;
		var img = $('<img />');
		var imgs = $('#photoblog_image p img');
		self.centralize_active();
		if ( imgs.length > 1 ) {
			var l = imgs.length - 1;
			imgs.filter(function(index) {
				return index < l;
			}).remove();
		}
		img.load(function() {
			self.remove_load();
			img.fadeInOnAnother(self.image, function() {
				//self.image.remove();
				self.image = img;
			});
		}).error(function() {
			self.remove_load();
			var clone = hp.photoblog.view.failimage.clone();
			self.image.replaceWith(clone);
			self.image = clone;
			//alert('Den här dumma bilden finns inte. Den är en dumdum sillsill!');
		}).attr('src', src);
	},
	
	reload: function() {
		this.load_image(this.current_id);
	},
	
	set_prevnext: function(id) {
		var cimg = 'a[href=#image-' + id + ']';
		var prevnext = this.get_prevnext_a(cimg);
		
		if ( ! prevnext ) {
			return false;
		}
		
		var only_one = $('#photoblog_thumbs dd').length <= 1 && hp.photoblog.current_user.album_view;
		
		var prev_image = prevnext[0];
		var next_image = prevnext[1];
		
		var url = prev_image.attr('href'),
			can_prev = true;
		if ( hp.photoblog.current_user.album_view && prevnext[2] ) {
			can_prev = false;
		} else if ( prevnext[2] ) {
			var prev_date = hp.photoblog.year_month.get_prev_date();
			if ( ! prev_date || hp.photoblog.album_view ) can_prev = false;
			url = '#month-' + prev_date;
		}
		if ( can_prev && ! only_one ) {
			this.prev.css('visibility', 'visible');
			this.prev.attr('href', url);
		} else {
			this.prev.css('visibility', 'hidden');
		}
		
		var url = next_image.attr('href'),
			can_next = true;
		if ( hp.photoblog.current_user.album_view && prevnext[3] ) {
			can_next = false;
		} else if ( prevnext[3] ) {
			var next_date = hp.photoblog.year_month.get_next_date();
			if ( ! next_date ) can_next = false;
			url = '#month-' + next_date;
		}
		if ( can_next && ! only_one ) {
			this.next.css('visibility', 'visible');
			this.next.attr('href', url);
		} else {
			this.next.css('visibility', 'hidden');
		}
		
		return true;
	},
	
	get_prevnext_a: function(from) {
		var current_image = $(from);
		
		if ( ! current_image.length ) return false;
		
		var cp = current_image.parent();
		
		// get previous image
		var prev_image = cp.prev();
		if ( prev_image[0].tagName == 'DT' ) prev_image = prev_image.prev();
		
		if ( prev_image.attr('id') == 'photoblog_prevmonth' ) prev_image = current_image;
		else prev_image = prev_image.children('a');
	
		// get next image
		var next_image = cp.next();
		if ( next_image[0].tagName == 'DT' ) next_image = next_image.next();
		
		if ( next_image.attr('id') == 'photoblog_nextmonth' ) prev_image = current_image;
		else next_image = next_image.children('a');
		
		// is the same image
		var is_first = cp.hasClass('first-image');
		var is_last = cp.hasClass('last-image');
		
		return [prev_image, next_image, is_first, is_last];
	},
	
	create_load: function() {
		var self = this;
		
		if ( self.loader ) {
			self.loader.css('top', self.image.height() / 2);
			self.loader.css('visibility', 'visible');
		} else {
			self.loader = $('<img id="photoblog_loading" />').attr('src', 'http://images.hamsterpaj.net/photoblog/loading.gif');
			self.loader.css({
				zIndex: 100,
				position: 'absolute',
				top: self.image.height() / 2,
				left: self.imageContainer.width() / 2
			});
			
			self.loader.appendTo(self.imageContainer);
		}
	},
	
	remove_load: function() {
		if ( this.loader ) {
			this.loader.css('visibility', 'hidden');
		}
	},
	
	load_image: function(id) {
		var self = this;
		this.current_id = id;
		var load_new_month = false;
		
		if ( false == this.set_active('a[href=#image-' + id + ']') ) {
			load_new_month = true;
		}
		
		var json_callback = function(data) {
			self.set_data(data.photo[0]);
			if ( load_new_month ) {
				var date = hp.photoblog.format_date(data.photo[0].date);
				self.load_month(date, function() {
					self.set_active('a[href=#image-' + id + ']');
					
					self.set_prevnext(id);
					self.centralize_active();
				});
				hp.photoblog.year_month.set_date(date);
			}
			$('#photoblog_comments_list').html($('<div />').html(data.comments).text());
		};
		
		this.set_image(id);
		this.set_prevnext(id);
		this.create_load();
		$.getJSON('/ajax_gateways/photoblog.json.php?action=photo_fetch&id=' + id, json_callback);
		//this.load_comment(id);
	},
	
	load_comment: function(id) {
		$('#photoblog_comments_list').load('/ajax_gateways/photoblog.json.php?action=comments_fetch&id='+ id + '&blog_id=' + hp.photoblog.current_user.id);
	},
	
	load_from_hash: function() {
		var hash = window.location.hash;
		if ( hash.indexOf('#image-') != -1 ) {
			var id = parseInt(hash.replace('#image-', ''), 10);
			if ( isNaN(id) ) {
				alert('Erronous image #ID');
				return;
			}
			this.load_image(id);
		} else if ( hash.indexOf('#month-') != -1 ) {
			var date = parseInt(hash.replace('#month-', ''), 10);
			if ( isNaN(date) ) {
				alert('Erronous image date');
				return;
			}
			hp.photoblog.year_month.load_date(date);
		} else if ( hash.indexOf('#day-') != -1 ) {
			var date = parseInt(hash.replace('#day-', ''), 10);
			if ( isNaN(date) ) {
				alert('Erronous image date');
				return;
			}
			hp.photoblog.year_month.load_date(date, {useDay: true});
		}
		return true;
	},
	
	load_month: function(month, callback) {
		var user_id = hp.photoblog.current_user.id;
		var self = this;
		var nextMonth = $('#photoblog_nextmonth');
		this.thumbsList.css('opacity', 0.4);
		$.getJSON('/ajax_gateways/photoblog.json.php?action=photo_fetch&id=' + user_id + '&month=' + month, function(data) {
			self.thumbsList.children().not('#photoblog_prevmonth, #photoblog_nextmonth').remove();
			var lastDay = null;
			var finished = 0, toload = data.length;
			$.each(data, function(i, item) {
				var date = item.date.split('-');
				if ( date[2] != lastDay ) {
					lastDay = date[2];
					var dt = $('<dt>' + parseInt(date[1], 10) + '/' + parseInt(date[2], 10) + '</dt>')
					nextMonth.before(dt);
				}
				var photoname = hp.photoblog.make_thumbname(item.id);
				
				var dd = $('<dd><a href="#image-' + item.id + '"></a></dd>');
				
				if ( i == 0 ) dd.addClass('first-image');
				if ( i == data.length - 1 ) dd.addClass('last-image');
				
				nextMonth.before(dd);
				var img = $('<img alt="" width="50" height="38" />');
				
				var onDone = function() {
					self.make_month();
					self.reset_scroller();
					self.thumbsContainer.sWidth = self.thumbsContainer.container_width();
					self.set_scroller_width();
					self.scroller.pWidth = self.scroller.width() - self.handle.width();
					if ( typeof callback == 'function' ) {
						callback(data);
					}
					self.thumbsList.css('opacity', 1);
				};
				
				var load_callback = function() {
					self.set_scroller_width();
					finished++;
					if ( finished == toload ) {
						onDone();
					}
				};
				img.load(load_callback).error(function() {
					$(this).replaceWith(hp.photoblog.view.failthumb);
					load_callback();
				});
				img.attr('src', photoname);
				img.appendTo(dd.children('a'))
			});
			self.make_ajax_thumbs();
		});
	},
	
	post_comment: function(image_id, text) {
		var self = this;
		$.post('/ajax_gateways/photoblog.json.php?action=comments_post&id=' + image_id, {'comment': text}, function(data) {
			self.load_comment(image_id);
			$('.photoblog_comment_text textarea').val('');	
		});
	},
	
	save_reply: function(reply, id) {
		$.post('/ajax_gateways/photoblog.json.php?action=comments_reply&id=' + id, {reply: reply}, function() {
			hp.photoblog.view.load_comment(hp.photoblog.view.current_id);
		});
	},
	
	get_active: function() {
		return $('#photoblog_thumbs .photoblog_active');
	},
	
	in_album_view: function() {
		hp.photoblog.current_user.album_view = true;
		this.prevnext_month.hide();
	},
	
	out_album_view: function() {
		hp.photoblog.current_user.album_view = false;
		this.prevnext_month.show();
	}

	// end .view
	},
	
	year_month: {
		years: [],
		
		init: function() {
			if ( ! $('#photoblog_select_year') ) return;
			
			var self = this;
			
			var year = $('#photoblog_select_year');
			var months = $('#photoblog_select_months').children('select');
			
			this.year = year;			
			months.each(function() {
				self.years[self.years.length] = $(this);
			});
			
			this.show(year[0].value);
			this.current_month = this.current_month_select.val();
			year.change(function() {
				self.show(this.value);
				self.load(self.current_month_select.val());
			});
			
			months.change(function() {
				self.load(this.value.toString());
			});
		},
		
		load: function(month) {
			this.current_month = month;
			hp.photoblog.view.load_month(this.current_year.toString() + month, function(data) {
				// load first day in month
				hp.photoblog.view.load_image(data[0].id);
			});
		},
		
		load_date: function(date, opts) {
			// When the date is changed we jump out of album view.
			hp.photoblog.view.out_album_view();
		
			opts = opts || {};
			options = {
				useLastIndex: false,
				useDay: false
			};
			for ( var key in opts ) if ( opts.hasOwnProperty(key) ) {
				options[key] = opts[key];
			}
			
			if ( options.useDay ) {
				date = date.toString();
				day = date.substr(6, 2);
				date = date.substr(0, 6);
				
				var findDay = function(obj, day) {
					for ( var key in obj ) if (obj.hasOwnProperty(key)) {
						if ( obj[key].date.substr(8, 2) == day ) {
							return obj[key];
						}
					}
					return false;
				};
			}
			
			this.set_date(date);
			this.current_month = date.toString().substr(4, 2);
			
			hp.photoblog.view.load_month(date, function(data) {
				if ( options.useDay ) {
					var image = findDay(data, day);
				} else {
					var index = (options.useLastIndex) ? data.length - 1 : 0;
					var image = data[index];
				}
				
				hp.photoblog.view.load_image(image.id);
			});
		},
		
		show: function(new_year) {
			this.current_year = new_year;
			this.year.children('[value=' + new_year + ']')[0].selected = true;
			for ( var i = 0, year; year = this.years[i]; i++ ) {
				if ( year.attr('id') == 'photoblog_select_month_' + new_year ) {
					year.css('display', 'inline');
					this.current_month_select = year;
				} else {
					year.css('display', 'none');
				}
			}
		},
		
		select_month: function(new_month) {
			this.current_month_select.children('[value=' + new_month + ']').attr('selected', true);
		},
		
		set_date: function(date) {
			date = date.toString();
			var year = date.substr(0, 4);
			var month = date.substr(4, 2);
			this.show(year);
			this.select_month(month);
			hp.photoblog.calendar.load(year, month);
		},
		
		get_x_date: function(type) {
			var delta = (type == 'next') ? -1 : 1;
			
			var month_index = this.current_month_select[0].selectedIndex;
			var year_index = this.year[0].selectedIndex;
			var years_available = this.year[0].options.length - 1;
			var months_available = this.current_month_select[0].options.length - 1;
			
			// we need to select a new year
			if ( (type == 'prev' && month_index == 0) || (type == 'next' && month_index == months_available) ) {
				// out of luck, mate
				if ( (type == 'prev' && year_index == years_available) || (type == 'next' && year_index == 0) ) {
					return false;
				} else {
					var new_year = this.years[year_index + delta];
					var new_month = new_year[0];
					var value_year = this.year[0].options[year_index + delta].value;
					var month_index = (delta === -1) ? 0 : new_month.options.length - 1;
					var value_month = new_month.options[month_index].value;
					return value_year + value_month;
				}
			} else {
				var value_year = this.current_year;
				var new_month = this.years[year_index][0];
				var value_month = new_month.options[month_index - delta].value;
				return value_year + value_month;
			}
			return false;
		},
		
		get_next_date: function() {
			return this.get_x_date('next');
		},
		
		get_prev_date: function() {
			return this.get_x_date('prev');
		}
	},
	
	calendar: {
		init: function() {
			var self = this;
		},
		
		load: function(year, month) {
			if ( $('#photoblog_calendar_month.date-' + year.toString() + month).length ) return;
			
			$('#photoblog_calendar_month')
				.parent()
				.load('/ajax_gateways/photoblog.json.php?action=calendar_render&user_id='
					+ hp.photoblog.current_user.id
					+ '&year='
					+ year
					+ '&month='
					+ month
				);
		}
	},
	
	sort: {
		init: function() {
			this.make_sortable();
			
			$('.photoblog_sort_save').click(function() {
				hp.photoblog.sort.save(function() {
					alert('Sparat! :)');
				});
				return false;
			});
		},
		
		make_sortable: function() {
			this.sorter = new Sorter('#photoblog_sort li', '#photoblog_sort > ul', {
				ignore: 'input'
			});
		},
		
		serialize: function() {
			var result = {};
			$('#photoblog_sort > ul').each(function() {
				// category_id
				var id = $(this).attr('id').replace('album_', '');
				result[id] = [];
				$(this).children('li').each(function(i) {
					result[id][i] = $(this).attr('id').replace('photo_', '');
				});
			});
			return result;
		},
		
		serialize_to_url: function() {
			var result = this.serialize();
			return this.to_query(result, 'data');
		},

		/*
			Based on Mootools Hash.toQueryString
		*/
			
		to_query: function(obj, base) {
			var queryString = [];
			for ( var key in obj ) if ( obj.hasOwnProperty(key) ) {
				var value = obj[key];
				if ( base ) key = base + '[' + key + ']';
				var result, type = typeof value;
				if ( type == 'string' || type == 'number' ) {
					result = key + '=' + encodeURIComponent(value);
				} else if ( type == 'object' ) {
					result = this.to_query(value, key);
				}
				queryString[queryString.length] = result;
			}
			return queryString.join('&');
		},
		
		save: function(callback) {
			// serialize and send to server
			$.post('/ajax_gateways/photoblog.json.php?action=sort_save', this.serialize_to_url(), callback);
		}
	},
	
	ordna: {
		init: function() {
			this.album_names();
			this.remove_albums();
			
			$('.photoblog_sort_remove').click(function() {
				var ids = '';
				$('#photoblog_sort input:checked').each(function() {
					ids += $(this).parents('li').attr('id').replace('photo_', '') + '|';
				}).parent().slideUp();
				$.get('/ajax_gateways/photoblog.json.php?action=photos_remove&photos=' + ids);
				return false;
			});
		},
		
		album_names: function() {
			$('.photoblog_album_edit h2 input').hide();
			$('.photoblog_album_edit h2 span').click(function() {
				var inputs = $(this).parent().children('input');
				if ( inputs.css('display') == 'none' ) inputs.fadeIn();
				else inputs.fadeOut();
				return false;
			});
			
			$('.photoblog_album_edit').submit(function() {
				var self = $(this);
				$('span', this).text($('input[name=name]', this).val());
				$.get(self.attr('action'), $('input', this));
				$('input', this).fadeOut();
				return false;
			});
		},
		
		remove_albums: function() {
			var self = this;
			$('.photoblog_album_remove').click(function() {
				var t = $(this);
			
				var form = t.parents('form');
				var ul = form.next();
				form.remove();
				ul.remove();
				
				$.get(t.attr('href'));
				
				return false;
			});
		}
	},
	
	edit: {
		init: function() {
			if ( $('#photoblog_edit').length == 0 ) return;
			this.create();
			this.created = true;
		},
		
		create: function() {
			var self = this;
			
			this.activation_link = $('#photoblog_edit a[href=#photoblog_edit_actions]');
			this.container = $('#photoblog_edit_do');
			
			this.form = $('#photoblog_edit form');
			this.current_id = $('input[name=edit_id]');
			this.current_desc = $('#photoblog_edit_description textarea');
			this.current_date = $('input[name=edit_date]');
			this.current_delete = $('input[name=edit_delete]');
			
			this.activation_link.click(function() {
				self.container.toggle();
				return false;
			});
			
			this.current_desc.focus(function() {
				hp.photoblog.view.can_keyboard_nav = false;
			}).blur(function() {
				hp.photoblog.view.can_keyboard_nav = true;
			});
			
			this.current_date.focus(function() {
				hp.photoblog.view.can_keyboard_nav = false;
			}).blur(function() {
				hp.photoblog.view.can_keyboard_nav = true;
			});
			
			this.form.submit(function() {
				// validate date
				var date = self.current_date.val();
				if ( ! /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(date) ) {
					alert('Datumet måste vara i formatet YYYY-MM-DD');
					return false;
				}
				
				self.save();
				return false;
			});
			
			this.current_delete.click(function() {
				if ( confirm('Är du säker på att du vill ta bort bilden?') ) {
					$.post(self.form.attr('action'), self.form.serialize() + '&edit_delete=1', function(data) {
						// remove thumb from thumblist
						$('a[href="#image-' + hp.photoblog.view.current_id + '"]').parent().remove();
						// this should redirect to the next image
						hp.photoblog.view.next.click();
					});
				}
				return false;
			});
		},
		
		uptoDate: function(options) {
			if ( ! this.created ) return false;
			this.current_id.val(options.id);
			this.current_desc.val(options.description);
			this.current_date.val(options.date);
		},
		
		save: function() {
			if ( ! this.created ) return false;
			var self = this;
			$.post(this.form.attr('action'), this.form.serialize() + '&edit_submit=1', function(data) {
				hp.photoblog.view.reload();
				self.container.hide();
			});
		}
	},
	
	simpleupload: {
		init: function() {
			var today = new Date();
			this.today = {y: today.getFullYear(), m: today.getMonth() + 1, d: today.getDate()};
			
			$('select.photoblog_upload_album').change(function() {
				if ( $(this).find(':selected').hasClass('photoblog_upload_new_album') ) {
					var name = prompt('Nya albumets namn');
					$('<option>' + name + '</option>').attr({'value': name, 'selected': 'selected'}).appendTo(this);
				}
			});
			this.initUploadify();
			this.initTools();
		},
		
		initUploadify: function() {
			var queue = $('#photoblog_queue'), submit = $('#photoblog_upload_submit').parent().hide();
			this.queue = queue;
			this.uploading = false;
			
			this.load = $('<img />').attr('src', "http://images.hamsterpaj.net/photoblog/loading.gif").addClass('photoblog_load');
			
			$('#images').uploadify({
				uploader: '/fotoblogg/uploadify.swf',
				script: '/fotoblogg/ladda_upp_enkel/ladda_upp/ajax',
				scriptData: {
					PHPSESSID: document.cookie.split("PHPSESSID=")[1].split("&")[0]
				},
				fileDataName: 'image',
				queueID: 'photoblog_queue',
				cancelImg: 'http://images.hamsterpaj.net/common_icons/warning_sign.png',
				multi: true,
				
				onSelect: function(event, id, file) {
					file.uniqueID = id;
					submit.show();
					hp.photoblog.simpleupload.createForm(queue, file);
					return false;
				},
				
				onCancel: function(event, id, file, data) {
					queue.find('#' + id).remove();
					if ( data.fileCount == 0 )
						submit.hide();
					if ( hp.photoblog.simpleupload.uploading )
						hp.photoblog.simpleupload.setSettings(hp.photoblog.simpleupload.queue.children(':first-child'));
				},
				
				onClearQueue: function(event, data) {
					queue.empty();
				},
				
				onProgress: function(event, id, file, data) {
					$('#' + id).find('.photoblog_progress').text(percentage + '% (' + speed + 'kB/s)');
				},
				
				onComplete: function(event, id, file, response, data) {
					$('#' + id).fadeOut(function() {
						$(this).remove();
					});
					hp.photoblog.simpleupload.setSettings($('#' + id).next());
					
					if ( data.fileCount == 0 ) {
						submit.hide();
						
						hp.photoblog.simpleupload.uploading = false;
						var c = $('<div />').html(response).insertBefore(hp.photoblog.simpleupload.queue);
						setTimeout(function() {
							c.slideUp();
						}, 5000);
					}
				}
 			});
			
			$('#photoblog_upload_submit').click(function() {
				hp.photoblog.simpleupload.upload();
			});
		},
		
		initTools: function() {
			var textarea = $('#photoblog_text_area');
			
			$('#photoblog_text_add').click(function() {
				$('.photoblog_photo_info textarea').val(textarea.val());
			});
		},
		
		createForm: function(parent, info) {
			var wrapper = $('<div id="' + info.uniqueID + '" class="photoblog_photo_info">'
				+ '<a href="#" title="Ta bort bild" class="photoblog_upload_cancel">X</a>'
				+ '<h3>' + info.name + ' (' + Math.round(info.size / 1024) + ' kb)</h3>'
				+ '<p><label>Beskrivning:<br />'
					+ '<textarea name="description" rows="2" cols="30"></textarea></label></p>'
				+ '<p><label>Hämta datumet automagiskt: <input type="checkbox" checked="checked" value="1" name="use_exif_date" /></label> <label><span class="photoblog_upload_or">Eller...<br /></span> År: <input type="text" maxlength="4" name="year" value="' + this.today.y + '" /></label> <label>Månad: <input type="text" name="month" maxlength="2" value="' + this.today.m + '" /></label> <label>Dag: <input type="text" name="day" maxlength="2" value="' + this.today.d + '" /></label></p>'
				+ '<p><label>Album: <select class="photoblog_upload_categories"></select></label></p>'
				+ '<div class="photoblog_progress" />'
			+ '</div>').appendTo(parent).attr('uploadify_name', info.name);
			
			wrapper.find('.photoblog_upload_cancel').click(function() {
				$('#images').uploadifyCancel(info.uniqueID);
				return false;
			});
			
			var select = wrapper.find('.photoblog_upload_categories');
			this.makeOptions(select);
		},
		
		upload: function() {
			hp.photoblog.simpleupload.uploading = true;
			
			hp.photoblog.simpleupload.setSettings(hp.photoblog.simpleupload.queue.children(':first-child'));
			$('#images').uploadifyUpload();
		},
		
		setSettings: function(element) {
			var id = element.attr('id'),
				filename = element.attr('uploadify_name');
			
			this.addLoad(element);
			
			var data = {
				description: element.find('textarea').val(),
				use_exif_data: element.find('input[name=use_exif_date]:checked').val(),
				year: element.find('input[name=year]').val(),
				month: element.find('input[name=month]').val(),
				day: element.find('input[name=day]').val(),
				album: element.find('select').val()
			};
			
			$('#images').uploadifySettings('scriptData', data);
		},
		
		makeOptions: function(select) {
			var options = '';
			for ( var i = 0, j = hp.photoblog.categories.length; i < j; i++ ) {
				options += '<option>' + hp.photoblog.categories[i] + '</option>';
			}
			select.html(options);
		},
		
		addLoad: function(element) {
			if ( element.find('.photoblog_load').length )
				return false;
			element.addClass('photoblog_loading');
			this.load.clone().appendTo(element);
			return true;
		}
	},
	
	admin: {
		init: function() {
			$('#photoblog_admin_all .info_toggle a').click(function() {
				$(this).parents('li').toggleClass('active').find('.photoblog_info').toggle();
				return false;
			});
			
			$('#photoblog_admin_all form').submit(function() {
				$.post($(this).attr('action'), $(this).serialize() + '&edit_submit=1', function(data) {
					alert(data);
				});
				return false;
			});
			
			$('#photoblog_admin_all a img').hover(function(e) {
				var self = $(this), link = self.parent();
				$('<img class="photoblog_full_view" />')
					.css({
						position: 'fixed',
						bottom: 0,
						right: 0
					}).attr('src', link.attr('href'))
				.appendTo(document.body);
			}, function() {
				$('.photoblog_full_view').remove();
			});
		}
	},
	
	format_date: function(date) {
		var pieces = date.split('-');
		return pieces[0] + pieces[1];
	},
	
	make_name: function(id) {
		return 'http://images.hamsterpaj.net/photos/full/' + Math.floor(parseInt(id, 10) / 5000) + '/' + id + '.jpg';
	},
	
	make_thumbname: function(id) {
		return 'http://images.hamsterpaj.net/photos/mini/' + Math.floor(parseInt(id, 10) / 5000) + '/' + id + '.jpg';
	},
	
	image_id: function(a) {
		return parseInt($(a).attr('href').split('#')[1].replace('image-', ''), 10);
	},
	
	get_month: function(a) {
		return parseInt($(a).attr('href').split('#')[1].replace('month-', ''), 10);
	}
};

jQuery.fn.extend({
	slide_slider: function(to) {
		var slider = $(this);
		var handle = slider.find('.ui-slider-handle');
		
		to = Math.min(to, slider.width() - handle.width());
		to = Math.max(to, 0);
		handle.css('left', to)
			.trigger('scrollerScroll');
	},
	
	scroller: function(callback) {
		var scroller = $(this);
		var handle = scroller.find('.ui-slider-handle');
		
		handle.bind('scrollerScroll', callback);
		
		var mousemovefunc = function(e) {
			/*var pos = {
				left: e.pageX - handle.dragOffset.x,
				top: e.pageY - handle.dragOffset.y
			};*/
			var deltaX = e.pageX - handle.dragOffset.x,
				deltaY = e.pageY - handle.dragOffset.y;
			
			var pos = handle.position();
			pos.left += deltaX;
			pos.top += deltaY;
		
			pos.right = pos.left + handle.width();
			pos.bottom = pos.top + handle.height();
		
			if ( handle.dragging ) {
				var scrollerPos = scroller.position();
				scrollerPos.right = scrollerPos.left + scroller.width();
				scrollerPos.bottom = scrollerPos.top + scroller.height();
				
				if ( pos.left > scrollerPos.left && pos.right < scrollerPos.right )
					handle.css('left', pos.left);
				else
					if ( pos.left <= scrollerPos.left ) {
						handle.css('left', 0);
					} else {
						handle.css('left', scrollerPos.right - handle.width());
					}
				
				handle.css('top', 0);
				handle.trigger('scrollerScroll');
			}
			handle.dragOffset = {x:e.pageX, y:e.pageY};
			return false;
		};
	
		var mouseupfunc = function() {
			$(document).unbind('mousemove', mousemovefunc).unbind('mouseup', mouseupfunc);
			return false;
		};
		
		handle.mousedown(function (e){
			handle.dragging = true;
			handle.dragOffset = {x: e.pageX, y: e.pageY};
			
			$(document).bind('mousemove', mousemovefunc).bind('mouseup', mouseupfunc);
			
			return false;
		});
		
		return $(this);
	},
	
	container_width: function() {
		var width1 = 0;
		$(this).find('dl > *').each(function() {
			width1 += $(this).outerWidth(true);
		});
		return width1;
	},
	
	fadeInOnAnother: function(theOld, callback) {
		// Effects are laggy for many people, so let's wait another 5 years before using them.
		theOld.replaceWith(this);
		if ( typeof callback == 'function' )
			callback.call(theOld);
		return this;
		
		theOld = $(theOld);
		
		var parent = theOld.parent(), self = $(this);
		parent.css({
			'position': 'relative'
		});
		
		var pos = theOld.position();
		theOld.css({
			'position': 'absolute',
			'top': 0,
			'left': pos.left,
			'top': pos.top
		});
		
		theOld.fadeOut();
		
		self.css({
			'display': 'none'
		}).appendTo(parent).fadeIn(function() {
			if ( typeof callback == 'function' )
				callback.call(self);
		});
	}
});

$(window).load(function() {
	if ( $('#photoblog_image').length ) {
		hp.photoblog.view.init();
	}
	
	if ( $('#photoblog_sort').length ) {
		hp.photoblog.sort.init();
		hp.photoblog.ordna.init();
	}
	
	if ( $('#photoblog_upload_simple').length ) {
		hp.photoblog.simpleupload.init();
	}
	
	if ( $('#photoblog_admin_all').length ) {
		hp.photoblog.admin.init();
	}
});