gcjQuery(document).ready(function(){
	gcjQuery('#gc_gcalendar_view_toggle_status').bind('click', function(e) {
		gcjQuery('#gc_gcalendar_view_list').slideToggle('slow', function(){
			var oldImage = gcjQuery('#gc_gcalendar_view_toggle_status').attr('src');
			var gcalImage = oldImage;
			var path = oldImage.substring(0, oldImage.lastIndexOf('/'));
			
			if (gcjQuery('#gc_gcalendar_view_list').is(":hidden"))
				gcalImage = path + '/down.png';
			else
				gcalImage = path + '/up.png';
			
			gcjQuery('#gc_gcalendar_view_toggle_status').attr('src', gcalImage);
		});
	});
});

function updateGCalendarFrame(calendar) {
	if (calendar.checked) {
		gcjQuery('#gcalendar_component').fullCalendar('addEventSource', calendar.value);
	} else {
		gcjQuery('#gcalendar_component').fullCalendar('removeEventSource', calendar.value);
	}
}