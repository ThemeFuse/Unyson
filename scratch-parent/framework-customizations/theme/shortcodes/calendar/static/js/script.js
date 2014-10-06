(function($,_) {

	"use strict";
	var init = function($calendarWrapper){
		var ajaxParams      = $calendarWrapper.data('extends-ajax-params'),
			template = $calendarWrapper.data('template'),
			ajaxUrl         = $calendarWrapper.data('ajax-url'),
			templatePath    = $calendarWrapper.data('template-path'),
			firstDay        = $calendarWrapper.data('first-day'),
			hasEventSources = $calendarWrapper.get(0).hasAttribute('data-event-source'),
			eventSource     = [],
			gmtOffset       = new Date().getTimezoneOffset()*60, //signed int
			avaibleClasses  = ['event-warning', 'event-success', 'event-info', 'event-inverse', 'event-special', 'event-important'];

		//Set random event styling class. Also convert GMT datetime dates to browser timezone.
		var prepareEventSources = function(events) {
			events.forEach(function(element, index, events_array){

				events_array[index].start = ( parseInt(element.start) + gmtOffset ) * 1000;
				events_array[index].end   = ( parseInt(element.end) + gmtOffset ) * 1000;

				if ($.type(element.class) == 'undefined') {
					var eventClass = '';
					if ($.type(element.id) == 'number') {
						var key = element.id%(avaibleClasses.length);
						if (key >= avaibleClasses.length) {key = 0;}
						eventClass = avaibleClasses[key];
					}
					events_array[index].class = eventClass;
				}
			});

			return events;
		}


		if (hasEventSources) {
			eventSource = prepareEventSources( $calendarWrapper.data('event-source') );
			//save updated events list
			$calendarWrapper.data('event-source', eventSource );
		}

		var ajaxRequestFunction = function(start_date, end_date){
				var events = [],
					params = {from: (Math.floor(start_date.getTime()/1000) - 86400), to: (Math.floor(end_date.getTime()/1000) + 86400 ), action: 'shortcode_calendar_get_events', template: template };

				if (ajaxParams !== false) {
					params = _.extend(params, ajaxParams);
				}

				$.ajax({
					url:      ajaxUrl,
					data:     params,
					dataType: 'json',
					type:     'POST',
					async:    false
				}).done(function(json) {
					if(!json.success) {
						$.error(json.data);
					}
					if(json.data) {
						events = prepareEventSources(json.data);
					}
				});

				return events;
			};

		//available options https://github.com/Serhioromano/bootstrap-calendar
		var options = {
				language: fwShortcodeCalendarLocalize.locale,
				events_source: hasEventSources ? eventSource : ajaxRequestFunction,
				view: template,
				tmpl_path: templatePath,
				first_day: firstDay,
				//time_start:         '06:00',
				//time_end:           '22:00',
				time_split:          '30', //minutes
				tmpl_cache: false,
				day: (function(){
					var today = new Date(),
						month = (today.getMonth()+1) < 10 ? '0' + (today.getMonth()+1) : (today.getMonth()+1),
						date  = today.getDate() < 10 ? '0' + today.getDate() : today.getDate();
					return today.getFullYear() + '-' + month + '-' + date;
				})(), //allowed only YYYY-MM-DD format
				onAfterViewLoad: function(view) {
					$calendarWrapper.find('.page-header h3').text(this.getTitle());
					$calendarWrapper.find('.btn-group button').removeClass('active');

					//disable calendar events, which load specific view
					{
						$('*[data-cal-date]').off('click');
						$('.cal-cell').off('dblclick');
						$('.cal-month-box .cal-row-fluid').off('mouseenter mouseleave');
					}

					$calendarWrapper.find('.hidden-header').removeClass('hidden-header');

					if ( view === 'day' )
					{
						//set height for timeblocks container
						$calendarWrapper.find('.cal-day-panel-class').css('height', $calendarWrapper.find('.cal-day-panel-hour-class').css('height'));

						//calculate timeblock's width for daily calendar
						{
							var $dayEventsBlocks = $calendarWrapper.find('.cal-day-panel-class .day-event'),
								rowWidth = $calendarWrapper.find('.cal-day-hour-part').width();
							//set middle width max for 3 block
							$dayEventsBlocks.css('width', Math.floor( (rowWidth-(rowWidth/100)*20)/$dayEventsBlocks.length));

							var width_content = $calendarWrapper.parent().outerWidth();
							if(width_content < 701){
								$calendarWrapper.find('.day-event').css('max-width','100px');
							}
						}
					}

				}
			},

			calendar = $calendarWrapper.find('.fw-shortcode-calendar').calendar(options);
			$calendarWrapper.data('fw-shortcode-calendar.calendar', calendar);

		//navigation buttons
		{
			$calendarWrapper.find('.btn-group button[data-calendar-nav]').each(function() {
				var $this = $(this);
				$this.click(function() {
					calendar.navigate($this.data('calendar-nav'));
				});
			});
		}

	}

	$(document).ready(function(){
		$('.fw-shortcode-calendar-wrapper:not(fw-initialized)').each(function(){
			init($(this));
		}).addClass('fw-initialized');
	});

}(jQuery, _));