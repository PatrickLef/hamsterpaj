var hamstermas = {
    init: function() {
        var calendar = $('#christmas_calendar');
        var inner = $('#christmas_calendar_inner');
        var list = $('#christmas_tralist');
        
        var scroll = calendar.get(0).scrollWidth;
        
        hamstermas.calendar = calendar;
        hamstermas.calendar.scrollWidth = scroll - calendar.width();
        hamstermas.inner = inner;
        
        inner.width(scroll);
        list.width(scroll);
        
        // fix bar for IE
        if ( jQuery.browser.msie ) {
            list.css('margin-top', -100);
        }
        
        hamstermas.scroll();
    },
    
    scroll: function() {
        hamstermas.calendar.hover(hamstermas.on, hamstermas.out);
        hamstermas.calendar.mousemove(hamstermas.mousemove);
    },
    
    on: function() {
        if (hamstermas.interval) hamstermas.out();
        hamstermas.interval = setInterval(hamstermas.move, 15);
    },
    
    out: function() {
        clearInterval(hamstermas.interval);
    },
    
    mousemove: function(e) {
        e = e || window.event;
        var xpos = e.pageX - hamstermas.calendar.position().left;
        hamstermas.from_middle = xpos - hamstermas.calendar.width() / 2;
        hamstermas.to_move = (hamstermas.from_middle/12);
    },
    
    move: function() {
        var from_middle = hamstermas.from_middle;
        if (from_middle > 150 || from_middle < -150 ) {
            hamstermas.calendar.scrollLeft(hamstermas.calendar.scrollLeft() + hamstermas.to_move);
        }
    }
};

hamstermas.init();