<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

GCalendarUtil::loadjQuery();

if ($this->params->get('show_page_heading', 1)) { ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php }

$document = JFactory::getDocument();
$document->addScript(JURI::base().'components/com_gcalendar/libraries/fullcalendar/fullcalendar.min.js');
$document->addScript(JURI::base().'components/com_gcalendar/libraries/jquery/gcalendar/jquery.gcalendar-all.min.js');
$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/fullcalendar/fullcalendar.css');
$document->addScript(JURI::base().'components/com_gcalendar/libraries/jquery/ui/jquery-ui.custom.min.js');
$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/jquery/fancybox/jquery.fancybox-1.3.4.css');
$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/jquery/ext/tipTip.css');
$document->addStyleDeclaration("#ui-datepicker-div { z-index: 15 !important; }");
$document->addStyleSheet(JURI::base().'components/com_gcalendar/views/gcalendar/tmpl/gcalendar.css');

$params = $this->params;

$theme = $params->get('theme', '');
if(JRequest::getVar('theme', null) != null)
	$theme = JRequest::getWord('theme', null);
if(!empty($theme))
	$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/jquery/themes/'.$theme.'/jquery-ui.custom.css');
else
	$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/jquery/themes/aristo/jquery-ui.custom.css');

$calendarids = $this->calendarids;
$allCalendars = GCalendarDBUtil::getAllCalendars();

$calsSources = "		eventSources: [\n";
foreach($allCalendars as $calendar) {
	$cssClass = "gcal-event_gccal_".$calendar->id;
	$color = GCalendarUtil::getFadedColor($calendar->color);
	$document->addStyleDeclaration(".".$cssClass.",.fc-agenda ".$cssClass." .fc-event-time, .".$cssClass." a, .".$cssClass." div{background-color: ".$color." !important; border-color: #".$calendar->color."; color: white;}");
	if(empty($calendarids) || in_array($calendar->id, $calendarids)){
		$value = html_entity_decode(JRoute::_('index.php?option=com_gcalendar&view=jsonfeed&format=raw&gcid='.$calendar->id.'&Itemid='.JRequest::getInt('Itemid')));
		$calsSources .= "				'".$value."',\n";
	}
}
$calsSources = trim($calsSources, ",\n");
$calsSources .= "	],\n";

$defaultView = $params->get('defaultView', 'month');
if($params->get('defaultView', 'month') == 'week')
	$defaultView = 'agendaWeek';
else if($params->get('defaultView', 'month') == 'day')
	$defaultView = 'agendaDay';

$daysLong = "[";
$daysShort = "[";
$daysMin = "[";
$monthsLong = "[";
$monthsShort = "[";
for ($i=0; $i<7; $i++) {
	$daysLong .= "'".htmlspecialchars(GCalendarUtil::dayToString($i, false), ENT_QUOTES)."'";
	$daysShort .= "'".htmlspecialchars(GCalendarUtil::dayToString($i, true), ENT_QUOTES)."'";
	$daysMin .= "'".htmlspecialchars(mb_substr(GCalendarUtil::dayToString($i, true), 0, 2), ENT_QUOTES)."'";
	if($i < 6){
		$daysLong .= ",";
		$daysShort .= ",";
		$daysMin .= ",";
	}
}
for ($i=1; $i<=12; $i++) {
	$monthsLong .= "'".htmlspecialchars(GCalendarUtil::monthToString($i, false), ENT_QUOTES)."'";
	$monthsShort .= "'".htmlspecialchars(GCalendarUtil::monthToString($i, true), ENT_QUOTES)."'";
	if($i < 12){
		$monthsLong .= ",";
		$monthsShort .= ",";
	}
}
$daysLong .= "]";
$daysShort .= "]";
$daysMin .= "]";
$monthsLong .= "]";
$monthsShort .= "]";

$calCode = "// <![CDATA[ \n";
$calCode .= "gcjQuery(document).ready(function(){\n";
$calCode .= "	var today = new Date();\n";
$calCode .= "	var tmpYear = today.getFullYear();\n";
$calCode .= "	var tmpMonth = today.getMonth();\n";
$calCode .= "	var tmpDay = today.getDate();\n";
$calCode .= "	var tmpView = '".$defaultView."';\n";
$calCode .= "	var vars = window.location.hash.replace(/&amp;/gi, \"&\").split(\"&\");\n";
$calCode .= "	for ( var i = 0; i < vars.length; i++ ){\n";
$calCode .= "		if(vars[i].match(\"^#year\"))tmpYear = vars[i].substring(6);\n";
$calCode .= "		if(vars[i].match(\"^month\"))tmpMonth = vars[i].substring(6)-1;\n";
$calCode .= "		if(vars[i].match(\"^day\"))tmpDay = vars[i].substring(4);\n";
$calCode .= "		if(vars[i].match(\"^view\"))tmpView = vars[i].substring(5);\n";
$calCode .= "	}\n";
$calCode .= "	gcjQuery('#gcalendar_component').fullCalendar({\n";
$calCode .= "		header: {\n";
$calCode .= "			left: 'prev,next today',\n";
$calCode .= "			center: 'title',\n";
$calCode .= "			right: 'month,agendaWeek,agendaDay,list'\n";
$calCode .= "		},\n";
$calCode .= "		year: tmpYear,\n";
$calCode .= "		month: tmpMonth,\n";
$calCode .= "		date: tmpDay,\n";
$calCode .= "		defaultView: tmpView,\n";
$calCode .= "		editable: false, theme: ".(!empty($theme)?'true':'false').",\n";
$calCode .= "		weekends: ".($params->get('weekend', 1)==1?'true':'false').",\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "			month: '".Fullcalendar::convertFromPHPDate($params->get('titleformat_month', 'F Y'))."',\n";
$calCode .= "			week: \"".Fullcalendar::convertFromPHPDate($params->get('titleformat_week', "M j[ Y]{ '&#8212;'[ M] j o}"))."\",\n";
$calCode .= "			day: '".Fullcalendar::convertFromPHPDate($params->get('titleformat_day', 'l, M j, Y'))."',\n";
$calCode .= "			list: '".Fullcalendar::convertFromPHPDate($params->get('titleformat_list', 'M j Y'))."'},\n";
$calCode .= "		firstDay: ".$params->get('weekstart', 0).",\n";
$calCode .= "		firstHour: ".$params->get('first_hour', 6).",\n";
$calCode .= "		maxTime: ".$params->get('max_time', 24).",\n";
$calCode .= "		minTime: ".$params->get('min_time', 0).",\n";
$calCode .= "		weekNumbers: ".($params->get('weeknumbers', 1)==1?'true':'false').",\n";
$calCode .= "		monthNames: ".$monthsLong.",\n";
$calCode .= "		monthNamesShort: ".$monthsShort.",\n";
$calCode .= "		dayNames: ".$daysLong.",\n";
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
if($params->get('calendar_height', 0) > 0){
	$calCode .= "		contentHeight: ".$params->get('calendar_height', 0).",\n";
}
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "			month: '".Fullcalendar::convertFromPHPDate($params->get('timeformat_month', 'g:i a{ - g:i a}'))."',\n";
$calCode .= "			week: \"".Fullcalendar::convertFromPHPDate($params->get('timeformat_week', "g:i a{ - g:i a}"))."\",\n";
$calCode .= "			day: '".Fullcalendar::convertFromPHPDate($params->get('timeformat_day', 'g:i a{ - g:i a}'))."',\n";
$calCode .= "			list: '".Fullcalendar::convertFromPHPDate($params->get('timeformat_list', 'g:i a{ - g:i a}'))."'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		axisFormat: '".Fullcalendar::convertFromPHPDate($params->get('axisformat', 'g:i a'))."',\n";
$calCode .= "		allDayText: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_ALL_DAY'), ENT_QUOTES)."',\n";
$calCode .= "		buttonText: {\n";
$calCode .= "			prev:     '&nbsp;&#9668;&nbsp;',\n";  // left triangle
$calCode .= "			next:     '&nbsp;&#9658;&nbsp;',\n";  // right triangle
$calCode .= "			prevYear: '&nbsp;&lt;&lt;&nbsp;',\n"; // <<
$calCode .= "			nextYear: '&nbsp;&gt;&gt;&nbsp;',\n"; // >>
$calCode .= "			today:    '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TOOLBAR_TODAY'), ENT_QUOTES)."',\n";
$calCode .= "			month:    '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_VIEW_MONTH'), ENT_QUOTES)."',\n";
$calCode .= "			week:     '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_VIEW_WEEK'), ENT_QUOTES)."',\n";
$calCode .= "			day:      '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_VIEW_DAY'), ENT_QUOTES)."',\n";
$calCode .= "			list:      '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_VIEW_LIST'), ENT_QUOTES)."'\n";
$calCode .= "		},\n";
$calCode .= "		listSections: 'smart',\n";
$calCode .= "		listRange: 30,\n";
$calCode .= "		listPage: 30,\n";
$calCode .= "		listTexts: {
						until: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_UNTIL'), ENT_QUOTES)."',
						past: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_PAST'), ENT_QUOTES)."',
						today: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_TODAY'), ENT_QUOTES)."',
						tomorrow: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_TOMORROW'), ENT_QUOTES)."',
						thisWeek: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_THIS_WEEK'), ENT_QUOTES)."',
						nextWeek: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_NEXT_WEEK'), ENT_QUOTES)."',
						thisMonth: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_THIS_MONTH'), ENT_QUOTES)."',
						nextMonth: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_NEXT_MONTH'), ENT_QUOTES)."',
						future: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_FUTURE'), ENT_QUOTES)."',
						week: '".htmlspecialchars(JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TEXTS_WEEK'), ENT_QUOTES)."'
					},\n";
$calCode .= $calsSources;
$calCode .= "		viewDisplay: function(view) {\n";
$calCode .= "			var d = gcjQuery('#gcalendar_component').fullCalendar('getDate');\n";
$calCode .= "			var newHash = 'year='+d.getFullYear()+'&month='+(d.getMonth()+1)+'&day='+d.getDate()+'&view='+view.name;\n";
$calCode .= "			if(window.location.hash.replace(/&amp;/gi, \"&\") != newHash)\n";
$calCode .= "			window.location.hash = newHash;\n";
$calCode .= "		},\n";
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "			if (event.description){\n";
$calCode .= "				element.tipTip({content: event.description, defaultPosition: 'top'});}\n";
$calCode .= "		},\n";
if($params->get('show_event_as_popup', 1) == 1){
	$popupWidth = $params->get('popup_width', 650);
	$popupHeight = $params->get('popup_height', 500);
	$calCode .= "		eventRender: function(event, element, view) {\n";
	$calCode .= "		        element.attr('href', event.url + (event.url.indexOf('?') != -1 ? '&' : '?')+'tmpl=component');\n";
	$calCode .= "		        element.fancybox({\n";
	$calCode .= "		           width: ".$popupWidth.",\n";
	$calCode .= "		           height: ".$popupHeight.",\n";
	$calCode .= "		           autoScale : false,\n";
	$calCode .= "		           autoDimensions : false, \n";
	$calCode .= "		           transitionIn : 'elastic',\n";
	$calCode .= "		           transitionOut : 'elastic',\n";
	$calCode .= "		           speedIn : 600,\n";
	$calCode .= "		           speedOut : 200,\n";
	$calCode .= "		           type : 'iframe',\n";
	if (GCalendarUtil::isJoomlaVersion('2.5')) {
		$calCode .= "		           onCleanup : function(){if(gcjQuery('#fancybox-frame').contents().find('#system-message dt').length > 0){gcjQuery('#gcalendar_component').fullCalendar('refetchEvents');}}\n";
	}
	if (GCalendarUtil::isJoomlaVersion('3')) {
		$calCode .= "		           onCleanup : function(){if(gcjQuery('#fancybox-frame').contents().find('#system-message div').length > 0){gcjQuery('#gcalendar_component').fullCalendar('refetchEvents');}}\n";
	}
	$calCode .= "		        });\n";
	$calCode .= "			if (event.description){\n";
	$calCode .= "				element.tipTip({content: event.description, defaultPosition: 'top'});}\n";
	$calCode .= "		},\n";
	$calCode .= "		eventClick: function(event) {if (event.url) {return false;}},\n";
}
$calCode .= "		dayClick: function(date, allDay, jsEvent, view) {\n";
$calCode .= "			dayClickCustom(date, allDay, jsEvent, view);\n";
$calCode .= "		},\n";
$calCode .= "		eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {\n";
$calCode .= "			eventDropCustom(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view);\n";
$calCode .= "		},\n";
$calCode .= "		eventResize: function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {\n";
$calCode .= "			eventResizeCustom(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view);\n";
$calCode .= "		},\n";
$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				gcjQuery('#gcalendar_component_loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				gcjQuery('#gcalendar_component_loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$class = empty($theme)?'fc':'ui';
$calCode .= "	var custom_buttons = '<span class=\"fc-button fc-button-datepicker ".$class."-state-default ".$class."-corner-left ".$class."-corner-right\">'+\n";
$calCode .= "			'<span class=\"fc-button-inner\"><span class=\"fc-button-content\">'+\n";
$calCode .= "			'<input type=\"hidden\" id=\"gcalendar_component_date_picker\" value=\"\">'+\n";
$calCode .= "			'<a onClick=\"gcjQuery(\'#gcalendar_component_date_picker\').datepicker(\'show\');\"><span>".JText::_('COM_GCALENDAR_GCALENDAR_VIEW_SHOW_DATEPICKER')."'+\n";
$calCode .= "			'</span></a>'+\n";
$calCode .= "			'</span></span></span>';\n";
$calCode .= "		custom_buttons +='<span class=\"fc-button fc-button-print ".$class."-state-default ".$class."-corner-left ".$class."-corner-right\">'+\n";
$calCode .= "			'<span class=\"fc-button-inner\"><span class=\"fc-button-content\">'+\n";
$calCode .= "			'<a onClick=\"print_view();\"><span class=\"".$class."-icon ".$class."-icon-print\">".JText::_('COM_GCALENDAR_GCALENDAR_VIEW_TOOLBAR_PRINT')."'+\n";
$calCode .= "			'</span></a>'+\n";
$calCode .= "			'</span></span></span>';\n";
$calCode .= "	gcjQuery('span.fc-button-today').after(custom_buttons);\n";
$calCode .= "	if (gcjQuery('table').disableSelection) gcjQuery('div.fc-button-today').closest('table.fc-header').disableSelection();\n";
$calCode .= "	gcjQuery('div.fc-button-datepicker, div.fc-button-print')\n";
$calCode .= "		.mousedown( function(){ $(this).addClass('$class-state-down'); })\n";
$calCode .= "		.mouseup( function(){ $(this).removeClass('$class-state-down'); })\n";
$calCode .= "		.hover( function(){ $(this).addClass('$class-state-hover'); },\n";
$calCode .= "			function(){ $(this).removeClass('$class-state-hover').removeClass('$class-state-down'); }\n";
$calCode .= "		);\n";
$calCode .= "	gcjQuery(\"#gcalendar_component_date_picker\").datepicker({\n";
$calCode .= "		dateFormat: 'dd-mm-yy',\n";
$calCode .= "		changeYear: true, \n";
$calCode .= "		dayNames: ".$daysLong.",\n";
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
$calCode .= "		dayNamesMin: ".$daysMin.",\n";
$calCode .= "		monthNames: ".$monthsLong.",\n";
$calCode .= "		monthNamesShort: ".$monthsShort.",\n";
$calCode .= "		onSelect: function(dateText, inst) {\n";
$calCode .= "			var d = gcjQuery('#gcalendar_component_date_picker').datepicker('getDate');\n";
$calCode .= "			var view = gcjQuery('#gcalendar_component').fullCalendar('getView').name;\n";
$calCode .= "			gcjQuery('#gcalendar_component').fullCalendar('gotoDate', d);\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "	gcjQuery(window).bind( 'hashchange', function(){\n";
$calCode .= "		var today = new Date();\n";
$calCode .= "		var tmpYear = today.getFullYear();\n";
$calCode .= "		var tmpMonth = today.getMonth();\n";
$calCode .= "		var tmpDay = today.getDate();\n";
$calCode .= "		var tmpView = '".$defaultView."';\n";
$calCode .= "		var vars = window.location.hash.replace(/&amp;/gi, \"&\").split(\"&\");\n";
$calCode .= "		for ( var i = 0; i < vars.length; i++ ){\n";
$calCode .= "			if(vars[i].match(\"^#year\"))tmpYear = vars[i].substring(6);\n";
$calCode .= "			if(vars[i].match(\"^month\"))tmpMonth = vars[i].substring(6)-1;\n";
$calCode .= "			if(vars[i].match(\"^day\"))tmpDay = vars[i].substring(4);\n";
$calCode .= "			if(vars[i].match(\"^view\"))tmpView = vars[i].substring(5);\n";
$calCode .= "		}\n";
$calCode .= "		var date = new Date(tmpYear, tmpMonth, tmpDay,0,0,0);\n";
$calCode .= "		var d = gcjQuery('#gcalendar_component').fullCalendar('getDate');\n";
$calCode .= "		var view = gcjQuery('#gcalendar_component').fullCalendar('getView');\n";
$calCode .= "		if(date.getFullYear() != d.getFullYear() || date.getMonth() != d.getMonth() || date.getDate() != d.getDate())\n";
$calCode .= "			gcjQuery('#gcalendar_component').fullCalendar('gotoDate', date);\n";
$calCode .= "		if(view.name != tmpView)\n";
$calCode .= "			gcjQuery('#gcalendar_component').fullCalendar('changeView', tmpView);\n";
$calCode .= "	});\n";
$calCode .= "	gcjQuery('.ui-widget-overlay').live('click', function() { gcjQuery('#gcalendar-dialog').dialog('close'); });\n";
if($params->get('show_selection', 1) == 1) {
	$calCode .= "gcjQuery('#gc_gcalendar_view_list').hide();\n";
}
$calCode .= "});\n";
$calCode .= "var dayClickCustom = function(date, allDay, jsEvent, view){gcjQuery('#gcalendar_component').fullCalendar('gotoDate', date).fullCalendar('changeView', 'agendaDay');}\n";
$calCode .= "var eventDropCustom = function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view){};\n";
$calCode .= "var eventResizeCustom = function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view){};\n";
$calCode .= "// ]]>\n";
$document->addScriptDeclaration($calCode);

echo JHTML::_('content.prepare', $params->get('textbefore'));
if($params->get('show_selection', 1) == 1 || $params->get('show_selection', 1) == 3){
	$document->addScript(JURI::base(). 'components/com_gcalendar/views/gcalendar/tmpl/gcalendar.js' );
	$calendar_list = '<div id="gc_gcalendar_view_list"><table class="gcalendar-table">';
	foreach($allCalendars as $calendar) {
		$value = html_entity_decode(JRoute::_('index.php?option=com_gcalendar&view=jsonfeed&format=raw&gcid='.$calendar->id));
		$checked = '';
		if(empty($calendarids) || in_array($calendar->id, $calendarids)){
			$checked = 'checked="checked"';
		}

		$calendar_list .="<tr>\n";
		$calendar_list .="<td><input type=\"checkbox\" name=\"".$calendar->calendar_id."\" value=\"".$value."\" ".$checked." onclick=\"updateGCalendarFrame(this)\"/></td>\n";
		$calendar_list .="<td><font color=\"".GCalendarUtil::getFadedColor($calendar->color)."\">".$calendar->name."</font></td></tr>\n";
	}
	$calendar_list .="</table></div>\n";
	echo $calendar_list;
	echo "<div align=\"center\" style=\"text-align:center\">\n";
	$image = JURI::base().'media/com_gcalendar/images/down.png';
	if($params->get('show_selection', 1) == 3) $image = JURI::base().'media/com_gcalendar/images/up.png';
	echo "<img id=\"gc_gcalendar_view_toggle_status\" name=\"gc_gcalendar_view_toggle_status\" src=\"".$image."\" alt=\"".JText::_('COM_GCALENDAR_GCALENDAR_VIEW_CALENDAR_LIST')."\" title=\"".JText::_('COM_GCALENDAR_GCALENDAR_VIEW_CALENDAR_LIST')."\"/>\n";
	echo "</div>\n";
}

echo "<div id='gcalendar_component_loading' style=\"text-align: center;\"><img src=\"".JURI::base() . "media/com_gcalendar/images/ajax-loader.gif\"  alt=\"loader\" /></div>";
echo "<div id='gcalendar_component'></div><div id='gcalendar_component_popup' style=\"visibility:hidden\" ></div>";
echo JHTML::_('content.prepare', $params->get('textafter'));

$dispatcher = JDispatcher::getInstance();
JPluginHelper::importPlugin('gcalendar');
$dispatcher->trigger('onGCCalendarLoad', array('gcalendar_component'));

if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendarap'.DS.'gcalendarap.php'))
	echo "<div style=\"text-align:center;margin-top:10px\" ><a href=\"http://g4j.digital-peak.com\">GCalendar</a></div>\n";

//hide buttons and tune CSS for printable format
if (JRequest::getVar('tmpl') == 'component'){
	$document->addStyleSheet(JURI::base().'components/com_gcalendar/libraries/fullcalendar/fullcalendar.print.css', 'text/css', 'print');
	$document->addStyleDeclaration('.fc-header-left, .fc-header-right { display:none; }');
	$document->addStyleDeclaration('@page {size: A4 landscape;}');
} else {
	$document->addStyleDeclaration('@page {size: A4 landscape;}');
	$document->addScriptDeclaration('function print_view() {
					var loc=document.location.href.replace(/\?/,"\?tmpl=component\&");
					if (loc==document.location.href)
						loc=document.location.href.replace(/#/,"\?tmpl=component#");
					var printWindow = window.open(loc);
					printWindow.focus();
				}');
}