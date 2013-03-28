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

JLoader::import('joomla.plugin.plugin');

JLoader::import('components.com_gcalendar.util', JPATH_ADMINISTRATOR);

class plgContentgcalendar_next extends JPlugin {

	public function onContentPrepare($context, &$article, &$params, $page = 0 ) {
		if (!$article->text) return;
		$calendarids = $this->params->get('calendarids');
		$results = GCalendarDBUtil::getCalendars($calendarids);
		if(empty($results)){
			return;
		}

		$maxEvents = $this->params->get('max_events', 10);
		$filter = $this->params->get('find', '');

		$values = array();
		foreach ($results as $result) {
			$events = GCalendarZendHelper::getEvents($result, null, null, $maxEvents, $filter);
			if(!empty($events)){
				foreach ($events as $event) {
					if(!($event instanceof GCalendar_Entry)){
						continue;
					}
					$values[] = $event;
				}
			}
		}

		usort($values, array("GCalendar_Entry", "compare"));
		$values = array_slice($values, 0, $maxEvents);

		$article->text = GCalendarUtil::renderEvents($values, $article->text, JComponentHelper::getParams('com_gcalendar'));
	}
}