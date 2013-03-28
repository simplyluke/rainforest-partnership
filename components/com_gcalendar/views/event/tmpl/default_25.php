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

$dispatcher = JDispatcher::getInstance();
JPluginHelper::importPlugin('gcalendar');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base(). 'components/com_gcalendar/views/gcalendar/tmpl/gcalendar.css' );
$document->addStyleSheet(JURI::base().'components/com_gcalendar/views/event/tmpl/default_25.css');
$document->addScript(JURI::base().'components/com_gcalendar/views/event/tmpl/default_25.js');

$content = '{{#events}}
{{#calendarLink}}
<table class="gcalendar-table">
	<tr>
		<td valign="middle">
			<a href="{{calendarLink}}">
				<img id="prevBtn_img" height="16" border="0" width="16" alt="backlink" src="media/com_gcalendar/images/back.png"/>
			</a>
		</td>
		<td valign="middle">
			<a href="{{calendarLink}}">{{{calendarLinkLabel}}}</a>
		</td>
	</tr>
</table>
{{/calendarLink}}
<div id="gcal-event-container">
{{#pluginsBefore}} {{{.}}} {{/pluginsBefore}}
<h2>{{eventLabel}}</h2>
<table class="gcal-event-list">
<tr><td class="event_content_key">{{titleLabel}}: </td><td>{{title}}</td><td rowspan="6">{{#maplink}}<iframe width="250px" height="160px" frameborder="0" src="{{{maplink}}}"></iframe>{{/maplink}}</td></tr>
<tr><td class="event_content_key">{{calendarNameLabel}}: </td><td>{{calendarName}}</td></tr>
<tr><td class="event_content_key">{{dateLabel}}: </td><td>{{date}}</td></tr>
<tr><td class="event_content_key">{{locationLabel}}: </td><td><a href="http://maps.google.com/?q={{location}}" target="_blank">{{location}}</a></td></tr>
<tr>
	<td class="event_content_key">{{copyLabel}}: </td>
	<td>
		<a target="_blank" href="{{copyGoogleUrl}}">{{copyGoogleLabel}}</a>
	</td>
</tr>
<tr>
	<td class="event_content_key"></td>
	<td>
		<a target="_blank" href="{{copyOutlookUrl}}">{{copyOutlookLabel}}</a>
	</td>
</tr>
</table>
{{#description}}
<h2>{{descriptionLabel}}</h2>
{{{description}}}
{{/description}}
{{#pluginsAfter}} {{{.}}} {{/pluginsAfter}}
</div>
{{/events}}
{{^events}}
{{emptyText}}
{{/events}}
';

$plugins = array();
$plugins['pluginsBefore'] = array();
$plugins['pluginsAfter'] = array();
$dispatcher->trigger('onBeforeDisplayEvent', array($this->event,  &$content, &$plugins['pluginsBefore']));
$dispatcher->trigger('onAfterDisplayEvent', array($this->event,  &$content, &$plugins['pluginsAfter']));

echo GCalendarUtil::renderEvents(array($this->event), $content, JFactory::getApplication()->getParams(), $plugins);

if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendarap'.DS.'gcalendarap.php'))
	echo "<div style=\"text-align:center;margin-top:10px\" ><a href=\"http://g4j.digital-peak.com\">GCalendar</a></div>\n";
